<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CrawlJob;
use App\Models\Domain;
use App\Models\WebPage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CrawlApiController extends Controller
{
    /**
     * Submit a crawl request: POST /api/v1/crawl
     */
    public function submit(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'url' => 'required|url|max:2048',
            'maxDepth' => 'nullable|integer|min:1|max:10',
            'maxPages' => 'nullable|integer|min:1|max:5000',
            'concurrency' => 'nullable|integer|min:1|max:30',
        ]);

        $job = CrawlJob::create([
            'id' => (string) Str::uuid(),
            'seed_url' => $validated['url'],
            'status' => 'queued',
            'max_depth' => $validated['maxDepth'] ?? 3,
            'max_pages' => $validated['maxPages'] ?? 200,
            'concurrency' => $validated['concurrency'] ?? 5,
        ]);

        return response()->json($job, 201);
    }

    /**
     * Get crawl status: GET /api/v1/crawl/status/{id}
     */
    public function status(string $id): JsonResponse
    {
        $job = CrawlJob::find($id);

        if (!$job) {
            return response()->json(['message' => 'Crawl job not found'], 404);
        }

        $progress = $job->max_pages > 0 ? round(($job->pages_crawled / $job->max_pages) * 100, 1) : 0;

        return response()->json([
            'id' => $job->id,
            'seedUrl' => $job->seed_url,
            'status' => $job->status,
            'pagesCrawled' => $job->pages_crawled,
            'pagesDiscovered' => $job->pages_discovered,
            'pagesIndexed' => $job->pages_indexed,
            'errorsCount' => $job->errors_count,
            'progressPercent' => min(100, $progress),
            'startedAt' => $job->started_at?->toIso8601String(),
            'finishedAt' => $job->finished_at?->toIso8601String(),
            'createdAt' => $job->created_at->toIso8601String(),
            'updatedAt' => $job->updated_at->toIso8601String(),
        ]);
    }

    /**
     * Ingestion endpoint from Python crawler worker: POST /api/v1/crawl/ingest
     */
    public function ingest(Request $request): JsonResponse
    {
        $data = $request->validate([
            'url' => 'required|string|max:2048',
            'domain' => 'required|string|max:255',
            'title' => 'nullable|string|max:1000',
            'description' => 'nullable|string',
            'keywords' => 'nullable|array',
            'headings' => 'nullable|array',
            'content' => 'nullable|string',
            'language' => 'nullable|string|max:10',
            'favicon_url' => 'nullable|string',
            'og_image' => 'nullable|string',
            'canonical_url' => 'nullable|string',
            'outbound_links_count' => 'nullable|integer',
            'content_hash' => 'nullable|string|max:64',
            'status_code' => 'nullable|integer',
            'response_time_ms' => 'nullable|numeric',
        ]);

        // Find or create domain
        $domain = Domain::firstOrCreate(
            ['name' => strtolower($data['domain'])],
            [
                'protocol' => parse_url($data['url'], PHP_URL_SCHEME) ?: 'https',
                'favicon_url' => $data['favicon_url'] ?? null,
                'crawl_status' => 'idle',
                'last_crawled_at' => now(),
            ]
        );
        $domain->increment('total_pages');
        $domain->update(['last_crawled_at' => now()]);

        // Detect category (e.g. tech, code, news)
        $category = 'all';
        $fullText = strtolower(($data['title'] ?? '') . ' ' . ($data['content'] ?? ''));
        if (str_contains($fullText, 'github') || str_contains($fullText, 'repository') || str_contains($fullText, 'documentation') || str_contains($fullText, 'api reference') || str_contains($fullText, 'source code')) {
            $category = 'code';
        } elseif (str_contains($fullText, 'technology') || str_contains($fullText, 'software') || str_contains($fullText, 'open source') || str_contains($fullText, 'programming') || str_contains($fullText, 'developer')) {
            $category = 'tech';
        }

        // Upsert WebPage
        $page = WebPage::updateOrCreate(
            ['url' => $data['url']],
            [
                'domain_id' => $domain->id,
                'domain' => strtolower($data['domain']),
                'title' => $data['title'] ?: $data['domain'],
                'description' => $data['description'] ?? null,
                'keywords' => $data['keywords'] ?? [],
                'headings' => $data['headings'] ?? [],
                'body_text' => $data['content'] ?? '',
                'category' => $category,
                'language' => $data['language'] ?? 'en',
                'content_hash' => $data['content_hash'] ?? null,
                'canonical_url' => $data['canonical_url'] ?? null,
                'favicon_url' => $data['favicon_url'] ?? null,
                'og_image' => $data['og_image'] ?? null,
                'http_status' => $data['status_code'] ?? 200,
                'response_time_ms' => $data['response_time_ms'] ?? 0,
                'out_links_count' => $data['outbound_links_count'] ?? 0,
                'is_indexed' => true,
                'crawled_at' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'id' => $page->id,
            'url' => $page->url,
        ], 201);
    }
}
