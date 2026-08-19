<script>
    import { Link, useForm } from '@inertiajs/svelte';
    import Navbar from '../../Components/Navbar.svelte';
    import Footer from '../../Components/Footer.svelte';
    import { SearchCheck, FileText, Plus, RefreshCw, CheckCircle2, AlertCircle, ExternalLink } from 'lucide-svelte';

    let { currentDomain = {}, sitemaps = [], domains = [] } = $props();

    const form = useForm({
        domain_id: '',
        sitemap_url: '',
    });

    function handleSubmit(e) {
        e.preventDefault();
        $form.domain_id = currentDomain.id;
        $form.post('/console/sitemaps', {
            onSuccess: () => {
                $form.reset('sitemap_url');
            }
        });
    }
</script>

<svelte:head>
    <title>Sitemaps - {currentDomain.name} - Web-Search Console</title>
</svelte:head>

<div class="min-h-screen flex flex-col bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 transition-colors">
    <Navbar showSearch={false} />

    <!-- Subheader -->
    <div class="border-b border-slate-200/80 dark:border-slate-800/80 bg-white dark:bg-slate-900/90 transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <Link href="/console" class="p-2 rounded-xl bg-indigo-600 text-white shadow-sm">
                    <SearchCheck class="w-5 h-5" />
                </Link>
                <div>
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Search Console</span>
                    <h1 class="font-bold text-base text-slate-900 dark:text-white">XML Sitemaps Management</h1>
                </div>
            </div>

            <!-- Property Switcher -->
            <select
                value={currentDomain.name}
                onchange={(e) => window.location.href = `/console/sitemaps?domain=${encodeURIComponent(e.currentTarget.value)}`}
                class="bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3 py-1.5 rounded-xl text-xs font-semibold focus:outline-none"
            >
                {#each domains as d}
                    <option value={d.name}>{d.name}</option>
                {/each}
            </select>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="border-b border-slate-200/80 dark:border-slate-800/80 bg-white/50 dark:bg-slate-950/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center gap-2 overflow-x-auto py-2">
            <Link
                href={`/console?domain=${currentDomain.name}`}
                class="px-3.5 py-1.5 rounded-xl text-xs font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-900 transition-colors"
            >
                Overview
            </Link>
            <Link
                href={`/console/performance?domain=${currentDomain.name}`}
                class="px-3.5 py-1.5 rounded-xl text-xs font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-900 transition-colors"
            >
                Performance
            </Link>
            <Link
                href={`/console/inspect?url=https://${currentDomain.name}`}
                class="px-3.5 py-1.5 rounded-xl text-xs font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-900 transition-colors"
            >
                URL Inspection
            </Link>
            <span class="px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-indigo-600 text-white shadow-2xs">
                Sitemaps
            </span>
        </div>
    </div>

    <main class="flex-1 max-w-5xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        <!-- Add Sitemap Card -->
        <div class="p-6 rounded-3xl bg-white dark:bg-slate-900/90 border border-slate-200/80 dark:border-slate-800/80 shadow-2xs">
            <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2 mb-2">
                <Plus class="w-4 h-4 text-indigo-600" />
                Submit a new sitemap
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">
                Submit an XML sitemap URL so WebSearchBot can discover and index all your published pages automatically.
            </p>

            <form onsubmit={handleSubmit} class="flex flex-col sm:flex-row items-center gap-3">
                <div class="relative flex-1 w-full">
                    <input
                        type="url"
                        bind:value={$form.sitemap_url}
                        required
                        placeholder={`https://${currentDomain.name}/sitemap.xml`}
                        class="w-full px-3.5 py-2.5 rounded-xl text-xs bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30 font-mono"
                    />
                </div>
                <button
                    type="submit"
                    disabled={$form.processing}
                    class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white text-xs font-semibold shadow-md shadow-indigo-600/20 transition-all flex items-center justify-center gap-2 shrink-0"
                >
                    {#if $form.processing}
                        <RefreshCw class="w-3.5 h-3.5 animate-spin" />
                        Submitting...
                    {:else}
                        Submit Sitemap
                    {/if}
                </button>
            </form>
        </div>

        <!-- Submitted Sitemaps Table -->
        <div class="bg-white dark:bg-slate-900/80 rounded-3xl border border-slate-200/80 dark:border-slate-800/80 shadow-2xs overflow-hidden">
            <div class="p-5 border-b border-slate-200/80 dark:border-slate-800/80 flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <FileText class="w-4 h-4 text-indigo-600" />
                    Submitted Sitemaps ({sitemaps.length})
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-950/60 text-slate-500 border-b border-slate-200/80 dark:border-slate-800/80">
                        <tr>
                            <th class="px-5 py-3 font-medium">Sitemap URL</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 font-medium">Last Read</th>
                            <th class="px-4 py-3 font-medium">Discovered URLs</th>
                            <th class="px-4 py-3 font-medium">Indexed URLs</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                        {#each sitemaps as sm}
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="px-5 py-3.5 font-medium text-slate-900 dark:text-white max-w-sm truncate">
                                    <a href={sm.url} target="_blank" rel="noreferrer" class="hover:text-indigo-600 hover:underline inline-flex items-center gap-1.5">
                                        <span>{sm.url}</span>
                                        <ExternalLink class="w-3 h-3 text-slate-400" />
                                    </a>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wider bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-400">
                                        <CheckCircle2 class="w-3 h-3" />
                                        {sm.status}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-slate-500 font-mono text-[11px]">
                                    {new Date(sm.last_crawled_at || sm.created_at).toLocaleDateString()}
                                </td>
                                <td class="px-4 py-3.5 font-mono text-slate-700 dark:text-slate-300 font-semibold">
                                    {sm.total_urls}
                                </td>
                                <td class="px-4 py-3.5 font-mono text-emerald-600 dark:text-emerald-400 font-bold">
                                    {sm.indexed_urls}
                                </td>
                            </tr>
                        {:else}
                            <tr>
                                <td colspan="5" class="px-5 py-8 text-center text-slate-400">
                                    No sitemaps recorded for this domain property.
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
