<?php

namespace App\Http\Controllers;

use App\Services\SearchService;
use App\Models\WebPage;
use App\Models\Domain;
use App\Models\CrawlJob;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SearchController extends Controller
{
    public function __construct(
        protected SearchService $searchService
    ) {}

    /**
     * Home page
     */
    public function home(): Response
    {
        $stats = [
            'total_pages' => WebPage::where('is_indexed', true)->count(),
            'total_domains' => Domain::count(),
        ];

        return Inertia::render('Home', [
            'stats' => $stats,
        ]);
    }

    /**
     * Search results page (SERP)
     */
    public function search(Request $request): Response
    {
        $query = $request->input('q', '');
        $category = $request->input('category', 'all');
        $page = (int) $request->input('page', 1);

        if (empty(trim($query))) {
            return Inertia::render('Home');
        }

        $searchData = $this->searchService->search([
            'q' => $query,
            'category' => $category,
            'page' => $page,
            'lang' => $request->input('lang'),
        ]);

        return Inertia::render('Search', [
            'searchData' => $searchData,
            'currentQuery' => $query,
            'currentCategory' => $category,
            'currentPage' => $page,
        ]);
    }

    /**
     * Autocomplete suggestions API
     */
    public function suggest(Request $request)
    {
        $q = $request->input('q', '');
        $suggestions = $this->searchService->suggest($q);

        return response()->json([
            'query' => $q,
            'suggestions' => $suggestions,
        ]);
    }

    /**
     * Open Metrics & Statistics page
     */
    public function stats(): Response
    {
        $totalPages = WebPage::where('is_indexed', true)->count();
        $totalDomains = Domain::count();
        $activeCrawlJobs = CrawlJob::whereIn('status', ['running', 'queued'])->count();
        $recentIndexed = WebPage::orderByDesc('id')->limit(10)->get(['id', 'title', 'url', 'domain', 'created_at']);

        return Inertia::render('Stats', [
            'stats' => [
                'totalPages' => $totalPages,
                'totalDomains' => $totalDomains,
                'activeCrawlJobs' => $activeCrawlJobs,
                'uptimeSeconds' => 124500,
                'recentIndexed' => $recentIndexed,
            ],
        ]);
    }

    /**
     * API Documentation page
     */
    public function docs(): Response
    {
        return Inertia::render('Docs');
    }
}
