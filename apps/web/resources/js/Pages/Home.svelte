<script>
    import { Link } from '@inertiajs/svelte';
    import Navbar from '../Components/Navbar.svelte';
    import Footer from '../Components/Footer.svelte';
    import SearchBar from '../Components/SearchBar.svelte';
    import { Shield, Lock, Cpu, Globe, Search, Terminal, Plus, BarChart2, CheckCircle2, Shuffle } from 'lucide-svelte';

    let { totalDocuments = 0, totalDomains = 0, isReady = true } = $props();

    const quickQueries = [
        'laravel 12 svelte 5',
        'open source search engine',
        'decentralized crawler',
        'sqlite fts5 ranking',
    ];
</script>

<svelte:head>
    <title>Web-Search.org - The Open Source, Privacy Search Engine</title>
</svelte:head>

<div class="min-h-screen flex flex-col bg-white dark:bg-black text-zinc-900 dark:text-zinc-100 transition-colors">
    <Navbar showSearch={false} />

    <main class="flex-1 flex flex-col items-center justify-center px-4 sm:px-6 lg:px-8 py-12 max-w-4xl mx-auto w-full">
        <!-- Brand Hero -->
        <div class="text-center mb-8 space-y-3">
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-zinc-100 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 text-zinc-800 dark:text-zinc-200">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span>Open Source • 100% Private</span>
            </div>

            <h1 class="text-4xl sm:text-6xl font-extrabold tracking-tight text-black dark:text-white">
                Web-Search<span class="text-zinc-400">.org</span>
            </h1>

            <p class="text-xs sm:text-sm text-zinc-500 dark:text-zinc-400 max-w-md mx-auto">
                Decentralized, open-source search engine. Zero tracking, zero telemetry, full transparency.
            </p>
        </div>

        <!-- Central Search Bar -->
        <div class="w-full max-w-2xl mb-4">
            <SearchBar size="large" />
        </div>

        <!-- Quick Suggestions & Random Word Button -->
        <div class="flex flex-wrap items-center justify-center gap-1.5 mb-12 text-xs">
            <a
                href="/search/random"
                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-black text-white dark:bg-white dark:text-black font-semibold hover:bg-zinc-800 dark:hover:bg-zinc-200 transition-colors"
                title="Search a random word from the dictionary"
            >
                <Shuffle class="w-3 h-3" />
                <span>Random Word</span>
            </a>
            <span class="text-zinc-400 mx-1">or explore:</span>
            {#each quickQueries as query}
                <a
                    href={`/search?q=${encodeURIComponent(query)}`}
                    class="px-2.5 py-1 rounded-md bg-zinc-100 dark:bg-zinc-900 text-zinc-700 dark:text-zinc-300 hover:text-black dark:hover:text-white hover:bg-zinc-200 dark:hover:bg-zinc-800 border border-zinc-200 dark:border-zinc-800 transition-colors"
                >
                    {query}
                </a>
            {/each}
        </div>

        <!-- 3 Feature Pillars (Monochrome Flat) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 w-full max-w-3xl">
            <div class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 space-y-1.5">
                <div class="w-8 h-8 rounded-lg bg-black text-white dark:bg-white dark:text-black flex items-center justify-center font-bold">
                    <Shield class="w-4 h-4" />
                </div>
                <h2 class="text-xs font-bold uppercase tracking-wider text-black dark:text-white">100% Private</h2>
                <p class="text-xs text-zinc-500 leading-relaxed">
                    No search profiling, no tracking cookies, and no query history saved to external advertisers.
                </p>
            </div>

            <div class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 space-y-1.5">
                <div class="w-8 h-8 rounded-lg bg-black text-white dark:bg-white dark:text-black flex items-center justify-center font-bold">
                    <Globe class="w-4 h-4" />
                </div>
                <h2 class="text-xs font-bold uppercase tracking-wider text-black dark:text-white">Distributed Crawling</h2>
                <p class="text-xs text-zinc-500 leading-relaxed">
                    Autonomous high-performance crawler nodes running asyncio and PageRank graph analysis.
                </p>
            </div>

            <div class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 space-y-1.5">
                <div class="w-8 h-8 rounded-lg bg-black text-white dark:bg-white dark:text-black flex items-center justify-center font-bold">
                    <Terminal class="w-4 h-4" />
                </div>
                <h2 class="text-xs font-bold uppercase tracking-wider text-black dark:text-white">Open Monorepo</h2>
                <p class="text-xs text-zinc-500 leading-relaxed">
                    Laravel 12, Svelte 5, Python indexer, and TypeScript / PHP client SDKs.
                </p>
            </div>
        </div>

        <!-- Submit website prompt -->
        <div class="mt-8 text-center">
            <Link
                href="/submit"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-black text-white dark:bg-white dark:text-black text-xs font-bold hover:bg-zinc-800 dark:hover:bg-zinc-200 transition-colors"
            >
                <Plus class="w-3.5 h-3.5" />
                <span>Submit your website to the index</span>
            </Link>
        </div>
    </main>

    <Footer />
</div>
