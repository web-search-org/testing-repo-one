<?php

namespace App\Http\Controllers;

use App\Models\CrawlJob;
use App\Models\Domain;
use App\Models\WebPage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CrawlerController extends Controller
{
    /**
     * Display the Crawler Management Dashboard
     */
    public function dashboard(): Response
    {
        $jobs = CrawlJob::orderByDesc('created_at')->limit(20)->get();
        $domains = Domain::orderByDesc('total_pages')->limit(15)->get();

        $metrics = [
            'totalCrawled' => WebPage::count(),
            'totalIndexed' => WebPage::where('is_indexed', true)->count(),
            'totalDomains' => Domain::count(),
            'activeJobs' => CrawlJob::where('status', 'running')->count(),
            'queuedJobs' => CrawlJob::where('status', 'queued')->count(),
        ];

        return Inertia::render('Crawler/Dashboard', [
            'jobs' => $jobs,
            'domains' => $domains,
            'metrics' => $metrics,
        ]);
    }

    /**
     * Submit a new crawl request via UI
     */
    public function submitJob(Request $request)
    {
        $validated = $request->validate([
            'seed_url' => 'required|url|max:2048',
            'max_depth' => 'nullable|integer|min:1|max:10',
            'max_pages' => 'nullable|integer|min:1|max:5000',
            'concurrency' => 'nullable|integer|min:1|max:30',
        ]);

        $job = CrawlJob::create([
            'id' => (string) Str::uuid(),
            'seed_url' => $validated['seed_url'],
            'status' => 'queued',
            'max_depth' => $validated['max_depth'] ?? 3,
            'max_pages' => $validated['max_pages'] ?? 200,
            'concurrency' => $validated['concurrency'] ?? 5,
        ]);

        return redirect()->route('crawler.dashboard')->with('success', 'Crawl job queued successfully: ' . $job->id);
    }
}
