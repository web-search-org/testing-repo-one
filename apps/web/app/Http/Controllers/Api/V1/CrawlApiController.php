<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CrawlJob;
use App\Models\Domain;
use App\Models\WebLink;
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
            'outbound_links' => 'nullable|array',
            'outbound_links_count' => 'nullable|integer',
            'content_hash' => 'nullable|string|max:64',
            'status_code' => 'nullable|integer',
            'response_time_ms' => 'nullable|numeric',
        ]);

        $pageDomain = strtolower($data['domain']);

        // Find or create domain
        $domain = Domain::firstOrCreate(
            ['name' => $pageDomain],
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
                'domain' => $pageDomain,
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
                'out_links_count' => $data['outbound_links_count'] ?? (isset($data['outbound_links']) ? count($data['outbound_links']) : 0),
                'is_indexed' => true,
                'crawled_at' => now(),
            ]
        );

        // Process Outbound Links & Auto-Queue Discovered URLs
        if (!empty($data['outbound_links'])) {
            foreach ($data['outbound_links'] as $link) {
                $targetUrl = is_array($link) ? ($link['url'] ?? '') : (string) $link;
                if (empty($targetUrl)) continue;

                $targetParsed = parse_url($targetUrl);
                $targetDomain = strtolower($targetParsed['host'] ?? '');
                if (empty($targetDomain)) continue;

                $isExternal = ($targetDomain !== $pageDomain);
                $anchor = is_array($link) ? ($link['anchor'] ?? '') : parse_url($targetUrl, PHP_URL_PATH);

                WebLink::updateOrCreate(
                    [
                        'source_url' => $data['url'],
                        'target_url' => $targetUrl,
                    ],
                    [
                        'source_page_id' => $page->id,
                        'source_domain' => $pageDomain,
                        'target_domain' => $targetDomain,
                        'anchor_text' => substr($anchor ?: 'Link', 0, 100),
                        'is_external' => $isExternal,
                    ]
                );

                if (!$isExternal) {
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
                                'source' => 'api_internal_link_discovery',
                                'referred_by' => $data['url'],
                            ],
                        ]);
                    }
                } else {
                    Domain::firstOrCreate(
                        ['name' => $targetDomain],
                        [
                            'protocol' => $targetParsed['scheme'] ?? 'https',
                            'verification_token' => 'web-search-site-verification=' . Str::random(32),
                            'is_verified' => false,
                            'crawl_status' => 'idle',
                        ]
                    );

                    $targetSeedUrl = "{$targetParsed['scheme']}://{$targetDomain}/";
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
                                'source' => 'api_external_link_discovery',
                                'referred_by' => $data['url'],
                            ],
                        ]);
                    }
                }
            }
        }

        // Update PageRank
        $inLinksCount = WebLink::where('target_url', $page->url)->count();
        $page->update([
            'in_links_count' => $inLinksCount,
            'page_rank' => round(min(10.0, 1.0 + ($inLinksCount * 0.5)), 1),
        ]);

        return response()->json([
            'success' => true,
            'id' => $page->id,
            'url' => $page->url,
        ], 201);
    }
}
