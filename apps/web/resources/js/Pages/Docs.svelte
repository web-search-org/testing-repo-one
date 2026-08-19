<script>
    import { Link } from '@inertiajs/svelte';
    import Navbar from '../Components/Navbar.svelte';
    import Footer from '../Components/Footer.svelte';
    import { BookOpen, Terminal, Code2, Layers, Cpu, Database, Check } from 'lucide-svelte';

    let copiedSnippet = $state(null);

    function copyCode(id, code) {
        navigator.clipboard.writeText(code);
        copiedSnippet = id;
        setTimeout(() => copiedSnippet = null, 2000);
    }
</script>

<svelte:head>
    <title>API & Developer Documentation - Web-Search.org</title>
</svelte:head>

<div class="min-h-screen flex flex-col bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 transition-colors">
    <Navbar showSearch={true} />

    <main class="flex-1 max-w-5xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white flex items-center gap-2.5">
                <BookOpen class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
                Developer Documentation & Monorepo Guide
            </h1>
            <p class="mt-1 text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                Explore the Web-Search.org architecture, REST API endpoints, and official client SDKs.
            </p>
        </div>

        <div class="space-y-6">
            <!-- Monorepo Overview (Flat) -->
            <section class="p-5 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-2 mb-3">
                    <Layers class="w-4 h-4 text-indigo-600" />
                    Monorepo Architecture
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div class="p-3.5 rounded-lg bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800">
                        <span class="font-mono font-bold text-xs text-indigo-600 dark:text-indigo-400">apps/web</span>
                        <p class="mt-1 text-xs text-slate-500">Main search engine UI, Search Console & REST API (Laravel 12 + Svelte 5).</p>
                    </div>
                    <div class="p-3.5 rounded-lg bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800">
                        <span class="font-mono font-bold text-xs text-indigo-600 dark:text-indigo-400">apps/crawler</span>
                        <p class="mt-1 text-xs text-slate-500">High-throughput Python asyncio crawler with robots.txt, extractor & rate limiter.</p>
                    </div>
                    <div class="p-3.5 rounded-lg bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800">
                        <span class="font-mono font-bold text-xs text-indigo-600 dark:text-indigo-400">apps/indexer</span>
                        <p class="mt-1 text-xs text-slate-500">BM25 ranker, Porter stemmer, link graph analyzer & PageRank calculator.</p>
                    </div>
                </div>
            </section>

            <!-- REST API Reference -->
            <section class="p-5 rounded-xl bg-white dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 space-y-5">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-2">
                    <Terminal class="w-4 h-4 text-indigo-600" />
                    Search REST API Reference
                </h2>

                <div class="space-y-4">
                    <!-- Endpoint 1 -->
                    <div class="p-3.5 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800">
                        <div class="flex items-center gap-2 mb-1.5">
                            <span class="px-1.5 py-0.5 rounded bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300 font-mono text-[10px] font-bold">GET</span>
                            <code class="text-xs font-mono text-slate-800 dark:text-slate-200">/api/v1/search?q={query}&category={category}&page={page}</code>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-2">
                            Query the search engine with optional category filters (all, tech, code, news) and pagination.
                        </p>
                        <pre class="p-2.5 rounded bg-slate-900 text-slate-200 text-xs font-mono overflow-x-auto"><code>curl -X GET "http://localhost:8000/api/v1/search?q=laravel+svelte"</code></pre>
                    </div>

                    <!-- Endpoint 2 -->
                    <div class="p-3.5 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800">
                        <div class="flex items-center gap-2 mb-1.5">
                            <span class="px-1.5 py-0.5 rounded bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300 font-mono text-[10px] font-bold">GET</span>
                            <code class="text-xs font-mono text-slate-800 dark:text-slate-200">/api/v1/suggest?q={prefix}</code>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-2">
                            Fetch instant autocomplete search query suggestions.
                        </p>
                        <pre class="p-2.5 rounded bg-slate-900 text-slate-200 text-xs font-mono overflow-x-auto"><code>curl -X GET "http://localhost:8000/api/v1/suggest?q=open"</code></pre>
                    </div>

                    <!-- Endpoint 3 -->
                    <div class="p-3.5 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800">
                        <div class="flex items-center gap-2 mb-1.5">
                            <span class="px-1.5 py-0.5 rounded bg-indigo-100 dark:bg-indigo-950 text-indigo-800 dark:text-indigo-300 font-mono text-[10px] font-bold">POST</span>
                            <code class="text-xs font-mono text-slate-800 dark:text-slate-200">/api/v1/submit</code>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-2">
                            Queue a new website or XML sitemap URL to be indexed by the crawler.
                        </p>
                        <pre class="p-2.5 rounded bg-slate-900 text-slate-200 text-xs font-mono overflow-x-auto"><code>curl -X POST "http://localhost:8000/api/v1/submit" \
  -H "Content-Type: application/json" \
  -d '&#123;"url": "https://svelte.dev", "max_pages": 50&#125;'</code></pre>
                    </div>
                </div>
            </section>

            <!-- SDK Usage (Flat) -->
            <section class="p-5 rounded-xl bg-white dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-2 mb-3">
                    <Code2 class="w-4 h-4 text-indigo-600" />
                    Client SDK Usage
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- TypeScript SDK -->
                    <div>
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300 block mb-1.5">TypeScript SDK (@web-search/sdk)</span>
                        <pre class="p-3 rounded-lg bg-slate-900 text-slate-200 text-[11px] font-mono overflow-x-auto leading-relaxed"><code>import &#123; WebSearchClient &#125; from '@web-search/sdk';

const client = new WebSearchClient(&#123;
  baseUrl: 'http://localhost:8000'
&#125;);

const results = await client.search(&#123;
  q: 'svelte runes tutorial',
  category: 'tech'
&#125;);

console.log(results.results);</code></pre>
                    </div>

                    <!-- PHP SDK -->
                    <div>
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300 block mb-1.5">PHP SDK (websearch/sdk)</span>
                        <pre class="p-3 rounded-lg bg-slate-900 text-slate-200 text-[11px] font-mono overflow-x-auto leading-relaxed"><code>use WebSearch\Sdk\Client;

$client = new Client('http://localhost:8000');

$response = $client->search([
    'q' => 'laravel inertia svelte',
    'category' => 'code'
]);

print_r($response['results']);</code></pre>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <Footer />
</div>
