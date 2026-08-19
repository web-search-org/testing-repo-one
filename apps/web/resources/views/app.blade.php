<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
    <meta name="description" content="Web-Search.org - The Open Source, Privacy-First Search Engine" />
    <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
    <title inertia>Web-Search.org</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <script>
        (function() {
            try {
                const stored = localStorage.getItem('theme');
                if (stored === 'dark' || (!stored && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            } catch (e) {}
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @inertiaHead
</head>
<body class="h-full bg-white dark:bg-black text-zinc-900 dark:text-zinc-100 antialiased font-sans transition-colors duration-100 selection:bg-zinc-200 dark:selection:bg-zinc-800 selection:text-black dark:selection:text-white">
    @inertia
</body>
</html>
