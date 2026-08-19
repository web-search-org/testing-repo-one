<script>
    import { Link } from '@inertiajs/svelte';
    import Navbar from '../Components/Navbar.svelte';
    import Footer from '../Components/Footer.svelte';
    import SearchBar from '../Components/SearchBar.svelte';
    import { Globe, Shield, Zap, Sparkles, Terminal, Code2, Database } from 'lucide-svelte';

    let { stats = { total_pages: 0, total_domains: 0 } } = $props();

    const quickLinks = [
        { label: 'Laravel Framework', query: 'laravel' },
        { label: 'Svelte 5 Runes', query: 'svelte' },
        { label: 'Python Async Crawler', query: 'python crawler' },
        { label: 'Open Source Search', query: 'open source search engine' },
        { label: 'GitHub Repository', query: 'site:github.com' },
    ];
</script>

<svelte:head>
    <title>Web-Search.org - The Open Source Search Engine</title>
</svelte:head>

<div class="min-h-screen flex flex-col bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 transition-colors">
    <Navbar showSearch={false} />

    <!-- Hero Search Section -->
    <main class="flex-1 flex flex-col items-center justify-center px-4 sm:px-6 lg:px-8 py-12 max-w-4xl mx-auto w-full">
        <!-- Logo & Title -->
        <div class="text-center mb-8 animate-in fade-in zoom-in-95 duration-200">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-3xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-purple-500 text-white shadow-xl shadow-indigo-500/25 mb-5 ring-4 ring-indigo-50 dark:ring-indigo-950/50">
                <Globe class="w-10 h-10" />
            </div>

            <h1 class="text-4xl sm:text-5xl font-extrabold tracking-tight bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-800 dark:from-white dark:via-indigo-200 dark:to-slate-200 bg-clip-text text-transparent">
                Web-Search<span class="text-indigo-600 dark:text-indigo-400">.org</span>
            </h1>

            <p class="mt-3 text-base sm:text-lg text-slate-500 dark:text-slate-400 max-w-lg mx-auto">
                The open-source, privacy-first, community-indexed search engine.
            </p>
        </div>

        <!-- Search Box -->
        <div class="w-full max-w-2xl">
            <SearchBar size="large" />

            <!-- Quick Suggestions -->
            <div class="mt-4 flex flex-wrap items-center justify-center gap-2">
                <span class="text-xs text-slate-400 dark:text-slate-500 mr-1">Trending:</span>
                {#each quickLinks as item}
                    <a
                        href={`/search?q=${encodeURIComponent(item.query)}`}
                        class="px-2.5 py-1 rounded-full text-xs font-medium bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:border-indigo-300 dark:hover:border-indigo-800 transition-all shadow-2xs"
                    >
                        {item.label}
                    </a>
                {/each}
            </div>
        </div>

        <!-- Features Grid -->
        <div class="mt-16 grid grid-cols-1 sm:grid-cols-3 gap-6 w-full max-w-3xl">
            <div class="p-5 rounded-2xl bg-white dark:bg-slate-900/60 border border-slate-200/80 dark:border-slate-800/80 shadow-xs">
                <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mb-3">
                    <Shield class="w-5 h-5" />
                </div>
                <h2 class="font-semibold text-sm text-slate-900 dark:text-white">Zero Tracking</h2>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                    No search logs, no fingerprinting, no ad auctions. Complete anonymity by design.
                </p>
            </div>

            <div class="p-5 rounded-2xl bg-white dark:bg-slate-900/60 border border-slate-200/80 dark:border-slate-800/80 shadow-xs">
                <div class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center mb-3">
                    <Zap class="w-5 h-5" />
                </div>
                <h2 class="font-semibold text-sm text-slate-900 dark:text-white">High-Speed Index</h2>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                    Sub-millisecond BM25 relevance scoring and distributed async crawling pipeline.
                </p>
            </div>

            <div class="p-5 rounded-2xl bg-white dark:bg-slate-900/60 border border-slate-200/80 dark:border-slate-800/80 shadow-xs">
                <div class="w-9 h-9 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center mb-3">
                    <Code2 class="w-5 h-5" />
                </div>
                <h2 class="font-semibold text-sm text-slate-900 dark:text-white">Open Developer APIs</h2>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                    Full REST API, TypeScript and PHP SDKs for building your own search apps.
                </p>
            </div>
        </div>

        <!-- Monorepo Architecture Overview Banner -->
        <div class="mt-12 w-full max-w-3xl p-5 rounded-2xl bg-slate-900 text-slate-100 dark:bg-slate-900/90 border border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="p-2.5 rounded-xl bg-indigo-500/20 text-indigo-400">
                    <Database class="w-5 h-5" />
                </div>
                <div>
                    <h2 class="text-sm font-semibold">Decentralized Web Index</h2>
                    <p class="text-xs text-slate-400">
                        {stats.total_pages} web pages indexed across {stats.total_domains} verified domains.
                    </p>
                </div>
            </div>

            <Link
                href="/crawler"
                class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-md shadow-indigo-600/20 transition-colors whitespace-nowrap"
            >
                Submit URL to Crawler →
            </Link>
        </div>
    </main>

    <Footer />
</div>
