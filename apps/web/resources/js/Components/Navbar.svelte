<script>
    import { Link } from '@inertiajs/svelte';
    import ThemeToggle from './ThemeToggle.svelte';
    import SearchBar from './SearchBar.svelte';
    import { Globe, Database, BarChart3, BookOpen, Github, SlidersHorizontal, SearchCheck } from 'lucide-svelte';

    let { showSearch = false, initialQuery = '' } = $props();
</script>

<header class="sticky top-0 z-40 w-full border-b border-slate-200/80 dark:border-slate-800/80 bg-white/85 dark:bg-slate-950/85 backdrop-blur-md transition-colors">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
        <!-- Logo & Branding -->
        <div class="flex items-center gap-6">
            <Link href="/" class="flex items-center gap-2.5 group">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-purple-500 flex items-center justify-center text-white shadow-md shadow-indigo-500/20 group-hover:scale-105 transition-transform">
                    <Globe class="w-5 h-5" />
                </div>
                <div class="flex flex-col">
                    <span class="font-bold text-base tracking-tight bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-800 dark:from-white dark:via-indigo-200 dark:to-slate-200 bg-clip-text text-transparent">
                        Web-Search<span class="text-indigo-600 dark:text-indigo-400">.org</span>
                    </span>
                    <span class="text-[10px] text-slate-400 font-mono -mt-1 hidden sm:block">Open Index Engine</span>
                </div>
            </Link>

            {#if showSearch}
                <div class="hidden md:block w-96 lg:w-[460px]">
                    <SearchBar initialQuery={initialQuery} size="small" />
                </div>
            {/if}
        </div>

        <!-- Navigation Links -->
        <div class="flex items-center gap-1 sm:gap-2">
            <Link
                href="/console"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200/60 dark:border-indigo-800/60 hover:bg-indigo-100 dark:hover:bg-indigo-900/60 transition-colors"
            >
                <SearchCheck class="w-3.5 h-3.5" />
                <span>Search Console</span>
            </Link>

            <Link
                href="/crawler"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg text-slate-600 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-slate-100 dark:hover:bg-slate-900 transition-colors"
            >
                <Database class="w-3.5 h-3.5" />
                <span class="hidden sm:inline">Crawler</span>
            </Link>

            <Link
                href="/stats"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg text-slate-600 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-slate-100 dark:hover:bg-slate-900 transition-colors"
            >
                <BarChart3 class="w-3.5 h-3.5" />
                <span class="hidden sm:inline">Stats</span>
            </Link>

            <Link
                href="/docs"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg text-slate-600 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-slate-100 dark:hover:bg-slate-900 transition-colors"
            >
                <BookOpen class="w-3.5 h-3.5" />
                <span class="hidden sm:inline">API</span>
            </Link>

            <div class="h-4 w-px bg-slate-200 dark:bg-slate-800 mx-1"></div>

            <ThemeToggle />

            <a
                href="https://github.com/web-search/search"
                target="_blank"
                rel="noreferrer"
                class="p-2 rounded-xl text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-900 transition-colors"
                aria-label="GitHub Repository"
            >
                <Github class="w-4.5 h-4.5" />
            </a>
        </div>
    </div>
</header>
