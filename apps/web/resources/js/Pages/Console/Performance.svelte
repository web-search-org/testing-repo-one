<script>
    import { Link } from '@inertiajs/svelte';
    import Navbar from '../../Components/Navbar.svelte';
    import Footer from '../../Components/Footer.svelte';
    import { SearchCheck, BarChart3, Search } from 'lucide-svelte';

    let { currentDomain = null, performance = {}, domains = [] } = $props();

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
    <title>Performance Analytics {currentDomain?.name ? `- ${currentDomain.name}` : ''} - Web-Search Console</title>
</svelte:head>

<div class="min-h-screen flex flex-col bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 transition-colors">
    <Navbar showSearch={false} />

    <!-- Subheader -->
    <div class="border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/60 transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <Link href="/console" class="w-8 h-8 rounded-lg bg-indigo-600 text-white flex items-center justify-center font-bold">
                    <SearchCheck class="w-4.5 h-4.5" />
                </Link>
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Search Console</span>
                    <h1 class="font-bold text-sm text-slate-900 dark:text-white">Performance on Web-Search.org</h1>
                </div>
            </div>

            <!-- Property Switcher -->
            {#if domains.length > 0 && currentDomain}
                <select
                    value={currentDomain.name}
                    onchange={(e) => window.location.href = `/console/performance?domain=${encodeURIComponent(e.currentTarget.value)}`}
                    class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 px-3 py-1.5 rounded-lg text-xs font-semibold focus:outline-none"
                >
                    {#each domains as d}
                        <option value={d.name}>{d.name}</option>
                    {/each}
                </select>
            {/if}
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="border-b border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center gap-2 overflow-x-auto py-2">
            <Link
                href={`/console${currentDomain ? `?domain=${currentDomain.name}` : ''}`}
                class="px-3 py-1 rounded-md text-xs font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-900 transition-colors"
            >
                Overview
            </Link>
            <span class="px-3 py-1 rounded-md text-xs font-semibold bg-indigo-600 text-white">
                Performance
            </span>
            <Link
                href="/console/inspect"
                class="px-3 py-1 rounded-md text-xs font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-900 transition-colors"
            >
                URL Inspection
            </Link>
            <Link
                href={`/console/sitemaps${currentDomain ? `?domain=${currentDomain.name}` : ''}`}
                class="px-3 py-1 rounded-md text-xs font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-900 transition-colors"
            >
                Sitemaps
            </Link>
        </div>
    </div>

    <main class="flex-1 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
        <!-- 4 Metric Cards (Flat) -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800">
                <span class="text-[10px] font-bold text-slate-500 uppercase">Total Clicks</span>
                <div class="text-2xl font-bold font-mono text-indigo-600 dark:text-indigo-400 mt-1">
                    {performance.summary?.totalClicks || 0}
                </div>
                <span class="text-[11px] text-slate-500">SERP result clicks</span>
            </div>

            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800">
                <span class="text-[10px] font-bold text-slate-500 uppercase">Total Impressions</span>
                <div class="text-2xl font-bold font-mono text-slate-900 dark:text-white mt-1">
                    {performance.summary?.totalImpressions || 0}
                </div>
                <span class="text-[11px] text-slate-500">Search appearances</span>
            </div>

            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800">
                <span class="text-[10px] font-bold text-slate-500 uppercase">Average CTR</span>
                <div class="text-2xl font-bold font-mono text-emerald-600 dark:text-emerald-400 mt-1">
                    {performance.summary?.averageCtr || 0}%
                </div>
                <span class="text-[11px] text-slate-500">Click-through rate</span>
            </div>

            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800">
                <span class="text-[10px] font-bold text-slate-500 uppercase">Average Position</span>
                <div class="text-2xl font-bold font-mono text-slate-700 dark:text-slate-300 mt-1">
                    #{performance.summary?.averagePosition || 0}
                </div>
                <span class="text-[11px] text-slate-500">SERP rank position</span>
            </div>
        </div>

        <!-- Table Container (Flat) -->
        <div class="bg-white dark:bg-slate-900/60 rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden">
            <!-- Header & Filter -->
            <div class="p-3.5 border-b border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <button
                        onclick={() => activeTab = 'queries'}
                        class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer {activeTab === 'queries' ? 'bg-indigo-600 text-white' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'}"
                    >
                        Queries ({performance.queries?.length || 0})
                    </button>
                    <button
                        onclick={() => activeTab = 'pages'}
                        class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer {activeTab === 'pages' ? 'bg-indigo-600 text-white' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'}"
                    >
                        Pages ({performance.pages?.length || 0})
                    </button>
                </div>

                <div class="relative w-full sm:w-60">
                    <input
                        type="text"
                        bind:value={tableFilter}
                        placeholder="Filter rows..."
                        class="w-full pl-8 pr-3 py-1 rounded-lg text-xs bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                    />
                    <Search class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-1.5" />
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-950 text-slate-500 border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="px-4 py-2.5 font-medium">{activeTab === 'queries' ? 'Search Query' : 'Page URL'}</th>
                            <th class="px-3 py-2.5 font-medium">Clicks</th>
                            <th class="px-3 py-2.5 font-medium">Impressions</th>
                            <th class="px-3 py-2.5 font-medium">CTR</th>
                            <th class="px-3 py-2.5 font-medium">Position</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-mono">
                        {#if activeTab === 'queries'}
                            {#each filteredQueries as row}
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                                    <td class="px-4 py-2.5 font-sans font-medium text-slate-900 dark:text-white">
                                        <a href={`/search?q=${encodeURIComponent(row.query)}`} class="hover:text-indigo-600 hover:underline">
                                            {row.query}
                                        </a>
                                    </td>
                                    <td class="px-3 py-2.5 font-bold text-indigo-600 dark:text-indigo-400">{row.clicks}</td>
                                    <td class="px-3 py-2.5 text-slate-500">{row.impressions}</td>
                                    <td class="px-3 py-2.5 font-semibold text-emerald-600 dark:text-emerald-400">{row.ctr}%</td>
                                    <td class="px-3 py-2.5 text-slate-500">#{row.position}</td>
                                </tr>
                            {:else}
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-slate-400 font-sans text-xs">No search queries recorded yet.</td>
                                </tr>
                            {/each}
                        {:else}
                            {#each filteredPages as row}
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                                    <td class="px-4 py-2.5 font-sans font-medium text-slate-900 dark:text-white max-w-md truncate">
                                        <a href={`/console/inspect?url=${encodeURIComponent(row.url)}`} class="hover:text-indigo-600 hover:underline">
                                            {row.url}
                                        </a>
                                    </td>
                                    <td class="px-3 py-2.5 font-bold text-indigo-600 dark:text-indigo-400">{row.clicks}</td>
                                    <td class="px-3 py-2.5 text-slate-500">{row.impressions}</td>
                                    <td class="px-3 py-2.5 font-semibold text-emerald-600 dark:text-emerald-400">{row.ctr}%</td>
                                    <td class="px-3 py-2.5 text-slate-500">#{row.position}</td>
                                </tr>
                            {:else}
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-slate-400 font-sans text-xs">No indexed pages recorded yet.</td>
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
