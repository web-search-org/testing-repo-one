<script>
    import { Link } from '@inertiajs/svelte';
    import Navbar from '../../Components/Navbar.svelte';
    import Footer from '../../Components/Footer.svelte';
    import { SearchCheck, TrendingUp, Filter, Calendar, ArrowUpRight } from 'lucide-svelte';

    let { currentDomain = null, performance = {}, domains = [] } = $props();

    const summary = $derived(performance.summary || {});
    const queries = $derived(performance.queries || []);
    const pages = $derived(performance.pages || []);

    let activeTab = $state('queries'); // 'queries' | 'pages'
</script>

<svelte:head>
    <title>Search Performance {currentDomain?.name ? `- ${currentDomain.name}` : ''} - Web-Search Console</title>
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
                    <h1 class="font-bold text-sm text-black dark:text-white">Performance on Web-Search.org</h1>
                </div>
            </div>

            <!-- Property Switcher -->
            {#if domains.length > 0 && currentDomain}
                <select
                    value={currentDomain.name}
                    onchange={(e) => window.location.href = `/console/performance?domain=${encodeURIComponent(e.currentTarget.value)}`}
                    class="bg-white dark:bg-black border border-zinc-300 dark:border-zinc-800 px-3 py-1.5 rounded-lg text-xs font-semibold focus:outline-none cursor-pointer"
                >
                    {#each domains as d}
                        <option value={d.name}>{d.name}</option>
                    {/each}
                </select>
            {/if}
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="border-b border-zinc-200 dark:border-zinc-800 bg-white dark:bg-black">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center gap-2 overflow-x-auto py-2">
            <Link
                href={`/console${currentDomain ? `?domain=${currentDomain.name}` : ''}`}
                class="px-3 py-1 rounded-md text-xs font-medium text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-900 transition-colors"
            >
                Overview
            </Link>
            <span class="px-3 py-1 rounded-md text-xs font-semibold bg-black text-white dark:bg-white dark:text-black">
                Performance
            </span>
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

    <main class="flex-1 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
        {#if currentDomain}
            <!-- 4 Metric Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <div class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800">
                    <span class="text-[10px] font-bold text-zinc-400 uppercase">Total Clicks</span>
                    <div class="text-2xl font-bold font-mono text-black dark:text-white mt-1">
                        {summary.totalClicks || 0}
                    </div>
                </div>

                <div class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800">
                    <span class="text-[10px] font-bold text-zinc-400 uppercase">Total Impressions</span>
                    <div class="text-2xl font-bold font-mono text-black dark:text-white mt-1">
                        {summary.totalImpressions || 0}
                    </div>
                </div>

                <div class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800">
                    <span class="text-[10px] font-bold text-zinc-400 uppercase">Average CTR</span>
                    <div class="text-2xl font-bold font-mono text-black dark:text-white mt-1">
                        {summary.averageCtr || 0}%
                    </div>
                </div>

                <div class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800">
                    <span class="text-[10px] font-bold text-zinc-400 uppercase">Average Position</span>
                    <div class="text-2xl font-bold font-mono text-black dark:text-white mt-1">
                        {summary.averagePosition || 0}
                    </div>
                </div>
            </div>

            <!-- Table Container (Monochrome) -->
            <div class="bg-white dark:bg-zinc-950 rounded-xl border border-zinc-200 dark:border-zinc-800 overflow-hidden">
                <div class="p-3.5 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between">
                    <div class="flex items-center gap-1.5">
                        <button
                            onclick={() => activeTab = 'queries'}
                            class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer {activeTab === 'queries' ? 'bg-black text-white dark:bg-white dark:text-black' : 'text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-900'}"
                        >
                            Queries ({queries.length})
                        </button>
                        <button
                            onclick={() => activeTab = 'pages'}
                            class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer {activeTab === 'pages' ? 'bg-black text-white dark:bg-white dark:text-black' : 'text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-900'}"
                        >
                            Pages ({pages.length})
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-zinc-50 dark:bg-black text-zinc-500 border-b border-zinc-200 dark:border-zinc-800">
                            <tr>
                                <th class="px-4 py-2.5 font-medium">{activeTab === 'queries' ? 'Top Queries' : 'Pages'}</th>
                                <th class="px-3 py-2.5 font-medium">Clicks</th>
                                <th class="px-3 py-2.5 font-medium">Impressions</th>
                                <th class="px-3 py-2.5 font-medium">CTR</th>
                                <th class="px-3 py-2.5 font-medium">Position</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800 font-mono">
                            {#if activeTab === 'queries'}
                                {#each queries as q}
                                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-900/40 transition-colors">
                                        <td class="px-4 py-2.5 font-sans font-medium text-black dark:text-white max-w-sm truncate">{q.query}</td>
                                        <td class="px-3 py-2.5 font-bold text-black dark:text-white">{q.clicks}</td>
                                        <td class="px-3 py-2.5 text-zinc-500">{q.impressions}</td>
                                        <td class="px-3 py-2.5">{q.ctr}%</td>
                                        <td class="px-3 py-2.5">{q.position}</td>
                                    </tr>
                                {:else}
                                    <tr>
                                        <td colspan="5" class="px-4 py-8 text-center text-zinc-400 font-sans text-xs">
                                            No search query performance data recorded yet.
                                        </td>
                                    </tr>
                                {/each}
                            {:else}
                                {#each pages as p}
                                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-900/40 transition-colors">
                                        <td class="px-4 py-2.5 font-sans font-medium text-black dark:text-white max-w-md truncate">
                                            <a href={p.url} target="_blank" rel="noreferrer" class="hover:underline">{p.url}</a>
                                        </td>
                                        <td class="px-3 py-2.5 font-bold text-black dark:text-white">{p.clicks}</td>
                                        <td class="px-3 py-2.5 text-zinc-500">{p.impressions}</td>
                                        <td class="px-3 py-2.5">{p.ctr}%</td>
                                        <td class="px-3 py-2.5">{p.position}</td>
                                    </tr>
                                {:else}
                                    <tr>
                                        <td colspan="5" class="px-4 py-8 text-center text-zinc-400 font-sans text-xs">
                                            No pages recorded for this domain yet.
                                        </td>
                                    </tr>
                                {/each}
                            {/if}
                        </tbody>
                    </table>
                </div>
            </div>
        {/if}
    </main>

    <Footer />
</div>
