<script>
    import { useForm } from '@inertiajs/svelte';
    import Navbar from '../../Components/Navbar.svelte';
    import Footer from '../../Components/Footer.svelte';
    import { Database, Plus, RefreshCw, CheckCircle2, Clock, AlertTriangle, Layers, Server, Globe } from 'lucide-svelte';

    let { jobs = [], domains = [], metrics = {} } = $props();

    const form = useForm({
        seed_url: '',
        max_depth: 3,
        max_pages: 150,
        concurrency: 5,
    });

    function handleSubmit(e) {
        e.preventDefault();
        $form.post('/crawler/jobs', {
            onSuccess: () => {
                $form.reset();
            }
        });
    }
</script>

<svelte:head>
    <title>Crawler Dashboard - Web-Search.org</title>
</svelte:head>

<div class="min-h-screen flex flex-col bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 transition-colors">
    <Navbar showSearch={true} />

    <main class="flex-1 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-8">
        <!-- Page Title & Refresh -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900 dark:text-white flex items-center gap-3">
                    <span class="p-2 rounded-xl bg-indigo-600 text-white shadow-md shadow-indigo-600/20">
                        <Database class="w-6 h-6" />
                    </span>
                    Distributed Crawler Control
                </h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Submit seed URLs, inspect active crawler nodes, and monitor indexing throughput in real-time.
                </p>
            </div>

            <button
                onclick={() => window.location.reload()}
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 text-xs font-medium hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors shadow-2xs self-start sm:self-auto"
            >
                <RefreshCw class="w-3.5 h-3.5" />
                Refresh State
            </button>
        </div>

        <!-- Metrics Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
            <div class="p-4.5 rounded-2xl bg-white dark:bg-slate-900/80 border border-slate-200/80 dark:border-slate-800/80 shadow-2xs">
                <span class="text-xs text-slate-400 font-medium">Total Crawled</span>
                <div class="mt-1 text-2xl font-bold text-slate-900 dark:text-white font-mono">{metrics.totalCrawled || 0}</div>
                <span class="text-[11px] text-slate-400">Pages visited</span>
            </div>

            <div class="p-4.5 rounded-2xl bg-white dark:bg-slate-900/80 border border-slate-200/80 dark:border-slate-800/80 shadow-2xs">
                <span class="text-xs text-slate-400 font-medium">Search Index</span>
                <div class="mt-1 text-2xl font-bold text-indigo-600 dark:text-indigo-400 font-mono">{metrics.totalIndexed || 0}</div>
                <span class="text-[11px] text-indigo-500/80">Active documents</span>
            </div>

            <div class="p-4.5 rounded-2xl bg-white dark:bg-slate-900/80 border border-slate-200/80 dark:border-slate-800/80 shadow-2xs">
                <span class="text-xs text-slate-400 font-medium">Indexed Domains</span>
                <div class="mt-1 text-2xl font-bold text-slate-900 dark:text-white font-mono">{metrics.totalDomains || 0}</div>
                <span class="text-[11px] text-slate-400">Unique hosts</span>
            </div>

            <div class="p-4.5 rounded-2xl bg-white dark:bg-slate-900/80 border border-slate-200/80 dark:border-slate-800/80 shadow-2xs">
                <span class="text-xs text-slate-400 font-medium">Active Workers</span>
                <div class="mt-1 text-2xl font-bold text-emerald-600 dark:text-emerald-400 font-mono">{metrics.activeJobs || 0}</div>
                <span class="text-[11px] text-emerald-500/80">Running jobs</span>
            </div>

            <div class="p-4.5 rounded-2xl bg-white dark:bg-slate-900/80 border border-slate-200/80 dark:border-slate-800/80 shadow-2xs col-span-2 lg:col-span-1">
                <span class="text-xs text-slate-400 font-medium">Queue Status</span>
                <div class="mt-1 text-2xl font-bold text-purple-600 dark:text-purple-400 font-mono">{metrics.queuedJobs || 0}</div>
                <span class="text-[11px] text-purple-500/80">Pending jobs</span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Submit URL Form -->
            <div class="lg:col-span-1">
                <div class="p-6 rounded-2xl bg-white dark:bg-slate-900/80 border border-slate-200/80 dark:border-slate-800/80 shadow-2xs sticky top-24">
                    <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2 mb-4">
                        <Plus class="w-4 h-4 text-indigo-600" />
                        Queue New Crawl Job
                    </h2>

                    <form onsubmit={handleSubmit} class="space-y-4">
                        <div>
                            <label for="seed-url" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Seed URL
                            </label>
                            <input
                                id="seed-url"
                                type="url"
                                bind:value={$form.seed_url}
                                required
                                placeholder="https://news.ycombinator.com"
                                class="w-full px-3.5 py-2.5 rounded-xl text-xs bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500"
                            />
                            {#if $form.errors.seed_url}
                                <span class="text-[11px] text-rose-500 mt-1 block">{$form.errors.seed_url}</span>
                            {/if}
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="max-depth" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                    Max Depth
                                </label>
                                <input
                                    id="max-depth"
                                    type="number"
                                    min="1"
                                    max="8"
                                    bind:value={$form.max_depth}
                                    class="w-full px-3 py-2 rounded-xl text-xs bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                                />
                            </div>

                            <div>
                                <label for="max-pages" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                    Max Pages
                                </label>
                                <input
                                    id="max-pages"
                                    type="number"
                                    min="10"
                                    max="2000"
                                    step="10"
                                    bind:value={$form.max_pages}
                                    class="w-full px-3 py-2 rounded-xl text-xs bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                                />
                            </div>
                        </div>

                        <div>
                            <label for="concurrency" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Worker Concurrency: {$form.concurrency}
                            </label>
                            <input
                                id="concurrency"
                                type="range"
                                min="1"
                                max="20"
                                bind:value={$form.concurrency}
                                class="w-full accent-indigo-600"
                            />
                        </div>

                        <button
                            type="submit"
                            disabled={$form.processing}
                            class="w-full py-2.5 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white text-xs font-semibold shadow-md shadow-indigo-600/20 transition-all flex items-center justify-center gap-2"
                        >
                            {#if $form.processing}
                                <RefreshCw class="w-3.5 h-3.5 animate-spin" />
                                Submitting...
                            {:else}
                                <Database class="w-3.5 h-3.5" />
                                Launch Crawler Task
                            {/if}
                        </button>
                    </form>

                    <!-- CLI instructions -->
                    <div class="mt-6 pt-6 border-t border-slate-200/80 dark:border-slate-800/80">
                        <span class="text-xs font-semibold text-slate-400 block mb-2">CLI Worker Command</span>
                        <pre class="p-2.5 rounded-xl bg-slate-900 text-slate-200 text-[11px] font-mono overflow-x-auto"><code>pnpm crawler:dev</code></pre>
                    </div>
                </div>
            </div>

            <!-- Jobs & Domains Table -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Recent Crawl Jobs -->
                <div class="bg-white dark:bg-slate-900/80 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 shadow-2xs overflow-hidden">
                    <div class="p-4 border-b border-slate-200/80 dark:border-slate-800/80 flex items-center justify-between">
                        <h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <Clock class="w-4 h-4 text-indigo-600" />
                            Recent Crawl Jobs
                        </h2>
                        <span class="text-xs text-slate-400 font-mono">{jobs.length} jobs listed</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 dark:bg-slate-950/60 text-slate-500 border-b border-slate-200/80 dark:border-slate-800/80">
                                <tr>
                                    <th class="px-4 py-3 font-medium">Seed URL</th>
                                    <th class="px-4 py-3 font-medium">Status</th>
                                    <th class="px-4 py-3 font-medium">Pages</th>
                                    <th class="px-4 py-3 font-medium">Indexed</th>
                                    <th class="px-4 py-3 font-medium">Date</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                                {#each jobs as job}
                                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                                        <td class="px-4 py-3 font-medium text-slate-900 dark:text-white max-w-xs truncate">
                                            <a href={job.seed_url} target="_blank" rel="noreferrer" class="hover:text-indigo-600 hover:underline">
                                                {job.seed_url}
                                            </a>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wider {job.status === 'completed' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-400' : job.status === 'running' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/50 dark:text-indigo-400 animate-pulse' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400'}">
                                                {job.status}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 font-mono">{job.pages_crawled} / {job.max_pages}</td>
                                        <td class="px-4 py-3 font-mono text-emerald-600 dark:text-emerald-400 font-semibold">{job.pages_indexed}</td>
                                        <td class="px-4 py-3 text-slate-400 text-[11px] font-mono">
                                            {new Date(job.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                                        </td>
                                    </tr>
                                {:else}
                                    <tr>
                                        <td colspan="5" class="px-4 py-8 text-center text-slate-400">
                                            No crawl jobs recorded yet. Submit a URL above to start!
                                        </td>
                                    </tr>
                                {/each}
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Top Indexed Domains -->
                <div class="bg-white dark:bg-slate-900/80 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 shadow-2xs overflow-hidden">
                    <div class="p-4 border-b border-slate-200/80 dark:border-slate-800/80 flex items-center justify-between">
                        <h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <Globe class="w-4 h-4 text-indigo-600" />
                            Top Indexed Domains
                        </h2>
                    </div>

                    <div class="p-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
                        {#each domains as domain}
                            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-950/50 border border-slate-200/60 dark:border-slate-800/60 flex items-center justify-between">
                                <div class="flex items-center gap-2.5">
                                    <img
                                        src={domain.favicon_url || `https://www.google.com/s2/favicons?domain=${domain.name}&sz=32`}
                                        alt=""
                                        class="w-4 h-4 rounded-full"
                                        onerror={(e) => { e.currentTarget.style.display = 'none'; }}
                                    />
                                    <div>
                                        <a href={`/search?q=site:${domain.name}`} class="font-semibold text-xs text-slate-900 dark:text-white hover:text-indigo-600 hover:underline">
                                            {domain.name}
                                        </a>
                                        <div class="text-[10px] text-slate-400">Rank: {domain.domain_rank}</div>
                                    </div>
                                </div>
                                <span class="px-2 py-1 rounded-lg bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 font-mono text-[11px] font-bold">
                                    {domain.total_pages} pages
                                </span>
                            </div>
                        {/each}
                    </div>
                </div>
            </div>
        </div>
    </main>

    <Footer />
</div>
