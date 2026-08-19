<script>
    import { Link, useForm, page } from '@inertiajs/svelte';
    import Navbar from '../Components/Navbar.svelte';
    import Footer from '../Components/Footer.svelte';
    import { 
        PlusCircle, 
        Globe, 
        Layers, 
        CheckCircle2, 
        RefreshCw, 
        ArrowRight, 
        FileText, 
        ShieldCheck, 
        Zap, 
        ExternalLink, 
        AlertCircle, 
        SearchCheck,
        Sparkles,
        Clock,
        HelpCircle
    } from 'lucide-svelte';

    let { recentSubmissions = [], totalIndexed = 0, totalDomains = 0 } = $props();

    const form = useForm({
        url: '',
        category: 'all',
        max_pages: 50,
        is_sitemap: false,
    });

    let isChecking = $state(false);
    let checkStatus = $state(null); // { ok: boolean, message: string }

    function handleSubmit(e) {
        e.preventDefault();
        $form.post('/submit', {
            onSuccess: () => {
                $form.reset('url');
                checkStatus = null;
            }
        });
    }

    async function preflightCheck() {
        if (!$form.url || !$form.url.startsWith('http')) {
            checkStatus = { ok: false, message: 'Please enter a valid URL starting with https:// or http://' };
            return;
        }
        isChecking = true;
        checkStatus = null;

        try {
            const urlObj = new URL($form.url);
            checkStatus = {
                ok: true,
                message: `Valid domain: ${urlObj.hostname}. Ready for crawler dispatch.`
            };
            if (urlObj.pathname.endsWith('.xml')) {
                $form.is_sitemap = true;
            }
        } catch (e) {
            checkStatus = { ok: false, message: 'Invalid URL format.' };
        } finally {
            isChecking = false;
        }
    }
</script>

<svelte:head>
    <title>Submit Your Website - Web-Search.org</title>
</svelte:head>

<div class="min-h-screen flex flex-col bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 transition-colors">
    <Navbar showSearch={true} />

    <main class="flex-1 max-w-5xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-10 space-y-10">
        <!-- Hero Banner -->
        <div class="text-center max-w-2xl mx-auto space-y-3">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-400 border border-indigo-200/60 dark:border-indigo-900/60">
                <Sparkles class="w-3.5 h-3.5" />
                Community Index Ingestion • Free & Open
            </div>
            <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                Submit Your Website
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">
                Submit your homepage, developer documentation, blog, or XML sitemap to the open-source search index. Zero fees, zero tracking, instant crawler dispatch.
            </p>
        </div>

        <!-- Submission Form Card -->
        <div class="p-6 sm:p-8 rounded-3xl bg-white dark:bg-slate-900/90 border border-slate-200/80 dark:border-slate-800/80 shadow-sm">
            <!-- Flash Message -->
            {#if $page.props.flash?.success}
                <div class="mb-6 p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-900 text-xs text-emerald-800 dark:text-emerald-300 flex items-start gap-3">
                    <CheckCircle2 class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" />
                    <div>
                        <div class="font-bold text-emerald-900 dark:text-emerald-200">Submission Queued!</div>
                        <div>{$page.props.flash.success}</div>
                    </div>
                </div>
            {/if}

            <form onsubmit={handleSubmit} class="space-y-6">
                <!-- Website URL Input -->
                <div>
                    <label for="submit-url" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">
                        Website or Sitemap URL <span class="text-rose-500">*</span>
                    </label>
                    <div class="flex flex-col sm:flex-row gap-2">
                        <div class="relative flex-1">
                            <input
                                id="submit-url"
                                type="url"
                                bind:value={$form.url}
                                required
                                placeholder="https://example.com or https://example.com/sitemap.xml"
                                class="w-full pl-10 pr-4 py-3 rounded-xl text-sm bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30 font-mono"
                            />
                            <Globe class="w-4 h-4 text-slate-400 absolute left-3.5 top-3.5" />
                        </div>
                        <button
                            type="button"
                            onclick={preflightCheck}
                            disabled={isChecking || !$form.url}
                            class="px-4 py-3 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 disabled:opacity-50 text-slate-700 dark:text-slate-300 text-xs font-semibold transition-colors shrink-0"
                        >
                            Validate URL
                        </button>
                    </div>

                    {#if checkStatus}
                        <div class="mt-2 text-xs flex items-center gap-1.5 {checkStatus.ok ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-500'}">
                            {#if checkStatus.ok}
                                <CheckCircle2 class="w-3.5 h-3.5 shrink-0" />
                            {:else}
                                <AlertCircle class="w-3.5 h-3.5 shrink-0" />
                            {/if}
                            <span>{checkStatus.message}</span>
                        </div>
                    {/if}

                    {#if $form.errors.url}
                        <div class="mt-1.5 text-xs text-rose-500">{$form.errors.url}</div>
                    {/if}
                </div>

                <!-- Settings Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <!-- Category Selector -->
                    <div>
                        <label for="category-select" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">
                            Primary Category
                        </label>
                        <select
                            id="category-select"
                            bind:value={$form.category}
                            class="w-full px-3.5 py-2.5 rounded-xl text-xs bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30 font-medium"
                        >
                            <option value="all">All / General Content</option>
                            <option value="tech">Technology & AI</option>
                            <option value="code">Code, SDKs & Repos</option>
                            <option value="news">News & Media</option>
                            <option value="science">Science & Academia</option>
                        </select>
                    </div>

                    <!-- Crawl Budget -->
                    <div>
                        <label for="budget-select" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">
                            Crawl Depth & Page Budget
                        </label>
                        <select
                            id="budget-select"
                            bind:value={$form.max_pages}
                            class="w-full px-3.5 py-2.5 rounded-xl text-xs bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30 font-medium"
                        >
                            <option value={10}>Quick Crawl (Up to 10 pages)</option>
                            <option value={50}>Standard Crawl (Up to 50 pages)</option>
                            <option value={200}>Deep Crawl (Up to 200 pages)</option>
                        </select>
                    </div>
                </div>

                <!-- Sitemap Checkbox -->
                <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200/60 dark:border-slate-800/60 flex items-center gap-3">
                    <input
                        type="checkbox"
                        id="is_sitemap"
                        bind:checked={$form.is_sitemap}
                        class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 h-4 w-4 cursor-pointer"
                    />
                    <label for="is_sitemap" class="text-xs text-slate-700 dark:text-slate-300 cursor-pointer select-none">
                        <span class="font-bold">This is an XML Sitemap URL</span> (e.g. <code class="font-mono text-indigo-600 dark:text-indigo-400">/sitemap.xml</code>) — automatically extracts and indexes all listed URLs.
                    </label>
                </div>

                <!-- Submit Button -->
                <button
                    type="submit"
                    disabled={$form.processing}
                    class="w-full py-3.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white text-sm font-semibold shadow-md shadow-indigo-600/20 transition-all flex items-center justify-center gap-2 cursor-pointer"
                >
                    {#if $form.processing}
                        <RefreshCw class="w-4 h-4 animate-spin" />
                        Queueing Submission...
                    {:else}
                        <PlusCircle class="w-4 h-4" />
                        Submit Website for Indexing
                    {/if}
                </button>
            </form>
        </div>

        <!-- Recent Submissions & Guidelines Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Recent Submissions -->
            <div class="p-6 rounded-3xl bg-white dark:bg-slate-900/80 border border-slate-200/80 dark:border-slate-800/80 shadow-2xs">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <Clock class="w-4 h-4 text-indigo-600" />
                        Recent Public Submissions
                    </h2>
                    <Link href="/crawler" class="text-xs text-indigo-600 hover:underline">
                        Crawler Queue →
                    </Link>
                </div>

                <div class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    {#each recentSubmissions as sub}
                        <div class="py-3 flex items-center justify-between text-xs">
                            <div class="truncate max-w-xs sm:max-w-sm">
                                <a href={sub.seed_url} target="_blank" rel="noreferrer" class="font-semibold text-slate-900 dark:text-white hover:text-indigo-600 truncate block">
                                    {sub.seed_url}
                                </a>
                                <span class="text-[11px] text-slate-400 font-mono">
                                    {sub.pages_indexed} indexed • {new Date(sub.created_at).toLocaleTimeString()}
                                </span>
                            </div>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wider bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
                                {sub.status}
                            </span>
                        </div>
                    {:else}
                        <div class="py-6 text-center text-xs text-slate-400">
                            No public submissions in queue yet. Be the first to submit a website!
                        </div>
                    {/each}
                </div>
            </div>

            <!-- Submission Guidelines & FAQ -->
            <div class="p-6 rounded-3xl bg-white dark:bg-slate-900/80 border border-slate-200/80 dark:border-slate-800/80 shadow-2xs space-y-4">
                <h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <HelpCircle class="w-4 h-4 text-indigo-600" />
                    Submission Guidelines & FAQ
                </h2>

                <div class="space-y-3 text-xs">
                    <div>
                        <span class="font-bold text-slate-800 dark:text-slate-200">How fast is my site crawled?</span>
                        <p class="text-slate-500 dark:text-slate-400 mt-0.5">
                            New submissions are picked up by the priority crawler worker queue within seconds.
                        </p>
                    </div>

                    <div>
                        <span class="font-bold text-slate-800 dark:text-slate-200">Do you respect robots.txt?</span>
                        <p class="text-slate-500 dark:text-slate-400 mt-0.5">
                            Yes. WebSearchBot strictly parses and respects <code class="font-mono text-indigo-600">robots.txt</code> Disallow directives and crawl delays.
                        </p>
                    </div>

                    <div>
                        <span class="font-bold text-slate-800 dark:text-slate-200">Can I inspect my URL later?</span>
                        <p class="text-slate-500 dark:text-slate-400 mt-0.5">
                            Yes! Use our <Link href="/console/inspect" class="text-indigo-600 underline">URL Inspection Tool</Link> or the <Link href="/console" class="text-indigo-600 underline">Search Console</Link> to inspect SERP signals and PageRank scores.
                        </p>
                    </div>

                    <div>
                        <span class="font-bold text-slate-800 dark:text-slate-200">Is there an API to submit sites?</span>
                        <p class="text-slate-500 dark:text-slate-400 mt-0.5">
                            Yes. Developers can POST to <code class="font-mono text-indigo-600">/api/v1/submit</code> directly from CI/CD pipelines or CMS webhooks.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <Footer />
</div>
