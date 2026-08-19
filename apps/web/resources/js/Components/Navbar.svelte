<script>
    import { Link } from '@inertiajs/svelte';
    import ThemeToggle from './ThemeToggle.svelte';
    import SearchBar from './SearchBar.svelte';
    import { Search, Globe, Plus, Github, BarChart2, Shield } from 'lucide-svelte';

    let { showSearch = false, initialQuery = '' } = $props();
</script>

<header class="border-b border-zinc-200 dark:border-zinc-800 bg-white/95 dark:bg-black/95 backdrop-blur sticky top-0 z-40 transition-colors">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-14 gap-4">
            <!-- Brand Logo -->
            <div class="flex items-center gap-6 shrink-0">
                <Link href="/" class="flex items-center gap-2 group">
                    <div class="w-7 h-7 rounded bg-black dark:bg-white text-white dark:text-black flex items-center justify-center font-bold text-xs">
                        W
                    </div>
                    <div class="flex items-baseline gap-1">
                        <span class="font-bold tracking-tight text-sm text-black dark:text-white">Web-Search</span>
                        <span class="text-[10px] font-mono text-zinc-500">.org</span>
                    </div>
                </Link>
            </div>

            <!-- Optional Header Search Bar -->
            {#if showSearch}
                <div class="flex-1 max-w-2xl px-2">
                    <SearchBar initialQuery={initialQuery} size="small" />
                </div>
            {/if}

            <!-- Nav Actions -->
            <div class="flex items-center gap-2 sm:gap-3">
                <Link
                    href="/submit"
                    class="hidden sm:inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold bg-black text-white dark:bg-white dark:text-black hover:bg-zinc-800 dark:hover:bg-zinc-200 transition-colors"
                >
                    <Plus class="w-3.5 h-3.5" />
                    <span>Submit Website</span>
                </Link>

                <Link
                    href="/stats"
                    class="p-2 rounded-lg text-zinc-600 hover:text-black dark:text-zinc-400 dark:hover:text-white hover:bg-zinc-100 dark:hover:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 transition-colors"
                    title="Live Open Metrics & Stats"
                >
                    <BarChart2 class="w-4 h-4" />
                </Link>

                <Link
                    href="/console"
                    class="hidden md:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-zinc-700 dark:text-zinc-300 hover:text-black dark:hover:text-white hover:bg-zinc-100 dark:hover:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 transition-colors"
                >
                    <span>Console</span>
                </Link>

                <a
                    href="https://github.com/web-search-org"
                    target="_blank"
                    rel="noreferrer"
                    class="p-2 rounded-lg text-zinc-600 hover:text-black dark:text-zinc-400 dark:hover:text-white hover:bg-zinc-100 dark:hover:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 transition-colors"
                    title="GitHub Repository (web-search-org)"
                >
                    <Github class="w-4 h-4" />
                </a>

                <ThemeToggle />
            </div>
        </div>
    </div>
</header>
