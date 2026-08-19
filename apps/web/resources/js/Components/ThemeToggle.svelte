<script>
    import { onMount } from 'svelte';
    import { Sun, Moon } from 'lucide-svelte';

    let isDark = $state(false);

    onMount(() => {
        const storedTheme = localStorage.getItem('theme');
        if (storedTheme === 'dark') {
            isDark = true;
        } else if (storedTheme === 'light') {
            isDark = false;
        } else {
            isDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        }
        applyTheme();
    });

    function toggleTheme() {
        isDark = !isDark;
        applyTheme();
    }

    function applyTheme() {
        if (isDark) {
            document.documentElement.classList.add('dark');
            localStorage.setItem('theme', 'dark');
        } else {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('theme', 'light');
        }
    }
</script>

<button
    onclick={toggleTheme}
    aria-label="Toggle Light / Dark Mode"
    title={isDark ? "Switch to Light Mode" : "Switch to Dark Mode"}
    class="p-2 rounded-lg text-zinc-600 hover:text-black dark:text-zinc-400 dark:hover:text-white hover:bg-zinc-100 dark:hover:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 transition-colors cursor-pointer"
>
    {#if isDark}
        <Sun class="w-4 h-4" />
    {:else}
        <Moon class="w-4 h-4" />
    {/if}
</button>
