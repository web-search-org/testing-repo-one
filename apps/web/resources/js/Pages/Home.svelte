<script>
    import { Link } from '@inertiajs/svelte';
    import Navbar from '../Components/Navbar.svelte';
    import Footer from '../Components/Footer.svelte';
    import SearchBar from '../Components/SearchBar.svelte';
    import { Globe, Shield, Zap, Code2, Database, PlusCircle } from 'lucide-svelte';

    let { stats = { total_pages: 0, total_domains: 0 } } = $props();

    const quickLinks = [
        { label: 'Submit Website', query: '/submit', isLink: true },
        { label: 'Laravel Framework', query: 'laravel' },
        { label: 'Svelte 5 Runes', query: 'svelte' },
        { label: 'Python Async Crawler', query: 'python crawler' },
        { label: 'Open Source Search', query: 'open source search engine' },
    ];
</script>

<svelte:head>
    <title>Web-Search.org - The Open Source Search Engine</title>
</svelte:head>

<div class="min-h-screen flex flex-col bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 transition-colors">
    <Navbar showSearch={false} />

    <!-- Hero Search Section -->
    <main class="flex-1 flex flex-col items-center justify-center px-4 sm:px-6 lg:px-8 py-12 max-w-4xl mx-auto w-full">
        <!-- Logo & Title -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-indigo-600 text-white mb-4">
                <Globe class="w-8 h-8" />
            </div>

            <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                Web-Search<span class="text-indigo-600 dark:text-indigo-400">.org</span>
            </h1>

            <p class="mt-2 text-sm sm:text-base text-slate-600 dark:text-slate-400 max-w-lg mx-auto">
                The open-source, privacy-first, community-indexed search engine.
            </p>
        </div>

        <!-- Search Box -->
        <div class="w-full max-w-2xl">
            <SearchBar size="large" />

            <!-- Quick Suggestions -->
            <div class="mt-4 flex flex-wrap items-center justify-center gap-2">
                <span class="text-xs text-slate-400 mr-1">Quick:</span>
                {#each quickLinks as item}
                    {#if item.isLink}
                        <Link
                            href={item.query}
                            class="px-2.5 py-1 rounded-md text-xs font-semibold bg-indigo-50 dark:bg-indigo-950/80 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 hover:bg-indigo-100 transition-colors"
                        >
                            + {item.label}
                        </Link>
                    {:else}
                        <a
                            href={`/search?q=${encodeURIComponent(item.query)}`}
                            class="px-2.5 py-1 rounded-md text-xs font-medium bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 hover:border-slate-300 transition-colors"
                        >
                            {item.label}
                        </a>
                    {/if}
                {/each}
            </div>
        </div>

        <!-- Features Grid (Flat) -->
        <div class="mt-14 grid grid-cols-1 sm:grid-cols-3 gap-4 w-full max-w-3xl">
            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800">
                <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-400 flex items-center justify-center mb-2.5">
                    <Shield class="w-4 h-4" />
                </div>
                <h2 class="font-bold text-xs text-slate-900 dark:text-white">Zero Tracking</h2>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                    No search logs, no user profiling, no cookies. Complete privacy by design.
                </p>
            </div>

            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800">
                <div class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-400 flex items-center justify-center mb-2.5">
                    <Zap class="w-4 h-4" />
                </div>
                <h2 class="font-bold text-xs text-slate-900 dark:text-white">High-Speed Index</h2>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                    Sub-millisecond BM25 ranking and distributed async web crawling.
                </p>
            </div>

            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800">
                <div class="w-8 h-8 rounded-lg bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 flex items-center justify-center mb-2.5">
                    <Code2 class="w-4 h-4" />
                </div>
                <h2 class="font-bold text-xs text-slate-900 dark:text-white">Open Developer APIs</h2>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                    REST API, TypeScript & PHP SDKs to integrate search into your apps.
                </p>
            </div>
        </div>

        <!-- Submit Website CTA Banner -->
        <div class="mt-8 w-full max-w-3xl p-4 rounded-xl bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-lg bg-indigo-600 text-white">
                    <PlusCircle class="w-4 h-4" />
                </div>
                <div>
                    <h2 class="text-xs font-bold text-slate-900 dark:text-white">Add Your Website to Web-Search</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        {stats.total_pages} pages indexed across {stats.total_domains} domains.
                    </p>
                </div>
            </div>

            <Link
                href="/submit"
                class="px-3.5 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold transition-colors whitespace-nowrap"
            >
                Submit Website →
            </Link>
        </div>
    </main>

    <Footer />
</div>
