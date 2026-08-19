<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Services\SearchConsoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConsoleApiController extends Controller
{
    public function __construct(
        protected SearchConsoleService $consoleService
    ) {}

    /**
     * Inspect URL: GET /api/v1/console/inspect?url=https://example.com
     */
    public function inspect(Request $request): JsonResponse
    {
        $request->validate(['url' => 'required|url']);
        $data = $this->consoleService->inspectUrl($request->input('url'));
        return response()->json($data);
    }

    /**
     * Request Indexing: POST /api/v1/console/request-indexing
     */
    public function requestIndexing(Request $request): JsonResponse
    {
        $request->validate(['url' => 'required|url']);
        $result = $this->consoleService->requestIndexing($request->input('url'));
        return response()->json($result);
    }

    /**
     * Get Performance Metrics: GET /api/v1/console/performance?domain=example.com
     */
    public function performance(Request $request): JsonResponse
    {
        $domainName = $request->input('domain', 'web-search.org');
        $domain = Domain::where('name', $domainName)->first();

        if (!$domain) {
            return response()->json(['message' => 'Domain property not found'], 404);
        }

        $metrics = $this->consoleService->getPerformanceMetrics($domain, $request->input('period', '28d'));
        return response()->json($metrics);
    }

    /**
     * Get Interlinking & Backlinks Report: GET /api/v1/console/links?domain=example.com
     */
    public function links(Request $request): JsonResponse
    {
        $domainName = $request->input('domain', 'web-search.org');
        $domain = Domain::where('name', $domainName)->first();

        if (!$domain) {
            return response()->json(['message' => 'Domain property not found'], 404);
        }

        $links = $this->consoleService->getLinksReport($domain);
        return response()->json($links);
    }

    /**
     * Verify Domain: POST /api/v1/console/verify-domain
     */
    public function verifyDomain(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'domain' => 'required|string',
            'method' => 'nullable|string|in:dns_txt,meta_tag,file_upload',
        ]);

        $res = $this->consoleService->verifyDomain($validated['domain'], $validated['method'] ?? 'meta_tag');
        return response()->json($res, $res['verified'] ? 200 : 422);
    }
}
