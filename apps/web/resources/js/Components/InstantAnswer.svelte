<script>
    import { Calculator, Sparkles, Terminal, Info } from 'lucide-svelte';

    let { instantAnswer } = $props();
</script>

{#if instantAnswer}
    <div class="mb-6 p-4 rounded-xl bg-indigo-50/60 dark:bg-indigo-950/30 border border-indigo-200 dark:border-indigo-800/80">
        <div class="flex items-center justify-between mb-2">
            <div class="flex items-center gap-2">
                <div class="p-1 rounded-md bg-indigo-600 text-white">
                    {#if instantAnswer.type === 'calculator'}
                        <Calculator class="w-3.5 h-3.5" />
                    {:else}
                        <Sparkles class="w-3.5 h-3.5" />
                    {/if}
                </div>
                <span class="text-xs font-bold uppercase tracking-wider text-indigo-700 dark:text-indigo-300">
                    {instantAnswer.title}
                </span>
            </div>
            {#if instantAnswer.subtitle}
                <span class="text-xs font-mono text-slate-500 dark:text-slate-400">
                    {instantAnswer.subtitle}
                </span>
            {/if}
        </div>

        <div class="mt-1">
            <div class="text-xl font-bold font-mono text-slate-900 dark:text-white tracking-tight">
                {instantAnswer.content}
            </div>
        </div>
    </div>
{/if}
