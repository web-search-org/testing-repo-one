<script>
    import { Link } from '@inertiajs/svelte';
    import Navbar from '../Components/Navbar.svelte';
    import Footer from '../Components/Footer.svelte';
    import InstantAnswer from '../Components/InstantAnswer.svelte';
    import ResultItem from '../Components/ResultItem.svelte';
    import { Globe, Code2, Cpu, Newspaper, Sparkles, Filter, AlertCircle } from 'lucide-svelte';

    let { searchData = {}, currentQuery = '', currentCategory = 'all', currentPage = 1 } = $props();

    const categories = [
        { id: 'all', label: 'All', icon: Globe },
        { id: 'tech', label: 'Tech & AI', icon: Cpu },
        { id: 'code', label: 'Code & Repos', icon: Code2 },
        { id: 'news', label: 'News', icon: Newspaper },
    ];
</script>

<svelte:head>
    <title>Search: {currentQuery} - Web-Search.org</title>
</svelte:head>

<div class="min-h-screen flex flex-col bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 transition-colors">
    <Navbar showSearch={true} initialQuery={currentQuery} />

    <!-- Sub-header category navigation -->
    <div class="border-b border-slate-200/80 dark:border-slate-800/80 bg-white/50 dark:bg-slate-950/50 transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between">
            <div class="flex items-center gap-1 sm:gap-2 overflow-x-auto py-2">
                {#each categories as cat}
                    {@const Icon = cat.icon}
                    <a
                        href={`/search?q=${encodeURIComponent(currentQuery)}&category=${cat.id}`}
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-medium transition-all whitespace-nowrap {currentCategory === cat.id ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-900'}"
                    >
                        <Icon class="w-3.5 h-3.5" />
                        <span>{cat.label}</span>
                    </a>
                {/each}
            </div>

            <!-- Execution Time -->
            <div class="hidden sm:block text-xs font-mono text-slate-400 dark:text-slate-500">
                {searchData.totalHits} results ({searchData.executionTimeMs} ms)
            </div>
        </div>
    </div>

    <!-- Main Results Area -->
    <main class="flex-1 max-w-4xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-6">
        <!-- Instant Answer Card -->
        {#if searchData.instantAnswer}
            <InstantAnswer instantAnswer={searchData.instantAnswer} />
        {/if}

        <!-- Results List -->
        {#if searchData.results && searchData.results.length > 0}
            <div class="space-y-1">
                {#each searchData.results as item (item.id)}
                    <ResultItem result={item} />
                {/each}
            </div>

            <!-- Pagination -->
            {#if searchData.totalPages > 1}
                <div class="mt-10 mb-8 flex items-center justify-center gap-2">
                    {#if searchData.page > 1}
                        <a
                            href={`/search?q=${encodeURIComponent(currentQuery)}&category=${currentCategory}&page=${searchData.page - 1}`}
                            class="px-4 py-2 rounded-xl text-xs font-medium bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:bg-indigo-50 dark:hover:bg-indigo-950/50 hover:text-indigo-600 transition-colors shadow-2xs"
                        >
                            ← Previous
                        </a>
                    {/if}

                    <span class="text-xs text-slate-400 px-3 font-mono">
                        Page {searchData.page} of {searchData.totalPages}
                    </span>

                    {#if searchData.page < searchData.totalPages}
                        <a
                            href={`/search?q=${encodeURIComponent(currentQuery)}&category=${currentCategory}&page=${searchData.page + 1}`}
                            class="px-4 py-2 rounded-xl text-xs font-medium bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:bg-indigo-50 dark:hover:bg-indigo-950/50 hover:text-indigo-600 transition-colors shadow-2xs"
                        >
                            Next →
                        </a>
                    {/if}
                </div>
            {/if}
        {:else}
            <!-- Empty State -->
            <div class="text-center py-16 px-4">
                <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 mx-auto flex items-center justify-center mb-4">
                    <AlertCircle class="w-6 h-6" />
                </div>
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
                    No matching results found for "{currentQuery}"
                </h2>
                <p class="mt-2 text-xs text-slate-500 dark:text-slate-400 max-w-md mx-auto">
                    Try checking for spelling errors, using more general search terms, or submitting this domain to our distributed crawler!
                </p>

                <div class="mt-6 flex justify-center gap-3">
                    <Link
                        href="/crawler"
                        class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium shadow-sm transition-colors"
                    >
                        Index a New Website with Crawler
                    </Link>
                    <Link
                        href="/"
                        class="px-4 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 text-xs font-medium hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors"
                    >
                        Back to Home
                    </Link>
                </div>
            </div>
        {/if}

        <!-- Related Searches -->
        {#if searchData.suggestions && searchData.suggestions.length > 0}
            <div class="mt-8 pt-6 border-t border-slate-200/80 dark:border-slate-800/80">
                <h2 class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-3">
                    Related Searches
                </h2>
                <div class="flex flex-wrap gap-2">
                    {#each searchData.suggestions as sug}
                        <a
                            href={`/search?q=${encodeURIComponent(sug)}`}
                            class="px-3 py-1.5 rounded-xl text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 hover:border-indigo-300 dark:hover:border-indigo-800 transition-all shadow-2xs"
                        >
                            {sug}
                        </a>
                    {/each}
                </div>
            </div>
        {/if}
    </main>

    <Footer />
</div>
