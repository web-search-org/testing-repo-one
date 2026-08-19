<?php

namespace App\Services;

use App\Models\Sitemap;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class RobotsTxtService
{
    protected array $cache = [];
    protected string $userAgent = 'WebSearchBot';

    /**
     * Determine if a URL can be fetched according to robots.txt rules.
     */
    public function canFetch(string $url, string $userAgent = 'WebSearchBot'): bool
    {
        $parsed = parse_url($url);
        if (empty($parsed['scheme']) || empty($parsed['host'])) {
            return false;
        }

        $scheme = strtolower($parsed['scheme']);
        $host = strtolower($parsed['host']);
        $path = $parsed['path'] ?? '/';
        if ($path === '') $path = '/';
        if (isset($parsed['query'])) {
            $path .= '?' . $parsed['query'];
        }

        $rules = $this->getRulesForDomain($scheme, $host);

        // Check user-agent specific rules first, fallback to wildcard '*'
        $agentRules = $rules['agents'][strtolower($userAgent)] ?? $rules['agents']['*'] ?? null;

        if (!$agentRules) {
            return true; // No restrictive rules found
        }

        // Check explicitly allowed paths first
        foreach ($agentRules['allow'] as $allowedPath) {
            if ($this->pathMatches($path, $allowedPath)) {
                return true;
            }
        }

        // Check disallowed paths
        foreach ($agentRules['disallow'] as $disallowedPath) {
            if ($this->pathMatches($path, $disallowedPath)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get crawl delay specified in robots.txt (in seconds).
     */
    public function getCrawlDelay(string $domain, string $userAgent = 'WebSearchBot'): float
    {
        $domain = strtolower($domain);
        $rules = $this->cache[$domain] ?? null;

        if (!$rules) {
            return 0.0;
        }

        $agentRules = $rules['agents'][strtolower($userAgent)] ?? $rules['agents']['*'] ?? null;
        return (float) ($agentRules['crawl_delay'] ?? 0.0);
    }

    /**
     * Extract XML sitemap URLs defined in robots.txt.
     */
    public function getDiscoveredSitemaps(string $domain): array
    {
        $domain = strtolower($domain);
        return $this->cache[$domain]['sitemaps'] ?? [];
    }

    /**
     * Fetch, parse, and cache robots.txt for a domain.
     */
    public function getRulesForDomain(string $scheme, string $host): array
    {
        $cacheKey = "{$scheme}://{$host}";
        if (isset($this->cache[$host])) {
            return $this->cache[$host];
        }

        $robotsUrl = "{$scheme}://{$host}/robots.txt";
        $rules = [
            'agents' => [],
            'sitemaps' => [],
        ];

        try {
            $response = Http::timeout(4)
                ->connectTimeout(2)
                ->withHeaders([
                    'User-Agent' => 'WebSearchBot/1.0 (+https://web-search.org/bot.html)',
                ])
                ->get($robotsUrl);

            if ($response->successful()) {
                $content = $response->body();
                $rules = $this->parseRobotsTxt($content);
            }
        } catch (\Exception) {
            // If unreachable or 404, assume permissive
        }

        $this->cache[$host] = $rules;
        return $rules;
    }

    /**
     * Parse robots.txt file content.
     */
    public function parseRobotsTxt(string $content): array
    {
        $lines = explode("\n", $content);
        $rules = [
            'agents' => [],
            'sitemaps' => [],
        ];

        $currentAgents = [];

        foreach ($lines as $line) {
            $line = trim(preg_replace('/#.*$/', '', $line)); // Strip comments
            if (empty($line)) continue;

            $parts = explode(':', $line, 2);
            if (count($parts) < 2) continue;

            $directive = strtolower(trim($parts[0]));
            $value = trim($parts[1]);

            if ($directive === 'user-agent') {
                $agent = strtolower($value);
                // If consecutive user-agent lines appear, group them
                $currentAgents[] = $agent;
                if (!isset($rules['agents'][$agent])) {
                    $rules['agents'][$agent] = [
                        'allow' => [],
                        'disallow' => [],
                        'crawl_delay' => 0.0,
                    ];
                }
            } elseif ($directive === 'disallow') {
                if (empty($currentAgents)) {
                    $currentAgents = ['*'];
                }
                foreach ($currentAgents as $agent) {
                    if (!empty($value)) {
                        $rules['agents'][$agent]['disallow'][] = $value;
                    }
                }
            } elseif ($directive === 'allow') {
                if (empty($currentAgents)) {
                    $currentAgents = ['*'];
                }
                foreach ($currentAgents as $agent) {
                    if (!empty($value)) {
                        $rules['agents'][$agent]['allow'][] = $value;
                    }
                }
            } elseif ($directive === 'crawl-delay') {
                foreach ($currentAgents as $agent) {
                    $rules['agents'][$agent]['crawl_delay'] = (float) $value;
                }
            } elseif ($directive === 'sitemap') {
                if (filter_var($value, FILTER_VALIDATE_URL)) {
                    $rules['sitemaps'][] = $value;
                }
            }
        }

        return $rules;
    }

    /**
     * Check if a path matches a robots.txt pattern (supporting wildcard * and end marker $).
     */
    protected function pathMatches(string $path, string $pattern): bool
    {
        if ($pattern === '' || $pattern === '/') {
            return $pattern === '/' ? true : false;
        }

        // Convert robots.txt pattern to regex
        $regex = preg_quote($pattern, '/');
        $regex = str_replace('\*', '.*', $regex);
        $regex = str_replace('\$', '$', $regex);

        return (bool) preg_match('/^' . $regex . '/i', $path);
    }
}
