<script>
    import { Link } from '@inertiajs/svelte';
    import Navbar from '../Components/Navbar.svelte';
    import Footer from '../Components/Footer.svelte';
    import { 
        BarChart3, 
        Database, 
        Globe, 
        Server, 
        Activity, 
        ShieldCheck, 
        Zap, 
        TrendingUp, 
        RefreshCw, 
        Download, 
        ExternalLink, 
        CheckCircle2, 
        Layers, 
        Cpu, 
        Clock,
        Sparkles
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
    <title>Open Insights & Live Transparency - Web-Search.org</title>
</svelte:head>

<div class="min-h-screen flex flex-col bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 transition-colors">
    <Navbar showSearch={true} />

    <main class="flex-1 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-10 space-y-10">
        <!-- Hero Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 pb-6 border-b border-slate-200/80 dark:border-slate-800/80">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-400 border border-indigo-200/60 dark:border-indigo-900/60 mb-3">
                    <Sparkles class="w-3.5 h-3.5" />
                    Public Transparency Report • Open Telemetry
                </div>
                <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900 dark:text-white flex items-center gap-3">
                    Search Engine Insights & Live Stats
                </h1>
                <p class="mt-1.5 text-sm text-slate-500 dark:text-slate-400 max-w-2xl">
                    Real-time operational metrics, crawl index growth, search query throughput, and zero-tracking privacy verification. Open for everyone to inspect.
                </p>
            </div>

            <div class="flex items-center gap-3 self-start md:self-auto">
                <button
                    onclick={() => window.location.reload()}
                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors shadow-2xs cursor-pointer"
                >
                    <RefreshCw class="w-3.5 h-3.5" />
                    Live Refresh
                </button>

                <a
                    href="/api/v1/stats"
                    target="_blank"
                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-md shadow-indigo-600/20 transition-colors"
                >
                    <Download class="w-3.5 h-3.5" />
                    Raw JSON API
                </a>
            </div>
        </div>

        <!-- 6-Metric Highlights Grid -->
        <div class="grid grid-cols-2 lg:grid-cols-6 gap-4">
            <div class="p-4.5 rounded-2xl bg-white dark:bg-slate-900/80 border border-slate-200/80 dark:border-slate-800/80 shadow-2xs">
                <div class="flex items-center justify-between text-slate-400 mb-1">
                    <span class="text-[11px] font-semibold uppercase tracking-wider">Search Index</span>
                    <Database class="w-4 h-4 text-indigo-500" />
                </div>
                <div class="text-2xl font-bold font-mono text-indigo-600 dark:text-indigo-400">
                    {summary.totalPages || 0}
                </div>
                <span class="text-[11px] text-slate-400">Indexed documents</span>
            </div>

            <div class="p-4.5 rounded-2xl bg-white dark:bg-slate-900/80 border border-slate-200/80 dark:border-slate-800/80 shadow-2xs">
                <div class="flex items-center justify-between text-slate-400 mb-1">
                    <span class="text-[11px] font-semibold uppercase tracking-wider">Indexed Hosts</span>
                    <Globe class="w-4 h-4 text-purple-500" />
                </div>
                <div class="text-2xl font-bold font-mono text-purple-600 dark:text-purple-400">
                    {summary.totalDomains || 0}
                </div>
                <span class="text-[11px] text-slate-400">{summary.verifiedDomains || 0} verified</span>
            </div>

            <div class="p-4.5 rounded-2xl bg-white dark:bg-slate-900/80 border border-slate-200/80 dark:border-slate-800/80 shadow-2xs">
                <div class="flex items-center justify-between text-slate-400 mb-1">
                    <span class="text-[11px] font-semibold uppercase tracking-wider">Avg Query Time</span>
                    <Zap class="w-4 h-4 text-amber-500" />
                </div>
                <div class="text-2xl font-bold font-mono text-amber-500">
                    {summary.averageQueryTimeMs || 0} <span class="text-xs font-normal">ms</span>
                </div>
                <span class="text-[11px] text-slate-400">BM25 Ranker latency</span>
            </div>

            <div class="p-4.5 rounded-2xl bg-white dark:bg-slate-900/80 border border-slate-200/80 dark:border-slate-800/80 shadow-2xs">
                <div class="flex items-center justify-between text-slate-400 mb-1">
                    <span class="text-[11px] font-semibold uppercase tracking-wider">Queries Served</span>
                    <TrendingUp class="w-4 h-4 text-emerald-500" />
                </div>
                <div class="text-2xl font-bold font-mono text-slate-900 dark:text-white">
                    {summary.totalQueries || 0}
                </div>
                <span class="text-[11px] text-emerald-600">+{summary.queriesLast24h || 0} past 24h</span>
            </div>

            <div class="p-4.5 rounded-2xl bg-white dark:bg-slate-900/80 border border-slate-200/80 dark:border-slate-800/80 shadow-2xs">
                <div class="flex items-center justify-between text-slate-400 mb-1">
                    <span class="text-[11px] font-semibold uppercase tracking-wider">Crawl Engine</span>
                    <Cpu class="w-4 h-4 text-cyan-500" />
                </div>
                <div class="text-2xl font-bold font-mono text-cyan-600 dark:text-cyan-400">
                    {summary.completedCrawlJobs || 0}
                </div>
                <span class="text-[11px] text-slate-400">{summary.activeCrawlJobs || 0} active workers</span>
            </div>

            <div class="p-4.5 rounded-2xl bg-white dark:bg-slate-900/80 border border-slate-200/80 dark:border-slate-800/80 shadow-2xs">
                <div class="flex items-center justify-between text-slate-400 mb-1">
                    <span class="text-[11px] font-semibold uppercase tracking-wider">Privacy Rating</span>
                    <ShieldCheck class="w-4 h-4 text-emerald-500" />
                </div>
                <div class="text-xl font-bold font-mono text-emerald-600 dark:text-emerald-400">
                    100% Private
                </div>
                <span class="text-[11px] text-emerald-600">0 IP logs stored</span>
            </div>
        </div>

        <!-- Middle Section: Top Domains & Category / TLD Distributions -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Top Indexed Domains (2 Cols) -->
            <div class="lg:col-span-2 bg-white dark:bg-slate-900/80 rounded-3xl border border-slate-200/80 dark:border-slate-800/80 shadow-2xs overflow-hidden">
                <div class="p-5 border-b border-slate-200/80 dark:border-slate-800/80 flex items-center justify-between">
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <Globe class="w-4 h-4 text-indigo-600" />
                        Top Indexed Domain Properties
                    </h2>
                    <span class="text-xs text-slate-400 font-mono">{topDomains.length} domains</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 dark:bg-slate-950/60 text-slate-500 border-b border-slate-200/80 dark:border-slate-800/80">
                            <tr>
                                <th class="px-5 py-3 font-medium">Domain Name</th>
                                <th class="px-4 py-3 font-medium">Status</th>
                                <th class="px-4 py-3 font-medium">Pages Indexed</th>
                                <th class="px-4 py-3 font-medium">Domain Rank</th>
                                <th class="px-4 py-3 font-medium">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                            {#each topDomains as d}
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                                    <td class="px-5 py-3.5 font-medium text-slate-900 dark:text-white">
                                        <div class="flex items-center gap-2.5">
                                            <img
                                                src={d.favicon_url || `https://www.google.com/s2/favicons?domain=${d.name}&sz=32`}
                                                alt=""
                                                class="w-4 h-4 rounded-full"
                                                onerror={(e) => { e.currentTarget.style.display = 'none'; }}
                                            />
                                            <span class="font-bold">{d.name}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3.5">
                                        {#if d.is_verified}
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-400">
                                                <CheckCircle2 class="w-3 h-3" />
                                                Verified
                                            </span>
                                        {:else}
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                                                Indexed
                                            </span>
                                        {/if}
                                    </td>
                                    <td class="px-4 py-3.5 font-mono text-indigo-600 dark:text-indigo-400 font-bold">
                                        {d.total_pages}
                                    </td>
                                    <td class="px-4 py-3.5 font-mono text-slate-600 dark:text-slate-300">
                                        {d.domain_rank} / 10
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <a
                                            href={`/search?q=site:${d.name}`}
                                            class="inline-flex items-center gap-1 text-indigo-600 dark:text-indigo-400 hover:underline text-[11px]"
                                        >
                                            <span>Search</span>
                                            <ExternalLink class="w-3 h-3" />
                                        </a>
                                    </td>
                                </tr>
                            {:else}
                                <tr>
                                    <td colspan="5" class="px-5 py-8 text-center text-slate-400 font-sans">
                                        No domain properties indexed yet. Start by crawling a website.
                                    </td>
                                </tr>
                            {/each}
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Category & TLD Breakdown Column -->
            <div class="space-y-6">
                <!-- Category Breakdown Card -->
                <div class="p-5 rounded-3xl bg-white dark:bg-slate-900/80 border border-slate-200/80 dark:border-slate-800/80 shadow-2xs">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2 mb-4">
                        <Layers class="w-4 h-4 text-indigo-600" />
                        Index Category Breakdown
                    </h3>

                    <div class="space-y-3">
                        {#each categories as cat}
                            <div>
                                <div class="flex items-center justify-between text-xs mb-1">
                                    <span class="font-medium text-slate-700 dark:text-slate-300">{cat.name}</span>
                                    <span class="font-mono text-slate-500">{cat.count} pages ({cat.percentage}%)</span>
                                </div>
                                <div class="w-full h-2 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                                    <div
                                        class="h-full bg-gradient-to-r from-indigo-500 to-purple-500 rounded-full"
                                        style={`width: ${cat.percentage}%`}
                                    ></div>
                                </div>
                            </div>
                        {:else}
                            <div class="text-center text-xs text-slate-400 py-4">No indexed documents yet.</div>
                        {/each}
                    </div>
                </div>

                <!-- TLD Distribution Card -->
                <div class="p-5 rounded-3xl bg-white dark:bg-slate-900/80 border border-slate-200/80 dark:border-slate-800/80 shadow-2xs">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2 mb-4">
                        <Globe class="w-4 h-4 text-indigo-600" />
                        Top TLD Distribution
                    </h3>

                    <div class="flex flex-wrap gap-2">
                        {#each tlds as t}
                            <div class="px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200/60 dark:border-slate-800/60 text-xs">
                                <span class="font-mono font-bold text-indigo-600 dark:text-indigo-400">{t.tld}</span>
                                <span class="text-slate-500 ml-1">({t.percentage}%)</span>
                            </div>
                        {:else}
                            <div class="text-center text-xs text-slate-400 py-4 w-full">No domains recorded.</div>
                        {/each}
                    </div>
                </div>
            </div>
        </div>

        <!-- Trending Public Queries & Node Status Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Trending Queries (Anonymized) -->
            <div class="p-6 rounded-3xl bg-white dark:bg-slate-900/80 border border-slate-200/80 dark:border-slate-800/80 shadow-2xs">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2 mb-4">
                    <TrendingUp class="w-4 h-4 text-indigo-600" />
                    Trending Search Topics (Anonymized)
                </h3>

                <div class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    {#each trendingQueries as tq}
                        <div class="py-3 flex items-center justify-between text-xs">
                            <a
                                href={`/search?q=${encodeURIComponent(tq.query)}`}
                                class="font-medium text-slate-800 dark:text-slate-200 hover:text-indigo-600 hover:underline flex items-center gap-2"
                            >
                                <span>{tq.query}</span>
                            </a>
                            <div class="flex items-center gap-3 font-mono">
                                <span class="text-indigo-600 dark:text-indigo-400 font-bold">{tq.searches} searches</span>
                                <span class="text-slate-400 text-[11px]">{tq.avgTime} ms</span>
                            </div>
                        </div>
                    {:else}
                        <div class="text-center text-xs text-slate-400 py-8">
                            No search queries logged yet. Search terms will appear here in real time.
                        </div>
                    {/each}
                </div>
            </div>

            <!-- System Node Health -->
            <div class="p-6 rounded-3xl bg-white dark:bg-slate-900/80 border border-slate-200/80 dark:border-slate-800/80 shadow-2xs">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2 mb-4">
                    <Server class="w-4 h-4 text-indigo-600" />
                    Infrastructure & Node Health
                </h3>

                <div class="space-y-3">
                    {#each systemNodes as node}
                        <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200/60 dark:border-slate-800/60 flex items-center justify-between text-xs">
                            <div class="flex items-center gap-3">
                                <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></div>
                                <div>
                                    <div class="font-bold text-slate-900 dark:text-white">{node.name}</div>
                                    <div class="text-[11px] text-slate-400 font-mono">
                                        {#if node.latency}Latency: {node.latency}{/if}
                                        {#if node.hitRate}Hit Rate: {node.hitRate}{/if}
                                        {#if node.activeWorkers !== undefined}Workers: {node.activeWorkers}{/if}
                                    </div>
                                </div>
                            </div>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wider bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900">
                                {node.status}
                            </span>
                        </div>
                    {/each}
                </div>
            </div>
        </div>

        <!-- Live Ingestion Feed -->
        <div class="p-6 rounded-3xl bg-white dark:bg-slate-900/80 border border-slate-200/80 dark:border-slate-800/80 shadow-2xs">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <Clock class="w-4 h-4 text-indigo-600" />
                    Live Ingestion Stream (Latest Indexed Documents)
                </h3>
                <span class="text-xs text-slate-400 font-mono">Auto-updates from crawler pipeline</span>
            </div>

            <div class="divide-y divide-slate-100 dark:divide-slate-800/60">
                {#each recentIndexed as page}
                    <div class="py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                        <div class="space-y-0.5">
                            <div class="flex items-center gap-2">
                                <span class="px-1.5 py-0.5 rounded-md bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 font-mono text-[10px] font-bold">
                                    HTTP {page.http_status || 200}
                                </span>
                                <a
                                    href={page.url}
                                    target="_blank"
                                    rel="noreferrer"
                                    class="font-semibold text-indigo-600 dark:text-indigo-400 hover:underline truncate max-w-lg"
                                >
                                    {page.title || page.url}
                                </a>
                            </div>
                            <div class="text-[11px] text-slate-400 font-mono truncate max-w-md">
                                {page.domain} • {page.response_time_ms} ms fetch • PR: {page.page_rank}
                            </div>
                        </div>

                        <span class="text-[11px] font-mono text-slate-400 shrink-0">
                            {new Date(page.crawled_at || page.created_at).toLocaleTimeString()}
                        </span>
                    </div>
                {:else}
                    <div class="py-8 text-center text-slate-400 text-xs">
                        No documents crawled yet. Launch the crawler from <Link href="/crawler" class="text-indigo-600 underline">Crawler Dashboard</Link> to start indexing.
                    </div>
                {/each}
            </div>
        </div>
    </main>

    <Footer />
</div>
