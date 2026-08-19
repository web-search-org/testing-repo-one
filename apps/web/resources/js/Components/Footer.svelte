<script>
    import { ShieldCheck, Terminal, PlusCircle } from 'lucide-svelte';
</script>

<footer class="mt-auto border-t border-slate-200/80 dark:border-slate-800/80 bg-white/50 dark:bg-slate-950/50 py-8 transition-colors">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center justify-between gap-4 text-xs text-slate-500 dark:text-slate-400">
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 font-medium border border-emerald-200 dark:border-emerald-900">
                <ShieldCheck class="w-3.5 h-3.5" />
                100% Privacy • No Tracking
            </span>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-400 font-medium border border-indigo-200 dark:border-indigo-900">
                <Terminal class="w-3.5 h-3.5" />
                AGPL-3.0 Open Source
            </span>
        </div>

        <div class="flex items-center gap-6">
            <a href="/submit" class="hover:text-indigo-600 dark:hover:text-indigo-400 font-semibold text-indigo-600 dark:text-indigo-400 transition-colors">Submit Website</a>
            <a href="/console" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">Search Console</a>
            <a href="/docs" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">Developers & API</a>
            <a href="/stats" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">Index Transparency</a>
            <a href="/crawler" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">Crawl Bot</a>
            <a href="https://github.com/web-search-org" target="_blank" rel="noreferrer" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">GitHub Org</a>
        </div>

        <div class="text-center md:text-right">
            <span>Powered by Laravel 12 + Svelte 5 + Python Async Crawler</span>
        </div>
    </div>
</footer>
