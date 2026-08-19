<script>
    import { Link } from '@inertiajs/svelte';
    import Navbar from '../../Components/Navbar.svelte';
    import Footer from '../../Components/Footer.svelte';
    import { 
        SearchCheck, 
        TrendingUp, 
        CheckCircle2, 
        AlertCircle, 
        FileText, 
        ExternalLink, 
        ArrowUpRight, 
        Plus,
        Layers,
        Shield,
        Link2
    } from 'lucide-svelte';

    let { domains = [], currentDomain = null, performance = {}, coverage = {}, sitemaps = [] } = $props();

    const summary = $derived(performance.summary || {});
</script>

<svelte:head>
    <title>Search Console {currentDomain?.name ? `- ${currentDomain.name}` : ''} - Web-Search.org</title>
</svelte:head>

<div class="min-h-screen flex flex-col bg-white dark:bg-black text-zinc-900 dark:text-zinc-100 transition-colors">
    <Navbar showSearch={false} />

    <!-- Subheader (Monochrome) -->
    <div class="border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-950 transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <Link href="/console" class="w-8 h-8 rounded-lg bg-black text-white dark:bg-white dark:text-black flex items-center justify-center font-bold">
                    <SearchCheck class="w-4.5 h-4.5" />
                </Link>
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-400">Search Console</span>
                    <h1 class="font-bold text-sm text-black dark:text-white">Domain Property Overview</h1>
                </div>
            </div>

            <!-- Property Switcher -->
            {#if domains.length > 0 && currentDomain}
                <div class="flex items-center gap-2">
                    <select
                        value={currentDomain.name}
                        onchange={(e) => window.location.href = `/console?domain=${encodeURIComponent(e.currentTarget.value)}`}
                        class="bg-white dark:bg-black border border-zinc-300 dark:border-zinc-800 px-3 py-1.5 rounded-lg text-xs font-semibold focus:outline-none cursor-pointer"
                    >
                        {#each domains as d}
                            <option value={d.name}>{d.name}</option>
                        {/each}
                    </select>

                    {#if !currentDomain.is_verified}
                        <form method="POST" action="/console/verify" class="inline">
                            <input type="hidden" name="domain" value={currentDomain.name} />
                            <button
                                type="submit"
                                class="px-2.5 py-1.5 rounded-lg bg-zinc-200 dark:bg-zinc-800 hover:bg-zinc-300 dark:hover:bg-zinc-700 text-[11px] font-semibold text-black dark:text-white transition-colors cursor-pointer"
                            >
                                Verify Ownership
                            </button>
                        </form>
                    {:else}
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded text-[10px] font-bold bg-zinc-100 dark:bg-zinc-900 text-emerald-600 dark:text-emerald-400">
                            <CheckCircle2 class="w-3 h-3" />
                            Verified
                        </span>
                    {/if}
                </div>
            {/if}
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="border-b border-zinc-200 dark:border-zinc-800 bg-white dark:bg-black">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center gap-2 overflow-x-auto py-2">
            <span class="px-3 py-1 rounded-md text-xs font-semibold bg-black text-white dark:bg-white dark:text-black">
                Overview
            </span>
            <Link
                href={`/console/performance${currentDomain ? `?domain=${currentDomain.name}` : ''}`}
                class="px-3 py-1 rounded-md text-xs font-medium text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-900 transition-colors"
            >
                Performance
            </Link>
            <Link
                href={`/console/links${currentDomain ? `?domain=${currentDomain.name}` : ''}`}
                class="px-3 py-1 rounded-md text-xs font-medium text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-900 transition-colors"
            >
                Links & Interlinking
            </Link>
            <Link
                href="/console/inspect"
                class="px-3 py-1 rounded-md text-xs font-medium text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-900 transition-colors"
            >
                URL Inspection
            </Link>
            <Link
                href={`/console/sitemaps${currentDomain ? `?domain=${currentDomain.name}` : ''}`}
                class="px-3 py-1 rounded-md text-xs font-medium text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-900 transition-colors"
            >
                Sitemaps
            </Link>
        </div>
    </div>

    <!-- Main Content -->
    <main class="flex-1 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
        {#if currentDomain}
            <!-- 4 Performance Metric Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <div class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800">
                    <span class="text-[10px] font-bold text-zinc-400 uppercase">Search Clicks</span>
                    <div class="text-2xl font-bold font-mono text-black dark:text-white mt-1">
                        {summary.totalClicks || 0}
                    </div>
                    <span class="text-[11px] text-zinc-500">Past 28 days</span>
                </div>

                <div class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800">
                    <span class="text-[10px] font-bold text-zinc-400 uppercase">Total Impressions</span>
                    <div class="text-2xl font-bold font-mono text-black dark:text-white mt-1">
                        {summary.totalImpressions || 0}
                    </div>
                    <span class="text-[11px] text-zinc-500">Search appearances</span>
                </div>

                <div class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800">
                    <span class="text-[10px] font-bold text-zinc-400 uppercase">Average CTR</span>
                    <div class="text-2xl font-bold font-mono text-black dark:text-white mt-1">
                        {summary.averageCtr || 0}%
                    </div>
                    <span class="text-[11px] text-zinc-500">Click-through rate</span>
                </div>

                <div class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800">
                    <span class="text-[10px] font-bold text-zinc-400 uppercase">Indexed Pages</span>
                    <div class="text-2xl font-bold font-mono text-black dark:text-white mt-1">
                        {coverage.validIndexed || 0}
                    </div>
                    <span class="text-[11px] text-zinc-500">Valid in index</span>
                </div>
            </div>

            <!-- Two Column Overview Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Top Queries -->
                <div class="p-5 rounded-xl bg-white dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-xs font-bold uppercase tracking-wider text-black dark:text-white flex items-center gap-2">
                            <TrendingUp class="w-4 h-4" />
                            Top Search Queries
                        </h2>
                        <Link href={`/console/performance?domain=${currentDomain.name}`} class="text-xs text-zinc-500 hover:text-black dark:hover:text-white hover:underline">
                            Full report →
                        </Link>
                    </div>

                    <div class="divide-y divide-zinc-100 dark:divide-zinc-800 text-xs">
                        {#each performance.queries || [] as q}
                            <div class="py-2.5 flex items-center justify-between font-mono">
                                <span class="font-sans font-medium text-black dark:text-white truncate max-w-xs">{q.query}</span>
                                <div class="flex items-center gap-4">
                                    <span class="text-zinc-500">{q.impressions} impr</span>
                                    <span class="font-bold text-black dark:text-white">{q.clicks} clicks</span>
                                </div>
                            </div>
                        {:else}
                            <div class="py-6 text-center text-xs text-zinc-400">
                                No search query performance data recorded yet.
                            </div>
                        {/each}
                    </div>
                </div>

                <!-- Coverage Breakdown -->
                <div class="p-5 rounded-xl bg-white dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-xs font-bold uppercase tracking-wider text-black dark:text-white flex items-center gap-2">
                            <Layers class="w-4 h-4" />
                            Index Coverage Status
                        </h2>
                    </div>

                    <div class="space-y-2.5 text-xs">
                        {#each coverage.breakdown || [] as item}
                            <div class="flex items-center justify-between py-1 border-b border-zinc-100 dark:border-zinc-800">
                                <span class="text-zinc-600 dark:text-zinc-400">{item.status}</span>
                                <span class="font-mono font-bold text-black dark:text-white">{item.count}</span>
                            </div>
                        {:else}
                            <div class="py-6 text-center text-xs text-zinc-400">
                                Coverage report is updating.
                            </div>
                        {/each}
                    </div>
                </div>
            </div>
        {:else}
            <!-- Empty state when no properties exist -->
            <div class="p-8 rounded-2xl bg-white dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 text-center max-w-lg mx-auto space-y-3">
                <div class="w-12 h-12 rounded-xl bg-zinc-100 dark:bg-zinc-900 text-black dark:text-white flex items-center justify-center mx-auto">
                    <SearchCheck class="w-6 h-6" />
                </div>
                <h2 class="text-base font-bold text-black dark:text-white">No Domain Properties</h2>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 leading-relaxed">
                    Add and submit your website to Web-Search.org to inspect URLs, track search clicks, submit XML sitemaps, and monitor backlinks.
                </p>
                <div class="pt-2">
                    <Link
                        href="/submit"
                        class="px-3.5 py-1.5 rounded-lg bg-black text-white dark:bg-white dark:text-black text-xs font-semibold hover:bg-zinc-800 dark:hover:bg-zinc-200 transition-colors"
                    >
                        Submit Your Website →
                    </Link>
                </div>
            </div>
        {/if}
    </main>

    <Footer />
</div>
