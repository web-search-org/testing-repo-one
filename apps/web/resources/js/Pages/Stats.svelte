<script>
    import { Link } from '@inertiajs/svelte';
    import Navbar from '../Components/Navbar.svelte';
    import Footer from '../Components/Footer.svelte';
    import { 
        Database, 
        Server, 
        Clock, 
        ShieldCheck, 
        Cpu, 
        Globe, 
        Activity, 
        CheckCircle2, 
        TrendingUp,
        Lock,
        ArrowUpRight
    } from 'lucide-svelte';

    let { insights = {} } = $props();

    const summary = $derived(insights.summary || {});
    const topDomains = $derived(insights.topDomains || []);
    const categories = $derived(insights.categories || []);
    const trendingQueries = $derived(insights.trendingQueries || []);
    const tlds = $derived(insights.tlds || []);
    const recentIndexed = $derived(insights.recentIndexed || []);
    const systemNodes = $derived(insights.systemNodes || []);
</script>

<svelte:head>
    <title>Open Engine Transparency & Live Stats - Web-Search.org</title>
</svelte:head>

<div class="min-h-screen flex flex-col bg-white dark:bg-black text-zinc-900 dark:text-zinc-100 transition-colors">
    <Navbar showSearch={true} />

    <main class="flex-1 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        <!-- Header -->
        <div class="space-y-1.5">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span class="text-xs font-bold uppercase tracking-wider text-zinc-400">Live Network Telemetry</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-black dark:text-white">
                Public Open Metrics & Transparency Insights
            </h1>
            <p class="text-xs sm:text-sm text-zinc-500 dark:text-zinc-400 max-w-2xl">
                Real-time open telemetry of our crawler network, BM25 search index, and privacy verification.
            </p>
        </div>

        <!-- 4 Key Verified Real Stat Cards (Monochrome) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <!-- Metric 1: Total Indexed Pages -->
            <div class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800">
                <div class="flex items-center justify-between text-zinc-400 mb-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider">Indexed Documents</span>
                    <Database class="w-4 h-4 text-black dark:text-white" />
                </div>
                <div class="text-2xl font-bold font-mono text-black dark:text-white">
                    {summary.totalPages || 0}
                </div>
                <div class="text-[11px] text-zinc-500 mt-1">
                    Live searchable pages
                </div>
            </div>

            <!-- Metric 2: Domains -->
            <div class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800">
                <div class="flex items-center justify-between text-zinc-400 mb-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider">Indexed Domains</span>
                    <Globe class="w-4 h-4 text-black dark:text-white" />
                </div>
                <div class="text-2xl font-bold font-mono text-black dark:text-white">
                    {summary.totalDomains || 0}
                </div>
                <div class="text-[11px] text-zinc-500 mt-1">
                    Unique web hostnames
                </div>
            </div>

            <!-- Metric 3: Active Crawler Status -->
            <div class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800">
                <div class="flex items-center justify-between text-zinc-400 mb-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider">Crawl Engine Queue</span>
                    <Cpu class="w-4 h-4 text-black dark:text-white" />
                </div>
                <div class="text-2xl font-bold font-mono text-black dark:text-white">
                    {summary.activeCrawlJobs || 0} active
                </div>
                <div class="text-[11px] text-zinc-500 mt-1">
                    {summary.completedCrawlJobs || 0} jobs completed
                </div>
            </div>

            <!-- Metric 4: Privacy Status -->
            <div class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800">
                <div class="flex items-center justify-between text-zinc-400 mb-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider">Privacy Rating</span>
                    <Lock class="w-4 h-4 text-black dark:text-white" />
                </div>
                <div class="text-2xl font-bold font-mono text-black dark:text-white">
                    100% Private
                </div>
                <div class="text-[11px] text-zinc-500 mt-1">
                    Zero user profiling
                </div>
            </div>
        </div>

        <!-- Two Column Metrics Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Top Indexed Domains -->
            <div class="p-5 rounded-xl bg-white dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800">
                <h2 class="text-xs font-bold uppercase tracking-wider text-black dark:text-white flex items-center gap-2 mb-3">
                    <Globe class="w-4 h-4" />
                    Top Crawled & Indexed Web Domains
                </h2>
                <div class="divide-y divide-zinc-100 dark:divide-zinc-800 text-xs">
                    {#each topDomains as d}
                        <div class="py-2.5 flex items-center justify-between">
                            <div class="flex items-center gap-2.5 truncate max-w-xs">
                                <span class="font-bold text-black dark:text-white font-mono truncate">{d.name}</span>
                            </div>
                            <div class="flex items-center gap-4 font-mono">
                                <span class="text-zinc-500">{d.total_pages} pages</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-zinc-100 dark:bg-zinc-900 text-black dark:text-white">
                                    PR {d.domain_rank}
                                </span>
                            </div>
                        </div>
                    {:else}
                        <div class="py-6 text-center text-xs text-zinc-400">
                            No domains indexed yet. Submit a website to begin!
                        </div>
                    {/each}
                </div>
            </div>

            <!-- Category Distribution -->
            <div class="p-5 rounded-xl bg-white dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800">
                <h2 class="text-xs font-bold uppercase tracking-wider text-black dark:text-white flex items-center gap-2 mb-3">
                    <Database class="w-4 h-4" />
                    Indexed Category Breakdown
                </h2>
                <div class="space-y-3">
                    {#each categories as cat}
                        <div>
                            <div class="flex justify-between text-xs mb-1 font-medium">
                                <span class="text-black dark:text-white">{cat.name}</span>
                                <span class="font-mono text-zinc-500">{cat.count} pages ({cat.percentage}%)</span>
                            </div>
                            <div class="w-full bg-zinc-100 dark:bg-zinc-900 h-1.5 rounded-full overflow-hidden">
                                <div class="bg-black dark:bg-white h-1.5 rounded-full" style={`width: ${cat.percentage}%`}></div>
                            </div>
                        </div>
                    {:else}
                        <div class="py-6 text-center text-xs text-zinc-400">
                            Category distribution will populate as pages are crawled.
                        </div>
                    {/each}
                </div>
            </div>
        </div>

        <!-- Live Ingest Feed -->
        <div class="bg-white dark:bg-zinc-950 rounded-xl border border-zinc-200 dark:border-zinc-800 overflow-hidden">
            <div class="p-4 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between">
                <h2 class="text-xs font-bold uppercase tracking-wider text-black dark:text-white flex items-center gap-2">
                    <Activity class="w-4 h-4" />
                    Live Ingested Documents
                </h2>
                <span class="text-[11px] font-mono text-zinc-400">Real-time DB stream</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-zinc-50 dark:bg-black text-zinc-500 border-b border-zinc-200 dark:border-zinc-800">
                        <tr>
                            <th class="px-4 py-2.5 font-medium">Document Title & URL</th>
                            <th class="px-3 py-2.5 font-medium">Category</th>
                            <th class="px-3 py-2.5 font-medium">HTTP</th>
                            <th class="px-3 py-2.5 font-medium">Fetch Latency</th>
                            <th class="px-3 py-2.5 font-medium">Rank</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800 font-mono">
                        {#each recentIndexed as page}
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-900/40 transition-colors">
                                <td class="px-4 py-2.5 font-sans font-medium text-black dark:text-white max-w-sm truncate">
                                    <div class="truncate">{page.title || page.url}</div>
                                    <div class="text-[10px] text-zinc-400 truncate">{page.url}</div>
                                </td>
                                <td class="px-3 py-2.5">
                                    <span class="px-1.5 py-0.5 rounded text-[10px] uppercase font-sans font-medium bg-zinc-100 dark:bg-zinc-900 text-zinc-700 dark:text-zinc-300">
                                        {page.category || 'all'}
                                    </span>
                                </td>
                                <td class="px-3 py-2.5 text-emerald-600 dark:text-emerald-400 font-bold">
                                    {page.http_status || 200}
                                </td>
                                <td class="px-3 py-2.5 text-zinc-500">
                                    {page.response_time_ms} ms
                                </td>
                                <td class="px-3 py-2.5 font-bold text-black dark:text-white">
                                    {page.page_rank}
                                </td>
                            </tr>
                        {:else}
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-zinc-400 font-sans text-xs">
                                    No indexed documents in database yet.
                                </td>
                            </tr>
                        {/each}
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <Footer />
</div>
