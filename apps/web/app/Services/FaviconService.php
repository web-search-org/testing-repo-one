<?php

namespace App\Services;

use App\Models\Favicon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class FaviconService
{
    /**
     * Get or download and store favicon for a domain.
     */
    public function getOrFetchFavicon(string $domain, ?string $originalUrl = null): Favicon
    {
        $cleanDomain = strtolower(trim($domain));
        $cached = Favicon::where('domain', $cleanDomain)->first();

        if ($cached && !empty($cached->data_base64)) {
            return $cached;
        }

        return $this->downloadAndStore($cleanDomain, $originalUrl);
    }

    /**
     * Download and store favicon from remote source into local database.
     */
    public function downloadAndStore(string $domain, ?string $originalUrl = null): Favicon
    {
        $cleanDomain = strtolower(trim($domain));
        $urlsToTry = [];

        if (!empty($originalUrl) && filter_var($originalUrl, FILTER_VALIDATE_URL)) {
            $urlsToTry[] = $originalUrl;
        }

        $urlsToTry[] = "https://{$cleanDomain}/favicon.ico";
        $urlsToTry[] = "https://{$cleanDomain}/favicon.png";
        $urlsToTry[] = "https://{$cleanDomain}/apple-touch-icon.png";
        $urlsToTry[] = "http://{$cleanDomain}/favicon.ico";

        $downloadedData = null;
        $mimeType = 'image/x-icon';
        $successfulUrl = null;

        foreach ($urlsToTry as $url) {
            try {
                $res = Http::timeout(3)
                    ->connectTimeout(2)
                    ->withHeaders([
                        'User-Agent' => 'WebSearchBot/1.0 (+https://web-search.org/bot.html)',
                        'Accept' => 'image/*,*/*;q=0.8',
                    ])
                    ->get($url);

                if ($res->successful() && strlen($res->body()) > 20) {
                    $body = $res->body();
                    $headerType = $res->header('Content-Type');

                    // Basic verification that it looks like an image or binary icon
                    if (str_starts_with($headerType, 'text/html') && !str_contains($body, '<svg')) {
                        continue; // HTML error page returned as 200
                    }

                    $downloadedData = $body;
                    $mimeType = $headerType ?: $this->guessMimeType($url);
                    $successfulUrl = $url;
                    break;
                }
            } catch (\Exception) {
                // Try next URL candidate
            }
        }

        if (!$downloadedData) {
            // Generate clean SVG initial letter fallback
            $downloadedData = $this->generateFallbackSvg($cleanDomain);
            $mimeType = 'image/svg+xml';
            $successfulUrl = "local:fallback:{$cleanDomain}";
        }

        $base64 = base64_encode($downloadedData);

        return Favicon::updateOrCreate(
            ['domain' => $cleanDomain],
            [
                'mime_type' => $mimeType,
                'data_base64' => $base64,
                'original_url' => $successfulUrl,
                'size_bytes' => strlen($downloadedData),
            ]
        );
    }

    /**
     * Generate a minimalist SVG favicon fallback with the domain's initial letter.
     */
    protected function generateFallbackSvg(string $domain): string
    {
        $initial = strtoupper(substr($domain, 0, 1)) ?: 'W';

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" width="32" height="32">
  <rect width="32" height="32" rx="8" fill="#18181b"/>
  <text x="16" y="22" font-family="-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif" font-size="16" font-weight="700" fill="#ffffff" text-anchor="middle">{$initial}</text>
</svg>
SVG;
    }

    protected function guessMimeType(string $url): string
    {
        $path = strtolower(parse_url($url, PHP_URL_PATH) ?? '');
        if (str_ends_with($path, '.png')) return 'image/png';
        if (str_ends_with($path, '.svg')) return 'image/svg+xml';
        if (str_ends_with($path, '.webp')) return 'image/webp';
        if (str_ends_with($path, '.jpg') || str_ends_with($path, '.jpeg')) return 'image/jpeg';
        return 'image/x-icon';
    }
}
