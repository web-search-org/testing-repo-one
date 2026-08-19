<?php

namespace Tests\Feature;

use App\Models\Domain;
use App\Models\WebLink;
use App\Models\WebPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SearchEngineTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_returns_successful_response(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_search_results_page_returns_successful_response(): void
    {
        $response = $this->get('/search?q=laravel');
        $response->assertStatus(200);
    }

    public function test_submit_website_page_returns_successful_response(): void
    {
        $response = $this->get('/submit');
        $response->assertStatus(200);
    }

    public function test_submit_website_form_post_creates_crawl_job_and_domain(): void
    {
        $response = $this->post('/submit', [
            'url' => 'https://docs.astro.build',
            'category' => 'tech',
            'max_pages' => 50,
            'is_sitemap' => false,
        ]);

        $response->assertStatus(302);

        $this->assertDatabaseHas('domains', [
            'name' => 'docs.astro.build',
        ]);

        $this->assertDatabaseHas('crawl_jobs', [
            'seed_url' => 'https://docs.astro.build',
            'status' => 'queued',
        ]);
    }

    public function test_submit_website_api_endpoint(): void
    {
        $response = $this->postJson('/api/v1/submit', [
            'url' => 'https://vitest.dev/guide/sitemap.xml',
            'category' => 'code',
            'max_pages' => 100,
            'is_sitemap' => true,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'status' => 'queued',
                'domain' => 'vitest.dev',
            ]);

        $this->assertDatabaseHas('domains', ['name' => 'vitest.dev']);
        $this->assertDatabaseHas('sitemaps', ['url' => 'https://vitest.dev/guide/sitemap.xml']);
        $this->assertDatabaseHas('crawl_jobs', ['seed_url' => 'https://vitest.dev/guide/sitemap.xml']);
    }

    public function test_models_use_valid_uuids(): void
    {
        $domain = Domain::create([
            'name' => 'uuid-test.org',
            'protocol' => 'https',
            'domain_rank' => 5.0,
        ]);

        $this->assertTrue(Str::isUuid($domain->id));

        $page = WebPage::create([
            'domain_id' => $domain->id,
            'url' => 'https://uuid-test.org/welcome',
            'domain' => 'uuid-test.org',
            'title' => 'UUID Test Page',
            'description' => 'Testing UUID primary keys',
            'is_indexed' => true,
        ]);

        $this->assertTrue(Str::isUuid($page->id));
        $this->assertEquals($domain->id, $page->domain_id);
    }

    public function test_web_links_and_interlinking_relationships(): void
    {
        $sourceDomain = Domain::create(['name' => 'github.com']);
        $targetDomain = Domain::create(['name' => 'web-search.org']);

        $sourcePage = WebPage::create([
            'domain_id' => $sourceDomain->id,
            'url' => 'https://github.com/web-search-org/search',
            'domain' => 'github.com',
            'title' => 'Web-Search GitHub Repository',
            'is_indexed' => true,
        ]);

        $targetPage = WebPage::create([
            'domain_id' => $targetDomain->id,
            'url' => 'https://web-search.org',
            'domain' => 'web-search.org',
            'title' => 'Web-Search Home',
            'is_indexed' => true,
        ]);

        $link = WebLink::create([
            'source_page_id' => $sourcePage->id,
            'source_url' => $sourcePage->url,
            'source_domain' => 'github.com',
            'target_page_id' => $targetPage->id,
            'target_url' => $targetPage->url,
            'target_domain' => 'web-search.org',
            'anchor_text' => 'Official Search Engine',
            'is_external' => true,
        ]);

        $this->assertTrue(Str::isUuid($link->id));
        $this->assertTrue($link->is_external);
        $this->assertEquals('Official Search Engine', $link->anchor_text);
    }

    public function test_search_console_links_api_endpoint(): void
    {
        $domain = Domain::create(['name' => 'laravel.com']);
        $targetPage = WebPage::create([
            'domain_id' => $domain->id,
            'url' => 'https://laravel.com/docs',
            'domain' => 'laravel.com',
            'title' => 'Laravel Docs',
            'is_indexed' => true,
        ]);

        WebLink::create([
            'source_url' => 'https://news.ycombinator.com/item?id=123',
            'source_domain' => 'news.ycombinator.com',
            'target_page_id' => $targetPage->id,
            'target_url' => 'https://laravel.com/docs',
            'target_domain' => 'laravel.com',
            'anchor_text' => 'Laravel Documentation',
            'is_external' => true,
        ]);

        $response = $this->getJson('/api/v1/console/links?domain=laravel.com');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'domain',
                'summary' => ['totalExternalLinks', 'totalLinkingDomains', 'totalInternalLinks'],
                'topLinkingDomains',
                'topLinkedPages',
                'topAnchorTexts',
                'topInternalPages',
                'recentLinks',
            ]);

        $this->assertEquals(1, $response->json('summary.totalExternalLinks'));
        $this->assertEquals('news.ycombinator.com', $response->json('topLinkingDomains.0.domain'));
    }

    public function test_search_api_endpoint_returns_json_results_with_uuids(): void
    {
        $domain = Domain::create([
            'name' => 'laravel.com',
            'protocol' => 'https',
            'domain_rank' => 9.5,
            'total_pages' => 1,
        ]);

        $page = WebPage::create([
            'domain_id' => $domain->id,
            'url' => 'https://laravel.com',
            'domain' => 'laravel.com',
            'title' => 'Laravel Framework',
            'description' => 'The PHP framework for web artisans',
            'body_text' => 'Modern scalable web application backend framework',
            'page_rank' => 9.5,
            'is_indexed' => true,
        ]);

        $response = $this->getJson('/api/v1/search?q=laravel');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'query',
                'totalHits',
                'page',
                'results' => [
                    '*' => [
                        'id',
                        'url',
                        'domain',
                        'title',
                        'snippet',
                    ]
                ]
            ]);

        $results = $response->json('results');
        $this->assertNotEmpty($results);
        $this->assertTrue(Str::isUuid($results[0]['id']));
    }

    public function test_crawler_ingest_endpoint_creates_document_and_domain(): void
    {
        $payload = [
            'url' => 'https://svelte.dev/docs',
            'domain' => 'svelte.dev',
            'title' => 'Svelte 5 Documentation',
            'description' => 'Documentation for runes and reactivity in Svelte',
            'content' => 'Complete guide to Svelte 5 state management',
            'keywords' => ['svelte', 'docs'],
            'headings' => ['Getting Started', 'Runes'],
            'response_time_ms' => 12.5,
            'status_code' => 200,
        ];

        $response = $this->postJson('/api/v1/crawl/ingest', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'url' => 'https://svelte.dev/docs',
            ]);

        $this->assertDatabaseHas('domains', ['name' => 'svelte.dev']);
        $this->assertDatabaseHas('web_pages', ['url' => 'https://svelte.dev/docs']);
    }

    public function test_search_console_url_inspection_api(): void
    {
        $domain = Domain::create([
            'name' => 'web-search.org',
            'protocol' => 'https',
            'domain_rank' => 10.0,
        ]);

        WebPage::create([
            'domain_id' => $domain->id,
            'url' => 'https://web-search.org/about',
            'domain' => 'web-search.org',
            'title' => 'About Web-Search.org',
            'description' => 'Privacy search engine information',
            'is_indexed' => true,
            'index_status' => 'indexed',
        ]);

        $response = $this->getJson('/api/v1/console/inspect?url=https://web-search.org/about');

        $response->assertStatus(200)
            ->assertJson([
                'url' => 'https://web-search.org/about',
                'isIndexed' => true,
                'verdict' => 'URL is on Web-Search.org',
            ])
            ->assertJsonStructure([
                'id',
                'coverage',
                'enhancements',
                'metadata',
            ]);
    }

    public function test_search_console_request_indexing_api(): void
    {
        $response = $this->postJson('/api/v1/console/request-indexing', [
            'url' => 'https://svelte.dev/tutorial',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'url' => 'https://svelte.dev/tutorial',
            ]);

        $this->assertDatabaseHas('crawl_jobs', [
            'seed_url' => 'https://svelte.dev/tutorial',
            'status' => 'queued',
        ]);
    }

    public function test_search_console_domain_verification_api(): void
    {
        $response = $this->postJson('/api/v1/console/verify-domain', [
            'domain' => 'my-open-source-project.org',
            'method' => 'meta_tag',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'verified' => true,
            ]);

        $this->assertDatabaseHas('domains', [
            'name' => 'my-open-source-project.org',
            'is_verified' => true,
        ]);
    }

    public function test_engine_stats_api_returns_metrics(): void
    {
        $response = $this->getJson('/api/v1/stats');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'totalDocuments',
                'totalDomains',
                'totalCrawledPages',
                'activeCrawlJobs',
                'averageQueryTimeMs',
                'systemHealth',
            ]);
    }

    public function test_robots_txt_parser_and_enforcement(): void
    {
        $robots = app(\App\Services\RobotsTxtService::class);
        $sample = "User-agent: *\nDisallow: /admin/\nDisallow: /private\nAllow: /public\nCrawl-delay: 3\nSitemap: https://test-site.org/sitemap.xml\n";

        $rules = $robots->parseRobotsTxt($sample);

        $this->assertCount(2, $rules['agents']['*']['disallow']);
        $this->assertCount(1, $rules['agents']['*']['allow']);
        $this->assertEquals(3.0, $rules['agents']['*']['crawl_delay']);
        $this->assertEquals(['https://test-site.org/sitemap.xml'], $rules['sitemaps']);
    }
}
