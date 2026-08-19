<?php

namespace App\Console\Commands;

use App\Models\CrawlJob;
use App\Models\Domain;
use App\Models\Sitemap;
use App\Models\WebLink;
use App\Models\WebPage;
use App\Models\Word;
use App\Services\RobotsTxtService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CrawlWorkerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawl:worker 
                            {--once : Process pending jobs once and exit}
                            {--poll=2 : Seconds to wait between polling for new queued jobs}
                            {--limit=50 : Maximum pages to crawl per job}
                            {--recover : Force recover all running jobs back to queued}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process queued websites, respect robots.txt rules, auto-extract interlinks, build link graphs, and continuously crawl the web';

    protected RobotsTxtService $robots;

    public function __construct(RobotsTxtService $robots)
    {
        parent::__construct();
        $this->robots = $robots;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $once = $this->option('once');
        $poll = (int) $this->option('poll');
        $forceRecover = $this->option('recover');

        $this->info("🚀 Web-Search Autonomous Crawler Worker active (robots.txt validation: ON).");

        // Recovery of stale or interrupted running jobs
        if ($forceRecover) {
            $recovered = CrawlJob::where('status', 'running')->update(['status' => 'queued']);
            $this->info("♻️ Force recovered {$recovered} running job(s) back to queue.");
        } else {
            $staleCutoff = now()->subMinutes(2);
            $recovered = CrawlJob::where('status', 'running')
                ->where('updated_at', '<', $staleCutoff)
                ->update(['status' => 'queued']);
            if ($recovered > 0) {
                $this->warn("♻️ Auto-recovered {$recovered} stale running job(s) back to queue.");
            }
        }

        do {
            $job = CrawlJob::where('status', 'queued')
                ->orderBy('created_at')
                ->first();

            // If queue is empty, auto-seed from uncrawled domains in database
            if (!$job) {
                $unCrawledDomain = Domain::where('total_pages', 0)
                    ->whereNull('last_crawled_at')
                    ->whereDoesntHave('crawlJobs', function ($q) {
                        $q->whereIn('status', ['queued', 'running']);
                    })
                    ->first();

                if ($unCrawledDomain) {
                    $job = CrawlJob::create([
                        'id' => (string) Str::uuid(),
                        'seed_url' => "{$unCrawledDomain->protocol}://{$unCrawledDomain->name}/",
                        'status' => 'queued',
                        'max_depth' => 2,
                        'max_pages' => 25,
                        'concurrency' => 2,
                        'metadata' => ['source' => 'autonomous_domain_frontier'],
                    ]);
                    $this->line("🌱 Auto-seeded frontier job for uncrawled domain: {$job->seed_url}");
                }
            }

            if ($job) {
                $this->processJob($job);
            } else {
                if ($once) {
                    $this->info("No queued jobs remaining. Exiting.");
                    break;
                }
                sleep($poll);
            }
        } while (true);

        return Command::SUCCESS;
    }

    protected function processJob(CrawlJob $job): void
    {
        $this->info("⚡ Processing Job {$job->id}: {$job->seed_url}");
        $job->update([
            'status' => 'running',
            'started_at' => now(),
            'updated_at' => now(),
        ]);

        $seedUrl = $job->seed_url;
        $parsed = parse_url($seedUrl);
        $seedDomain = strtolower($parsed['host'] ?? $seedUrl);
        $scheme = $parsed['scheme'] ?? 'https';

        $domain = Domain::firstOrCreate(
            ['name' => $seedDomain],
            [
                'protocol' => $scheme,
                'verification_token' => 'web-search-site-verification=' . Str::random(32),
                'is_verified' => false,
                'crawl_status' => 'crawling',
            ]
        );
        $domain->update(['crawl_status' => 'crawling']);

        // Check & parse robots.txt for domain
        $robotsRules = $this->robots->getRulesForDomain($scheme, $seedDomain);
        
        // Auto-register any XML Sitemaps declared in robots.txt
        $discoveredSitemaps = $robotsRules['sitemaps'] ?? [];
        foreach ($discoveredSitemaps as $sitemapUrl) {
            Sitemap::firstOrCreate(
                [
                    'domain_id' => $domain->id,
                    'url' => $sitemapUrl,
                ],
                [
                    'status' => 'submitted',
                    'submitted_at' => now(),
                ]
            );
        }

        $isSitemap = (bool) ($job->metadata['is_sitemap'] ?? str_ends_with(strtolower($seedUrl), '.xml'));
        $maxPages = min((int) ($job->max_pages ?: 50), 200);

        $crawledCount = 0;
        $indexedCount = 0;
        $discoveredLinksCount = 0;
        $frontier = [$seedUrl];
        $visited = [];
        $discoveredExternalDomains = [];

        try {
            if ($isSitemap) {
                try {
                    $res = Http::timeout(6)->connectTimeout(3)->get($seedUrl);
                    if ($res->successful()) {
                        $xmlContent = $res->body();
                        preg_match_all('/<loc>(https?:\/\/[^<]+)<\/loc>/i', $xmlContent, $matches);
                        $frontier = array_unique(array_slice($matches[1] ?? [$seedUrl], 0, $maxPages));
                    }
                } catch (\Exception $e) {
                    $this->warn("  ✗ Sitemap fetch timed out: {$seedUrl}");
                }
            }

            while (!empty($frontier) && $crawledCount < $maxPages) {
                $url = array_shift($frontier);
                $url = $this->normalizeUrl($url);

                if (!$url || in_array($url, $visited)) {
                    continue;
                }
                $visited[] = $url;

                // 1. Robots.txt Compliance Check
                if (!$this->robots->canFetch($url, 'WebSearchBot')) {
                    $this->warn("  🚫 Disallowed by robots.txt: {$url}");
                    WebPage::updateOrCreate(
                        ['url' => $url],
                        [
                            'domain_id' => $domain->id,
                            'domain' => parse_url($url, PHP_URL_HOST) ?: $seedDomain,
                            'title' => 'Blocked by robots.txt',
                            'is_indexed' => false,
                            'index_status' => 'blocked_by_robots_txt',
                            'crawled_at' => now(),
                        ]
                    );
                    continue;
                }

                // Keep job alive in heartbeat
                $job->touch();

                $start = microtime(true);
                try {
                    $response = Http::timeout(5)
                        ->connectTimeout(3)
                        ->withHeaders([
                            'User-Agent' => 'WebSearchBot/1.0 (+https://web-search.org/bot.html)',
                            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                        ])
                        ->get($url);
                    
                    $durationMs = round((microtime(true) - $start) * 1000, 2);
                    $crawledCount++;

                    if ($response->successful()) {
                        $html = $response->body();
                        $title = $this->sanitizeUtf8($this->extractTitle($html) ?: $url);
                        $description = $this->sanitizeUtf8($this->extractMetaDescription($html));
                        $bodyText = $this->sanitizeUtf8($this->extractBodyText($html));
                        $keywords = array_map([$this, 'sanitizeUtf8'], $this->extractKeywords($title, $bodyText));
                        $headings = array_map([$this, 'sanitizeUtf8'], $this->extractHeadings($html));
                        $ogImage = $this->extractOgImage($html);
                        $favicon = $this->extractFavicon($html, $url);

                        $pageDomain = parse_url($url, PHP_URL_HOST) ?: $seedDomain;
                        $pageDomain = strtolower($pageDomain);

                        $page = WebPage::updateOrCreate(
                            ['url' => $url],
                            [
                                'domain_id' => $domain->id,
                                'domain' => $pageDomain,
                                'title' => $title,
                                'description' => $description,
                                'body_text' => $bodyText,
                                'keywords' => $keywords,
                                'headings' => $headings,
                                'og_image' => $ogImage,
                                'favicon_url' => $favicon,
                                'category' => $job->metadata['category'] ?? 'all',
                                'http_status' => $response->status(),
                                'response_time_ms' => $durationMs,
                                'is_indexed' => true,
                                'index_status' => 'indexed',
                                'mobile_friendly' => true,
                                'crawled_at' => now(),
                            ]
                        );
                        $indexedCount++;

                        // Index content words into words dictionary
                        $this->indexWords($title, $bodyText, $keywords);

                        // Extract all outbound links
                        $links = $this->extractLinks($html, $url);
                        $outLinksCount = 0;

                        foreach ($links as $linkData) {
                            $targetUrl = $linkData['url'];
                            $targetParsed = parse_url($targetUrl);
                            $targetDomain = strtolower($targetParsed['host'] ?? '');
                            $isExternal = ($targetDomain !== $pageDomain && !empty($targetDomain));

                            if (empty($targetDomain)) continue;

                            $outLinksCount++;
                            $discoveredLinksCount++;

                            // Save link graph connection
                            WebLink::updateOrCreate(
                                [
                                    'source_url' => $url,
                                    'target_url' => $targetUrl,
                                ],
                                [
                                    'source_page_id' => $page->id,
                                    'source_domain' => $pageDomain,
                                    'target_domain' => $targetDomain,
                                    'anchor_text' => $this->sanitizeUtf8($linkData['anchor']),
                                    'is_external' => $isExternal,
                                    'rel' => $linkData['rel'],
                                ]
                            );

                            // Automatic Discovery & Queueing
                            if (!$isExternal) {
                                // Internal Link on the same domain
                                if (!in_array($targetUrl, $visited) && count($frontier) < ($maxPages * 2)) {
                                    $frontier[] = $targetUrl;
                                }

                                // If not indexed yet and not already queued, auto-queue into database
                                if (WebPage::where('url', $targetUrl)->doesntExist() &&
                                    CrawlJob::where('seed_url', $targetUrl)->whereIn('status', ['queued', 'running'])->doesntExist()) {
                                    CrawlJob::create([
                                        'id' => (string) Str::uuid(),
                                        'seed_url' => $targetUrl,
                                        'status' => 'queued',
                                        'max_depth' => 1,
                                        'max_pages' => 10,
                                        'concurrency' => 2,
                                        'metadata' => [
                                            'source' => 'auto_internal_link_discovery',
                                            'referred_by' => $url,
                                        ],
                                    ]);
                                }
                            } else {
                                // External Link to another domain
                                if (!in_array($targetDomain, $discoveredExternalDomains)) {
                                    $discoveredExternalDomains[] = $targetDomain;
                                    
                                    Domain::firstOrCreate(
                                        ['name' => $targetDomain],
                                        [
                                            'protocol' => $targetParsed['scheme'] ?? 'https',
                                            'verification_token' => 'web-search-site-verification=' . Str::random(32),
                                            'is_verified' => false,
                                            'crawl_status' => 'idle',
                                        ]
                                    );

                                    $targetSeedUrl = ($targetParsed['scheme'] ?? 'https') . "://{$targetDomain}/";
                                    if (CrawlJob::where('seed_url', $targetSeedUrl)->whereIn('status', ['queued', 'running'])->doesntExist() &&
                                        WebPage::where('domain', $targetDomain)->doesntExist()) {
                                        CrawlJob::create([
                                            'id' => (string) Str::uuid(),
                                            'seed_url' => $targetSeedUrl,
                                            'status' => 'queued',
                                            'max_depth' => 2,
                                            'max_pages' => 25,
                                            'concurrency' => 2,
                                            'metadata' => [
                                                'source' => 'auto_external_link_discovery',
                                                'referred_by' => $url,
                                            ],
                                        ]);
                                    }
                                }
                            }
                        }

                        // Update in-links count, out-links count and PageRank
                        $inLinksCount = WebLink::where('target_url', $url)->count();
                        $computedPageRank = round(min(10.0, 1.0 + ($inLinksCount * 0.5)), 1);

                        $page->update([
                            'out_links_count' => $outLinksCount,
                            'in_links_count' => $inLinksCount,
                            'page_rank' => $computedPageRank,
                        ]);

                        $this->line("  ✓ Indexed: {$url} ({$durationMs}ms, {$outLinksCount} links extracted, PR: {$computedPageRank})");
                    }
                } catch (\Exception $e) {
                    $this->warn("  ✗ Skipped {$url} (timeout/network error: {$e->getMessage()})");
                }
            }

            // Recalculate domain totals and domain rank based on total inbound backlinks
            $domainInLinks = WebLink::where('target_domain', $seedDomain)->where('is_external', true)->count();
            $domainRank = round(min(10.0, 1.0 + ($domainInLinks * 0.4)), 1);

            $domain->update([
                'total_pages' => WebPage::where('domain_id', $domain->id)->count(),
                'domain_rank' => $domainRank,
                'crawl_status' => 'idle',
                'last_crawled_at' => now(),
            ]);

            $job->update([
                'status' => 'completed',
                'pages_crawled' => $crawledCount,
                'pages_discovered' => count($visited) + count($frontier) + $discoveredLinksCount,
                'pages_indexed' => $indexedCount,
                'finished_at' => now(),
            ]);

            $this->info("✅ Job {$job->id} completed. Crawled {$crawledCount}, Indexed {$indexedCount} pages. Discovered " . count($discoveredExternalDomains) . " external domains.");
        } catch (\Exception $e) {
            $domain->update(['crawl_status' => 'idle']);
            $job->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'finished_at' => now(),
            ]);
            $this->error("❌ Job {$job->id} failed: {$e->getMessage()}");
        }
    }

    protected function sanitizeUtf8(?string $string): ?string
    {
        if ($string === null) {
            return null;
        }
        return mb_convert_encoding($string, 'UTF-8', 'UTF-8');
    }

    protected function extractLinks(string $html, string $baseUrl): array
    {
        $links = [];
        preg_match_all('/<a\b[^>]*href=["\']([^"\']+)["\'][^>]*>(.*?)<\/a>/is', $html, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $rawHref = trim($match[1]);
            $anchor = trim(html_entity_decode(strip_tags($match[2])));
            
            // Skip non-HTTP links
            if (empty($rawHref) || preg_match('/^(javascript|mailto|tel|data|#):/i', $rawHref) || str_starts_with($rawHref, '#')) {
                continue;
            }

            // Skip binary asset extensions
            if (preg_match('/\.(png|jpe?g|gif|webp|svg|pdf|zip|tar|gz|mp4|mp3|exe|dmg|iso)$/i', parse_url($rawHref, PHP_URL_PATH) ?? '')) {
                continue;
            }

            $absoluteUrl = $this->resolveAbsoluteUrl($rawHref, $baseUrl);
            if ($absoluteUrl) {
                // Extract rel if present
                $rel = null;
                if (preg_match('/rel=["\']([^"\']+)["\']/i', $match[0], $relMatch)) {
                    $rel = trim($relMatch[1]);
                }

                $links[] = [
                    'url' => $absoluteUrl,
                    'anchor' => substr($anchor ?: parse_url($absoluteUrl, PHP_URL_PATH) ?: 'Link', 0, 100),
                    'rel' => $rel,
                ];
            }
        }

        return $links;
    }

    protected function resolveAbsoluteUrl(string $href, string $baseUrl): ?string
    {
        if (str_starts_with($href, 'http://') || str_starts_with($href, 'https://')) {
            return $this->normalizeUrl($href);
        }

        if (str_starts_with($href, '//')) {
            $scheme = parse_url($baseUrl, PHP_URL_SCHEME) ?: 'https';
            return $this->normalizeUrl("{$scheme}:{$href}");
        }

        $baseParsed = parse_url($baseUrl);
        $scheme = $baseParsed['scheme'] ?? 'https';
        $host = $baseParsed['host'] ?? '';
        $port = isset($baseParsed['port']) ? ":{$baseParsed['port']}" : '';

        if (empty($host)) return null;

        if (str_starts_with($href, '/')) {
            return $this->normalizeUrl("{$scheme}://{$host}{$port}{$href}");
        }

        $basePath = $baseParsed['path'] ?? '/';
        $baseDir = rtrim(dirname($basePath), '/');
        $resolved = $baseDir . '/' . $href;
        return $this->normalizeUrl("{$scheme}://{$host}{$port}/" . ltrim($resolved, '/'));
    }

    protected function normalizeUrl(string $url): ?string
    {
        $parsed = parse_url(trim($url));
        if (empty($parsed['scheme']) || empty($parsed['host'])) {
            return null;
        }

        $scheme = strtolower($parsed['scheme']);
        if (!in_array($scheme, ['http', 'https'])) {
            return null;
        }

        $host = strtolower($parsed['host']);
        $port = isset($parsed['port']) && !(($scheme === 'http' && $parsed['port'] == 80) || ($scheme === 'https' && $parsed['port'] == 443)) ? ":{$parsed['port']}" : '';
        $path = $parsed['path'] ?? '/';
        if ($path === '') $path = '/';
        
        $query = isset($parsed['query']) ? "?{$parsed['query']}" : '';

        return "{$scheme}://{$host}{$port}{$path}{$query}";
    }

    protected function extractTitle(string $html): ?string
    {
        if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $matches)) {
            return trim(html_entity_decode(strip_tags($matches[1])));
        }
        return null;
    }

    protected function extractMetaDescription(string $html): ?string
    {
        if (preg_match('/<meta[^>]*name=["\']description["\'][^>]*content=["\'](.*?)["\']/is', $html, $matches)) {
            return trim(html_entity_decode($matches[1]));
        }
        return null;
    }

    protected function extractBodyText(string $html): string
    {
        $clean = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);
        $clean = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $clean);
        $clean = strip_tags($clean);
        $clean = preg_replace('/\s+/', ' ', $clean);
        return trim(substr($clean, 0, 10000));
    }

    protected function extractHeadings(string $html): array
    {
        preg_match_all('/<h[1-3][^>]*>(.*?)<\/h[1-3]>/is', $html, $matches);
        return array_slice(array_map('trim', array_map('strip_tags', $matches[1] ?? [])), 0, 6);
    }

    protected function extractOgImage(string $html): ?string
    {
        if (preg_match('/<meta[^>]*property=["\']og:image["\'][^>]*content=["\'](.*?)["\']/is', $html, $matches)) {
            return trim($matches[1]);
        }
        return null;
    }

    protected function extractFavicon(string $html, string $url): ?string
    {
        if (preg_match('/<link[^>]*rel=["\'](?:shortcut )?icon["\'][^>]*href=["\'](.*?)["\']/is', $html, $matches)) {
            return $this->resolveAbsoluteUrl(trim($matches[1]), $url);
        }
        $parsed = parse_url($url);
        return ($parsed['scheme'] ?? 'https') . '://' . ($parsed['host'] ?? '') . '/favicon.ico';
    }

    protected function extractKeywords(string $title, string $body): array
    {
        $words = str_word_count(strtolower($title . ' ' . substr($body, 0, 500)), 1);
        $stopWords = ['the', 'and', 'a', 'to', 'of', 'in', 'is', 'for', 'that', 'on', 'with', 'by', 'at', 'from', 'this', 'it', 'an'];
        $filtered = array_filter($words, fn($w) => strlen($w) > 3 && !in_array($w, $stopWords));
        $freq = array_count_values($filtered);
        arsort($freq);
        return array_slice(array_keys($freq), 0, 8);
    }

    protected function indexWords(string $title, string $body, array $keywords): void
    {
        try {
            $text = strtolower($title . ' ' . substr($body, 0, 800) . ' ' . implode(' ', $keywords));
            $words = str_word_count($text, 1);
            $stopWords = ['the', 'and', 'a', 'to', 'of', 'in', 'is', 'for', 'that', 'on', 'with', 'by', 'at', 'from', 'this', 'it', 'an'];
            
            $uniqueWords = [];
            foreach ($words as $w) {
                if (strlen($w) >= 3 && strlen($w) <= 40 && !in_array($w, $stopWords) && preg_match('/^[a-z0-9\-_]+$/i', $w)) {
                    $uniqueWords[$w] = true;
                }
            }

            foreach (array_keys($uniqueWords) as $word) {
                Word::firstOrCreate(
                    ['word' => $word],
                    ['language' => 'en', 'frequency' => 1]
                );
            }
        } catch (\Exception) {
            // Ignore word indexing errors silently
        }
    }
}
