<script>
    import { Link, useForm } from '@inertiajs/svelte';
    import Navbar from '../../Components/Navbar.svelte';
    import Footer from '../../Components/Footer.svelte';
    import { 
        SearchCheck, 
        CheckCircle2, 
        AlertCircle, 
        ExternalLink, 
        RefreshCw, 
        Shield, 
        Smartphone, 
        Search,
        Layers,
        Clock,
        FileCode
    } from 'lucide-svelte';

    let { url = '', inspection = null, domains = [] } = $props();

    const form = useForm({
        url: '',
    });

    const indexForm = useForm({
        url: '',
    });

    $effect(() => {
        $form.url = url || '';
        $indexForm.url = inspection?.url || '';
    });

    function handleSearch(e) {
        e.preventDefault();
        window.location.href = `/console/inspect?url=${encodeURIComponent($form.url)}`;
    }

    function handleRequestIndexing(e) {
        e.preventDefault();
        if (inspection?.url) {
            $indexForm.url = inspection.url;
            $indexForm.post('/console/inspect/request-indexing');
        }
    }
</script>

<svelte:head>
    <title>URL Inspection - Web-Search Console</title>
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
                    <h1 class="font-bold text-sm text-black dark:text-white">URL Inspection Tool</h1>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="border-b border-zinc-200 dark:border-zinc-800 bg-white dark:bg-black">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center gap-2 overflow-x-auto py-2">
            <Link
                href="/console"
                class="px-3 py-1 rounded-md text-xs font-medium text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-900 transition-colors"
            >
                Overview
            </Link>
            <Link
                href={`/console/performance${inspection?.domain ? `?domain=${inspection.domain}` : ''}`}
                class="px-3 py-1 rounded-md text-xs font-medium text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-900 transition-colors"
            >
                Performance
            </Link>
            <Link
                href={`/console/links${inspection?.domain ? `?domain=${inspection.domain}` : ''}`}
                class="px-3 py-1 rounded-md text-xs font-medium text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-900 transition-colors"
            >
                Links & Interlinking
            </Link>
            <span class="px-3 py-1 rounded-md text-xs font-semibold bg-black text-white dark:bg-white dark:text-black">
                URL Inspection
            </span>
            <Link
                href={`/console/sitemaps${inspection?.domain ? `?domain=${inspection.domain}` : ''}`}
                class="px-3 py-1 rounded-md text-xs font-medium text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-900 transition-colors"
            >
                Sitemaps
            </Link>
        </div>
    </div>

    <main class="flex-1 max-w-5xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
        <!-- Inspection Search Input Bar -->
        <div class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800">
            <form onsubmit={handleSearch} class="flex items-center gap-2">
                <div class="relative flex-1">
                    <input
                        type="url"
                        bind:value={$form.url}
                        required
                        placeholder="Inspect any URL (e.g. https://example.com/docs)"
                        class="w-full pl-9 pr-3 py-2 rounded-lg text-xs bg-white dark:bg-black border border-zinc-300 dark:border-zinc-800 text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-black dark:focus:ring-white font-mono"
                    />
                    <Search class="w-4 h-4 text-zinc-400 absolute left-3 top-2.5" />
                </div>
                <button
                    type="submit"
                    class="px-4 py-2 rounded-lg bg-black text-white dark:bg-white dark:text-black hover:bg-zinc-800 dark:hover:bg-zinc-200 text-xs font-bold transition-colors shrink-0 cursor-pointer"
                >
                    Inspect URL
                </button>
            </form>
        </div>

        {#if inspection}
            <!-- Verdict Status Banner (Monochrome) -->
            <div class="p-5 rounded-xl border {inspection.isIndexed ? 'bg-zinc-50 dark:bg-zinc-950 border-zinc-300 dark:border-zinc-700' : 'bg-zinc-50 dark:bg-zinc-950 border-zinc-300 dark:border-zinc-800'}">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-start gap-3">
                        <div class="p-2 rounded-lg bg-black text-white dark:bg-white dark:text-black shrink-0 mt-0.5">
                            {#if inspection.isIndexed}
                                <CheckCircle2 class="w-5 h-5" />
                            {:else}
                                <AlertCircle class="w-5 h-5" />
                            {/if}
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-black dark:text-white">{inspection.verdict}</h2>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5 max-w-xl">{inspection.verdictDescription}</p>
                            <span class="inline-block mt-2 font-mono text-[11px] text-zinc-400 truncate max-w-lg">{inspection.url}</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <form onsubmit={handleRequestIndexing}>
                            <button
                                type="submit"
                                disabled={$indexForm.processing}
                                class="px-3.5 py-1.5 rounded-lg bg-black text-white dark:bg-white dark:text-black hover:bg-zinc-800 dark:hover:bg-zinc-200 text-xs font-bold transition-colors cursor-pointer"
                            >
                                Request Indexing
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Detailed Technical Breakdown -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Page Indexing & Coverage -->
                <div class="p-5 rounded-xl bg-white dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 space-y-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-black dark:text-white flex items-center gap-2">
                        <Layers class="w-4 h-4" />
                        Page Indexing & Discovery
                    </h3>

                    <div class="divide-y divide-zinc-100 dark:divide-zinc-800 text-xs">
                        <div class="py-2 flex justify-between">
                            <span class="text-zinc-500">Discovery</span>
                            <span class="font-medium text-black dark:text-white text-right">{inspection.coverage?.discovery}</span>
                        </div>
                        <div class="py-2 flex justify-between">
                            <span class="text-zinc-500">Crawl Allowed</span>
                            <span class="font-medium text-emerald-600 dark:text-emerald-400 font-mono">{inspection.coverage?.crawlAllowed}</span>
                        </div>
                        <div class="py-2 flex justify-between">
                            <span class="text-zinc-500">Page Fetch</span>
                            <span class="font-medium text-black dark:text-white font-mono">{inspection.coverage?.pageFetch}</span>
                        </div>
                        <div class="py-2 flex justify-between">
                            <span class="text-zinc-500">Indexing Allowed</span>
                            <span class="font-medium text-black dark:text-white font-mono">{inspection.coverage?.indexingAllowed}</span>
                        </div>
                    </div>
                </div>

                <!-- Enhancements & PageRank -->
                <div class="p-5 rounded-xl bg-white dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 space-y-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-black dark:text-white flex items-center gap-2">
                        <Shield class="w-4 h-4" />
                        Enhancements & Rank
                    </h3>

                    <div class="divide-y divide-zinc-100 dark:divide-zinc-800 text-xs">
                        <div class="py-2 flex justify-between">
                            <span class="text-zinc-500">Mobile Usability</span>
                            <span class="font-medium text-emerald-600 dark:text-emerald-400">Mobile Friendly</span>
                        </div>
                        <div class="py-2 flex justify-between">
                            <span class="text-zinc-500">HTTPS Security</span>
                            <span class="font-medium text-black dark:text-white">HTTPS Valid</span>
                        </div>
                        <div class="py-2 flex justify-between">
                            <span class="text-zinc-500">Computed PageRank</span>
                            <span class="font-medium text-black dark:text-white font-mono font-bold">
                                {inspection.enhancements?.pageRank ?? 1.0} / 10.0
                            </span>
                        </div>
                        <div class="py-2 flex justify-between">
                            <span class="text-zinc-500">Inbound Backlinks</span>
                            <span class="font-medium text-black dark:text-white font-mono">
                                {inspection.enhancements?.inLinksCount ?? 0} referring links
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        {/if}
    </main>

    <Footer />
</div>
