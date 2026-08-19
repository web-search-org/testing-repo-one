<script>
    import { onMount } from 'svelte';
    import { Sun, Moon } from 'lucide-svelte';

    let isDark = $state(false);

    onMount(() => {
        isDark = document.documentElement.classList.contains('dark') ||
            (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches);
        updateTheme();
    });

    function toggleTheme() {
        isDark = !isDark;
        updateTheme();
    }

    function updateTheme() {
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
    aria-label="Toggle Dark Mode"
    class="p-2 rounded-xl text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
>
    {#if isDark}
        <Sun class="w-5 h-5" />
    {:else}
        <Moon class="w-5 h-5" />
    {/if}
</button>
