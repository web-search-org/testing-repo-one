<?php

namespace App\Http\Controllers;

use App\Models\Domain;
use App\Models\Sitemap;
use App\Models\WebPage;
use App\Services\SearchConsoleService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ConsoleController extends Controller
{
    public function __construct(
        protected SearchConsoleService $consoleService
    ) {}

    /**
     * Search Console Main Dashboard
     */
    public function dashboard(Request $request): Response
    {
        $domains = Domain::orderByDesc('domain_rank')->get();
        $currentDomain = null;

        if ($request->filled('domain')) {
            $currentDomain = Domain::where('name', $request->input('domain'))->first();
        }

        if (!$currentDomain) {
            $currentDomain = $domains->first();
        }

        $performance = $currentDomain ? $this->consoleService->getPerformanceMetrics($currentDomain) : [
            'domain' => '',
            'summary' => ['totalClicks' => 0, 'totalImpressions' => 0, 'averageCtr' => 0, 'averagePosition' => 0],
            'queries' => [],
            'pages' => [],
        ];

        $coverage = $currentDomain ? $this->consoleService->getCoverageReport($currentDomain) : [
            'validIndexed' => 0,
            'excluded' => 0,
            'errors' => 0,
            'warnings' => 0,
            'breakdown' => [],
        ];

        $sitemaps = $currentDomain ? Sitemap::where('domain_id', $currentDomain->id)->get() : [];

        return Inertia::render('Console/Dashboard', [
            'domains' => $domains,
            'currentDomain' => $currentDomain,
            'performance' => $performance,
            'coverage' => $coverage,
            'sitemaps' => $sitemaps,
        ]);
    }

    /**
     * URL Inspection View
     */
    public function inspect(Request $request): Response
    {
        $url = $request->input('url', '');
        $inspectionData = !empty($url) ? $this->consoleService->inspectUrl($url) : null;
        $domains = Domain::all();

        return Inertia::render('Console/Inspect', [
            'url' => $url,
            'inspection' => $inspectionData,
            'domains' => $domains,
        ]);
    }

    /**
     * Request Indexing Action
     */
    public function requestIndexing(Request $request)
    {
        $request->validate(['url' => 'required|url']);
        $result = $this->consoleService->requestIndexing($request->input('url'));

        return back()->with('success', $result['message']);
    }

    /**
     * Performance Analytics View
     */
    public function performance(Request $request): Response
    {
        $domains = Domain::all();
        $domainName = $request->input('domain');
        $domain = $domainName ? Domain::where('name', $domainName)->first() : $domains->first();

        $performanceData = $domain ? $this->consoleService->getPerformanceMetrics($domain) : [
            'domain' => '',
            'summary' => ['totalClicks' => 0, 'totalImpressions' => 0, 'averageCtr' => 0, 'averagePosition' => 0],
            'queries' => [],
            'pages' => [],
        ];

        return Inertia::render('Console/Performance', [
            'currentDomain' => $domain,
            'performance' => $performanceData,
            'domains' => $domains,
        ]);
    }

    /**
     * Sitemaps View
     */
    public function sitemaps(Request $request): Response
    {
        $domains = Domain::all();
        $domainName = $request->input('domain');
        $domain = $domainName ? Domain::where('name', $domainName)->first() : $domains->first();
        $sitemaps = $domain ? Sitemap::where('domain_id', $domain->id)->orderByDesc('created_at')->get() : [];

        return Inertia::render('Console/Sitemaps', [
            'currentDomain' => $domain,
            'sitemaps' => $sitemaps,
            'domains' => $domains,
        ]);
    }

    /**
     * Submit Sitemap Action
     */
    public function submitSitemap(Request $request)
    {
        $validated = $request->validate([
            'domain_id' => 'required|uuid|exists:domains,id',
            'sitemap_url' => 'required|url',
        ]);

        $domain = Domain::findOrFail($validated['domain_id']);
        $this->consoleService->submitSitemap($domain, $validated['sitemap_url']);

        return back()->with('success', 'Sitemap submitted and queued for crawling.');
    }

    /**
     * Verify Domain Action
     */
    public function verifyDomain(Request $request)
    {
        $validated = $request->validate([
            'domain' => 'required|string',
            'method' => 'nullable|string|in:dns_txt,meta_tag,file_upload',
        ]);

        $res = $this->consoleService->verifyDomain($validated['domain'], $validated['method'] ?? 'meta_tag');

        return back()->with($res['verified'] ? 'success' : 'error', $res['message']);
    }
}
