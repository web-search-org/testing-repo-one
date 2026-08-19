<script>
    import { Link } from '@inertiajs/svelte';
    import { Github, Shield, Terminal, BookOpen, PlusCircle } from 'lucide-svelte';
</script>

<footer class="border-t border-zinc-200 dark:border-zinc-800 bg-white dark:bg-black text-zinc-600 dark:text-zinc-400 text-xs py-8 transition-colors">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row items-center justify-between gap-4">
            <!-- Brand info -->
            <div class="flex items-center gap-3">
                <span class="font-bold text-black dark:text-white">Web-Search.org</span>
                <span class="text-zinc-300 dark:text-zinc-700">|</span>
                <span class="text-zinc-500">Decentralized & Open Source</span>
            </div>

            <!-- Links -->
            <div class="flex flex-wrap items-center justify-center gap-6">
                <Link href="/submit" class="hover:text-black dark:hover:text-white transition-colors">
                    Submit Site
                </Link>
                <Link href="/stats" class="hover:text-black dark:hover:text-white transition-colors">
                    Live Stats
                </Link>
                <Link href="/console" class="hover:text-black dark:hover:text-white transition-colors">
                    Search Console
                </Link>
                <Link href="/crawler" class="hover:text-black dark:hover:text-white transition-colors">
                    Crawler
                </Link>
                <Link href="/docs" class="hover:text-black dark:hover:text-white transition-colors">
                    API Docs
                </Link>
                <a
                    href="https://github.com/web-search-org"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="hover:text-black dark:hover:text-white transition-colors flex items-center gap-1"
                >
                    <Github class="w-3.5 h-3.5" />
                    <span>GitHub</span>
                </a>
            </div>

            <!-- Privacy Badge -->
            <div class="text-[11px] text-zinc-500">
                100% Private • Zero Tracking
            </div>
        </div>
    </div>
</footer>
