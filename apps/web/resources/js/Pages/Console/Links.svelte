<script>
    import { Link } from '@inertiajs/svelte';
    import Navbar from '../../Components/Navbar.svelte';
    import Footer from '../../Components/Footer.svelte';
    import { 
        SearchCheck, 
        Link2, 
        ExternalLink, 
        Globe, 
        FileText, 
        Search,
        Layers,
        Share2,
        Network
    } from 'lucide-svelte';

    let { currentDomain = null, links = {}, domains = [] } = $props();

    let activeTab = $state('external_domains'); // 'external_domains' | 'target_pages' | 'anchors' | 'internal' | 'explorer'
    let tableFilter = $state('');

    const topLinkingDomains = $derived(
        (links.topLinkingDomains || []).filter(d => d.domain.toLowerCase().includes(tableFilter.toLowerCase()))
    );

    const topLinkedPages = $derived(
        (links.topLinkedPages || []).filter(p => p.url.toLowerCase().includes(tableFilter.toLowerCase()))
    );

    const topAnchorTexts = $derived(
        (links.topAnchorTexts || []).filter(a => a.text.toLowerCase().includes(tableFilter.toLowerCase()))
    );

    const topInternalPages = $derived(
        (links.topInternalPages || []).filter(p => p.url.toLowerCase().includes(tableFilter.toLowerCase()))
    );

    const recentLinks = $derived(
        (links.recentLinks || []).filter(l => 
            l.source_url.toLowerCase().includes(tableFilter.toLowerCase()) || 
            l.target_url.toLowerCase().includes(tableFilter.toLowerCase()) ||
            (l.anchor_text && l.anchor_text.toLowerCase().includes(tableFilter.toLowerCase()))
        )
    );
</script>

<svelte:head>
    <title>Links & Interlinking {currentDomain?.name ? `- ${currentDomain.name}` : ''} - Web-Search Console</title>
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
                    <h1 class="font-bold text-sm text-slate-900 dark:text-white">Links & Web Interlinking Graph</h1>
                </div>
            </div>

            <!-- Property Switcher -->
            {#if domains.length > 0 && currentDomain}
                <select
                    value={currentDomain.name}
                    onchange={(e) => window.location.href = `/console/links?domain=${encodeURIComponent(e.currentTarget.value)}`}
                    class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 px-3 py-1.5 rounded-lg text-xs font-semibold focus:outline-none cursor-pointer"
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
            <Link
                href={`/console/performance${currentDomain ? `?domain=${currentDomain.name}` : ''}`}
                class="px-3 py-1 rounded-md text-xs font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-900 transition-colors"
            >
                Performance
            </Link>
            <span class="px-3 py-1 rounded-md text-xs font-semibold bg-indigo-600 text-white">
                Links & Interlinking
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
        {#if currentDomain}
            <!-- 3 Summary Metric Cards (Flat) -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800">
                    <span class="text-[10px] font-bold text-slate-500 uppercase">External Backlinks</span>
                    <div class="text-2xl font-bold font-mono text-indigo-600 dark:text-indigo-400 mt-1">
                        {links.summary?.totalExternalLinks || 0}
                    </div>
                    <span class="text-[11px] text-slate-500">Inbound links from other sites</span>
                </div>

                <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800">
                    <span class="text-[10px] font-bold text-slate-500 uppercase">Top Linking Root Sites</span>
                    <div class="text-2xl font-bold font-mono text-slate-900 dark:text-white mt-1">
                        {links.summary?.totalLinkingDomains || 0}
                    </div>
                    <span class="text-[11px] text-slate-500">Unique referring domains</span>
                </div>

                <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800">
                    <span class="text-[10px] font-bold text-slate-500 uppercase">Internal Interlinks</span>
                    <div class="text-2xl font-bold font-mono text-slate-700 dark:text-slate-300 mt-1">
                        {links.summary?.totalInternalLinks || 0}
                    </div>
                    <span class="text-[11px] text-slate-500">Navigation links within domain</span>
                </div>
            </div>

            <!-- Interlinking Table Container (Flat) -->
            <div class="bg-white dark:bg-slate-900/60 rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                <!-- Header & Subtabs -->
                <div class="p-3.5 border-b border-slate-200 dark:border-slate-800 flex flex-col lg:flex-row lg:items-center justify-between gap-3">
                    <div class="flex items-center gap-1.5 overflow-x-auto">
                        <button
                            onclick={() => activeTab = 'external_domains'}
                            class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer whitespace-nowrap {activeTab === 'external_domains' ? 'bg-indigo-600 text-white' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'}"
                        >
                            Top Linking Sites ({links.topLinkingDomains?.length || 0})
                        </button>
                        <button
                            onclick={() => activeTab = 'target_pages'}
                            class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer whitespace-nowrap {activeTab === 'target_pages' ? 'bg-indigo-600 text-white' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'}"
                        >
                            Top Linked Pages ({links.topLinkedPages?.length || 0})
                        </button>
                        <button
                            onclick={() => activeTab = 'anchors'}
                            class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer whitespace-nowrap {activeTab === 'anchors' ? 'bg-indigo-600 text-white' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'}"
                        >
                            Top Anchor Texts ({links.topAnchorTexts?.length || 0})
                        </button>
                        <button
                            onclick={() => activeTab = 'internal'}
                            class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer whitespace-nowrap {activeTab === 'internal' ? 'bg-indigo-600 text-white' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'}"
                        >
                            Internal Links ({links.topInternalPages?.length || 0})
                        </button>
                        <button
                            onclick={() => activeTab = 'explorer'}
                            class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer whitespace-nowrap {activeTab === 'explorer' ? 'bg-indigo-600 text-white' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'}"
                        >
                            Link Explorer ({links.recentLinks?.length || 0})
                        </button>
                    </div>

                    <div class="relative w-full lg:w-60">
                        <input
                            type="text"
                            bind:value={tableFilter}
                            placeholder="Filter link rows..."
                            class="w-full pl-8 pr-3 py-1 rounded-lg text-xs bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                        />
                        <Search class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-1.5" />
                    </div>
                </div>

                <!-- Table View by Active Tab -->
                <div class="overflow-x-auto">
                    {#if activeTab === 'external_domains'}
                        <!-- Top Linking Domains -->
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 dark:bg-slate-950 text-slate-500 border-b border-slate-200 dark:border-slate-800">
                                <tr>
                                    <th class="px-4 py-2.5 font-medium">Referring Domain</th>
                                    <th class="px-3 py-2.5 font-medium">Target Pages Linked</th>
                                    <th class="px-3 py-2.5 font-medium">Total Backlinks</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-mono">
                                {#each topLinkingDomains as row}
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                                        <td class="px-4 py-2.5 font-sans font-medium text-slate-900 dark:text-white">
                                            <a href={`https://${row.domain}`} target="_blank" rel="noreferrer" class="hover:text-indigo-600 hover:underline inline-flex items-center gap-1.5">
                                                <span>{row.domain}</span>
                                                <ExternalLink class="w-3 h-3 text-slate-400" />
                                            </a>
                                        </td>
                                        <td class="px-3 py-2.5 text-slate-600 dark:text-slate-400">{row.targetPagesCount} pages</td>
                                        <td class="px-3 py-2.5 font-bold text-indigo-600 dark:text-indigo-400">{row.linkCount}</td>
                                    </tr>
                                {:else}
                                    <tr>
                                        <td colspan="3" class="px-4 py-8 text-center text-slate-400 font-sans text-xs">
                                            No external linking websites discovered yet. As the crawler crawls the web, inbound links to {currentDomain.name} will appear here.
                                        </td>
                                    </tr>
                                {/each}
                            </tbody>
                        </table>
                    {:else if activeTab === 'target_pages'}
                        <!-- Top Linked Pages -->
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 dark:bg-slate-950 text-slate-500 border-b border-slate-200 dark:border-slate-800">
                                <tr>
                                    <th class="px-4 py-2.5 font-medium">Target Page URL</th>
                                    <th class="px-3 py-2.5 font-medium">Inbound Links</th>
                                    <th class="px-3 py-2.5 font-medium">Referring Domains</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-mono">
                                {#each topLinkedPages as row}
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                                        <td class="px-4 py-2.5 font-sans font-medium text-slate-900 dark:text-white max-w-md truncate">
                                            <a href={`/console/inspect?url=${encodeURIComponent(row.url)}`} class="hover:text-indigo-600 hover:underline">
                                                {row.url}
                                            </a>
                                        </td>
                                        <td class="px-3 py-2.5 font-bold text-indigo-600 dark:text-indigo-400">{row.incomingLinks}</td>
                                        <td class="px-3 py-2.5 text-slate-600 dark:text-slate-400">{row.linkingDomainsCount}</td>
                                    </tr>
                                {:else}
                                    <tr>
                                        <td colspan="3" class="px-4 py-8 text-center text-slate-400 font-sans text-xs">
                                            No linked pages recorded yet.
                                        </td>
                                    </tr>
                                {/each}
                            </tbody>
                        </table>
                    {:else if activeTab === 'anchors'}
                        <!-- Top Anchor Texts -->
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 dark:bg-slate-950 text-slate-500 border-b border-slate-200 dark:border-slate-800">
                                <tr>
                                    <th class="px-4 py-2.5 font-medium">Anchor Text</th>
                                    <th class="px-3 py-2.5 font-medium">Inbound Links Count</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-mono">
                                {#each topAnchorTexts as row}
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                                        <td class="px-4 py-2.5 font-sans font-medium text-slate-900 dark:text-white">
                                            "{row.text}"
                                        </td>
                                        <td class="px-3 py-2.5 font-bold text-indigo-600 dark:text-indigo-400">{row.count}</td>
                                    </tr>
                                {:else}
                                    <tr>
                                        <td colspan="2" class="px-4 py-8 text-center text-slate-400 font-sans text-xs">
                                            No anchor texts indexed yet.
                                        </td>
                                    </tr>
                                {/each}
                            </tbody>
                        </table>
                    {:else if activeTab === 'internal'}
                        <!-- Top Internal Pages -->
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 dark:bg-slate-950 text-slate-500 border-b border-slate-200 dark:border-slate-800">
                                <tr>
                                    <th class="px-4 py-2.5 font-medium">Internal Page URL</th>
                                    <th class="px-3 py-2.5 font-medium">Internal Inbound Links</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-mono">
                                {#each topInternalPages as row}
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                                        <td class="px-4 py-2.5 font-sans font-medium text-slate-900 dark:text-white max-w-md truncate">
                                            <a href={`/console/inspect?url=${encodeURIComponent(row.url)}`} class="hover:text-indigo-600 hover:underline">
                                                {row.url}
                                            </a>
                                        </td>
                                        <td class="px-3 py-2.5 font-bold text-slate-900 dark:text-white">{row.internalLinks}</td>
                                    </tr>
                                {:else}
                                    <tr>
                                        <td colspan="2" class="px-4 py-8 text-center text-slate-400 font-sans text-xs">
                                            No internal links recorded yet.
                                        </td>
                                    </tr>
                                {/each}
                            </tbody>
                        </table>
                    {:else if activeTab === 'explorer'}
                        <!-- Link Graph Explorer -->
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 dark:bg-slate-950 text-slate-500 border-b border-slate-200 dark:border-slate-800">
                                <tr>
                                    <th class="px-4 py-2.5 font-medium">Source URL</th>
                                    <th class="px-3 py-2.5 font-medium">Target URL</th>
                                    <th class="px-3 py-2.5 font-medium">Anchor Text</th>
                                    <th class="px-3 py-2.5 font-medium">Type</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-mono">
                                {#each recentLinks as row}
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                                        <td class="px-4 py-2.5 font-sans font-medium text-slate-900 dark:text-white max-w-xs truncate">
                                            <a href={row.source_url} target="_blank" rel="noreferrer" class="hover:text-indigo-600 hover:underline">
                                                {row.source_url}
                                            </a>
                                        </td>
                                        <td class="px-3 py-2.5 font-sans text-slate-600 dark:text-slate-300 max-w-xs truncate">
                                            <a href={row.target_url} target="_blank" rel="noreferrer" class="hover:text-indigo-600 hover:underline">
                                                {row.target_url}
                                            </a>
                                        </td>
                                        <td class="px-3 py-2.5 text-slate-500 font-sans truncate max-w-[140px]">
                                            {row.anchor_text || '-'}
                                        </td>
                                        <td class="px-3 py-2.5">
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold {row.is_external ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-400' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400'}">
                                                {row.is_external ? 'External' : 'Internal'}
                                            </span>
                                        </td>
                                    </tr>
                                {:else}
                                    <tr>
                                        <td colspan="4" class="px-4 py-8 text-center text-slate-400 font-sans text-xs">
                                            No link records in explorer.
                                        </td>
                                    </tr>
                                {/each}
                            </tbody>
                        </table>
                    {/if}
                </div>
            </div>
        {:else}
            <!-- Empty state when no properties exist -->
            <div class="p-8 rounded-2xl bg-white dark:bg-slate-900/80 border border-slate-200 dark:border-slate-800 text-center max-w-lg mx-auto space-y-3">
                <div class="w-12 h-12 rounded-xl bg-indigo-50 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 flex items-center justify-center mx-auto">
                    <Network class="w-6 h-6" />
                </div>
                <h2 class="text-base font-bold text-slate-900 dark:text-white">No Link Graph Data</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                    Submit a website to start crawling and generating interlink relationships and backlink graphs.
                </p>
                <div class="pt-2">
                    <Link
                        href="/submit"
                        class="px-3.5 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold transition-colors"
                    >
                        Submit Website →
                    </Link>
                </div>
            </div>
        {/if}
    </main>

    <Footer />
</div>
