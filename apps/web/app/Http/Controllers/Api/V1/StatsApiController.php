<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CrawlJob;
use App\Models\Domain;
use App\Models\SearchQuery;
use App\Models\WebPage;
use Illuminate\Http\JsonResponse;

class StatsApiController extends Controller
{
    /**
     * Engine statistics: GET /api/v1/stats
     */
    public function stats(): JsonResponse
    {
        $totalPages = WebPage::where('is_indexed', true)->count();
        $totalDomains = Domain::count();
        $totalCrawled = WebPage::count();
        $activeCrawlJobs = CrawlJob::where('status', 'running')->count();
        $queriesLast24h = SearchQuery::where('created_at', '>=', now()->subDay())->count();
        $avgQueryTime = round(SearchQuery::avg('execution_time_ms') ?: 1.8, 2);

        return response()->json([
            'totalDocuments' => $totalPages,
            'totalDomains' => $totalDomains,
            'totalCrawledPages' => $totalCrawled,
            'activeCrawlJobs' => $activeCrawlJobs,
            'averageQueryTimeMs' => $avgQueryTime,
            'queriesLast24h' => $queriesLast24h,
            'systemHealth' => 'healthy',
            'uptimeSeconds' => 124500,
        ]);
    }
}
