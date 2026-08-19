<script>
    import { Link, useForm } from '@inertiajs/svelte';
    import Navbar from '../../Components/Navbar.svelte';
    import Footer from '../../Components/Footer.svelte';
    import { 
        Bot, 
        Play, 
        Pause, 
        RefreshCw, 
        Globe, 
        Layers, 
        Clock, 
        CheckCircle2, 
        AlertCircle,
        Server,
        Terminal,
        Cpu
    } from 'lucide-svelte';

    let { jobs = [], activeJobsCount = 0, totalPages = 0 } = $props();

    const form = useForm({
        seed_url: '',
        max_depth: 3,
        max_pages: 50,
        concurrency: 5,
    });

    function handleSubmit(e) {
        e.preventDefault();
        $form.post('/crawler/jobs', {
            onSuccess: () => {
                $form.reset('seed_url');
            }
        });
    }
</script>

<svelte:head>
    <title>Crawler Control Panel - Web-Search.org</title>
</svelte:head>

<div class="min-h-screen flex flex-col bg-white dark:bg-black text-zinc-900 dark:text-zinc-100 transition-colors">
    <Navbar showSearch={false} />

    <main class="flex-1 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        <!-- Dashboard Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span class="text-xs font-bold uppercase tracking-wider text-zinc-400">Distributed Crawler Engine</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-black dark:text-white">
                    Crawler Fleet Control Panel
                </h1>
            </div>

            <!-- CLI Helper (Monochrome) -->
            <div class="p-2.5 rounded-xl bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 text-xs font-mono">
                <span class="text-zinc-400">Worker CLI:</span> <code class="font-bold text-black dark:text-white">php artisan crawl:worker</code>
            </div>
        </div>

        <!-- 3 Metric Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800">
                <span class="text-[10px] font-bold text-zinc-400 uppercase">Active Crawler Jobs</span>
                <div class="text-2xl font-bold font-mono text-black dark:text-white mt-1">
                    {activeJobsCount}
                </div>
                <span class="text-[11px] text-zinc-500">Processing queues</span>
            </div>

            <div class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800">
                <span class="text-[10px] font-bold text-zinc-400 uppercase">Total Indexed Documents</span>
                <div class="text-2xl font-bold font-mono text-black dark:text-white mt-1">
                    {totalPages}
                </div>
                <span class="text-[11px] text-zinc-500">Searchable pages</span>
            </div>

            <div class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800">
                <span class="text-[10px] font-bold text-zinc-400 uppercase">Crawler Worker Status</span>
                <div class="text-2xl font-bold font-mono text-black dark:text-white mt-1">
                    Operational
                </div>
                <span class="text-[11px] text-zinc-500">Auto link graph discovery active</span>
            </div>
        </div>

        <!-- Dispatch New Job Card (Monochrome) -->
        <div class="p-6 rounded-2xl bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800">
            <h2 class="text-xs font-bold uppercase tracking-wider text-black dark:text-white flex items-center gap-2 mb-3">
                <Bot class="w-4 h-4" />
                Dispatch New Autonomous Crawler Job
            </h2>

            <form onsubmit={handleSubmit} class="space-y-4">
                <div>
                    <label for="seed-url-input" class="block text-xs font-bold text-zinc-500 uppercase mb-1">Seed URL or XML Sitemap</label>
                    <input
                        id="seed-url-input"
                        type="url"
                        bind:value={$form.seed_url}
                        required
                        placeholder="https://docs.github.com or https://laravel.com/sitemap.xml"
                        class="w-full px-3 py-2 rounded-lg text-xs bg-white dark:bg-black border border-zinc-300 dark:border-zinc-800 text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-black dark:focus:ring-white font-mono"
                    />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label for="max-depth-input" class="block text-xs font-bold text-zinc-500 uppercase mb-1">Max Depth</label>
                        <input
                            id="max-depth-input"
                            type="number"
                            min="1"
                            max="5"
                            bind:value={$form.max_depth}
                            class="w-full px-3 py-2 rounded-lg text-xs bg-white dark:bg-black border border-zinc-300 dark:border-zinc-800 text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-black dark:focus:ring-white font-mono"
                        />
                    </div>
                    <div>
                        <label for="max-pages-input" class="block text-xs font-bold text-zinc-500 uppercase mb-1">Max Pages Budget</label>
                        <input
                            id="max-pages-input"
                            type="number"
                            min="5"
                            max="200"
                            bind:value={$form.max_pages}
                            class="w-full px-3 py-2 rounded-lg text-xs bg-white dark:bg-black border border-zinc-300 dark:border-zinc-800 text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-black dark:focus:ring-white font-mono"
                        />
                    </div>
                    <div>
                        <label for="concurrency-input" class="block text-xs font-bold text-zinc-500 uppercase mb-1">Concurrency</label>
                        <input
                            id="concurrency-input"
                            type="number"
                            min="1"
                            max="10"
                            bind:value={$form.concurrency}
                            class="w-full px-3 py-2 rounded-lg text-xs bg-white dark:bg-black border border-zinc-300 dark:border-zinc-800 text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-black dark:focus:ring-white font-mono"
                        />
                    </div>
                </div>

                <button
                    type="submit"
                    disabled={$form.processing}
                    class="px-4 py-2.5 rounded-lg bg-black text-white dark:bg-white dark:text-black hover:bg-zinc-800 dark:hover:bg-zinc-200 text-xs font-bold transition-colors flex items-center justify-center gap-1.5 cursor-pointer"
                >
                    {#if $form.processing}
                        <RefreshCw class="w-3.5 h-3.5 animate-spin" />
                        Dispatching...
                    {:else}
                        <Play class="w-3.5 h-3.5" />
                        Start Crawl Job
                    {/if}
                </button>
            </form>
        </div>

        <!-- Crawler Jobs Table -->
        <div class="bg-white dark:bg-zinc-950 rounded-xl border border-zinc-200 dark:border-zinc-800 overflow-hidden">
            <div class="p-4 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between">
                <h2 class="text-xs font-bold uppercase tracking-wider text-black dark:text-white flex items-center gap-2">
                    <Clock class="w-4 h-4" />
                    Crawl Queue & Execution History ({jobs.length})
                </h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-zinc-50 dark:bg-black text-zinc-500 border-b border-zinc-200 dark:border-zinc-800">
                        <tr>
                            <th class="px-4 py-2.5 font-medium">Target Seed URL</th>
                            <th class="px-3 py-2.5 font-medium">Status</th>
                            <th class="px-3 py-2.5 font-medium">Crawled</th>
                            <th class="px-3 py-2.5 font-medium">Indexed</th>
                            <th class="px-3 py-2.5 font-medium">Started At</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800 font-mono">
                        {#each jobs as job}
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-900/40 transition-colors">
                                <td class="px-4 py-2.5 font-sans font-medium text-black dark:text-white max-w-sm truncate">
                                    <a href={job.seed_url} target="_blank" rel="noreferrer" class="hover:underline">
                                        {job.seed_url}
                                    </a>
                                </td>
                                <td class="px-3 py-2.5">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {job.status === 'completed' ? 'bg-zinc-100 dark:bg-zinc-900 text-emerald-700 dark:text-emerald-400' : job.status === 'running' ? 'bg-black text-white dark:bg-white dark:text-black' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-900 dark:text-zinc-400'}">
                                        {job.status}
                                    </span>
                                </td>
                                <td class="px-3 py-2.5 text-zinc-600 dark:text-zinc-400">{job.pages_crawled} / {job.max_pages}</td>
                                <td class="px-3 py-2.5 font-bold text-black dark:text-white">{job.pages_indexed}</td>
                                <td class="px-3 py-2.5 text-zinc-400">{new Date(job.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</td>
                            </tr>
                        {:else}
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-zinc-400 font-sans text-xs">
                                    No crawl jobs yet. Dispatch a new seed URL above.
                                </td>
                            </tr>
                        {/each}
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <Footer />
</div>
