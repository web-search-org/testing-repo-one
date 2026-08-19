<?php

namespace App\Console\Commands;

use App\Models\CrawlJob;
use App\Models\Domain;
use App\Models\Sitemap;
use App\Models\WebPage;
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
                            {--limit=10 : Maximum pages to crawl per job}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process and crawl queued websites and sitemaps in the background';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $once = $this->option('once');
        $poll = (int) $this->option('poll');

        $this->info("🚀 Web-Search Crawler Worker active. Waiting for queued jobs...");

        do {
            $job = CrawlJob::where('status', 'queued')
                ->orderBy('created_at')
                ->first();

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
        ]);

        $seedUrl = $job->seed_url;
        $parsed = parse_url($seedUrl);
        $domainName = strtolower($parsed['host'] ?? $seedUrl);
        $scheme = $parsed['scheme'] ?? 'https';

        $domain = Domain::firstOrCreate(
            ['name' => $domainName],
            [
                'protocol' => $scheme,
                'verification_token' => 'web-search-site-verification=' . Str::random(32),
                'is_verified' => false,
                'crawl_status' => 'crawling',
            ]
        );
        $domain->update(['crawl_status' => 'crawling']);

        $isSitemap = (bool) ($job->metadata['is_sitemap'] ?? str_ends_with(strtolower($seedUrl), '.xml'));
        $maxPages = min((int) ($job->max_pages ?: 20), 100);

        $crawledCount = 0;
        $indexedCount = 0;
        $discoveredUrls = [$seedUrl];

        try {
            if ($isSitemap) {
                // Fetch and parse XML sitemap
                $res = Http::timeout(10)->get($seedUrl);
                if ($res->successful()) {
                    $xmlContent = $res->body();
                    preg_match_all('/<loc>(https?:\/\/[^<]+)<\/loc>/i', $xmlContent, $matches);
                    $discoveredUrls = array_unique(array_slice($matches[1] ?? [$seedUrl], 0, $maxPages));
                }
            }

            foreach ($discoveredUrls as $url) {
                if ($crawledCount >= $maxPages) break;

                $start = microtime(true);
                try {
                    $response = Http::timeout(8)
                        ->withHeaders(['User-Agent' => 'WebSearchBot/1.0 (+https://web-search.org/bot.html)'])
                        ->get($url);
                    
                    $durationMs = round((microtime(true) - $start) * 1000, 2);
                    $crawledCount++;

                    if ($response->successful()) {
                        $html = $response->body();
                        $title = $this->extractTitle($html) ?: $url;
                        $description = $this->extractMetaDescription($html);
                        $bodyText = $this->extractBodyText($html);
                        $keywords = $this->extractKeywords($title, $bodyText);
                        $headings = $this->extractHeadings($html);

                        WebPage::updateOrCreate(
                            ['url' => $url],
                            [
                                'domain_id' => $domain->id,
                                'domain' => $domainName,
                                'title' => $title,
                                'description' => $description,
                                'body_text' => $bodyText,
                                'keywords' => $keywords,
                                'headings' => $headings,
                                'category' => $job->metadata['category'] ?? 'all',
                                'http_status' => $response->status(),
                                'response_time_ms' => $durationMs,
                                'is_indexed' => true,
                                'index_status' => 'indexed',
                                'mobile_friendly' => true,
                                'page_rank' => 5.0,
                                'crawled_at' => now(),
                            ]
                        );
                        $indexedCount++;
                        $this->line("  ✓ Indexed: {$url} ({$durationMs}ms)");
                    }
                } catch (\Exception $e) {
                    $this->warn("  ✗ Failed to fetch {$url}: {$e->getMessage()}");
                }
            }

            $domain->update([
                'total_pages' => WebPage::where('domain_id', $domain->id)->count(),
                'crawl_status' => 'idle',
                'last_crawled_at' => now(),
            ]);

            $job->update([
                'status' => 'completed',
                'pages_crawled' => $crawledCount,
                'pages_discovered' => count($discoveredUrls),
                'pages_indexed' => $indexedCount,
                'finished_at' => now(),
            ]);

            $this->info("✅ Job {$job->id} completed. Crawled {$crawledCount}, Indexed {$indexedCount} pages.");
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

    protected function extractKeywords(string $title, string $body): array
    {
        $words = str_word_count(strtolower($title . ' ' . substr($body, 0, 500)), 1);
        $stopWords = ['the', 'and', 'a', 'to', 'of', 'in', 'is', 'for', 'that', 'on', 'with', 'by', 'at', 'from', 'this', 'it', 'an'];
        $filtered = array_filter($words, fn($w) => strlen($w) > 3 && !in_array($w, $stopWords));
        $freq = array_count_values($filtered);
        arsort($freq);
        return array_slice(array_keys($freq), 0, 8);
    }
}
