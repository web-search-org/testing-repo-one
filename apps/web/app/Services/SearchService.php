<?php

namespace App\Services;

use App\Models\WebPage;
use App\Models\WebImage;
use App\Models\SearchQuery;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class SearchService
{
    /**
     * Perform a search query with multi-signal ranking (BM25 + Field Weighting + PageRank + Proximity).
     */
    public function search(array $params): array
    {
        $category = $params['category'] ?? 'all';
        if ($category === 'images') {
            return $this->searchImages($params);
        }

        $startTime = microtime(true);
        $rawQuery = trim($params['q'] ?? '');
        $page = max(1, (int) ($params['page'] ?? 1));
        $perPage = min(50, max(5, (int) ($params['perPage'] ?? 10)));
        $language = $params['lang'] ?? null;

        if (empty($rawQuery)) {
            return [
                'query' => '',
                'totalHits' => 0,
                'page' => 1,
                'perPage' => $perPage,
                'totalPages' => 0,
                'executionTimeMs' => 0,
                'instantAnswer' => null,
                'results' => [],
                'suggestions' => [],
                'correctedQuery' => null,
            ];
        }

        // Parse query filters: site:, -exclude, exact quotes
        $parsed = $this->parseQueryFilters($rawQuery);
        $cleanQuery = $parsed['cleanQuery'];
        $siteFilter = $parsed['site'];
        $excludeTerms = $parsed['exclude'];

        // Check for instant answer (calculator, code snippet, etc.)
        $instantAnswer = $this->detectInstantAnswer($cleanQuery);

        $queryBuilder = WebPage::query()->where('is_indexed', true);

        if ($siteFilter) {
            $queryBuilder->where('domain', 'like', "%{$siteFilter}%");
        }

        if ($category && $category !== 'all') {
            $queryBuilder->where('category', $category);
        }

        if ($language) {
            $queryBuilder->where('language', $language);
        }

        // Multi-term tokenization and stemming
        $terms = $this->tokenizeAndStem($cleanQuery);

        if (!empty($terms)) {
            $queryBuilder->where(function ($q) use ($terms, $cleanQuery) {
                // Exact phrase match candidate
                $q->where('title', 'like', "%{$cleanQuery}%")
                  ->orWhere('description', 'like', "%{$cleanQuery}%")
                  ->orWhere('domain', 'like', "%{$cleanQuery}%")
                  ->orWhere('body_text', 'like', "%{$cleanQuery}%");

                // Individual token candidates
                foreach ($terms as $term) {
                    $q->orWhere('title', 'like', "%{$term}%")
                      ->orWhere('description', 'like', "%{$term}%")
                      ->orWhere('domain', 'like', "%{$term}%")
                      ->orWhere('body_text', 'like', "%{$term}%");
                }
            });
        }

        // Handle excluded terms (-word)
        foreach ($excludeTerms as $ex) {
            $queryBuilder->where(function ($q) use ($ex) {
                $q->where('title', 'not like', "%{$ex}%")
                  ->where('body_text', 'not like', "%{$ex}%");
            });
        }

        // Fetch candidate documents pool (up to 300 candidates for scoring)
        $candidates = $queryBuilder->limit(300)->get();

        // Calculate multi-signal relevance score for each candidate document
        $scoredResults = $candidates->map(function (WebPage $doc) use ($terms, $cleanQuery) {
            $score = $this->calculateRelevanceScore($doc, $terms, $cleanQuery);
            return [
                'doc' => $doc,
                'score' => $score,
            ];
        });

        // Sort descending by calculated relevance score
        $sortedResults = $scoredResults->sortByDesc('score')->values();

        $totalHits = $sortedResults->count();
        $totalPages = (int) ceil($totalHits / $perPage);
        $offset = ($page - 1) * $perPage;

        $pagedItems = $sortedResults->slice($offset, $perPage);

        // Format and generate dense context snippets
        $results = $pagedItems->map(function ($item) use ($terms) {
            /** @var WebPage $doc */
            $doc = $item['doc'];
            $score = $item['score'];

            $rawSnippetSource = $doc->description ?: $doc->body_text ?: '';
            $snippet = $this->generateSnippet($rawSnippetSource, $terms);
            $highlighted = $this->highlightTerms($snippet, $terms);

            return [
                'id' => $doc->id,
                'url' => $doc->url,
                'domain' => $doc->domain,
                'title' => $doc->title ?: $doc->domain,
                'snippet' => $snippet,
                'highlightedSnippet' => $highlighted,
                'favicon' => "/favicon/{$doc->domain}",
                'rankScore' => round($score, 2),
                'category' => $doc->category,
                'publishedAt' => $doc->created_at?->toIso8601String(),
                'indexedAt' => $doc->crawled_at?->toIso8601String() ?: $doc->updated_at?->toIso8601String(),
            ];
        })->values()->toArray();

        $executionTimeMs = round((microtime(true) - $startTime) * 1000, 2);

        // Record telemetry (non-blocking)
        try {
            SearchQuery::create([
                'query' => Str::limit($rawQuery, 255),
                'category' => $category,
                'results_count' => $totalHits,
                'execution_time_ms' => $executionTimeMs,
            ]);
        } catch (\Exception) {
            // Ignore telemetry write errors
        }

        // Suggestions for related searches
        $suggestions = $this->getRelatedSuggestions($cleanQuery);

        return [
            'query' => $rawQuery,
            'totalHits' => $totalHits,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => $totalPages,
            'executionTimeMs' => $executionTimeMs,
            'instantAnswer' => $instantAnswer,
            'results' => $results,
            'suggestions' => $suggestions,
            'correctedQuery' => null,
        ];
    }

    /**
     * Compute multi-signal relevance score:
     * - Exact Title Match: 120.0 pts
     * - Title Token Frequency & Position: 30.0 pts each
     * - Domain Exact & Prefix Match: 60.0 pts
     * - URL Path / Slug Match: 25.0 pts
     * - Heading (H1, H2, H3) Matches: 18.0 pts each
     * - Description Match: 12.0 pts
     * - Body Term Frequency with length saturation (BM25 style): 6.0 pts
     * - All-Terms Coverage Conjunction Bonus: +40.0 pts
     * - Inbound Backlink & PageRank Multiplier: (1.0 + (page_rank * 0.25) + (in_links * 0.1))
     */
    protected function calculateRelevanceScore(WebPage $doc, array $terms, string $cleanQuery): float
    {
        $score = 0.0;
        $titleLower = strtolower($doc->title ?? '');
        $domainLower = strtolower($doc->domain ?? '');
        $urlLower = strtolower($doc->url ?? '');
        $descLower = strtolower($doc->description ?? '');
        $bodyLower = strtolower($doc->body_text ?? '');
        $cleanQueryLower = strtolower($cleanQuery);

        // 1. Exact Phrase Matches
        if ($titleLower === $cleanQueryLower) {
            $score += 150.0; // Perfect title match
        } elseif (str_starts_with($titleLower, $cleanQueryLower)) {
            $score += 90.0; // Starts with exact query
        } elseif (str_contains($titleLower, $cleanQueryLower)) {
            $score += 60.0; // Contains exact query phrase in title
        }

        if ($domainLower === $cleanQueryLower || str_starts_with($domainLower, $cleanQueryLower . '.')) {
            $score += 100.0; // Brand / Domain exact match
        } elseif (str_contains($domainLower, $cleanQueryLower)) {
            $score += 50.0;
        }

        if (str_contains($urlLower, $cleanQueryLower)) {
            $score += 30.0; // URL path match
        }

        if (str_contains($descLower, $cleanQueryLower)) {
            $score += 25.0; // Description exact match
        }

        // 2. Multi-Token Scoring
        $matchedTermsCount = 0;
        $headings = is_array($doc->headings) ? implode(' ', $doc->headings) : ($doc->headings ?? '');
        $headingsLower = strtolower($headings);

        $keywords = is_array($doc->keywords) ? implode(' ', $doc->keywords) : ($doc->keywords ?? '');
        $keywordsLower = strtolower($keywords);

        foreach ($terms as $term) {
            if (strlen($term) < 2) continue;
            $termFound = false;

            // Title matches (weight 30x)
            $titleCount = substr_count($titleLower, $term);
            if ($titleCount > 0) {
                $score += min(3, $titleCount) * 30.0;
                $termFound = true;
            }

            // Domain / URL matches (weight 25x)
            if (str_contains($domainLower, $term)) {
                $score += 25.0;
                $termFound = true;
            } elseif (str_contains($urlLower, $term)) {
                $score += 15.0;
                $termFound = true;
            }

            // Headings matches (weight 18x)
            $headingCount = substr_count($headingsLower, $term);
            if ($headingCount > 0) {
                $score += min(3, $headingCount) * 18.0;
                $termFound = true;
            }

            // Keywords matches (weight 15x)
            if (str_contains($keywordsLower, $term)) {
                $score += 15.0;
                $termFound = true;
            }

            // Description matches (weight 12x)
            $descCount = substr_count($descLower, $term);
            if ($descCount > 0) {
                $score += min(3, $descCount) * 12.0;
                $termFound = true;
            }

            // Body matches with BM25 term frequency saturation: tf / (tf + 1.5)
            $bodyCount = substr_count($bodyLower, $term);
            if ($bodyCount > 0) {
                $tfSaturated = $bodyCount / ($bodyCount + 1.5);
                $score += $tfSaturated * 15.0;
                $termFound = true;
            }

            if ($termFound) {
                $matchedTermsCount++;
            }
        }

        // 3. Conjunction & Coverage Multiplier
        $totalTerms = max(1, count($terms));
        $coverageRatio = $matchedTermsCount / $totalTerms;
        if ($coverageRatio >= 1.0) {
            $score += 50.0; // Contains 100% of query terms
            $score *= 1.4;
        } elseif ($coverageRatio >= 0.5) {
            $score *= 1.15;
        }

        // 4. PageRank & Inbound Backlink Multiplier
        $pageRank = max(1.0, (float) ($doc->page_rank ?? 1.0));
        $inLinks = max(0, (int) ($doc->in_links_count ?? 0));
        $authorityMultiplier = 1.0 + (log10($pageRank + 1.0) * 0.3) + (min(20, $inLinks) * 0.05);

        $finalScore = $score * $authorityMultiplier;

        // 5. Homepage / Root URL Boost for navigational queries
        if ($urlLower === "https://{$domainLower}/" || $urlLower === "http://{$domainLower}/") {
            $finalScore *= 1.25;
        }

        return round($finalScore, 3);
    }

    /**
     * Tokenize query into clean lowercase terms.
     */
    protected function tokenizeAndStem(string $query): array
    {
        $cleaned = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', strtolower($query));
        $words = preg_split('/\s+/', trim($cleaned));
        $stopWords = ['the', 'is', 'at', 'which', 'on', 'a', 'an', 'and', 'or', 'in', 'for', 'to', 'of'];
        
        $tokens = [];
        foreach ($words as $w) {
            $w = trim($w);
            if (strlen($w) >= 2 && !in_array($w, $stopWords)) {
                $tokens[] = $w;
                // Add simple singular forms for plurals
                if (str_ends_with($w, 's') && strlen($w) > 3) {
                    $tokens[] = rtrim($w, 's');
                }
            }
        }

        return array_unique($tokens);
    }

    /**
     * Parse site: and -exclude operators.
     */
    protected function parseQueryFilters(string $query): array
    {
        $site = null;
        $exclude = [];
        $words = [];

        $parts = preg_split('/\s+/', $query);
        foreach ($parts as $part) {
            if (str_starts_with($part, 'site:')) {
                $site = substr($part, 5);
            } elseif (str_starts_with($part, '-') && strlen($part) > 1) {
                $exclude[] = substr($part, 1);
            } else {
                $words[] = $part;
            }
        }

        return [
            'cleanQuery' => implode(' ', $words),
            'site' => $site,
            'exclude' => $exclude,
        ];
    }

    /**
     * Compute instant answers (Math calculations, converters, developer cheat-sheets).
     */
    protected function detectInstantAnswer(string $query): ?array
    {
        $q = trim(strtolower($query));

        // Math calculation: e.g. 5 * 12, (20 + 4) / 2, 2^10
        if (preg_match('/^[\d\s\+\-\*\/\^\(\)\.\%]+$/', $q) && preg_match('/[\+\-\*\/\^\%]/', $q)) {
            try {
                $expr = str_replace('^', '**', $q);
                if (preg_match('/^[\d\s\+\-\*\/\.\(\)]+$/', $expr)) {
                    $result = eval("return {$expr};");
                    if (is_numeric($result)) {
                        return [
                            'type' => 'calculator',
                            'title' => 'Calculator Result',
                            'subtitle' => $query,
                            'content' => (string) $result,
                        ];
                    }
                }
            } catch (\Throwable) {
                // Ignore eval error
            }
        }

        // IP / User Agent answer
        if (in_array($q, ['my ip', 'what is my ip', 'ip address', 'whats my ip'])) {
            return [
                'type' => 'definition',
                'title' => 'Your Public IP',
                'subtitle' => 'Network Diagnostic',
                'content' => request()->ip() ?: '127.0.0.1',
            ];
        }

        // Time / Epoch answer
        if (in_array($q, ['time', 'current time', 'what time is it', 'utc now', 'epoch', 'timestamp'])) {
            return [
                'type' => 'definition',
                'title' => 'Current UTC Time',
                'subtitle' => 'System Clock',
                'content' => now('UTC')->format('Y-m-d H:i:s T') . ' (Epoch: ' . time() . ')',
            ];
        }

        return null;
    }

    /**
     * Generate dynamic context snippet around the highest density keyword cluster.
     */
    protected function generateSnippet(string $text, array $terms, int $snippetLength = 220): string
    {
        if (empty($text)) {
            return '';
        }

        $text = strip_tags($text);
        $lowerText = strtolower($text);

        if (empty($terms)) {
            return Str::limit($text, $snippetLength);
        }

        // Find the position of each term and pick the best dense cluster
        $bestPos = 0;
        $maxTermsInWindow = 0;

        foreach ($terms as $term) {
            if (empty($term)) continue;
            $pos = strpos($lowerText, $term);
            if ($pos !== false) {
                // Count how many terms appear within a 200 char window starting near $pos
                $windowStart = max(0, $pos - 30);
                $windowText = substr($lowerText, $windowStart, $snippetLength);
                $termsCount = 0;
                foreach ($terms as $otherTerm) {
                    if (str_contains($windowText, $otherTerm)) {
                        $termsCount++;
                    }
                }

                if ($termsCount > $maxTermsInWindow) {
                    $maxTermsInWindow = $termsCount;
                    $bestPos = $windowStart;
                }
            }
        }

        if ($maxTermsInWindow === 0) {
            return Str::limit($text, $snippetLength);
        }

        $snippet = substr($text, $bestPos, $snippetLength);
        return ($bestPos > 0 ? '... ' : '') . trim($snippet) . (strlen($text) > ($bestPos + $snippetLength) ? ' ...' : '');
    }

    /**
     * Highlight query terms in snippet with clean monochrome <mark> tags without nesting.
     */
    protected function highlightTerms(string $snippet, array $terms): string
    {
        if (empty($snippet) || empty($terms)) {
            return e($snippet);
        }

        $escaped = e($snippet);
        
        // Sort by longest term first so longer terms match before substrings
        $validTerms = array_filter($terms, fn($t) => strlen(trim($t)) >= 2);
        usort($validTerms, fn($a, $b) => strlen($b) <=> strlen($a));

        if (empty($validTerms)) {
            return $escaped;
        }

        $quotedTerms = array_map(fn($t) => preg_quote($t, '/'), $validTerms);
        $pattern = '/\b(' . implode('|', $quotedTerms) . ')/i';

        return preg_replace(
            $pattern, 
            '<mark class="bg-zinc-200 dark:bg-zinc-800 text-black dark:text-white font-bold px-0.5 rounded">$1</mark>', 
            $escaped
        );
    }

    /**
     * Autocomplete suggestions for search input.
     */
    public function suggest(string $prefix): array
    {
        $prefix = trim(strtolower($prefix));
        if (strlen($prefix) < 2) {
            return [];
        }

        return SearchQuery::query()
            ->where('query', 'like', "{$prefix}%")
            ->select('query')
            ->distinct()
            ->orderByDesc('id')
            ->limit(8)
            ->pluck('query')
            ->toArray();
    }

    protected function getRelatedSuggestions(string $query): array
    {
        return SearchQuery::query()
            ->where('query', '!=', $query)
            ->where('query', 'like', "%{$query}%")
            ->select('query')
            ->distinct()
            ->limit(5)
            ->pluck('query')
            ->toArray();
    }

    /**
     * Retrieve a random word from the words table.
     */
    public function getRandomWord(): string
    {
        $word = \App\Models\Word::inRandomOrder()->value('word');
        if ($word) {
            return $word;
        }

        $fallbackWords = ['opensource', 'technology', 'developer', 'software', 'search', 'privacy', 'laravel', 'framework', 'database', 'crawler', 'indexer', 'algorithm', 'svelte'];
        return $fallbackWords[array_rand($fallbackWords)];
    }

    /**
     * Dedicated Image Search with visual metadata and downscaled thumbnails.
     */
    public function searchImages(array $params): array
    {
        $startTime = microtime(true);
        $rawQuery = trim($params['q'] ?? '');
        $page = max(1, (int) ($params['page'] ?? 1));
        $perPage = min(60, max(6, (int) ($params['perPage'] ?? 24)));

        if (empty($rawQuery)) {
            return [
                'query' => '',
                'totalHits' => 0,
                'page' => 1,
                'perPage' => $perPage,
                'totalPages' => 0,
                'executionTimeMs' => 0,
                'instantAnswer' => null,
                'results' => [],
                'imageResults' => [],
                'suggestions' => [],
                'correctedQuery' => null,
            ];
        }

        $parsed = $this->parseQueryFilters($rawQuery);
        $cleanQuery = $parsed['cleanQuery'];
        $siteFilter = $parsed['site'];
        $terms = $this->tokenizeAndStem($cleanQuery);

        $queryBuilder = WebImage::query();

        if ($siteFilter) {
            $queryBuilder->where('domain', 'like', "%{$siteFilter}%");
        }

        if (!empty($terms)) {
            $queryBuilder->where(function ($q) use ($terms, $cleanQuery) {
                $q->where('alt_text', 'like', "%{$cleanQuery}%")
                  ->orWhere('title', 'like', "%{$cleanQuery}%")
                  ->orWhere('domain', 'like', "%{$cleanQuery}%")
                  ->orWhere('page_url', 'like', "%{$cleanQuery}%")
                  ->orWhereHas('webPage', function ($sub) use ($cleanQuery) {
                      $sub->where('title', 'like', "%{$cleanQuery}%")
                          ->orWhere('body_text', 'like', "%{$cleanQuery}%");
                  });

                foreach ($terms as $term) {
                    $q->orWhere('alt_text', 'like', "%{$term}%")
                      ->orWhere('title', 'like', "%{$term}%")
                      ->orWhere('domain', 'like', "%{$term}%")
                      ->orWhereHas('webPage', function ($sub) use ($term) {
                          $sub->where('title', 'like', "%{$term}%")
                              ->orWhere('keywords', 'like', "%{$term}%");
                      });
                }
            });
        }

        $totalHits = $queryBuilder->count();
        $totalPages = (int) ceil($totalHits / $perPage);
        $offset = ($page - 1) * $perPage;

        $items = $queryBuilder->orderByDesc('created_at')
            ->skip($offset)
            ->take($perPage)
            ->get();

        $imageResults = $items->map(function (WebImage $img) {
            return [
                'id' => $img->id,
                'imageUrl' => $img->local_path ?: $img->image_url,
                'originalUrl' => $img->image_url,
                'thumbnailUrl' => $img->thumbnail_path ?: $img->local_path ?: $img->image_url,
                'pageUrl' => $img->page_url,
                'domain' => $img->domain,
                'alt' => $img->alt_text ?: $img->title ?: $img->domain,
                'title' => $img->title ?: $img->alt_text ?: $img->domain,
                'width' => $img->width,
                'height' => $img->height,
                'aspectRatio' => $img->aspect_ratio,
                'mimeType' => $img->mime_type,
            ];
        })->toArray();

        $executionTimeMs = round((microtime(true) - $startTime) * 1000, 2);

        return [
            'query' => $rawQuery,
            'totalHits' => $totalHits,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => $totalPages,
            'executionTimeMs' => $executionTimeMs,
            'instantAnswer' => null,
            'results' => [],
            'imageResults' => $imageResults,
            'suggestions' => $this->getRelatedSuggestions($cleanQuery),
            'correctedQuery' => null,
        ];
    }
}
