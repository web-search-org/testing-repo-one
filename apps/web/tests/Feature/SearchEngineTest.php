<?php

namespace Tests\Feature;

use App\Models\Domain;
use App\Models\WebPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    public function test_search_api_endpoint_returns_json_results(): void
    {
        $domain = Domain::create([
            'name' => 'laravel.com',
            'protocol' => 'https',
            'domain_rank' => 9.5,
            'total_pages' => 1,
        ]);

        WebPage::create([
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
