<script>
    import { Link } from '@inertiajs/svelte';
    import Navbar from '../Components/Navbar.svelte';
    import Footer from '../Components/Footer.svelte';
    import InstantAnswer from '../Components/InstantAnswer.svelte';
    import ResultItem from '../Components/ResultItem.svelte';
    import { Globe, Code2, Cpu, Newspaper, AlertCircle, PlusCircle } from 'lucide-svelte';

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

<div class="min-h-screen flex flex-col bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 transition-colors">
    <Navbar showSearch={true} initialQuery={currentQuery} />

    <!-- Sub-header category navigation (Flat) -->
    <div class="border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/60 transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between">
            <div class="flex items-center gap-1.5 overflow-x-auto py-2">
                {#each categories as cat}
                    {@const Icon = cat.icon}
                    <a
                        href={`/search?q=${encodeURIComponent(currentQuery)}&category=${cat.id}`}
                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md text-xs font-medium transition-all whitespace-nowrap {currentCategory === cat.id ? 'bg-indigo-600 text-white' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-800'}"
                    >
                        <Icon class="w-3.5 h-3.5" />
                        <span>{cat.label}</span>
                    </a>
                {/each}
            </div>

            <!-- Execution Time -->
            <div class="hidden sm:block text-xs font-mono text-slate-400">
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
                <div class="mt-8 mb-6 flex items-center justify-center gap-2">
                    {#if searchData.page > 1}
                        <a
                            href={`/search?q=${encodeURIComponent(currentQuery)}&category=${currentCategory}&page=${searchData.page - 1}`}
                            class="px-3.5 py-1.5 rounded-lg text-xs font-medium bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:text-indigo-600 transition-colors"
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
                            class="px-3.5 py-1.5 rounded-lg text-xs font-medium bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:text-indigo-600 transition-colors"
                        >
                            Next →
                        </a>
                    {/if}
                </div>
            {/if}
        {:else}
            <!-- Empty State -->
            <div class="text-center py-12 px-4 space-y-3">
                <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950 text-amber-600 dark:text-amber-400 mx-auto flex items-center justify-center">
                    <AlertCircle class="w-5 h-5" />
                </div>
                <h2 class="text-base font-bold text-slate-900 dark:text-white">
                    No matching results found for "{currentQuery}"
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 max-w-md mx-auto">
                    Submit this domain to be crawled and added to the search index!
                </p>

                <div class="pt-2 flex justify-center gap-2">
                    <Link
                        href="/submit"
                        class="px-3.5 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold transition-colors"
                    >
                        Submit Website →
                    </Link>
                    <Link
                        href="/"
                        class="px-3.5 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 text-xs font-medium hover:bg-slate-200 transition-colors"
                    >
                        Home
                    </Link>
                </div>
            </div>
        {/if}

        <!-- Related Searches -->
        {#if searchData.suggestions && searchData.suggestions.length > 0}
            <div class="mt-8 pt-6 border-t border-slate-200 dark:border-slate-800">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2.5">
                    Related Searches
                </h2>
                <div class="flex flex-wrap gap-1.5">
                    {#each searchData.suggestions as sug}
                        <a
                            href={`/search?q=${encodeURIComponent(sug)}`}
                            class="px-2.5 py-1 rounded-md text-xs bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:text-indigo-600 transition-colors"
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
