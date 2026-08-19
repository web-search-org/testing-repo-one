<?php

namespace Database\Seeders;

use App\Models\Domain;
use App\Models\WebPage;
use App\Models\SearchQuery;
use App\Models\CrawlJob;
use App\Models\Sitemap;
use App\Models\DomainPerformance;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $domains = [
            [
                'name' => 'web-search.org',
                'protocol' => 'https',
                'favicon_url' => 'https://www.google.com/s2/favicons?domain=web-search.org&sz=64',
                'domain_rank' => 10.0,
                'total_pages' => 3,
                'is_verified' => true,
                'verification_method' => 'dns_txt',
                'verification_token' => 'web-search-site-verification=ws_auth_root_domain_verified',
                'verified_at' => now()->subDays(30),
                'crawl_status' => 'idle',
            ],
            [
                'name' => 'svelte.dev',
                'protocol' => 'https',
                'favicon_url' => 'https://www.google.com/s2/favicons?domain=svelte.dev&sz=64',
                'domain_rank' => 9.5,
                'total_pages' => 2,
                'is_verified' => true,
                'verification_method' => 'meta_tag',
                'verification_token' => 'web-search-site-verification=ws_auth_svelte_verified',
                'verified_at' => now()->subDays(15),
                'crawl_status' => 'idle',
            ],
            [
                'name' => 'laravel.com',
                'protocol' => 'https',
                'favicon_url' => 'https://www.google.com/s2/favicons?domain=laravel.com&sz=64',
                'domain_rank' => 9.6,
                'total_pages' => 2,
                'is_verified' => true,
                'verification_method' => 'dns_txt',
                'verification_token' => 'web-search-site-verification=ws_auth_laravel_verified',
                'verified_at' => now()->subDays(20),
                'crawl_status' => 'idle',
            ],
            [
                'name' => 'python.org',
                'protocol' => 'https',
                'favicon_url' => 'https://www.google.com/s2/favicons?domain=python.org&sz=64',
                'domain_rank' => 9.8,
                'total_pages' => 1,
                'is_verified' => false,
                'crawl_status' => 'idle',
            ],
            [
                'name' => 'github.com',
                'protocol' => 'https',
                'favicon_url' => 'https://www.google.com/s2/favicons?domain=github.com&sz=64',
                'domain_rank' => 10.0,
                'total_pages' => 2,
                'is_verified' => false,
                'crawl_status' => 'idle',
            ],
        ];

        $domainMap = [];
        foreach ($domains as $d) {
            $domain = Domain::updateOrCreate(['name' => $d['name']], $d);
            $domainMap[$d['name']] = $domain->id;
        }

        $pages = [
            [
                'domain_id' => $domainMap['web-search.org'],
                'url' => 'https://web-search.org',
                'domain' => 'web-search.org',
                'title' => 'Web-Search.org - The Open-Source Privacy Search Engine',
                'description' => 'Web-Search.org is a modern, privacy-first, decentralized search engine powered by an open-source distributed crawler and modern web interface.',
                'keywords' => ['search', 'open source', 'crawler', 'privacy', 'laravel', 'svelte'],
                'headings' => ['Web-Search.org', 'Decentralized Search', 'Open Crawler', 'Zero Tracking'],
                'body_text' => 'Web-Search.org is an open-source search engine providing unbiased, transparent search results without tracking user data. Powered by Laravel, Svelte, and a high-performance Python crawler.',
                'category' => 'tech',
                'language' => 'en',
                'page_rank' => 10.0,
                'http_status' => 200,
                'response_time_ms' => 12.4,
                'is_indexed' => true,
                'index_status' => 'indexed',
                'mobile_friendly' => true,
                'crawled_at' => now(),
            ],
            [
                'domain_id' => $domainMap['web-search.org'],
                'url' => 'https://web-search.org/docs/api',
                'domain' => 'web-search.org',
                'title' => 'Web-Search.org Developer API Documentation & SDK',
                'description' => 'Comprehensive documentation for the Web-Search.org REST API, TypeScript SDK, PHP SDK, and webhook integrations.',
                'keywords' => ['api', 'sdk', 'developer', 'rest api', 'documentation'],
                'headings' => ['API Reference', 'Authentication', 'Search Endpoint', 'Crawler Dispatch'],
                'body_text' => 'Integrate Web-Search into your applications using our free, blazing-fast REST API or official TypeScript and PHP SDKs. Supports filtering, instant answers, and full text search.',
                'category' => 'code',
                'language' => 'en',
                'page_rank' => 8.5,
                'http_status' => 200,
                'response_time_ms' => 10.2,
                'is_indexed' => true,
                'index_status' => 'indexed',
                'mobile_friendly' => true,
                'crawled_at' => now(),
            ],
            [
                'domain_id' => $domainMap['svelte.dev'],
                'url' => 'https://svelte.dev',
                'domain' => 'svelte.dev',
                'title' => 'Svelte • Cybernetically enhanced web apps',
                'description' => 'Svelte is a radical new approach to building user interfaces with high performance, zero runtime overhead, and runes-based fine-grained reactivity in Svelte 5.',
                'keywords' => ['svelte', 'javascript', 'ui', 'frontend', 'runes', 'framework'],
                'headings' => ['Svelte 5', 'Runes', 'Reactivity', 'Compile-time UI'],
                'body_text' => 'Svelte is a modern reactive framework that compiles components into efficient, tiny JavaScript code. With Svelte 5 and runes ($state, $derived, $effect), building interactive web applications has never been cleaner.',
                'category' => 'tech',
                'language' => 'en',
                'page_rank' => 9.6,
                'http_status' => 200,
                'response_time_ms' => 15.1,
                'is_indexed' => true,
                'index_status' => 'indexed',
                'mobile_friendly' => true,
                'crawled_at' => now(),
            ],
            [
                'domain_id' => $domainMap['laravel.com'],
                'url' => 'https://laravel.com',
                'domain' => 'laravel.com',
                'title' => 'Laravel - The PHP Framework for Web Artisans',
                'description' => 'Laravel is a web application framework with expressive, elegant syntax. We’ve already laid the foundation — freeing you to create without sweating the small things.',
                'keywords' => ['laravel', 'php', 'backend', 'artisan', 'eloquent', 'inertia'],
                'headings' => ['Laravel Framework', 'Eloquent ORM', 'Artisan CLI', 'Vite & Inertia'],
                'body_text' => 'Laravel provides a robust backend architecture for building scalable applications, with built-in routing, authentication, queue workers, and seamless Inertia.js frontend integration.',
                'category' => 'tech',
                'language' => 'en',
                'page_rank' => 9.7,
                'http_status' => 200,
                'response_time_ms' => 14.8,
                'is_indexed' => true,
                'index_status' => 'indexed',
                'mobile_friendly' => true,
                'crawled_at' => now(),
            ],
            [
                'domain_id' => $domainMap['python.org'],
                'url' => 'https://www.python.org',
                'domain' => 'python.org',
                'title' => 'Welcome to Python.org',
                'description' => 'Python is a programming language that lets you work quickly and integrate systems more effectively with high productivity and clean syntax.',
                'keywords' => ['python', 'asyncio', 'machine learning', 'data science', 'crawler'],
                'headings' => ['Python Language', 'AsyncIO', 'Downloads', 'Documentation'],
                'body_text' => 'Python is widely used for building asynchronous web crawlers, machine learning models, search indexing pipelines, and backend microservices.',
                'category' => 'tech',
                'language' => 'en',
                'page_rank' => 9.8,
                'http_status' => 200,
                'response_time_ms' => 18.0,
                'is_indexed' => true,
                'index_status' => 'indexed',
                'mobile_friendly' => true,
                'crawled_at' => now(),
            ],
            [
                'domain_id' => $domainMap['github.com'],
                'url' => 'https://github.com/web-search/search',
                'domain' => 'github.com',
                'title' => 'GitHub - web-search/search: The Open-Source Search Engine Monorepo',
                'description' => 'Official monorepo containing the Laravel+Svelte web app, Python crawler, Indexer pipeline, and client SDKs.',
                'keywords' => ['github', 'repository', 'open source', 'git', 'pull request'],
                'headings' => ['web-search / search', 'Architecture', 'Getting Started', 'Docker Setup'],
                'body_text' => 'Explore the open source code of Web-Search.org on GitHub. Contributions, pull requests, and discussions welcome.',
                'category' => 'code',
                'language' => 'en',
                'page_rank' => 9.4,
                'http_status' => 200,
                'response_time_ms' => 22.0,
                'is_indexed' => true,
                'index_status' => 'indexed',
                'mobile_friendly' => true,
                'crawled_at' => now(),
            ],
        ];

        foreach ($pages as $p) {
            WebPage::updateOrCreate(['url' => $p['url']], $p);
        }

        // Seed Sitemaps
        Sitemap::updateOrCreate(
            ['url' => 'https://web-search.org/sitemap.xml'],
            [
                'domain_id' => $domainMap['web-search.org'],
                'status' => 'success',
                'total_urls' => 24,
                'indexed_urls' => 24,
                'last_crawled_at' => now()->subHours(2),
            ]
        );

        Sitemap::updateOrCreate(
            ['url' => 'https://svelte.dev/sitemap.xml'],
            [
                'domain_id' => $domainMap['svelte.dev'],
                'status' => 'success',
                'total_urls' => 18,
                'indexed_urls' => 18,
                'last_crawled_at' => now()->subHours(5),
            ]
        );

        // Seed Search Console Performance Metrics
        $perfData = [
            ['query' => 'open source search engine', 'clicks' => 312, 'impressions' => 4500, 'ctr' => 6.9, 'avg_position' => 1.2],
            ['query' => 'privacy search engine', 'clicks' => 195, 'impressions' => 3200, 'ctr' => 6.1, 'avg_position' => 1.5],
            ['query' => 'web-search org', 'clicks' => 140, 'impressions' => 1650, 'ctr' => 8.5, 'avg_position' => 1.0],
            ['query' => 'laravel svelte monorepo', 'clicks' => 85, 'impressions' => 1400, 'ctr' => 6.0, 'avg_position' => 2.3],
            ['query' => 'python async crawler', 'clicks' => 62, 'impressions' => 980, 'ctr' => 6.3, 'avg_position' => 2.8],
        ];

        foreach ($perfData as $item) {
            DomainPerformance::create([
                'domain_id' => $domainMap['web-search.org'],
                'query' => $item['query'],
                'page_url' => 'https://web-search.org',
                'clicks' => $item['clicks'],
                'impressions' => $item['impressions'],
                'ctr' => $item['ctr'],
                'avg_position' => $item['avg_position'],
                'recorded_date' => now()->toDateString(),
            ]);
        }

        // Seed sample queries
        $queries = [
            ['query' => 'laravel', 'category' => 'tech', 'results_count' => 3, 'execution_time_ms' => 1.2],
            ['query' => 'svelte', 'category' => 'tech', 'results_count' => 2, 'execution_time_ms' => 0.9],
            ['query' => 'open source search engine', 'category' => 'all', 'results_count' => 4, 'execution_time_ms' => 1.5],
            ['query' => 'python crawler', 'category' => 'code', 'results_count' => 2, 'execution_time_ms' => 1.1],
            ['query' => 'web-search', 'category' => 'all', 'results_count' => 3, 'execution_time_ms' => 0.8],
        ];

        foreach ($queries as $q) {
            SearchQuery::create($q);
        }

        // Seed a sample completed crawl job
        CrawlJob::updateOrCreate(
            ['id' => '11111111-2222-3333-4444-555555555555'],
            [
                'seed_url' => 'https://web-search.org',
                'status' => 'completed',
                'max_depth' => 2,
                'max_pages' => 50,
                'concurrency' => 5,
                'pages_crawled' => 14,
                'pages_discovered' => 42,
                'pages_indexed' => 14,
                'errors_count' => 0,
                'started_at' => now()->subMinutes(10),
                'finished_at' => now()->subMinutes(8),
            ]
        );
    }
}
