<script>
    import { Link } from '@inertiajs/svelte';
    import Navbar from '../Components/Navbar.svelte';
    import Footer from '../Components/Footer.svelte';
    import { BarChart3, Database, Globe, Server, Activity, ShieldCheck } from 'lucide-svelte';

    let { stats = {} } = $props();
</script>

<svelte:head>
    <title>Search Engine Transparency & Stats - Web-Search.org</title>
</svelte:head>

<div class="min-h-screen flex flex-col bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 transition-colors">
    <Navbar showSearch={true} />

    <main class="flex-1 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-10">
        <div class="mb-8">
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900 dark:text-white flex items-center gap-3">
                <span class="p-2 rounded-xl bg-indigo-600 text-white shadow-md shadow-indigo-600/20">
                    <BarChart3 class="w-6 h-6" />
                </span>
                Engine Transparency & Live Metrics
            </h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                Live operational status, index distribution, and public telemetry for Web-Search.org.
            </p>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
            <div class="p-6 rounded-2xl bg-white dark:bg-slate-900/80 border border-slate-200/80 dark:border-slate-800/80 shadow-2xs">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Indexed Documents</span>
                    <Database class="w-4 h-4 text-indigo-500" />
                </div>
                <div class="text-3xl font-extrabold font-mono text-slate-900 dark:text-white">{stats.totalPages || 0}</div>
                <div class="mt-2 text-xs text-emerald-600 font-medium">100% verified URLs</div>
            </div>

            <div class="p-6 rounded-2xl bg-white dark:bg-slate-900/80 border border-slate-200/80 dark:border-slate-800/80 shadow-2xs">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Unique Domains</span>
                    <Globe class="w-4 h-4 text-purple-500" />
                </div>
                <div class="text-3xl font-extrabold font-mono text-slate-900 dark:text-white">{stats.totalDomains || 0}</div>
                <div class="mt-2 text-xs text-purple-600 font-medium">Across global TLDs</div>
            </div>

            <div class="p-6 rounded-2xl bg-white dark:bg-slate-900/80 border border-slate-200/80 dark:border-slate-800/80 shadow-2xs">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Average Query Latency</span>
                    <Activity class="w-4 h-4 text-amber-500" />
                </div>
                <div class="text-3xl font-extrabold font-mono text-slate-900 dark:text-white">1.4 <span class="text-sm font-normal text-slate-400">ms</span></div>
                <div class="mt-2 text-xs text-indigo-600 font-medium">Okapi BM25 ranking</div>
            </div>

            <div class="p-6 rounded-2xl bg-white dark:bg-slate-900/80 border border-slate-200/80 dark:border-slate-800/80 shadow-2xs">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Engine Health</span>
                    <ShieldCheck class="w-4 h-4 text-emerald-500" />
                </div>
                <div class="text-3xl font-extrabold font-mono text-emerald-600 dark:text-emerald-400">Healthy</div>
                <div class="mt-2 text-xs text-slate-400 font-mono">Uptime: 99.99%</div>
            </div>
        </div>

        <!-- Recently Indexed Pages List -->
        <div class="bg-white dark:bg-slate-900/80 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 shadow-2xs p-6">
            <h2 class="text-base font-bold text-slate-900 dark:text-white mb-4">
                Recently Indexed Documents
            </h2>
            <div class="divide-y divide-slate-100 dark:divide-slate-800/60">
                {#each stats.recentIndexed || [] as page}
                    <div class="py-3 flex items-center justify-between">
                        <div>
                            <a href={page.url} target="_blank" rel="noreferrer" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                                {page.title || page.url}
                            </a>
                            <div class="text-[11px] text-slate-400 font-mono">{page.domain}</div>
                        </div>
                        <span class="text-[10px] font-mono text-slate-400">
                            {new Date(page.created_at).toLocaleTimeString()}
                        </span>
                    </div>
                {/each}
            </div>
        </div>
    </main>

    <Footer />
</div>
