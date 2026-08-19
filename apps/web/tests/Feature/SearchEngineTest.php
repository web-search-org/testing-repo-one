<?php

namespace Tests\Feature;

use App\Models\Domain;
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
}
