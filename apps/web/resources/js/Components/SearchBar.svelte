<script>
    import { onMount } from 'svelte';
    import { Search, X, ArrowUpRight } from 'lucide-svelte';

    let { initialQuery = '', size = 'large' } = $props();

    let query = $state('');
    let suggestions = $state([]);
    let showSuggestions = $state(false);
    let selectedIndex = $state(-1);
    let inputEl = $state(null);
    let debounceTimer = null;

    $effect(() => {
        query = initialQuery || '';
    });

    onMount(() => {
        const handleKeyDown = (e) => {
            if (e.key === '/' && document.activeElement !== inputEl) {
                e.preventDefault();
                inputEl?.focus();
            }
        };
        window.addEventListener('keydown', handleKeyDown);
        return () => window.removeEventListener('keydown', handleKeyDown);
    });

    function handleInput() {
        clearTimeout(debounceTimer);
        if (query.trim().length < 2) {
            suggestions = [];
            showSuggestions = false;
            return;
        }

        debounceTimer = setTimeout(async () => {
            try {
                const res = await fetch(`/suggest?q=${encodeURIComponent(query)}`);
                const data = await res.json();
                suggestions = data.suggestions || [];
                showSuggestions = suggestions.length > 0;
                selectedIndex = -1;
            } catch (err) {
                // Ignore suggestion fetch error
            }
        }, 150);
    }

    function handleKeydown(e) {
        if (!showSuggestions || suggestions.length === 0) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            selectedIndex = (selectedIndex + 1) % suggestions.length;
            query = suggestions[selectedIndex];
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            selectedIndex = (selectedIndex - 1 + suggestions.length) % suggestions.length;
            query = suggestions[selectedIndex];
        } else if (e.key === 'Escape') {
            showSuggestions = false;
        }
    }

    function selectSuggestion(s) {
        query = s;
        showSuggestions = false;
        document.getElementById('search-form')?.submit();
    }

    function clearQuery() {
        query = '';
        suggestions = [];
        showSuggestions = false;
        inputEl?.focus();
    }
</script>

<div class="relative w-full">
    <form id="search-form" action="/search" method="GET" class="relative flex items-center">
        <div class="absolute left-4 pointer-events-none text-slate-400">
            <Search class={size === 'large' ? 'w-5 h-5' : 'w-4 h-4'} />
        </div>

        <input
            bind:this={inputEl}
            type="text"
            name="q"
            bind:value={query}
            oninput={handleInput}
            onkeydown={handleKeydown}
            onfocus={() => query.length >= 2 && suggestions.length > 0 && (showSuggestions = true)}
            onblur={() => setTimeout(() => showSuggestions = false, 200)}
            placeholder="Search the open web or ask anything..."
            autocomplete="off"
            class="w-full rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-600 transition-colors placeholder:text-slate-400 {size === 'large' ? 'py-3.5 pl-12 pr-24 text-base' : 'py-2 pl-10 pr-16 text-xs'}"
        />

        <div class="absolute right-2.5 flex items-center gap-1">
            {#if query}
                <button
                    type="button"
                    onclick={clearQuery}
                    class="p-1 rounded text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                    aria-label="Clear Search"
                >
                    <X class="w-4 h-4" />
                </button>
            {/if}
            <button
                type="submit"
                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs transition-colors cursor-pointer"
            >
                Search
            </button>
        </div>
    </form>

    {#if showSuggestions && suggestions.length > 0}
        <div class="absolute top-full left-0 right-0 mt-1.5 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-lg overflow-hidden z-50 py-1">
            {#each suggestions as s, idx}
                <button
                    type="button"
                    onmousedown={() => selectSuggestion(s)}
                    class="w-full text-left px-3.5 py-2 flex items-center justify-between text-xs hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-800 dark:text-slate-200 transition-colors {selectedIndex === idx ? 'bg-slate-100 dark:bg-slate-800 font-semibold text-indigo-600 dark:text-indigo-400' : ''}"
                >
                    <div class="flex items-center gap-2.5">
                        <Search class="w-3.5 h-3.5 text-slate-400" />
                        <span>{s}</span>
                    </div>
                    <ArrowUpRight class="w-3 h-3 text-slate-400" />
                </button>
            {/each}
        </div>
    {/if}
</div>
