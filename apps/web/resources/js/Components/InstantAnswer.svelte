<script>
    import { Calculator, Sparkles, Terminal, Info } from 'lucide-svelte';

    let { instantAnswer } = $props();
</script>

{#if instantAnswer}
    <div class="mb-6 p-4.5 rounded-2xl bg-gradient-to-r from-indigo-50/80 via-purple-50/40 to-slate-50 dark:from-indigo-950/40 dark:via-purple-950/20 dark:to-slate-900/40 border border-indigo-200/80 dark:border-indigo-800/60 shadow-sm">
        <div class="flex items-center justify-between mb-2">
            <div class="flex items-center gap-2">
                <div class="p-1 rounded-lg bg-indigo-600 text-white">
                    {#if instantAnswer.type === 'calculator'}
                        <Calculator class="w-3.5 h-3.5" />
                    {:else}
                        <Sparkles class="w-3.5 h-3.5" />
                    {/if}
                </div>
                <span class="text-xs font-semibold uppercase tracking-wider text-indigo-700 dark:text-indigo-300">
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
            <div class="text-2xl font-bold font-mono text-slate-900 dark:text-white tracking-tight">
                {instantAnswer.content}
            </div>
        </div>
    </div>
{/if}
