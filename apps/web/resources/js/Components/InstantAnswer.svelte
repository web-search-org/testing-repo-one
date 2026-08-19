<script>
    import { Sparkles, ArrowRight, ExternalLink } from 'lucide-svelte';

    let { instantAnswer } = $props();
</script>

{#if instantAnswer}
    <div class="mb-6 p-4 rounded-xl bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 text-zinc-900 dark:text-zinc-100">
        <div class="flex items-center justify-between gap-2 mb-2 pb-2 border-b border-zinc-200 dark:border-zinc-800">
            <div class="flex items-center gap-1.5">
                <span class="p-1 rounded bg-black text-white dark:bg-white dark:text-black">
                    <Sparkles class="w-3.5 h-3.5" />
                </span>
                <span class="text-xs font-bold text-black dark:text-white uppercase tracking-wider">
                    {instantAnswer.title}
                </span>
            </div>

            {#if instantAnswer.type}
                <span class="px-2 py-0.5 rounded text-[10px] font-mono uppercase bg-zinc-200 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-300">
                    {instantAnswer.type}
                </span>
            {/if}
        </div>

        <div class="text-sm font-mono leading-relaxed text-black dark:text-white whitespace-pre-wrap">
            {instantAnswer.content}
        </div>

        {#if instantAnswer.sourceUrl}
            <div class="mt-3 pt-2 border-t border-zinc-200 dark:border-zinc-800 flex items-center justify-between text-xs text-zinc-500">
                <span>Source: {instantAnswer.sourceName || instantAnswer.sourceUrl}</span>
                <a
                    href={instantAnswer.sourceUrl}
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center gap-1 text-black dark:text-white hover:underline font-medium"
                >
                    <span>View Reference</span>
                    <ExternalLink class="w-3 h-3" />
                </a>
            </div>
        {/if}
    </div>
{/if}
