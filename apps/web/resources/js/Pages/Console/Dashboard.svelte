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
    <title>Web-Search Console {currentDomain?.name ? `- ${currentDomain.name}` : ''}</title>
</svelte:head>

<div class="min-h-screen flex flex-col bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 transition-colors">
    <Navbar showSearch={false} />

    <!-- Subheader -->
    <div class="border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/60 transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <!-- Property Selector -->
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-indigo-600 text-white flex items-center justify-center font-bold">
                    <SearchCheck class="w-4.5 h-4.5" />
                </div>
                <div>
                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Search Console Property</div>
                    <div class="flex items-center gap-2">
                        {#if domains.length > 0 && currentDomain}
                            <select
                                value={currentDomain.name}
                                onchange={(e) => window.location.href = `/console?domain=${encodeURIComponent(e.currentTarget.value)}`}
                                class="bg-transparent font-bold text-sm text-slate-900 dark:text-white focus:outline-none cursor-pointer"
                            >
                                {#each domains as d}
                                    <option value={d.name} class="bg-white dark:bg-slate-900 text-slate-900 dark:text-white">
                                        {d.protocol}://{d.name} {d.is_verified ? '✓' : '(unverified)'}
                                    </option>
                                {/each}
                            </select>

                            {#if currentDomain.is_verified}
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900">
                                    <CheckCircle2 class="w-2.5 h-2.5" />
                                    Verified
                                </span>
                            {:else}
                                <button
                                    onclick={() => showVerifyModal = true}
                                    class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-400 border border-amber-200 dark:border-amber-900 hover:bg-amber-100 cursor-pointer"
                                >
                                    <AlertCircle class="w-2.5 h-2.5" />
                                    Verify Domain
                                </button>
                            {/if}
                        {:else}
                            <span class="font-bold text-xs text-slate-600 dark:text-slate-300">
                                No domain properties
                            </span>
                        {/if}
                    </div>
                </div>
            </div>

            <!-- URL Inspection Search Bar -->
            <form onsubmit={handleInspectSubmit} class="relative w-full sm:w-80">
                <input
                    type="url"
                    bind:value={inspectUrlInput}
                    required
                    placeholder={currentDomain ? `Inspect URL in ${currentDomain.name}...` : "Inspect any URL in search index..."}
                    class="w-full pl-8 pr-16 py-1.5 rounded-lg text-xs bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                />
                <Search class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2" />
                <button
                    type="submit"
                    class="absolute right-1 top-1 px-2.5 py-0.5 rounded bg-indigo-600 hover:bg-indigo-700 text-white text-[11px] font-semibold transition-colors"
                >
                    Inspect
                </button>
            </form>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="border-b border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center gap-2 overflow-x-auto py-2">
            <span class="px-3 py-1 rounded-md text-xs font-semibold bg-indigo-600 text-white">
                Overview
            </span>
            <Link
                href={`/console/performance${currentDomain ? `?domain=${currentDomain.name}` : ''}`}
                class="px-3 py-1 rounded-md text-xs font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-900 transition-colors"
            >
                Performance
            </Link>
            <Link
                href={`/console/links${currentDomain ? `?domain=${currentDomain.name}` : ''}`}
                class="px-3 py-1 rounded-md text-xs font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-900 transition-colors"
            >
                Links & Interlinking
            </Link>
            <Link
                href="/console/inspect"
                class="px-3 py-1 rounded-md text-xs font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-900 transition-colors"
            >
                URL Inspection
            </Link>
            <Link
                href={`/console/sitemaps${currentDomain ? `?domain=${currentDomain.name}` : ''}`}
                class="px-3 py-1 rounded-md text-xs font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-900 transition-colors"
            >
                Sitemaps
            </Link>
        </div>
    </div>

    <!-- Main Console Content (Flat) -->
    <main class="flex-1 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
        {#if currentDomain}
            <!-- Performance Overview Card -->
            <div class="p-5 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <BarChart3 class="w-4 h-4 text-indigo-600" />
                            Search Performance Overview
                        </h2>
                        <p class="text-xs text-slate-400 mt-0.5">Clicks and impressions from Web-Search.org queries.</p>
                    </div>

                    <Link
                        href={`/console/performance?domain=${currentDomain.name}`}
                        class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline"
                    >
                        Full Performance Report →
                    </Link>
                </div>

                <!-- 4 Metrics Row (Flat) -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
                    <div class="p-3 rounded-lg bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800">
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Total Clicks</span>
                        <div class="text-xl font-bold font-mono text-indigo-600 dark:text-indigo-400 mt-0.5">
                            {performance.summary?.totalClicks || 0}
                        </div>
                    </div>

                    <div class="p-3 rounded-lg bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800">
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Total Impressions</span>
                        <div class="text-xl font-bold font-mono text-slate-900 dark:text-white mt-0.5">
                            {performance.summary?.totalImpressions || 0}
                        </div>
                    </div>

                    <div class="p-3 rounded-lg bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800">
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Average CTR</span>
                        <div class="text-xl font-bold font-mono text-emerald-600 dark:text-emerald-400 mt-0.5">
                            {performance.summary?.averageCtr || 0}%
                        </div>
                    </div>

                    <div class="p-3 rounded-lg bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800">
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Average Position</span>
                        <div class="text-xl font-bold font-mono text-slate-700 dark:text-slate-300 mt-0.5">
                            #{performance.summary?.averagePosition || 0}
                        </div>
                    </div>
                </div>

                <!-- Top Queries Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="text-slate-400 border-b border-slate-200 dark:border-slate-800">
                            <tr>
                                <th class="py-2 font-medium">Top Search Queries</th>
                                <th class="py-2 font-medium">Clicks</th>
                                <th class="py-2 font-medium">Impressions</th>
                                <th class="py-2 font-medium">CTR</th>
                                <th class="py-2 font-medium">Position</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800/60 font-mono">
                            {#each performance.queries?.slice(0, 4) || [] as q}
                                <tr class="hover:bg-white dark:hover:bg-slate-800/40">
                                    <td class="py-2 font-sans font-medium text-slate-800 dark:text-slate-200">{q.query}</td>
                                    <td class="py-2 text-indigo-600 dark:text-indigo-400 font-bold">{q.clicks}</td>
                                    <td class="py-2 text-slate-500">{q.impressions}</td>
                                    <td class="py-2 text-emerald-600 dark:text-emerald-400">{q.ctr}%</td>
                                    <td class="py-2 text-slate-500">#{q.position}</td>
                                </tr>
                            {:else}
                                <tr>
                                    <td colspan="5" class="py-4 text-center text-slate-400 font-sans text-xs">
                                        No search query data logged yet.
                                    </td>
                                </tr>
                            {/each}
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Coverage & Sitemaps Row (Flat) -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Index Coverage -->
                <div class="p-5 rounded-xl bg-white dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-2 mb-3">
                        <Layers class="w-4 h-4 text-indigo-600" />
                        Index Coverage
                    </h2>

                    <div class="grid grid-cols-3 gap-2.5 mb-4 text-center">
                        <div class="p-2.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-900">
                            <span class="text-[10px] font-bold text-emerald-700 dark:text-emerald-400 uppercase">Valid Indexed</span>
                            <div class="text-lg font-bold font-mono text-emerald-700 dark:text-emerald-300">
                                {coverage.validIndexed || currentDomain.total_pages || 0}
                            </div>
                        </div>

                        <div class="p-2.5 rounded-lg bg-slate-100 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700">
                            <span class="text-[10px] font-bold text-slate-500 uppercase">Excluded</span>
                            <div class="text-lg font-bold font-mono text-slate-700 dark:text-slate-300">
                                {coverage.excluded || 0}
                            </div>
                        </div>

                        <div class="p-2.5 rounded-lg bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900">
                            <span class="text-[10px] font-bold text-rose-700 dark:text-rose-400 uppercase">Errors</span>
                            <div class="text-lg font-bold font-mono text-rose-700 dark:text-rose-300">
                                {coverage.errors || 0}
                            </div>
                        </div>
                    </div>

                    <div class="space-y-1.5 text-xs">
                        {#each coverage.breakdown || [] as item}
                            <div class="p-2 rounded-lg bg-slate-50 dark:bg-slate-950 flex items-center justify-between">
                                <span class="text-slate-700 dark:text-slate-300">{item.status}</span>
                                <span class="font-mono font-semibold text-slate-500">{item.count}</span>
                            </div>
                        {:else}
                            <div class="text-center text-xs text-slate-400 py-2">No coverage records found.</div>
                        {/each}
                    </div>
                </div>

                <!-- Sitemaps Status -->
                <div class="p-5 rounded-xl bg-white dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-2">
                            <FileText class="w-4 h-4 text-indigo-600" />
                            XML Sitemaps
                        </h2>
                        <Link
                            href={`/console/sitemaps?domain=${currentDomain.name}`}
                            class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline"
                        >
                            Manage →
                        </Link>
                    </div>

                    <div class="space-y-2">
                        {#each sitemaps as sm}
                            <div class="p-2.5 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 flex items-center justify-between text-xs">
                                <div>
                                    <a href={sm.url} target="_blank" rel="noreferrer" class="font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                                        {sm.url}
                                    </a>
                                    <div class="text-[11px] text-slate-400">
                                        Status: <span class="text-emerald-600 font-semibold">{sm.status}</span> • {sm.indexed_urls} indexed
                                    </div>
                                </div>
                                <span class="text-[10px] text-slate-400 font-mono">
                                    {new Date(sm.last_crawled_at || sm.created_at).toLocaleDateString()}
                                </span>
                            </div>
                        {:else}
                            <div class="p-5 rounded-lg border border-dashed border-slate-200 dark:border-slate-800 text-center text-xs text-slate-400">
                                No sitemaps submitted.
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
            <div class="p-8 rounded-2xl bg-white dark:bg-slate-900/80 border border-slate-200 dark:border-slate-800 text-center max-w-lg mx-auto space-y-3">
                <div class="w-12 h-12 rounded-xl bg-indigo-50 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 flex items-center justify-center mx-auto">
                    <SearchCheck class="w-6 h-6" />
                </div>
                <h2 class="text-base font-bold text-slate-900 dark:text-white">Welcome to Web-Search Console</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                    No domain properties are indexed yet. Submit a website to start crawling or use URL inspection.
                </p>
                <div class="pt-2 flex justify-center gap-2">
                    <Link
                        href="/submit"
                        class="px-3.5 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold transition-colors"
                    >
                        Submit Website →
                    </Link>
                    <Link
                        href="/console/inspect"
                        class="px-3.5 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-medium hover:bg-slate-200 transition-colors"
                    >
                        Inspect a URL
                    </Link>
                </div>
            </div>
        {/if}
    </main>

    <!-- Verification Modal (Flat) -->
    {#if showVerifyModal && currentDomain}
        <div class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xl max-w-lg w-full p-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <ShieldCheck class="w-4.5 h-4.5 text-indigo-600" />
                        Verify Domain: {currentDomain.name}
                    </h3>
                    <button onclick={() => showVerifyModal = false} class="text-slate-400 hover:text-slate-600 cursor-pointer">✕</button>
                </div>

                <p class="text-xs text-slate-500 dark:text-slate-400 mb-3">
                    Verify ownership to unlock Search Console query analytics and indexing controls.
                </p>

                <!-- Method Tabs -->
                <div class="flex gap-2 mb-3">
                    <button
                        onclick={() => verifyMethod = 'dns_txt'}
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors cursor-pointer {verifyMethod === 'dns_txt' ? 'bg-indigo-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300'}"
                    >
                        DNS TXT Record
                    </button>
                    <button
                        onclick={() => verifyMethod = 'meta_tag'}
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors cursor-pointer {verifyMethod === 'meta_tag' ? 'bg-indigo-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300'}"
                    >
                        HTML Meta Tag
                    </button>
                </div>

                {#if verifyMethod === 'dns_txt'}
                    <div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs mb-4">
                        <span class="text-slate-500 block mb-1">Add this TXT record to your DNS host:</span>
                        <div class="flex items-center justify-between font-mono bg-white dark:bg-slate-900 p-2 rounded border border-slate-200 dark:border-slate-800">
                            <span class="truncate text-[11px]">{currentDomain.verification_token || 'web-search-site-verification=ws_token_123'}</span>
                            <button onclick={() => copyToken(currentDomain.verification_token)} class="p-1 text-indigo-600 cursor-pointer">
                                {#if copied}<Check class="w-3.5 h-3.5" />{:else}<Copy class="w-3.5 h-3.5" />{/if}
                            </button>
                        </div>
                    </div>
                {:else}
                    <div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs mb-4">
                        <span class="text-slate-500 block mb-1">Paste this meta tag in your homepage &lt;head&gt;:</span>
                        <div class="flex items-center justify-between font-mono bg-white dark:bg-slate-900 p-2 rounded border border-slate-200 dark:border-slate-800">
                            <span class="truncate text-[11px]">&lt;meta name="web-search-verification" content="{currentDomain.verification_token || 'ws_token_123'}" /&gt;</span>
                            <button onclick={() => copyToken(`<meta name="web-search-verification" content="${currentDomain.verification_token}" />`)} class="p-1 text-indigo-600 cursor-pointer">
                                {#if copied}<Check class="w-3.5 h-3.5" />{:else}<Copy class="w-3.5 h-3.5" />{/if}
                            </button>
                        </div>
                    </div>
                {/if}

                <form onsubmit={handleVerifySubmit} class="flex gap-2">
                    <button
                        type="submit"
                        disabled={$verifyForm.processing}
                        class="flex-1 py-2 px-3 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold transition-colors cursor-pointer"
                    >
                        {#if $verifyForm.processing}Verifying...{:else}Confirm Verification{/if}
                    </button>
                    <button
                        type="button"
                        onclick={() => showVerifyModal = false}
                        class="py-2 px-3 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-medium cursor-pointer"
                    >
                        Cancel
                    </button>
                </form>
            </div>
        </div>
    {/if}

    <Footer />
</div>
