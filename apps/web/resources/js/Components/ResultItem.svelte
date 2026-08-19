<script>
    import { ExternalLink, Shield, Code2, Globe } from 'lucide-svelte';

    let { result } = $props();
</script>

<article class="group mb-7 p-4 rounded-2xl hover:bg-white dark:hover:bg-slate-900/60 border border-transparent hover:border-slate-200/80 dark:hover:border-slate-800/80 transition-all">
    <!-- URL Breadcrumb + Favicon -->
    <div class="flex items-center gap-2 mb-1.5 text-xs text-slate-500 dark:text-slate-400">
        <img
            src={result.favicon || `https://www.google.com/s2/favicons?domain=${result.domain}&sz=32`}
            alt=""
            class="w-4 h-4 rounded-full bg-slate-100 dark:bg-slate-800 object-cover"
            onerror={(e) => { e.currentTarget.style.display = 'none'; }}
        />
        <span class="font-medium text-slate-700 dark:text-slate-300">{result.domain}</span>
        <span class="text-slate-300 dark:text-slate-700">›</span>
        <span class="truncate max-w-md font-mono text-[11px] text-slate-400 dark:text-slate-500">{result.url}</span>
    </div>

    <!-- Title Link -->
    <h2 class="text-lg font-semibold tracking-tight text-indigo-700 dark:text-indigo-400 group-hover:text-indigo-600 dark:group-hover:text-indigo-300 group-hover:underline">
        <a href={result.url} target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5">
            <span>{result.title}</span>
            <ExternalLink class="w-3.5 h-3.5 opacity-0 group-hover:opacity-100 transition-opacity text-slate-400" />
        </a>
    </h2>

    <!-- Snippet Text -->
    <p class="mt-1.5 text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
        {#if result.highlightedSnippet}
            <!-- eslint-disable-next-line svelte/no-at-html-tags -->
            {@html result.highlightedSnippet}
        {:else}
            {result.snippet}
        {/if}
    </p>

    <!-- Meta badges (Category, Rank, Cached) -->
    <div class="mt-2.5 flex items-center gap-2 text-[11px] text-slate-400 dark:text-slate-500">
        {#if result.category && result.category !== 'all'}
            <span class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-medium">
                #{result.category}
            </span>
        {/if}

        {#if result.rankScore}
            <span class="font-mono">
                PR: {result.rankScore}
            </span>
        {/if}

        {#if result.indexedAt}
            <span>•</span>
            <span>Indexed: {new Date(result.indexedAt).toLocaleDateString()}</span>
        {/if}
    </div>
</article>
