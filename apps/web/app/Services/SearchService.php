<?php

namespace App\Services;

use App\Models\WebPage;
use App\Models\SearchQuery;
use Illuminate\Support\Str;
use Illuminate\Pagination\LengthAwarePaginator;

class SearchService
{
    /**
     * Perform a search query with ranking, filtering, and snippet generation.
     */
    public function search(array $params): array
    {
        $startTime = microtime(true);
        $rawQuery = trim($params['q'] ?? '');
        $category = $params['category'] ?? 'all';
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

        // Multi-term search logic
        $terms = array_filter(explode(' ', strtolower($cleanQuery)));

        if (!empty($terms)) {
            $queryBuilder->where(function ($q) use ($terms, $cleanQuery) {
                // Exact match gets highest priority
                $q->where('title', 'like', "%{$cleanQuery}%")
                  ->orWhere('description', 'like', "%{$cleanQuery}%")
                  ->orWhere('body_text', 'like', "%{$cleanQuery}%");

                // Individual terms
                foreach ($terms as $term) {
                    $q->orWhere('title', 'like', "%{$term}%")
                      ->orWhere('description', 'like', "%{$term}%")
                      ->orWhere('body_text', 'like', "%{$term}%");
                }
            });
        }

        foreach ($excludeTerms as $ex) {
            $queryBuilder->where(function ($q) use ($ex) {
                $q->where('title', 'not like', "%{$ex}%")
                  ->where('body_text', 'not like', "%{$ex}%");
            });
        }

        // Sort by page_rank and latest indexed
        $queryBuilder->orderByDesc('page_rank')->orderByDesc('id');

        $totalHits = $queryBuilder->count();
        $totalPages = (int) ceil($totalHits / $perPage);
        $offset = ($page - 1) * $perPage;

        $items = $queryBuilder->skip($offset)->take($perPage)->get();

        // Format and generate highlighted snippets
        $results = $items->map(function (WebPage $item) use ($terms) {
            $snippet = $this->generateSnippet($item->description ?: $item->body_text ?: '', $terms);
            $highlighted = $this->highlightTerms($snippet, $terms);

            return [
                'id' => $item->id,
                'url' => $item->url,
                'domain' => $item->domain,
                'title' => $item->title ?: $item->domain,
                'snippet' => $snippet,
                'highlightedSnippet' => $highlighted,
                'favicon' => $item->favicon_url ?: "https://www.google.com/s2/favicons?domain={$item->domain}&sz=64",
                'rankScore' => $item->page_rank,
                'category' => $item->category,
                'publishedAt' => $item->created_at?->toIso8601String(),
                'indexedAt' => $item->crawled_at?->toIso8601String() ?: $item->updated_at?->toIso8601String(),
            ];
        })->toArray();

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

        // Math calculation: e.g. 5 * 12, (20 + 4) / 2, sqrt(256), 2^10
        if (preg_match('/^[\d\s\+\-\*\/\^\(\)\.\%]+$/', $q) && preg_match('/[\+\-\*\/\^\%]/', $q)) {
            try {
                // Safe basic evaluation
                $expr = str_replace('^', '**', $q);
                // Validate only numbers and basic operators
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
     * Generate dynamic context snippet around matching keywords.
     */
    protected function generateSnippet(string $text, array $terms, int $snippetLength = 220): string
    {
        if (empty($text)) {
            return '';
        }

        $text = strip_tags($text);
        $lowerText = strtolower($text);

        $firstPos = false;
        foreach ($terms as $term) {
            if (empty($term)) continue;
            $pos = strpos($lowerText, $term);
            if ($pos !== false && ($firstPos === false || $pos < $firstPos)) {
                $firstPos = $pos;
            }
        }

        if ($firstPos === false) {
            return Str::limit($text, $snippetLength);
        }

        $start = max(0, $firstPos - 40);
        $snippet = substr($text, $start, $snippetLength);

        return ($start > 0 ? '... ' : '') . trim($snippet) . (strlen($text) > ($start + $snippetLength) ? ' ...' : '');
    }

    /**
     * Highlight query terms in snippet with <mark> tags.
     */
    protected function highlightTerms(string $snippet, array $terms): string
    {
        if (empty($snippet) || empty($terms)) {
            return e($snippet);
        }

        $escaped = e($snippet);
        foreach ($terms as $term) {
            if (strlen($term) < 2) continue;
            $pattern = '/\b(' . preg_quote($term, '/') . ')/i';
            $escaped = preg_replace($pattern, '<mark class="bg-indigo-100 dark:bg-indigo-950/80 text-indigo-900 dark:text-indigo-200 font-semibold px-0.5 rounded">$1</mark>', $escaped);
        }

        return $escaped;
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
}
