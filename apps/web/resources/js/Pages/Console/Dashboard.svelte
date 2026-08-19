<script>
    import { Link, useForm } from '@inertiajs/svelte';
    import Navbar from '../../Components/Navbar.svelte';
    import Footer from '../../Components/Footer.svelte';
    import { 
        SearchCheck, 
        Search, 
        BarChart3, 
        FileText, 
        ShieldCheck, 
        CheckCircle2, 
        AlertCircle, 
        Layers, 
        ExternalLink, 
        Plus, 
        Globe, 
        Check,
        Copy
    } from 'lucide-svelte';

    let { domains = [], currentDomain = null, performance = {}, coverage = {}, sitemaps = [] } = $props();

    let inspectUrlInput = $state('');
    let showVerifyModal = $state(false);
    let verifyMethod = $state('dns_txt');
    let copied = $state(false);

    const verifyForm = useForm({
        domain: '',
        method: 'dns_txt',
    });

    function handleInspectSubmit(e) {
        e.preventDefault();
        if (inspectUrlInput.trim()) {
            window.location.href = `/console/inspect?url=${encodeURIComponent(inspectUrlInput.trim())}`;
        }
    }

    function handleVerifySubmit(e) {
        e.preventDefault();
        if (currentDomain?.name) {
            $verifyForm.domain = currentDomain.name;
            $verifyForm.method = verifyMethod;
            $verifyForm.post('/console/verify', {
                onSuccess: () => {
                    showVerifyModal = false;
                }
            });
        }
    }

    function copyToken(token) {
        if (!token) return;
        navigator.clipboard.writeText(token);
        copied = true;
        setTimeout(() => copied = false, 2000);
    }
</script>

<svelte:head>
    <title>Web-Search Console - {currentDomain?.name || 'Overview'}</title>
</svelte:head>

<div class="min-h-screen flex flex-col bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 transition-colors">
    <Navbar showSearch={false} />

    <!-- Search Console Subheader -->
    <div class="border-b border-slate-200/80 dark:border-slate-800/80 bg-white dark:bg-slate-900/90 transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <!-- Property Selector -->
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-indigo-600 text-white shadow-sm">
                    <SearchCheck class="w-5 h-5" />
                </div>
                <div>
                    <div class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Search Console Property</div>
                    <div class="flex items-center gap-2">
                        {#if domains.length > 0 && currentDomain}
                            <select
                                value={currentDomain.name}
                                onchange={(e) => window.location.href = `/console?domain=${encodeURIComponent(e.currentTarget.value)}`}
                                class="bg-transparent font-bold text-base text-slate-900 dark:text-white focus:outline-none cursor-pointer"
                            >
                                {#each domains as d}
                                    <option value={d.name} class="bg-white dark:bg-slate-900 text-slate-900 dark:text-white">
                                        {d.protocol}://{d.name} {d.is_verified ? '✓' : '(unverified)'}
                                    </option>
                                {/each}
                            </select>

                            {#if currentDomain.is_verified}
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900">
                                    <CheckCircle2 class="w-3 h-3" />
                                    Verified
                                </span>
                            {:else}
                                <button
                                    onclick={() => showVerifyModal = true}
                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-400 border border-amber-200 dark:border-amber-900 hover:bg-amber-100"
                                >
                                    <AlertCircle class="w-3 h-3" />
                                    Verify Ownership
                                </button>
                            {/if}
                        {:else}
                            <span class="font-bold text-sm text-slate-600 dark:text-slate-300">
                                No domain properties registered
                            </span>
                        {/if}
                    </div>
                </div>
            </div>

            <!-- Global URL Inspection Search Bar -->
            <form onsubmit={handleInspectSubmit} class="relative w-full sm:w-96">
                <input
                    type="url"
                    bind:value={inspectUrlInput}
                    required
                    placeholder={currentDomain ? `Inspect URL in ${currentDomain.name}...` : "Inspect any URL in search index..."}
                    class="w-full pl-9 pr-20 py-2 rounded-xl text-xs bg-slate-100 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                />
                <Search class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-2.5" />
                <button
                    type="submit"
                    class="absolute right-1.5 top-1 px-2.5 py-1 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-[11px] font-semibold transition-colors"
                >
                    Inspect
                </button>
            </form>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="border-b border-slate-200/80 dark:border-slate-800/80 bg-white/50 dark:bg-slate-950/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center gap-2 overflow-x-auto py-2">
            <span class="px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-indigo-600 text-white shadow-2xs">
                Overview
            </span>
            <Link
                href={`/console/performance${currentDomain ? `?domain=${currentDomain.name}` : ''}`}
                class="px-3.5 py-1.5 rounded-xl text-xs font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-900 transition-colors"
            >
                Performance
            </Link>
            <Link
                href="/console/inspect"
                class="px-3.5 py-1.5 rounded-xl text-xs font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-900 transition-colors"
            >
                URL Inspection
            </Link>
            <Link
                href={`/console/sitemaps${currentDomain ? `?domain=${currentDomain.name}` : ''}`}
                class="px-3.5 py-1.5 rounded-xl text-xs font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-900 transition-colors"
            >
                Sitemaps
            </Link>
        </div>
    </div>

    <!-- Main Console Grid -->
    <main class="flex-1 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        {#if currentDomain}
            <!-- Performance Overview Card -->
            <div class="p-6 rounded-2xl bg-white dark:bg-slate-900/80 border border-slate-200/80 dark:border-slate-800/80 shadow-2xs">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <BarChart3 class="w-5 h-5 text-indigo-600" />
                            Search Performance Overview (Last 28 Days)
                        </h2>
                        <p class="text-xs text-slate-400 mt-0.5">Organic impressions and click-through rates from Web-Search.org queries.</p>
                    </div>

                    <Link
                        href={`/console/performance?domain=${currentDomain.name}`}
                        class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline inline-flex items-center gap-1"
                    >
                        Full Performance Report →
                    </Link>
                </div>

                <!-- 4 Metrics Row -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200/60 dark:border-slate-800/60">
                        <span class="text-[11px] font-semibold text-slate-400 uppercase">Total Clicks</span>
                        <div class="text-2xl font-bold font-mono text-indigo-600 dark:text-indigo-400 mt-1">
                            {performance.summary?.totalClicks || 0}
                        </div>
                    </div>

                    <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200/60 dark:border-slate-800/60">
                        <span class="text-[11px] font-semibold text-slate-400 uppercase">Total Impressions</span>
                        <div class="text-2xl font-bold font-mono text-slate-900 dark:text-white mt-1">
                            {performance.summary?.totalImpressions || 0}
                        </div>
                    </div>

                    <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200/60 dark:border-slate-800/60">
                        <span class="text-[11px] font-semibold text-slate-400 uppercase">Average CTR</span>
                        <div class="text-2xl font-bold font-mono text-emerald-600 dark:text-emerald-400 mt-1">
                            {performance.summary?.averageCtr || 0}%
                        </div>
                    </div>

                    <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200/60 dark:border-slate-800/60">
                        <span class="text-[11px] font-semibold text-slate-400 uppercase">Average Position</span>
                        <div class="text-2xl font-bold font-mono text-purple-600 dark:text-purple-400 mt-1">
                            #{performance.summary?.averagePosition || 0}
                        </div>
                    </div>
                </div>

                <!-- Top Queries preview -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="text-slate-400 border-b border-slate-100 dark:border-slate-800">
                            <tr>
                                <th class="py-2.5 font-medium">Top Search Queries</th>
                                <th class="py-2.5 font-medium">Clicks</th>
                                <th class="py-2.5 font-medium">Impressions</th>
                                <th class="py-2.5 font-medium">CTR</th>
                                <th class="py-2.5 font-medium">Position</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/40 font-mono">
                            {#each performance.queries?.slice(0, 4) || [] as q}
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30">
                                    <td class="py-2.5 font-sans font-medium text-slate-800 dark:text-slate-200">{q.query}</td>
                                    <td class="py-2.5 text-indigo-600 dark:text-indigo-400 font-bold">{q.clicks}</td>
                                    <td class="py-2.5 text-slate-500">{q.impressions}</td>
                                    <td class="py-2.5 text-emerald-600 dark:text-emerald-400">{q.ctr}%</td>
                                    <td class="py-2.5 text-slate-500">#{q.position}</td>
                                </tr>
                            {:else}
                                <tr>
                                    <td colspan="5" class="py-6 text-center text-slate-400 font-sans">
                                        No search queries recorded yet for this property.
                                    </td>
                                </tr>
                            {/each}
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Index Coverage Breakdown -->
                <div class="p-6 rounded-2xl bg-white dark:bg-slate-900/80 border border-slate-200/80 dark:border-slate-800/80 shadow-2xs">
                    <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2 mb-4">
                        <Layers class="w-5 h-5 text-indigo-600" />
                        Index Coverage & Health
                    </h2>

                    <div class="grid grid-cols-3 gap-3 mb-6 text-center">
                        <div class="p-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200/60 dark:border-emerald-900/60">
                            <span class="text-[10px] font-semibold text-emerald-700 dark:text-emerald-400 uppercase">Valid Indexed</span>
                            <div class="text-xl font-bold font-mono text-emerald-700 dark:text-emerald-300 mt-0.5">
                                {coverage.validIndexed || currentDomain.total_pages || 0}
                            </div>
                        </div>

                        <div class="p-3 rounded-xl bg-slate-100 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700">
                            <span class="text-[10px] font-semibold text-slate-500 uppercase">Excluded</span>
                            <div class="text-xl font-bold font-mono text-slate-700 dark:text-slate-300 mt-0.5">
                                {coverage.excluded || 0}
                            </div>
                        </div>

                        <div class="p-3 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200/60 dark:border-rose-900/60">
                            <span class="text-[10px] font-semibold text-rose-700 dark:text-rose-400 uppercase">Errors</span>
                            <div class="text-xl font-bold font-mono text-rose-700 dark:text-rose-300 mt-0.5">
                                {coverage.errors || 0}
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2">
                        {#each coverage.breakdown || [] as item}
                            <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-950/60 flex items-center justify-between text-xs">
                                <span class="text-slate-700 dark:text-slate-300">{item.status}</span>
                                <span class="font-mono font-semibold text-slate-500">{item.count}</span>
                            </div>
                        {:else}
                            <div class="text-center text-xs text-slate-400 py-4">No coverage issues detected.</div>
                        {/each}
                    </div>
                </div>

                <!-- Sitemaps Status -->
                <div class="p-6 rounded-2xl bg-white dark:bg-slate-900/80 border border-slate-200/80 dark:border-slate-800/80 shadow-2xs">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <FileText class="w-5 h-5 text-indigo-600" />
                            Submitted Sitemaps
                        </h2>
                        <Link
                            href={`/console/sitemaps?domain=${currentDomain.name}`}
                            class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline"
                        >
                            Manage Sitemaps →
                        </Link>
                    </div>

                    <div class="space-y-3">
                        {#each sitemaps as sm}
                            <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200/60 dark:border-slate-800/60 flex items-center justify-between text-xs">
                                <div>
                                    <a href={sm.url} target="_blank" rel="noreferrer" class="font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                                        {sm.url}
                                    </a>
                                    <div class="text-[11px] text-slate-400 mt-0.5">
                                        Status: <span class="text-emerald-600 font-semibold">{sm.status}</span> • {sm.indexed_urls} URLs indexed
                                    </div>
                                </div>
                                <span class="text-[10px] text-slate-400 font-mono">
                                    {new Date(sm.last_crawled_at || sm.created_at).toLocaleDateString()}
                                </span>
                            </div>
                        {:else}
                            <div class="p-6 rounded-xl border border-dashed border-slate-200 dark:border-slate-800 text-center text-xs text-slate-400">
                                No sitemaps submitted yet.
                                <Link href={`/console/sitemaps?domain=${currentDomain.name}`} class="text-indigo-600 font-semibold block mt-1">
                                    Submit sitemap.xml
                                </Link>
                            </div>
                        {/each}
                    </div>
                </div>
            </div>
        {:else}
            <!-- Empty state when no properties exist yet -->
            <div class="p-12 rounded-3xl bg-white dark:bg-slate-900/80 border border-slate-200/80 dark:border-slate-800/80 shadow-2xs text-center max-w-xl mx-auto space-y-4">
                <div class="w-14 h-14 rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center mx-auto">
                    <SearchCheck class="w-7 h-7" />
                </div>
                <h2 class="text-lg font-bold text-slate-900 dark:text-white">Welcome to Web-Search Console</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                    No domain properties are indexed yet. Use the URL inspection tool above to inspect and request indexing for any website, or launch the crawler to build your index.
                </p>
                <div class="pt-2 flex justify-center gap-3">
                    <Link
                        href="/crawler"
                        class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-md shadow-indigo-600/20"
                    >
                        Launch Crawler →
                    </Link>
                    <Link
                        href="/console/inspect"
                        class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-medium"
                    >
                        Inspect a URL
                    </Link>
                </div>
            </div>
        {/if}
    </main>

    <!-- Domain Ownership Verification Modal -->
    {#if showVerifyModal && currentDomain}
        <div class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl max-w-lg w-full p-6 animate-in zoom-in-95 duration-150">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <ShieldCheck class="w-5 h-5 text-indigo-600" />
                        Verify Domain: {currentDomain.name}
                    </h3>
                    <button onclick={() => showVerifyModal = false} class="text-slate-400 hover:text-slate-600">✕</button>
                </div>

                <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">
                    Verify ownership to unlock full Search Console features, search query analytics, and indexing controls.
                </p>

                <!-- Method Tabs -->
                <div class="flex gap-2 mb-4">
                    <button
                        onclick={() => verifyMethod = 'dns_txt'}
                        class="px-3 py-1.5 rounded-xl text-xs font-semibold transition-colors {verifyMethod === 'dns_txt' ? 'bg-indigo-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300'}"
                    >
                        DNS TXT Record
                    </button>
                    <button
                        onclick={() => verifyMethod = 'meta_tag'}
                        class="px-3 py-1.5 rounded-xl text-xs font-semibold transition-colors {verifyMethod === 'meta_tag' ? 'bg-indigo-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300'}"
                    >
                        HTML &lt;meta&gt; Tag
                    </button>
                </div>

                {#if verifyMethod === 'dns_txt'}
                    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs mb-4">
                        <span class="text-slate-500 block mb-1">Add this TXT record to your DNS provider:</span>
                        <div class="flex items-center justify-between font-mono bg-white dark:bg-slate-900 p-2 rounded-lg border border-slate-200 dark:border-slate-800">
                            <span class="truncate text-[11px]">{currentDomain.verification_token || 'web-search-site-verification=ws_token_123'}</span>
                            <button onclick={() => copyToken(currentDomain.verification_token)} class="p-1 text-indigo-600">
                                {#if copied}<Check class="w-4 h-4" />{:else}<Copy class="w-4 h-4" />{/if}
                            </button>
                        </div>
                    </div>
                {:else}
                    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs mb-4">
                        <span class="text-slate-500 block mb-1">Paste this meta tag inside the &lt;head&gt; section of your homepage:</span>
                        <div class="flex items-center justify-between font-mono bg-white dark:bg-slate-900 p-2 rounded-lg border border-slate-200 dark:border-slate-800">
                            <span class="truncate text-[11px]">&lt;meta name="web-search-verification" content="{currentDomain.verification_token || 'ws_token_123'}" /&gt;</span>
                            <button onclick={() => copyToken(`<meta name="web-search-verification" content="${currentDomain.verification_token}" />`)} class="p-1 text-indigo-600">
                                {#if copied}<Check class="w-4 h-4" />{:else}<Copy class="w-4 h-4" />{/if}
                            </button>
                        </div>
                    </div>
                {/if}

                <form onsubmit={handleVerifySubmit} class="flex gap-3">
                    <button
                        type="submit"
                        disabled={$verifyForm.processing}
                        class="flex-1 py-2.5 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-md shadow-indigo-600/20 transition-colors"
                    >
                        {#if $verifyForm.processing}Verifying...{:else}Confirm Verification{/if}
                    </button>
                    <button
                        type="button"
                        onclick={() => showVerifyModal = false}
                        class="py-2.5 px-4 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold"
                    >
                        Cancel
                    </button>
                </form>
            </div>
        </div>
    {/if}

    <Footer />
</div>
