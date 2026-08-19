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
        <div class="absolute left-4 pointer-events-none text-zinc-400 dark:text-zinc-500">
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
            placeholder="Search the open web..."
            autocomplete="off"
            class="w-full rounded-xl bg-white dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-black dark:focus:ring-white focus:border-black dark:focus:border-white transition-colors placeholder:text-zinc-400 dark:placeholder:text-zinc-600 {size === 'large' ? 'py-3.5 pl-12 pr-24 text-base shadow-xs' : 'py-2 pl-10 pr-16 text-xs'}"
        />

        <div class="absolute right-2 flex items-center gap-1">
            {#if query}
                <button
                    type="button"
                    onclick={clearQuery}
                    class="p-1 rounded text-zinc-400 hover:text-black dark:hover:text-white hover:bg-zinc-100 dark:hover:bg-zinc-900 transition-colors cursor-pointer"
                    aria-label="Clear Search"
                >
                    <X class="w-4 h-4" />
                </button>
            {/if}
            <button
                type="submit"
                class="inline-flex items-center px-3 py-1.5 rounded-lg bg-black text-white dark:bg-white dark:text-black font-semibold text-xs hover:bg-zinc-800 dark:hover:bg-zinc-200 transition-colors cursor-pointer"
            >
                Search
            </button>
        </div>
    </form>

    {#if showSuggestions && suggestions.length > 0}
        <div class="absolute top-full left-0 right-0 mt-1.5 bg-white dark:bg-zinc-950 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-lg overflow-hidden z-50 py-1">
            {#each suggestions as s, idx}
                <button
                    type="button"
                    onmousedown={() => selectSuggestion(s)}
                    class="w-full text-left px-3.5 py-2 flex items-center justify-between text-xs hover:bg-zinc-100 dark:hover:bg-zinc-900 text-zinc-800 dark:text-zinc-200 transition-colors {selectedIndex === idx ? 'bg-zinc-100 dark:bg-zinc-900 font-semibold text-black dark:text-white' : ''}"
                >
                    <div class="flex items-center gap-2.5">
                        <Search class="w-3.5 h-3.5 text-zinc-400" />
                        <span>{s}</span>
                    </div>
                    <ArrowUpRight class="w-3 h-3 text-zinc-400" />
                </button>
            {/each}
        </div>
    {/if}
</div>
