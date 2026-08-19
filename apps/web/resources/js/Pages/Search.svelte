<script>
    import { Link } from '@inertiajs/svelte';
    import Navbar from '../Components/Navbar.svelte';
    import Footer from '../Components/Footer.svelte';
    import InstantAnswer from '../Components/InstantAnswer.svelte';
    import ResultItem from '../Components/ResultItem.svelte';
    import { Globe, Code2, Cpu, Newspaper, Image as ImageIcon, AlertCircle, ExternalLink, Maximize2, X } from 'lucide-svelte';

    let { searchData = {}, currentQuery = '', currentCategory = 'all', currentPage = 1 } = $props();

    let selectedImage = $state(null);

    const categories = [
        { id: 'all', label: 'All', icon: Globe },
        { id: 'images', label: 'Images', icon: ImageIcon },
        { id: 'tech', label: 'Tech & AI', icon: Cpu },
        { id: 'code', label: 'Code & Repos', icon: Code2 },
        { id: 'news', label: 'News', icon: Newspaper },
    ];
</script>

<svelte:head>
    <title>Search: {currentQuery} - Web-Search.org</title>
</svelte:head>

<div class="min-h-screen flex flex-col bg-white dark:bg-black text-zinc-900 dark:text-zinc-100 transition-colors">
    <Navbar showSearch={true} initialQuery={currentQuery} />

    <!-- Sub-header category navigation (Monochrome) -->
    <div class="border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-950 transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between">
            <div class="flex items-center gap-1.5 overflow-x-auto py-2">
                {#each categories as cat}
                    {@const Icon = cat.icon}
                    <a
                        href={`/search?q=${encodeURIComponent(currentQuery)}&category=${cat.id}`}
                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md text-xs font-medium transition-all whitespace-nowrap {currentCategory === cat.id ? 'bg-black text-white dark:bg-white dark:text-black font-semibold' : 'text-zinc-600 dark:text-zinc-400 hover:bg-zinc-200 dark:hover:bg-zinc-800'}"
                    >
                        <Icon class="w-3.5 h-3.5" />
                        <span>{cat.label}</span>
                    </a>
                {/each}
            </div>

            <!-- Execution Time -->
            <div class="hidden sm:block text-xs font-mono text-zinc-400">
                {searchData.totalHits || 0} results ({searchData.executionTimeMs || 0} ms)
            </div>
        </div>
    </div>

    <!-- Main Results Area -->
    <main class="flex-1 {currentCategory === 'images' ? 'max-w-7xl' : 'max-w-4xl'} mx-auto w-full px-4 sm:px-6 lg:px-8 py-6">
        <!-- Instant Answer Card -->
        {#if searchData.instantAnswer && currentCategory !== 'images'}
            <InstantAnswer instantAnswer={searchData.instantAnswer} />
        {/if}

        <!-- 1. Dedicated Image Search Grid -->
        {#if currentCategory === 'images'}
            {#if searchData.imageResults && searchData.imageResults.length > 0}
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3.5">
                    {#each searchData.imageResults as img (img.id)}
                        <div class="group relative rounded-xl overflow-hidden border border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-950 flex flex-col justify-between transition-all hover:border-black dark:hover:border-white">
                            <!-- Image Canvas -->
                            <button
                                type="button"
                                onclick={() => selectedImage = img}
                                class="w-full h-44 bg-zinc-100 dark:bg-zinc-900 overflow-hidden flex items-center justify-center cursor-pointer"
                            >
                                <img
                                    src={img.thumbnailUrl}
                                    alt={img.alt || img.title}
                                    loading="lazy"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200"
                                />
                            </button>

                            <!-- Image Info & Badges -->
                            <div class="p-2.5 space-y-1">
                                <a
                                    href={img.pageUrl}
                                    target="_blank"
                                    rel="noreferrer"
                                    class="text-xs font-semibold text-black dark:text-white line-clamp-1 hover:underline"
                                    title={img.title || img.alt}
                                >
                                    {img.title || img.alt || img.domain}
                                </a>
                                
                                <div class="flex items-center justify-between text-[10px] text-zinc-500 font-mono">
                                    <span class="truncate max-w-[110px]">{img.domain}</span>
                                    <span>{img.width}×{img.height}</span>
                                </div>
                            </div>
                        </div>
                    {/each}
                </div>

                <!-- Image Pagination -->
                {#if searchData.totalPages > 1}
                    <div class="mt-8 mb-6 flex items-center justify-center gap-2">
                        {#if searchData.page > 1}
                            <a
                                href={`/search?q=${encodeURIComponent(currentQuery)}&category=images&page=${searchData.page - 1}`}
                                class="px-3.5 py-1.5 rounded-lg text-xs font-medium bg-zinc-100 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 text-zinc-700 dark:text-zinc-300 hover:text-black dark:hover:text-white transition-colors"
                            >
                                ← Previous
                            </a>
                        {/if}

                        <span class="text-xs text-zinc-400 px-3 font-mono">
                            Page {searchData.page} of {searchData.totalPages}
                        </span>

                        {#if searchData.page < searchData.totalPages}
                            <a
                                href={`/search?q=${encodeURIComponent(currentQuery)}&category=images&page=${searchData.page + 1}`}
                                class="px-3.5 py-1.5 rounded-lg text-xs font-medium bg-zinc-100 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 text-zinc-700 dark:text-zinc-300 hover:text-black dark:hover:text-white transition-colors"
                            >
                                Next →
                            </a>
                        {/if}
                    </div>
                {/if}
            {:else}
                <!-- Empty State for Images -->
                <div class="text-center py-12 px-4 space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-zinc-100 dark:bg-zinc-900 text-zinc-700 dark:text-zinc-300 mx-auto flex items-center justify-center">
                        <ImageIcon class="w-5 h-5" />
                    </div>
                    <h2 class="text-base font-bold text-black dark:text-white">
                        No images found for "{currentQuery}"
                    </h2>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 max-w-md mx-auto">
                        As the crawler explores new pages, downscaled image assets will appear here.
                    </p>
                </div>
            {/if}

        <!-- 2. Standard Web Results List -->
        {:else if searchData.results && searchData.results.length > 0}
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
                            class="px-3.5 py-1.5 rounded-lg text-xs font-medium bg-zinc-100 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 text-zinc-700 dark:text-zinc-300 hover:text-black dark:hover:text-white transition-colors"
                        >
                            ← Previous
                        </a>
                    {/if}

                    <span class="text-xs text-zinc-400 px-3 font-mono">
                        Page {searchData.page} of {searchData.totalPages}
                    </span>

                    {#if searchData.page < searchData.totalPages}
                        <a
                            href={`/search?q=${encodeURIComponent(currentQuery)}&category=${currentCategory}&page=${searchData.page + 1}`}
                            class="px-3.5 py-1.5 rounded-lg text-xs font-medium bg-zinc-100 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 text-zinc-700 dark:text-zinc-300 hover:text-black dark:hover:text-white transition-colors"
                        >
                            Next →
                        </a>
                    {/if}
                </div>
            {/if}
        {:else}
            <!-- Empty State for Web -->
            <div class="text-center py-12 px-4 space-y-3">
                <div class="w-10 h-10 rounded-xl bg-zinc-100 dark:bg-zinc-900 text-zinc-700 dark:text-zinc-300 mx-auto flex items-center justify-center">
                    <AlertCircle class="w-5 h-5" />
                </div>
                <h2 class="text-base font-bold text-black dark:text-white">
                    No matching results found for "{currentQuery}"
                </h2>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 max-w-md mx-auto">
                    Submit this domain to be crawled and added to the search index!
                </p>

                <div class="pt-2 flex justify-center gap-2">
                    <Link
                        href="/submit"
                        class="px-3.5 py-1.5 rounded-lg bg-black text-white dark:bg-white dark:text-black text-xs font-semibold hover:bg-zinc-800 dark:hover:bg-zinc-200 transition-colors"
                    >
                        Submit Website →
                    </Link>
                    <Link
                        href="/"
                        class="px-3.5 py-1.5 rounded-lg bg-zinc-100 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 text-zinc-700 dark:text-zinc-300 text-xs font-medium hover:bg-zinc-200 transition-colors"
                    >
                        Home
                    </Link>
                </div>
            </div>
        {/if}

        <!-- Related Searches -->
        {#if searchData.suggestions && searchData.suggestions.length > 0}
            <div class="mt-8 pt-6 border-t border-zinc-200 dark:border-zinc-800">
                <h2 class="text-xs font-bold uppercase tracking-wider text-zinc-400 mb-2.5">
                    Related Searches
                </h2>
                <div class="flex flex-wrap gap-1.5">
                    {#each searchData.suggestions as sug}
                        <a
                            href={`/search?q=${encodeURIComponent(sug)}&category=${currentCategory}`}
                            class="px-2.5 py-1 rounded-md text-xs bg-zinc-100 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 text-zinc-700 dark:text-zinc-300 hover:text-black dark:hover:text-white transition-colors"
                        >
                            {sug}
                        </a>
                    {/each}
                </div>
            </div>
        {/if}
    </main>

    <!-- Image Lightbox Modal -->
    {#if selectedImage}
        <div class="fixed inset-0 z-50 bg-black/80 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="relative max-w-3xl w-full bg-white dark:bg-zinc-950 rounded-2xl border border-zinc-200 dark:border-zinc-800 overflow-hidden shadow-2xl flex flex-col max-h-[90vh]">
                <!-- Modal Header -->
                <div class="p-3 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between">
                    <span class="text-xs font-bold text-black dark:text-white truncate max-w-md">
                        {selectedImage.title || selectedImage.alt || selectedImage.domain}
                    </span>
                    <button
                        onclick={() => selectedImage = null}
                        class="p-1 rounded-lg text-zinc-400 hover:text-black dark:hover:text-white hover:bg-zinc-100 dark:hover:bg-zinc-900 transition-colors cursor-pointer"
                    >
                        <X class="w-4 h-4" />
                    </button>
                </div>

                <!-- Modal Image Content -->
                <div class="flex-1 bg-zinc-100 dark:bg-black p-4 flex items-center justify-center overflow-auto">
                    <img
                        src={selectedImage.imageUrl}
                        alt={selectedImage.alt}
                        class="max-h-[60vh] max-w-full object-contain rounded-lg"
                    />
                </div>

                <!-- Modal Footer -->
                <div class="p-3.5 border-t border-zinc-200 dark:border-zinc-800 flex items-center justify-between text-xs bg-zinc-50 dark:bg-zinc-950">
                    <div class="text-zinc-500 font-mono text-[11px]">
                        <span>{selectedImage.domain}</span> • <span>{selectedImage.width}×{selectedImage.height}px</span>
                    </div>

                    <div class="flex items-center gap-2">
                        <a
                            href={selectedImage.pageUrl}
                            target="_blank"
                            rel="noreferrer"
                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-zinc-200 dark:bg-zinc-800 text-black dark:text-white font-medium hover:bg-zinc-300 dark:hover:bg-zinc-700 transition-colors"
                        >
                            <span>Visit Page</span>
                            <ExternalLink class="w-3 h-3" />
                        </a>
                        <a
                            href={selectedImage.originalUrl || selectedImage.imageUrl}
                            target="_blank"
                            rel="noreferrer"
                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-black text-white dark:bg-white dark:text-black font-semibold hover:bg-zinc-800 dark:hover:bg-zinc-200 transition-colors"
                        >
                            <span>View Original</span>
                            <Maximize2 class="w-3 h-3" />
                        </a>
                    </div>
                </div>
            </div>
        </div>
    {/if}

    <Footer />
</div>
