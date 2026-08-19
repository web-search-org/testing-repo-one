<script>
    import { useForm, Link } from '@inertiajs/svelte';
    import Navbar from '../../Components/Navbar.svelte';
    import Footer from '../../Components/Footer.svelte';
    import { Database, Plus, RefreshCw, CheckCircle2, Clock, Play, Server, Globe, PlusCircle } from 'lucide-svelte';

    let { jobs = [], domains = [], metrics = {} } = $props();

    const form = useForm({
        seed_url: '',
        max_depth: 3,
        max_pages: 100,
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

<div class="min-h-screen flex flex-col bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 transition-colors">
    <Navbar showSearch={true} />

    <main class="flex-1 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <!-- Page Title & Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-200 dark:border-slate-800">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white flex items-center gap-2.5">
                    <Database class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
                    Distributed Crawler Control
                </h1>
                <p class="mt-1 text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                    Queue seed websites, monitor background crawler workers, and manage domain ingestion.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <Link
                    href="/submit"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold transition-colors"
                >
                    <PlusCircle class="w-3.5 h-3.5" />
                    Submit Site
                </Link>

                <button
                    onclick={() => window.location.reload()}
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 text-xs font-medium hover:bg-slate-200 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                >
                    <RefreshCw class="w-3.5 h-3.5" />
                    Refresh
                </button>
            </div>
        </div>

        <!-- Metrics Grid (Flat) -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800">
                <span class="text-xs text-slate-500 font-bold uppercase tracking-wider">Search Index</span>
                <div class="mt-1 text-2xl font-bold text-slate-900 dark:text-white font-mono">{metrics.totalIndexed || 0}</div>
                <span class="text-[11px] text-slate-500">Active indexed pages</span>
            </div>

            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800">
                <span class="text-xs text-slate-500 font-bold uppercase tracking-wider">Unique Hosts</span>
                <div class="mt-1 text-2xl font-bold text-slate-900 dark:text-white font-mono">{metrics.totalDomains || 0}</div>
                <span class="text-[11px] text-slate-500">Indexed domain properties</span>
            </div>

            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800">
                <span class="text-xs text-slate-500 font-bold uppercase tracking-wider">Active Workers</span>
                <div class="mt-1 text-2xl font-bold text-emerald-600 dark:text-emerald-400 font-mono">{metrics.activeJobs || 0}</div>
                <span class="text-[11px] text-slate-500">Running jobs</span>
            </div>

            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800">
                <span class="text-xs text-slate-500 font-bold uppercase tracking-wider">Queue Pending</span>
                <div class="mt-1 text-2xl font-bold text-indigo-600 dark:text-indigo-400 font-mono">{metrics.queuedJobs || 0}</div>
                <span class="text-[11px] text-slate-500">Queued for crawler</span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Submit URL Form -->
            <div class="lg:col-span-1">
                <div class="p-5 rounded-xl bg-white dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 space-y-4">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-2">
                        <Plus class="w-4 h-4 text-indigo-600" />
                        Queue New Crawl Job
                    </h2>

                    <form onsubmit={handleSubmit} class="space-y-4">
                        <div>
                            <label for="seed-url" class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                                Seed URL
                            </label>
                            <input
                                id="seed-url"
                                type="url"
                                bind:value={$form.seed_url}
                                required
                                placeholder="https://news.ycombinator.com"
                                class="w-full px-3 py-2 rounded-lg text-xs bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30 font-mono"
                            />
                            {#if $form.errors.seed_url}
                                <span class="text-[11px] text-rose-500 mt-1 block">{$form.errors.seed_url}</span>
                            {/if}
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label for="max-depth" class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                                    Max Depth
                                </label>
                                <input
                                    id="max-depth"
                                    type="number"
                                    min="1"
                                    max="8"
                                    bind:value={$form.max_depth}
                                    class="w-full px-3 py-2 rounded-lg text-xs bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                                />
                            </div>

                            <div>
                                <label for="max-pages" class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                                    Max Pages
                                </label>
                                <input
                                    id="max-pages"
                                    type="number"
                                    min="10"
                                    max="500"
                                    step="10"
                                    bind:value={$form.max_pages}
                                    class="w-full px-3 py-2 rounded-lg text-xs bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                                />
                            </div>
                        </div>

                        <button
                            type="submit"
                            disabled={$form.processing}
                            class="w-full py-2.5 px-4 rounded-lg bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white text-xs font-semibold transition-colors flex items-center justify-center gap-2 cursor-pointer"
                        >
                            {#if $form.processing}
                                <RefreshCw class="w-3.5 h-3.5 animate-spin" />
                                Submitting...
                            {:else}
                                <Database class="w-3.5 h-3.5" />
                                Queue Crawl Job
                            {/if}
                        </button>
                    </form>

                    <!-- How to run worker info box -->
                    <div class="pt-4 border-t border-slate-200 dark:border-slate-800 space-y-2">
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300 block">How to Process Queued Jobs:</span>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">
                            Run the Laravel crawler worker in your terminal to process queued websites:
                        </p>
                        <pre class="p-2.5 rounded-lg bg-slate-900 text-slate-200 text-[11px] font-mono overflow-x-auto"><code>php artisan crawl:worker</code></pre>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">
                            Or with the Python asyncio crawler engine:
                        </p>
                        <pre class="p-2.5 rounded-lg bg-slate-900 text-slate-200 text-[11px] font-mono overflow-x-auto"><code>python -m crawler run --seed "URL"</code></pre>
                    </div>
                </div>
            </div>

            <!-- Jobs & Domains Table -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Recent Crawl Jobs -->
                <div class="bg-white dark:bg-slate-900/60 rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                    <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-2">
                            <Clock class="w-4 h-4 text-indigo-600" />
                            Recent Crawl Queue
                        </h2>
                        <span class="text-xs text-slate-400 font-mono">{jobs.length} jobs</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 dark:bg-slate-950 text-slate-500 border-b border-slate-200 dark:border-slate-800">
                                <tr>
                                    <th class="px-4 py-2.5 font-semibold">Seed URL</th>
                                    <th class="px-3 py-2.5 font-semibold">Status</th>
                                    <th class="px-3 py-2.5 font-semibold">Crawled</th>
                                    <th class="px-3 py-2.5 font-semibold">Indexed</th>
                                    <th class="px-3 py-2.5 font-semibold">Date</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                {#each jobs as job}
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                                        <td class="px-4 py-2.5 font-medium text-slate-900 dark:text-white max-w-xs truncate">
                                            <a href={job.seed_url} target="_blank" rel="noreferrer" class="hover:text-indigo-600 hover:underline">
                                                {job.seed_url}
                                            </a>
                                        </td>
                                        <td class="px-3 py-2.5">
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-semibold uppercase {job.status === 'completed' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400' : job.status === 'running' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-400 animate-pulse' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400'}">
                                                {job.status}
                                            </span>
                                        </td>
                                        <td class="px-3 py-2.5 font-mono text-slate-700 dark:text-slate-300">{job.pages_crawled} / {job.max_pages}</td>
                                        <td class="px-3 py-2.5 font-mono text-indigo-600 dark:text-indigo-400 font-bold">{job.pages_indexed}</td>
                                        <td class="px-3 py-2.5 text-slate-400 text-[11px] font-mono">
                                            {new Date(job.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                                        </td>
                                    </tr>
                                {:else}
                                    <tr>
                                        <td colspan="5" class="px-4 py-8 text-center text-slate-400 text-xs">
                                            No crawl jobs recorded yet.
                                        </td>
                                    </tr>
                                {/each}
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Top Indexed Domains -->
                <div class="bg-white dark:bg-slate-900/60 rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                    <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-2">
                            <Globe class="w-4 h-4 text-indigo-600" />
                            Top Indexed Domains
                        </h2>
                    </div>

                    <div class="p-4 grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        {#each domains as domain}
                            <div class="p-2.5 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 flex items-center justify-between text-xs">
                                <div class="flex items-center gap-2">
                                    <img
                                        src={domain.favicon_url || `https://www.google.com/s2/favicons?domain=${domain.name}&sz=32`}
                                        alt=""
                                        class="w-3.5 h-3.5 rounded"
                                        onerror={(e) => { e.currentTarget.style.display = 'none'; }}
                                    />
                                    <div>
                                        <a href={`/search?q=site:${domain.name}`} class="font-semibold text-slate-900 dark:text-white hover:text-indigo-600 hover:underline">
                                            {domain.name}
                                        </a>
                                        <div class="text-[10px] text-slate-400">Rank: {domain.domain_rank}</div>
                                    </div>
                                </div>
                                <span class="px-2 py-0.5 rounded bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 font-mono text-[11px] font-bold">
                                    {domain.total_pages}
                                </span>
                            </div>
                        {:else}
                            <div class="col-span-2 text-center text-xs text-slate-400 py-4">No domains indexed yet.</div>
                        {/each}
                    </div>
                </div>
            </div>
        </div>
    </main>

    <Footer />
</div>
