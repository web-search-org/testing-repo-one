<script>
    import { ShieldCheck, Terminal } from 'lucide-svelte';
</script>

<footer class="mt-auto border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 py-8 transition-colors">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center justify-between gap-4 text-xs text-slate-500 dark:text-slate-400">
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 font-medium border border-emerald-200 dark:border-emerald-900">
                <ShieldCheck class="w-3.5 h-3.5" />
                100% Privacy • No Tracking
            </span>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-slate-100 dark:bg-slate-900 text-slate-700 dark:text-slate-300 font-medium border border-slate-200 dark:border-slate-800">
                <Terminal class="w-3.5 h-3.5" />
                AGPL-3.0 Open Source
            </span>
        </div>

        <div class="flex items-center gap-6">
            <a href="/submit" class="hover:text-indigo-600 dark:hover:text-indigo-400 font-medium text-slate-700 dark:text-slate-300 transition-colors">Submit Site</a>
            <a href="/console" class="hover:text-indigo-600 dark:hover:text-indigo-400 font-medium text-slate-700 dark:text-slate-300 transition-colors">Search Console</a>
            <a href="/docs" class="hover:text-indigo-600 dark:hover:text-indigo-400 font-medium text-slate-700 dark:text-slate-300 transition-colors">Developers & API</a>
            <a href="/stats" class="hover:text-indigo-600 dark:hover:text-indigo-400 font-medium text-slate-700 dark:text-slate-300 transition-colors">Stats</a>
            <a href="/crawler" class="hover:text-indigo-600 dark:hover:text-indigo-400 font-medium text-slate-700 dark:text-slate-300 transition-colors">Crawler</a>
            <a href="https://github.com/web-search-org" target="_blank" rel="noreferrer" class="hover:text-indigo-600 dark:hover:text-indigo-400 font-medium text-slate-700 dark:text-slate-300 transition-colors">GitHub</a>
        </div>

        <div class="text-center md:text-right">
            <span>Powered by Laravel 12 + Svelte 5 + Python Crawler</span>
        </div>
    </div>
</footer>
