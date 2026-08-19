<script>
    import { Link } from '@inertiajs/svelte';
    import Navbar from '../Components/Navbar.svelte';
    import Footer from '../Components/Footer.svelte';
    import { 
        BarChart3, 
        Database, 
        Globe, 
        Server, 
        ShieldCheck, 
        RefreshCw, 
        Download, 
        ExternalLink, 
        CheckCircle2, 
        Layers, 
        Cpu, 
        Clock,
        PlusCircle
    } from 'lucide-svelte';

    let { insights = {} } = $props();

    const summary = $derived(insights.summary || {});
    const topDomains = $derived(insights.topDomains || []);
    const categories = $derived(insights.categories || []);
    const tlds = $derived(insights.tlds || []);
    const recentIndexed = $derived(insights.recentIndexed || []);
    const systemNodes = $derived(insights.systemNodes || []);
</script>

<svelte:head>
    <title>Open Insights & Live Transparency - Web-Search.org</title>
</svelte:head>

<div class="min-h-screen flex flex-col bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 transition-colors">
    <Navbar showSearch={true} />

    <main class="flex-1 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-6 border-b border-slate-200 dark:border-slate-800">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                    Search Engine Telemetry & Stats
                </h1>
                <p class="mt-1 text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                    Live operational index metrics, verified host properties, and zero-tracking privacy audit.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <Link
                    href="/submit"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold transition-colors"
                >
                    <PlusCircle class="w-3.5 h-3.5" />
                    Submit Site
                </Link>

                <button
                    onclick={() => window.location.reload()}
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 text-xs font-medium hover:bg-slate-200 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                >
                    <RefreshCw class="w-3.5 h-3.5" />
                    Refresh
                </button>

                <a
                    href="/api/v1/stats"
                    target="_blank"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 text-xs font-medium hover:bg-slate-200 dark:hover:bg-slate-800 transition-colors"
                >
                    <Download class="w-3.5 h-3.5" />
                    JSON API
                </a>
            </div>
        </div>

        <!-- 4 Authentic Metric Tiles (No fake demo stats) -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800">
                <div class="flex items-center justify-between text-slate-500 mb-1">
                    <span class="text-[11px] font-bold uppercase tracking-wider">Indexed Documents</span>
                    <Database class="w-4 h-4 text-indigo-600 dark:text-indigo-400" />
                </div>
                <div class="text-2xl font-bold font-mono text-slate-900 dark:text-white">
                    {summary.totalPages || 0}
                </div>
                <span class="text-[11px] text-slate-500">Live indexed URLs</span>
            </div>

            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800">
                <div class="flex items-center justify-between text-slate-500 mb-1">
                    <span class="text-[11px] font-bold uppercase tracking-wider">Indexed Domains</span>
                    <Globe class="w-4 h-4 text-indigo-600 dark:text-indigo-400" />
                </div>
                <div class="text-2xl font-bold font-mono text-slate-900 dark:text-white">
                    {summary.totalDomains || 0}
                </div>
                <span class="text-[11px] text-slate-500">{summary.verifiedDomains || 0} verified</span>
            </div>

            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800">
                <div class="flex items-center justify-between text-slate-500 mb-1">
                    <span class="text-[11px] font-bold uppercase tracking-wider">Crawl Engine</span>
                    <Cpu class="w-4 h-4 text-indigo-600 dark:text-indigo-400" />
                </div>
                <div class="text-2xl font-bold font-mono text-slate-900 dark:text-white">
                    {summary.completedCrawlJobs || 0}
                </div>
                <span class="text-[11px] text-slate-500">{summary.activeCrawlJobs || 0} active workers</span>
            </div>

            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800">
                <div class="flex items-center justify-between text-emerald-600 dark:text-emerald-400 mb-1">
                    <span class="text-[11px] font-bold uppercase tracking-wider">Privacy Rating</span>
                    <ShieldCheck class="w-4 h-4" />
                </div>
                <div class="text-xl font-bold text-emerald-600 dark:text-emerald-400">
                    100% Private
                </div>
                <span class="text-[11px] text-emerald-600 dark:text-emerald-400">0 IP logs stored</span>
            </div>
        </div>

        <!-- Top Domains & Category Distribution (Flat) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Top Domains Table -->
            <div class="lg:col-span-2 bg-white dark:bg-slate-900/60 rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-2">
                        <Globe class="w-4 h-4 text-indigo-600" />
                        Top Indexed Domains
                    </h2>
                    <span class="text-xs text-slate-400 font-mono">{topDomains.length} domains</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 dark:bg-slate-950 text-slate-500 border-b border-slate-200 dark:border-slate-800">
                            <tr>
                                <th class="px-4 py-2.5 font-semibold">Domain</th>
                                <th class="px-3 py-2.5 font-semibold">Status</th>
                                <th class="px-3 py-2.5 font-semibold">Pages</th>
                                <th class="px-3 py-2.5 font-semibold">Rank</th>
                                <th class="px-3 py-2.5 font-semibold">Search</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            {#each topDomains as d}
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                                    <td class="px-4 py-2.5 font-medium text-slate-900 dark:text-white">
                                        <div class="flex items-center gap-2">
                                            <img
                                                src={d.favicon_url || `https://www.google.com/s2/favicons?domain=${d.name}&sz=32`}
                                                alt=""
                                                class="w-3.5 h-3.5 rounded"
                                                onerror={(e) => { e.currentTarget.style.display = 'none'; }}
                                            />
                                            <span class="font-bold">{d.name}</span>
                                        </div>
                                    </td>
                                    <td class="px-3 py-2.5">
                                        {#if d.is_verified}
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400">
                                                <CheckCircle2 class="w-2.5 h-2.5" />
                                                Verified
                                            </span>
                                        {:else}
                                            <span class="text-[10px] text-slate-500">Indexed</span>
                                        {/if}
                                    </td>
                                    <td class="px-3 py-2.5 font-mono text-indigo-600 dark:text-indigo-400 font-bold">
                                        {d.total_pages}
                                    </td>
                                    <td class="px-3 py-2.5 font-mono text-slate-500">
                                        {d.domain_rank}/10
                                    </td>
                                    <td class="px-3 py-2.5">
                                        <a
                                            href={`/search?q=site:${d.name}`}
                                            class="text-indigo-600 dark:text-indigo-400 hover:underline inline-flex items-center gap-1"
                                        >
                                            <span>site:</span>
                                            <ExternalLink class="w-2.5 h-2.5" />
                                        </a>
                                    </td>
                                </tr>
                            {:else}
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-slate-400 text-xs">
                                        No domain properties indexed yet. Submit a website at <Link href="/submit" class="text-indigo-600 underline">/submit</Link>.
                                    </td>
                                </tr>
                            {/each}
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Categories & TLDs -->
            <div class="space-y-4">
                <div class="p-4 rounded-xl bg-white dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-2 mb-3">
                        <Layers class="w-4 h-4 text-indigo-600" />
                        Category Breakdown
                    </h3>

                    <div class="space-y-2.5">
                        {#each categories as cat}
                            <div>
                                <div class="flex items-center justify-between text-xs mb-1">
                                    <span class="font-medium text-slate-700 dark:text-slate-300">{cat.name}</span>
                                    <span class="font-mono text-slate-500">{cat.count} ({cat.percentage}%)</span>
                                </div>
                                <div class="w-full h-1.5 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                                    <div
                                        class="h-full bg-indigo-600 rounded-full"
                                        style={`width: ${cat.percentage}%`}
                                    ></div>
                                </div>
                            </div>
                        {:else}
                            <div class="text-xs text-slate-400 py-3 text-center">No category data yet.</div>
                        {/each}
                    </div>
                </div>

                <div class="p-4 rounded-xl bg-white dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-2 mb-3">
                        <Globe class="w-4 h-4 text-indigo-600" />
                        Top TLD Distribution
                    </h3>

                    <div class="flex flex-wrap gap-1.5">
                        {#each tlds as t}
                            <div class="px-2.5 py-1 rounded-md bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs">
                                <span class="font-mono font-bold text-indigo-600 dark:text-indigo-400">{t.tld}</span>
                                <span class="text-slate-500 ml-1">({t.percentage}%)</span>
                            </div>
                        {:else}
                            <div class="text-xs text-slate-400 py-3 text-center w-full">No TLDs recorded.</div>
                        {/each}
                    </div>
                </div>
            </div>
        </div>

        <!-- Infrastructure Status (Flat) -->
        <div class="p-4 rounded-xl bg-white dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-2 mb-3">
                <Server class="w-4 h-4 text-indigo-600" />
                Infrastructure & Cluster Status
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                {#each systemNodes as node}
                    <div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2.5">
                            <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                            <div>
                                <div class="font-semibold text-slate-900 dark:text-white">{node.name}</div>
                                <div class="text-[10px] text-slate-400 font-mono">
                                    {#if node.latency}Latency: {node.latency}{/if}
                                    {#if node.activeWorkers !== undefined}Workers: {node.activeWorkers}{/if}
                                </div>
                            </div>
                        </div>
                        <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400">
                            {node.status}
                        </span>
                    </div>
                {/each}
            </div>
        </div>

        <!-- Live Ingestion Stream -->
        <div class="p-4 rounded-xl bg-white dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-2">
                    <Clock class="w-4 h-4 text-indigo-600" />
                    Latest Indexed Documents
                </h3>
                <Link href="/crawler" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">
                    View Crawler →
                </Link>
            </div>

            <div class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                {#each recentIndexed as page}
                    <div class="py-2.5 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div class="space-y-0.5">
                            <a
                                href={page.url}
                                target="_blank"
                                rel="noreferrer"
                                class="font-medium text-indigo-600 dark:text-indigo-400 hover:underline truncate max-w-lg block"
                            >
                                {page.title || page.url}
                            </a>
                            <div class="text-[11px] text-slate-500 font-mono">
                                {page.domain} • {page.response_time_ms}ms • HTTP {page.http_status}
                            </div>
                        </div>

                        <span class="text-[11px] font-mono text-slate-400 shrink-0">
                            {new Date(page.crawled_at || page.created_at).toLocaleTimeString()}
                        </span>
                    </div>
                {:else}
                    <div class="py-6 text-center text-slate-400 text-xs">
                        No documents crawled yet. Submit your site at <Link href="/submit" class="text-indigo-600 underline">/submit</Link>.
                    </div>
                {/each}
            </div>
        </div>
    </main>

    <Footer />
</div>
