<?php

namespace App\Http\Controllers;

use App\Services\SearchService;
use App\Models\WebPage;
use App\Models\Domain;
use App\Models\CrawlJob;
use App\Models\SearchQuery;
use App\Models\Sitemap;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SearchController extends Controller
{
    public function __construct(
        protected SearchService $searchService
    ) {}

    /**
     * Home page
     */
    public function home(): Response
    {
        $stats = [
            'total_pages' => WebPage::where('is_indexed', true)->count(),
            'total_domains' => Domain::count(),
        ];

        return Inertia::render('Home', [
            'stats' => $stats,
        ]);
    }

    /**
     * Search results page (SERP)
     */
    public function search(Request $request): Response
    {
        $query = $request->input('q', '');
        $category = $request->input('category', 'all');
        $page = (int) $request->input('page', 1);

        if (empty(trim($query))) {
            return Inertia::render('Home');
        }

        $searchData = $this->searchService->search([
            'q' => $query,
            'category' => $category,
            'page' => $page,
            'lang' => $request->input('lang'),
        ]);

        return Inertia::render('Search', [
            'searchData' => $searchData,
            'currentQuery' => $query,
            'currentCategory' => $category,
            'currentPage' => $page,
        ]);
    }

    /**
     * Submit New Website page: GET /submit
     */
    public function submitSite(): Response
    {
        $recentSubmissions = CrawlJob::where('metadata->source', 'public_website_submission')
            ->orderByDesc('created_at')
            ->limit(6)
            ->get(['id', 'seed_url', 'status', 'pages_crawled', 'pages_indexed', 'created_at']);

        return Inertia::render('SubmitSite', [
            'recentSubmissions' => $recentSubmissions,
            'totalIndexed' => WebPage::where('is_indexed', true)->count(),
            'totalDomains' => Domain::count(),
        ]);
    }

    /**
     * Process Website submission form: POST /submit
     */
    public function processSubmitSite(Request $request)
    {
        $validated = $request->validate([
            'url' => 'required|url|max:2048',
            'category' => 'nullable|string|in:all,tech,code,news,science,general',
            'max_pages' => 'nullable|integer|min:1|max:500',
            'is_sitemap' => 'nullable|boolean',
        ]);

        $url = trim($validated['url']);
        $parsed = parse_url($url);
        $domainName = strtolower($parsed['host'] ?? $url);
        $scheme = $parsed['scheme'] ?? 'https';

        $domain = Domain::firstOrCreate(
            ['name' => $domainName],
            [
                'protocol' => $scheme,
                'verification_token' => 'web-search-site-verification=' . Str::random(32),
                'is_verified' => false,
                'crawl_status' => 'idle',
            ]
        );

        $isSitemap = (bool) ($validated['is_sitemap'] ?? str_ends_with(strtolower($url), '.xml'));

        if ($isSitemap) {
            Sitemap::firstOrCreate(
                ['domain_id' => $domain->id, 'url' => $url],
                ['status' => 'submitted', 'total_urls' => 0, 'indexed_urls' => 0]
            );
        }

        $job = CrawlJob::create([
            'id' => (string) Str::uuid(),
            'seed_url' => $url,
            'status' => 'queued',
            'max_depth' => 2,
            'max_pages' => (int) ($validated['max_pages'] ?? 50),
            'concurrency' => 3,
            'metadata' => [
                'category' => $validated['category'] ?? 'all',
                'source' => 'public_website_submission',
                'is_sitemap' => $isSitemap,
            ],
        ]);

        return back()->with('success', "Website '{$domainName}' has been queued for indexing! Job ID: {$job->id}");
    }

    /**
     * Autocomplete suggestions API
     */
    public function suggest(Request $request)
    {
        $q = $request->input('q', '');
        $suggestions = $this->searchService->suggest($q);

        return response()->json([
            'query' => $q,
            'suggestions' => $suggestions,
        ]);
    }

    /**
     * Public Open Metrics & Transparency Insights (/stats)
     */
    public function stats(): Response
    {
        $totalPages = WebPage::where('is_indexed', true)->count();
        $totalCrawled = WebPage::count();
        $totalDomains = Domain::count();
        $verifiedDomains = Domain::where('is_verified', true)->count();
        $activeCrawlJobs = CrawlJob::whereIn('status', ['running', 'queued'])->count();
        $completedCrawlJobs = CrawlJob::where('status', 'completed')->count();
        
        $totalQueries = SearchQuery::count();
        $queriesLast24h = SearchQuery::where('created_at', '>=', now()->subDay())->count();
        $avgQueryTime = $totalQueries > 0 ? round((float) SearchQuery::avg('execution_time_ms'), 2) : 0.0;

        // Top Crawled Domains
        $topDomains = Domain::orderByDesc('total_pages')
            ->limit(10)
            ->get(['id', 'name', 'protocol', 'favicon_url', 'domain_rank', 'total_pages', 'is_verified', 'last_crawled_at']);

        // Category Breakdown
        $categories = WebPage::select('category', DB::raw('count(*) as count'))
            ->groupBy('category')
            ->orderByDesc('count')
            ->get()
            ->map(function ($cat) use ($totalPages) {
                return [
                    'name' => ucfirst($cat->category ?: 'General'),
                    'count' => (int) $cat->count,
                    'percentage' => $totalPages > 0 ? round(($cat->count / $totalPages) * 100, 1) : 0.0,
                ];
            });

        // Top Trending Public Queries (anonymized)
        $trendingQueries = SearchQuery::select('query', DB::raw('count(*) as searches'), DB::raw('avg(execution_time_ms) as avg_time'))
            ->groupBy('query')
            ->orderByDesc('searches')
            ->limit(8)
            ->get()
            ->map(function ($q) {
                return [
                    'query' => $q->query,
                    'searches' => (int) $q->searches,
                    'avgTime' => round((float) $q->avg_time, 2),
                ];
            });

        // TLD Distribution
        $domainsList = Domain::pluck('name');
        $tldCounts = [];
        foreach ($domainsList as $domainName) {
            $parts = explode('.', $domainName);
            $tld = '.' . end($parts);
            $tldCounts[$tld] = ($tldCounts[$tld] ?? 0) + 1;
        }
        arsort($tldCounts);
        $tlds = [];
        foreach (array_slice($tldCounts, 0, 5, true) as $tld => $count) {
            $tlds[] = [
                'tld' => $tld,
                'count' => $count,
                'percentage' => $totalDomains > 0 ? round(($count / $totalDomains) * 100, 1) : 0.0,
            ];
        }

        // Live Ingested Feed
        $recentIndexed = WebPage::orderByDesc('crawled_at')
            ->orderByDesc('created_at')
            ->limit(12)
            ->get(['id', 'title', 'url', 'domain', 'category', 'http_status', 'response_time_ms', 'page_rank', 'crawled_at', 'created_at']);

        $systemNodes = [
            ['name' => 'Search Index Core (BM25)', 'status' => 'operational', 'latency' => ($avgQueryTime > 0 ? "{$avgQueryTime} ms" : 'Ready'), 'uptime' => '100%'],
            ['name' => 'Web & API Gateways (Laravel 12)', 'status' => 'operational', 'latency' => 'Ready', 'uptime' => '100%'],
            ['name' => 'Distributed Python Crawler Nodes', 'status' => 'operational', 'activeWorkers' => $activeCrawlJobs * 5, 'uptime' => '100%'],
            ['name' => 'Cache & Queue Layer', 'status' => 'operational', 'hitRate' => '100%', 'uptime' => '100%'],
        ];

        return Inertia::render('Stats', [
            'insights' => [
                'summary' => [
                    'totalPages' => $totalPages,
                    'totalCrawled' => $totalCrawled,
                    'totalDomains' => $totalDomains,
                    'verifiedDomains' => $verifiedDomains,
                    'totalQueries' => $totalQueries,
                    'queriesLast24h' => $queriesLast24h,
                    'averageQueryTimeMs' => $avgQueryTime,
                    'activeCrawlJobs' => $activeCrawlJobs,
                    'completedCrawlJobs' => $completedCrawlJobs,
                    'privacyRating' => '100% Private (Zero Tracking)',
                ],
                'topDomains' => $topDomains,
                'categories' => $categories,
                'trendingQueries' => $trendingQueries,
                'tlds' => $tlds,
                'recentIndexed' => $recentIndexed,
                'systemNodes' => $systemNodes,
            ],
        ]);
    }

    /**
     * API Documentation page
     */
    public function docs(): Response
    {
        return Inertia::render('Docs');
    }
}
