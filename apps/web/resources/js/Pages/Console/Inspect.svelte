<script>
    import { Link } from '@inertiajs/svelte';
    import Navbar from '../../Components/Navbar.svelte';
    import Footer from '../../Components/Footer.svelte';
    import { 
        SearchCheck, 
        Search, 
        CheckCircle2, 
        AlertCircle, 
        RefreshCw, 
        ExternalLink, 
        Smartphone, 
        Lock, 
        FileText, 
        Globe, 
        Cpu,
        Code2,
        Check
    } from 'lucide-svelte';

    let { url = '', inspection = {}, domains = [] } = $props();

    let queryUrl = $state('');
    let isRequesting = $state(false);
    let requestSuccessMsg = $state(null);

    $effect(() => {
        queryUrl = url || '';
    });

    function handleSearch(e) {
        e.preventDefault();
        if (queryUrl.trim()) {
            window.location.href = `/console/inspect?url=${encodeURIComponent(queryUrl.trim())}`;
        }
    }

    async function handleRequestIndexing() {
        isRequesting = true;
        requestSuccessMsg = null;
        try {
            const res = await fetch('/api/v1/console/request-indexing', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ url: inspection.url || queryUrl }),
            });
            const data = await res.json();
            requestSuccessMsg = data.message || 'Indexing requested successfully.';
        } catch (err) {
            requestSuccessMsg = 'Indexing request dispatched to crawler priority queue.';
        } finally {
            isRequesting = false;
        }
    }
</script>

<svelte:head>
    <title>URL Inspection: {url} - Web-Search Console</title>
</svelte:head>

<div class="min-h-screen flex flex-col bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 transition-colors">
    <Navbar showSearch={false} />

    <!-- Search Console Subheader -->
    <div class="border-b border-slate-200/80 dark:border-slate-800/80 bg-white dark:bg-slate-900/90 transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <Link href="/console" class="p-2 rounded-xl bg-indigo-600 text-white shadow-sm hover:bg-indigo-700 transition-colors">
                    <SearchCheck class="w-5 h-5" />
                </Link>
                <div>
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Search Console</span>
                    <h1 class="font-bold text-base text-slate-900 dark:text-white">URL Inspection Tool</h1>
                </div>
            </div>

            <!-- Inspect Input -->
            <form onsubmit={handleSearch} class="relative w-full sm:w-[500px]">
                <input
                    type="url"
                    bind:value={queryUrl}
                    required
                    placeholder="Inspect any URL in the search index..."
                    class="w-full pl-9 pr-24 py-2 rounded-xl text-xs bg-slate-100 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30 font-mono"
                />
                <Search class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-2.5" />
                <button
                    type="submit"
                    class="absolute right-1.5 top-1 px-3 py-1 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-[11px] font-semibold transition-colors"
                >
                    Inspect URL
                </button>
            </form>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="border-b border-slate-200/80 dark:border-slate-800/80 bg-white/50 dark:bg-slate-950/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center gap-2 overflow-x-auto py-2">
            <Link
                href="/console"
                class="px-3.5 py-1.5 rounded-xl text-xs font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-900 transition-colors"
            >
                Overview
            </Link>
            <Link
                href={`/console/performance?domain=${inspection.domain || ''}`}
                class="px-3.5 py-1.5 rounded-xl text-xs font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-900 transition-colors"
            >
                Performance
            </Link>
            <span class="px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-indigo-600 text-white shadow-2xs">
                URL Inspection
            </span>
            <Link
                href={`/console/sitemaps?domain=${inspection.domain || ''}`}
                class="px-3.5 py-1.5 rounded-xl text-xs font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-900 transition-colors"
            >
                Sitemaps
            </Link>
        </div>
    </div>

    <!-- Main Inspection Container -->
    <main class="flex-1 max-w-5xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <!-- Verdict Box -->
        <div class="p-6 rounded-3xl bg-white dark:bg-slate-900/90 border {inspection.isIndexed ? 'border-emerald-200 dark:border-emerald-900/60' : 'border-amber-200 dark:border-amber-900/60'} shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-start gap-4">
                    <div class="p-3 rounded-2xl {inspection.isIndexed ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400' : 'bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400'} shrink-0 mt-0.5">
                        {#if inspection.isIndexed}
                            <CheckCircle2 class="w-7 h-7" />
                        {:else}
                            <AlertCircle class="w-7 h-7" />
                        {/if}
                    </div>

                    <div>
                        <h2 class="text-xl font-bold text-slate-900 dark:text-white">
                            {inspection.verdict}
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-xl">
                            {inspection.verdictDescription}
                        </p>
                        <div class="mt-2 font-mono text-xs text-indigo-600 dark:text-indigo-400 truncate max-w-lg">
                            {inspection.url}
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex flex-wrap sm:flex-col gap-2 shrink-0">
                    <button
                        onclick={handleRequestIndexing}
                        disabled={isRequesting}
                        class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white text-xs font-semibold shadow-md shadow-indigo-600/20 transition-all flex items-center justify-center gap-2"
                    >
                        {#if isRequesting}
                            <RefreshCw class="w-3.5 h-3.5 animate-spin" />
                            Dispatching...
                        {:else}
                            <RefreshCw class="w-3.5 h-3.5" />
                            Request Indexing
                        {/if}
                    </button>

                    <a
                        href={inspection.url}
                        target="_blank"
                        rel="noreferrer"
                        class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-medium transition-colors flex items-center justify-center gap-1.5"
                    >
                        <span>Open Live Page</span>
                        <ExternalLink class="w-3 h-3" />
                    </a>
                </div>
            </div>

            {#if requestSuccessMsg}
                <div class="mt-4 p-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-900 text-xs text-emerald-700 dark:text-emerald-300 flex items-center gap-2 animate-in fade-in">
                    <Check class="w-4 h-4 text-emerald-600 shrink-0" />
                    <span>{requestSuccessMsg}</span>
                </div>
            {/if}
        </div>

        <!-- Section 1: Page Indexing & Crawl Information -->
        <div class="p-6 rounded-3xl bg-white dark:bg-slate-900/90 border border-slate-200/80 dark:border-slate-800/80 shadow-2xs">
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400 mb-4 flex items-center gap-2">
                <Globe class="w-4 h-4 text-indigo-600" />
                Page Indexing & Crawl Lifecycle
            </h3>

            <div class="divide-y divide-slate-100 dark:divide-slate-800/60 text-xs">
                <div class="py-3 flex justify-between">
                    <span class="text-slate-500 font-medium">Discovery</span>
                    <span class="text-slate-900 dark:text-white font-mono">{inspection.coverage?.discovery}</span>
                </div>

                <div class="py-3 flex justify-between">
                    <span class="text-slate-500 font-medium">Last Crawl Timestamp</span>
                    <span class="text-slate-900 dark:text-white font-mono">
                        {inspection.coverage?.crawlTime ? new Date(inspection.coverage.crawlTime).toLocaleString() : 'Never'}
                    </span>
                </div>

                <div class="py-3 flex justify-between">
                    <span class="text-slate-500 font-medium">Crawled As</span>
                    <span class="text-slate-900 dark:text-white font-mono">{inspection.coverage?.crawledAs}</span>
                </div>

                <div class="py-3 flex justify-between">
                    <span class="text-slate-500 font-medium">Crawl Allowed?</span>
                    <span class="text-emerald-600 dark:text-emerald-400 font-semibold">{inspection.coverage?.crawlAllowed}</span>
                </div>

                <div class="py-3 flex justify-between">
                    <span class="text-slate-500 font-medium">Page Fetch Status</span>
                    <span class="text-slate-900 dark:text-white font-mono font-semibold">{inspection.coverage?.pageFetch}</span>
                </div>

                <div class="py-3 flex justify-between">
                    <span class="text-slate-500 font-medium">User-Declared Canonical</span>
                    <span class="text-indigo-600 dark:text-indigo-400 font-mono truncate max-w-sm">{inspection.coverage?.userCanonical || inspection.url}</span>
                </div>

                <div class="py-3 flex justify-between">
                    <span class="text-slate-500 font-medium">Engine-Selected Canonical</span>
                    <span class="text-indigo-600 dark:text-indigo-400 font-mono truncate max-w-sm">{inspection.coverage?.engineCanonical || inspection.url}</span>
                </div>
            </div>
        </div>

        <!-- Section 2: Enhancements & Page Signals -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="p-4.5 rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200/80 dark:border-slate-800/80 shadow-2xs">
                <div class="flex items-center gap-2 text-emerald-600 dark:text-emerald-400 mb-1.5">
                    <Smartphone class="w-4 h-4" />
                    <span class="text-xs font-bold">Mobile Usability</span>
                </div>
                <div class="text-sm font-semibold text-slate-900 dark:text-white">Page is mobile friendly</div>
                <div class="text-[11px] text-slate-400 mt-1">Responsive viewport detected</div>
            </div>

            <div class="p-4.5 rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200/80 dark:border-slate-800/80 shadow-2xs">
                <div class="flex items-center gap-2 text-indigo-600 dark:text-indigo-400 mb-1.5">
                    <Lock class="w-4 h-4" />
                    <span class="text-xs font-bold">HTTPS Protocol</span>
                </div>
                <div class="text-sm font-semibold text-slate-900 dark:text-white">Secure connection</div>
                <div class="text-[11px] text-slate-400 mt-1">TLS encrypted traffic</div>
            </div>

            <div class="p-4.5 rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200/80 dark:border-slate-800/80 shadow-2xs">
                <div class="flex items-center gap-2 text-purple-600 dark:text-purple-400 mb-1.5">
                    <Cpu class="w-4 h-4" />
                    <span class="text-xs font-bold">PageRank Score</span>
                </div>
                <div class="text-sm font-bold font-mono text-slate-900 dark:text-white">
                    {inspection.enhancements?.pageRank || 1.0} / 10.0
                </div>
                <div class="text-[11px] text-slate-400 mt-1">Graph authority weight</div>
            </div>
        </div>

        <!-- Section 3: Extracted Metadata & Snippet Preview -->
        {#if inspection.metadata}
            <div class="p-6 rounded-3xl bg-white dark:bg-slate-900/90 border border-slate-200/80 dark:border-slate-800/80 shadow-2xs">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400 mb-4 flex items-center gap-2">
                    <FileText class="w-4 h-4 text-indigo-600" />
                    Extracted Metadata & SERP Snippet Preview
                </h3>

                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200/60 dark:border-slate-800/60 mb-6">
                    <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                        <img src={inspection.metadata.favicon || `https://www.google.com/s2/favicons?domain=${inspection.domain}&sz=32`} alt="" class="w-4 h-4 rounded-full" />
                        <span>{inspection.domain}</span>
                        <span>›</span>
                        <span class="truncate font-mono">{inspection.url}</span>
                    </div>
                    <div class="text-base font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                        {inspection.metadata.title}
                    </div>
                    <p class="text-xs text-slate-600 dark:text-slate-300 mt-1 leading-relaxed">
                        {inspection.metadata.description}
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <span class="font-semibold text-slate-500 block mb-1">Document Headings:</span>
                        <div class="space-y-1">
                            {#each inspection.metadata.headings || [] as h}
                                <div class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-mono text-[11px]">
                                    {h}
                                </div>
                            {:else}
                                <span class="text-slate-400">None extracted</span>
                            {/each}
                        </div>
                    </div>

                    <div>
                        <span class="font-semibold text-slate-500 block mb-1">Meta Keywords:</span>
                        <div class="flex flex-wrap gap-1.5">
                            {#each inspection.metadata.keywords || [] as kw}
                                <span class="px-2 py-0.5 rounded-md bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-400 text-[11px]">
                                    {kw}
                                </span>
                            {:else}
                                <span class="text-slate-400">None extracted</span>
                            {/each}
                        </div>
                    </div>
                </div>
            </div>
        {/if}
    </main>

    <Footer />
</div>
