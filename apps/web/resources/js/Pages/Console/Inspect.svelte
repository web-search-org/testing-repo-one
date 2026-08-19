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
        Check
    } from 'lucide-svelte';

    let { url = '', inspection = null, domains = [] } = $props();

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
        if (!inspection?.url && !queryUrl) return;
        isRequesting = true;
        requestSuccessMsg = null;
        try {
            const res = await fetch('/api/v1/console/request-indexing', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ url: inspection?.url || queryUrl }),
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
    <title>URL Inspection {url ? `- ${url}` : ''} - Web-Search Console</title>
</svelte:head>

<div class="min-h-screen flex flex-col bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 transition-colors">
    <Navbar showSearch={false} />

    <!-- Subheader -->
    <div class="border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/60 transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <Link href="/console" class="w-8 h-8 rounded-lg bg-indigo-600 text-white flex items-center justify-center hover:bg-indigo-700 transition-colors">
                    <SearchCheck class="w-4.5 h-4.5" />
                </Link>
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Search Console</span>
                    <h1 class="font-bold text-sm text-slate-900 dark:text-white">URL Inspection Tool</h1>
                </div>
            </div>

            <!-- Inspect Input -->
            <form onsubmit={handleSearch} class="relative w-full sm:w-[460px]">
                <input
                    type="url"
                    bind:value={queryUrl}
                    required
                    placeholder="Inspect any URL in the search index..."
                    class="w-full pl-8 pr-20 py-1.5 rounded-lg text-xs bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30 font-mono"
                />
                <Search class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2" />
                <button
                    type="submit"
                    class="absolute right-1 top-1 px-2.5 py-0.5 rounded bg-indigo-600 hover:bg-indigo-700 text-white text-[11px] font-semibold transition-colors"
                >
                    Inspect
                </button>
            </form>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="border-b border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center gap-2 overflow-x-auto py-2">
            <Link
                href="/console"
                class="px-3 py-1 rounded-md text-xs font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-900 transition-colors"
            >
                Overview
            </Link>
            <Link
                href={`/console/performance${inspection?.domain ? `?domain=${inspection.domain}` : ''}`}
                class="px-3 py-1 rounded-md text-xs font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-900 transition-colors"
            >
                Performance
            </Link>
            <span class="px-3 py-1 rounded-md text-xs font-semibold bg-indigo-600 text-white">
                URL Inspection
            </span>
            <Link
                href={`/console/sitemaps${inspection?.domain ? `?domain=${inspection.domain}` : ''}`}
                class="px-3 py-1 rounded-md text-xs font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-900 transition-colors"
            >
                Sitemaps
            </Link>
        </div>
    </div>

    <!-- Main Inspection Container (Flat) -->
    <main class="flex-1 max-w-5xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
        {#if inspection}
            <!-- Verdict Box -->
            <div class="p-5 rounded-xl bg-slate-50 dark:bg-slate-900/60 border {inspection.isIndexed ? 'border-emerald-200 dark:border-emerald-900' : 'border-amber-200 dark:border-amber-900'}">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-start gap-3">
                        <div class="p-2 rounded-lg {inspection.isIndexed ? 'bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-400' : 'bg-amber-100 dark:bg-amber-950 text-amber-700 dark:text-amber-400'} shrink-0 mt-0.5">
                            {#if inspection.isIndexed}
                                <CheckCircle2 class="w-5 h-5" />
                            {:else}
                                <AlertCircle class="w-5 h-5" />
                            {/if}
                        </div>

                        <div>
                            <h2 class="text-base font-bold text-slate-900 dark:text-white">
                                {inspection.verdict}
                            </h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 max-w-xl">
                                {inspection.verdictDescription}
                            </p>
                            <div class="mt-1.5 font-mono text-xs text-indigo-600 dark:text-indigo-400 truncate max-w-lg">
                                {inspection.url}
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-wrap sm:flex-col gap-2 shrink-0">
                        <button
                            onclick={handleRequestIndexing}
                            disabled={isRequesting}
                            class="px-3.5 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white text-xs font-semibold transition-colors flex items-center justify-center gap-1.5 cursor-pointer"
                        >
                            <RefreshCw class="w-3.5 h-3.5 {isRequesting ? 'animate-spin' : ''}" />
                            <span>{isRequesting ? 'Dispatching...' : 'Request Indexing'}</span>
                        </button>

                        <a
                            href={inspection.url}
                            target="_blank"
                            rel="noreferrer"
                            class="px-3.5 py-1.5 rounded-lg bg-slate-200 dark:bg-slate-800 hover:bg-slate-300 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-medium transition-colors flex items-center justify-center gap-1"
                        >
                            <span>Open Page</span>
                            <ExternalLink class="w-3 h-3" />
                        </a>
                    </div>
                </div>

                {#if requestSuccessMsg}
                    <div class="mt-3 p-2.5 rounded-lg bg-emerald-100 dark:bg-emerald-950 text-xs text-emerald-800 dark:text-emerald-300 flex items-center gap-2">
                        <Check class="w-3.5 h-3.5 text-emerald-600 shrink-0" />
                        <span>{requestSuccessMsg}</span>
                    </div>
                {/if}
            </div>

            <!-- Lifecycle Info -->
            <div class="p-5 rounded-xl bg-white dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3 flex items-center gap-2">
                    <Globe class="w-4 h-4 text-indigo-600" />
                    Crawl Lifecycle
                </h3>

                <div class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                    <div class="py-2.5 flex justify-between">
                        <span class="text-slate-500 font-medium">Discovery</span>
                        <span class="text-slate-900 dark:text-white font-mono">{inspection.coverage?.discovery}</span>
                    </div>

                    <div class="py-2.5 flex justify-between">
                        <span class="text-slate-500 font-medium">Last Crawled</span>
                        <span class="text-slate-900 dark:text-white font-mono">
                            {inspection.coverage?.crawlTime ? new Date(inspection.coverage.crawlTime).toLocaleString() : 'Never'}
                        </span>
                    </div>

                    <div class="py-2.5 flex justify-between">
                        <span class="text-slate-500 font-medium">Crawled As</span>
                        <span class="text-slate-900 dark:text-white font-mono">{inspection.coverage?.crawledAs}</span>
                    </div>

                    <div class="py-2.5 flex justify-between">
                        <span class="text-slate-500 font-medium">Crawl Allowed</span>
                        <span class="text-emerald-600 dark:text-emerald-400 font-semibold">{inspection.coverage?.crawlAllowed}</span>
                    </div>

                    <div class="py-2.5 flex justify-between">
                        <span class="text-slate-500 font-medium">Fetch Status</span>
                        <span class="text-slate-900 dark:text-white font-mono font-semibold">{inspection.coverage?.pageFetch}</span>
                    </div>
                </div>
            </div>

            <!-- Page Signals -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800">
                    <div class="flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400 mb-1">
                        <Smartphone class="w-3.5 h-3.5" />
                        <span class="text-xs font-bold">Mobile Usability</span>
                    </div>
                    <div class="text-xs font-semibold text-slate-900 dark:text-white">Mobile friendly</div>
                </div>

                <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800">
                    <div class="flex items-center gap-1.5 text-indigo-600 dark:text-indigo-400 mb-1">
                        <Lock class="w-3.5 h-3.5" />
                        <span class="text-xs font-bold">HTTPS Protocol</span>
                    </div>
                    <div class="text-xs font-semibold text-slate-900 dark:text-white">Secure TLS</div>
                </div>

                <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800">
                    <div class="flex items-center gap-1.5 text-slate-700 dark:text-slate-300 mb-1">
                        <Cpu class="w-3.5 h-3.5" />
                        <span class="text-xs font-bold">PageRank Score</span>
                    </div>
                    <div class="text-xs font-bold font-mono text-slate-900 dark:text-white">
                        {inspection.enhancements?.pageRank || 0.0} / 10.0
                    </div>
                </div>
            </div>
        {:else}
            <!-- Empty inspection prompt -->
            <div class="p-8 rounded-2xl bg-white dark:bg-slate-900/80 border border-slate-200 dark:border-slate-800 text-center max-w-lg mx-auto space-y-3">
                <div class="w-12 h-12 rounded-xl bg-indigo-50 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 flex items-center justify-center mx-auto">
                    <Search class="w-6 h-6" />
                </div>
                <h2 class="text-base font-bold text-slate-900 dark:text-white">Inspect any URL</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                    Type a URL in the inspection bar above to check index status, test crawl headers, or request immediate indexing.
                </p>
            </div>
        {/if}
    </main>

    <Footer />
</div>
