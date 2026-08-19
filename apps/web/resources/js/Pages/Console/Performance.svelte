<script>
    import { Link } from '@inertiajs/svelte';
    import Navbar from '../../Components/Navbar.svelte';
    import Footer from '../../Components/Footer.svelte';
    import { SearchCheck, BarChart3, Filter, ArrowUpDown, Download, Search } from 'lucide-svelte';

    let { currentDomain = {}, performance = {}, domains = [] } = $props();

    let activeTab = $state('queries'); // 'queries' | 'pages'
    let tableFilter = $state('');

    const filteredQueries = $derived(
        (performance.queries || []).filter(q => q.query.toLowerCase().includes(tableFilter.toLowerCase()))
    );

    const filteredPages = $derived(
        (performance.pages || []).filter(p => p.url.toLowerCase().includes(tableFilter.toLowerCase()))
    );
</script>

<svelte:head>
    <title>Performance Analytics - {currentDomain.name} - Web-Search Console</title>
</svelte:head>

<div class="min-h-screen flex flex-col bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 transition-colors">
    <Navbar showSearch={false} />

    <!-- Subheader -->
    <div class="border-b border-slate-200/80 dark:border-slate-800/80 bg-white dark:bg-slate-900/90 transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <Link href="/console" class="p-2 rounded-xl bg-indigo-600 text-white shadow-sm">
                    <SearchCheck class="w-5 h-5" />
                </Link>
                <div>
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Search Console</span>
                    <h1 class="font-bold text-base text-slate-900 dark:text-white">Performance on Web-Search.org</h1>
                </div>
            </div>

            <!-- Property Switcher -->
            <select
                value={currentDomain.name}
                onchange={(e) => window.location.href = `/console/performance?domain=${encodeURIComponent(e.currentTarget.value)}`}
                class="bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3 py-1.5 rounded-xl text-xs font-semibold focus:outline-none"
            >
                {#each domains as d}
                    <option value={d.name}>{d.name}</option>
                {/each}
            </select>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="border-b border-slate-200/80 dark:border-slate-800/80 bg-white/50 dark:bg-slate-950/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center gap-2 overflow-x-auto py-2">
            <Link
                href={`/console?domain=${currentDomain.name}`}
                class="px-3.5 py-1.5 rounded-xl text-xs font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-900 transition-colors"
            >
                Overview
            </Link>
            <span class="px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-indigo-600 text-white shadow-2xs">
                Performance
            </span>
            <Link
                href={`/console/inspect?url=https://${currentDomain.name}`}
                class="px-3.5 py-1.5 rounded-xl text-xs font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-900 transition-colors"
            >
                URL Inspection
            </Link>
            <Link
                href={`/console/sitemaps?domain=${currentDomain.name}`}
                class="px-3.5 py-1.5 rounded-xl text-xs font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-900 transition-colors"
            >
                Sitemaps
            </Link>
        </div>
    </div>

    <main class="flex-1 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        <!-- 4 Metric Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="p-5 rounded-2xl bg-white dark:bg-slate-900/80 border-t-4 border-t-indigo-600 border border-slate-200/80 dark:border-slate-800/80 shadow-2xs">
                <span class="text-xs font-semibold text-slate-400 uppercase">Total Clicks</span>
                <div class="text-3xl font-extrabold font-mono text-indigo-600 dark:text-indigo-400 mt-1">
                    {performance.summary?.totalClicks || 0}
                </div>
                <span class="text-[11px] text-slate-400">Search result clicks</span>
            </div>

            <div class="p-5 rounded-2xl bg-white dark:bg-slate-900/80 border-t-4 border-t-purple-600 border border-slate-200/80 dark:border-slate-800/80 shadow-2xs">
                <span class="text-xs font-semibold text-slate-400 uppercase">Total Impressions</span>
                <div class="text-3xl font-extrabold font-mono text-purple-600 dark:text-purple-400 mt-1">
                    {performance.summary?.totalImpressions || 0}
                </div>
                <span class="text-[11px] text-slate-400">SERP appearances</span>
            </div>

            <div class="p-5 rounded-2xl bg-white dark:bg-slate-900/80 border-t-4 border-t-emerald-600 border border-slate-200/80 dark:border-slate-800/80 shadow-2xs">
                <span class="text-xs font-semibold text-slate-400 uppercase">Average CTR</span>
                <div class="text-3xl font-extrabold font-mono text-emerald-600 dark:text-emerald-400 mt-1">
                    {performance.summary?.averageCtr || 0}%
                </div>
                <span class="text-[11px] text-slate-400">Click-through rate</span>
            </div>

            <div class="p-5 rounded-2xl bg-white dark:bg-slate-900/80 border-t-4 border-t-amber-500 border border-slate-200/80 dark:border-slate-800/80 shadow-2xs">
                <span class="text-xs font-semibold text-slate-400 uppercase">Average Position</span>
                <div class="text-3xl font-extrabold font-mono text-amber-500 mt-1">
                    #{performance.summary?.averagePosition || 1.0}
                </div>
                <span class="text-[11px] text-slate-400">Average ranking rank</span>
            </div>
        </div>

        <!-- Table Container with Queries / Pages Tabs -->
        <div class="bg-white dark:bg-slate-900/80 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 shadow-2xs overflow-hidden">
            <!-- Header & Filter -->
            <div class="p-4 border-b border-slate-200/80 dark:border-slate-800/80 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <button
                        onclick={() => activeTab = 'queries'}
                        class="px-4 py-2 rounded-xl text-xs font-bold transition-all {activeTab === 'queries' ? 'bg-indigo-600 text-white shadow-2xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'}"
                    >
                        Queries ({performance.queries?.length || 0})
                    </button>
                    <button
                        onclick={() => activeTab = 'pages'}
                        class="px-4 py-2 rounded-xl text-xs font-bold transition-all {activeTab === 'pages' ? 'bg-indigo-600 text-white shadow-2xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'}"
                    >
                        Pages ({performance.pages?.length || 0})
                    </button>
                </div>

                <div class="relative w-full sm:w-64">
                    <input
                        type="text"
                        bind:value={tableFilter}
                        placeholder="Filter rows..."
                        class="w-full pl-8 pr-3 py-1.5 rounded-xl text-xs bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                    />
                    <Search class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2" />
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-950/60 text-slate-500 border-b border-slate-200/80 dark:border-slate-800/80">
                        <tr>
                            <th class="px-5 py-3 font-medium">{activeTab === 'queries' ? 'Search Query' : 'Page URL'}</th>
                            <th class="px-4 py-3 font-medium">Clicks</th>
                            <th class="px-4 py-3 font-medium">Impressions</th>
                            <th class="px-4 py-3 font-medium">CTR</th>
                            <th class="px-4 py-3 font-medium">Position</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-mono">
                        {#if activeTab === 'queries'}
                            {#each filteredQueries as row}
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                                    <td class="px-5 py-3 font-sans font-medium text-slate-900 dark:text-white">
                                        <a href={`/search?q=${encodeURIComponent(row.query)}`} class="hover:text-indigo-600 hover:underline">
                                            {row.query}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 font-bold text-indigo-600 dark:text-indigo-400">{row.clicks}</td>
                                    <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{row.impressions}</td>
                                    <td class="px-4 py-3 font-semibold text-emerald-600 dark:text-emerald-400">{row.ctr}%</td>
                                    <td class="px-4 py-3 text-slate-600 dark:text-slate-400">#{row.position}</td>
                                </tr>
                            {:else}
                                <tr>
                                    <td colspan="5" class="px-5 py-8 text-center text-slate-400 font-sans">No queries match filter.</td>
                                </tr>
                            {/each}
                        {:else}
                            {#each filteredPages as row}
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                                    <td class="px-5 py-3 font-sans font-medium text-slate-900 dark:text-white max-w-md truncate">
                                        <a href={`/console/inspect?url=${encodeURIComponent(row.url)}`} class="hover:text-indigo-600 hover:underline">
                                            {row.url}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 font-bold text-indigo-600 dark:text-indigo-400">{row.clicks}</td>
                                    <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{row.impressions}</td>
                                    <td class="px-4 py-3 font-semibold text-emerald-600 dark:text-emerald-400">{row.ctr}%</td>
                                    <td class="px-4 py-3 text-slate-600 dark:text-slate-400">#{row.position}</td>
                                </tr>
                            {:else}
                                <tr>
                                    <td colspan="5" class="px-5 py-8 text-center text-slate-400 font-sans">No pages match filter.</td>
                                </tr>
                            {/each}
                        {/if}
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <Footer />
</div>
