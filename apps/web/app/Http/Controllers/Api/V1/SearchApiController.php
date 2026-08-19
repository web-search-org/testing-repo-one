<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CrawlJob;
use App\Models\Domain;
use App\Models\Sitemap;
use App\Services\SearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SearchApiController extends Controller
{
    public function __construct(
        protected SearchService $searchService
    ) {}

    /**
     * Search endpoint: GET /api/v1/search
     */
    public function search(Request $request): JsonResponse
    {
        $params = $request->validate([
            'q' => 'required|string|max:500',
            'category' => 'nullable|string|in:all,images,news,videos,tech,code',
            'page' => 'nullable|integer|min:1',
            'perPage' => 'nullable|integer|min:1|max:100',
            'lang' => 'nullable|string|max:10',
            'country' => 'nullable|string|max:10',
            'safeSearch' => 'nullable|string|in:strict,moderate,off',
        ]);

        $results = $this->searchService->search($params);

        return response()->json($results);
    }

    /**
     * Suggest endpoint: GET /api/v1/suggest
     */
    public function suggest(Request $request): JsonResponse
    {
        $q = $request->input('q', '');
        $suggestions = $this->searchService->suggest($q);

        return response()->json([
            'query' => $q,
            'suggestions' => $suggestions,
        ]);
    }

    /**
     * Random search word: GET /api/v1/search/random
     */
    public function random(Request $request): JsonResponse
    {
        $word = $this->searchService->getRandomWord();

        return response()->json([
            'word' => $word,
            'url' => url('/search?q=' . urlencode($word)),
        ]);
    }

    /**
     * Public submit website endpoint: POST /api/v1/submit
     */
    public function submitSite(Request $request): JsonResponse
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
            'max_depth' => $isSitemap ? 2 : 2,
            'max_pages' => $validated['max_pages'] ?? 50,
            'concurrency' => 3,
            'metadata' => [
                'category' => $validated['category'] ?? 'all',
                'source' => 'public_website_submission',
                'is_sitemap' => $isSitemap,
            ],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Website successfully submitted and queued for crawler indexing.',
            'jobId' => $job->id,
            'domain' => $domain->name,
            'url' => $url,
            'status' => 'queued',
        ], 201);
    }
}
