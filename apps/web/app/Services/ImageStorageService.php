<?php

namespace App\Services;

use App\Models\WebImage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageStorageService
{
    protected string $disk = 'public';

    /**
     * Download, downscale, and store a web image locally (ready for S3 in the future).
     */
    public function downloadAndProcessImage(
        string $imageUrl,
        string $pageUrl,
        ?string $pageId,
        ?string $domainId,
        string $domain,
        ?string $altText = null,
        ?string $title = null
    ): ?WebImage {
        $imageUrl = trim($imageUrl);
        if (!filter_var($imageUrl, FILTER_VALIDATE_URL)) {
            return null;
        }

        // Check if image already cached
        $existing = WebImage::where('image_url', $imageUrl)->first();
        if ($existing) {
            return $existing;
        }

        try {
            $response = Http::timeout(5)
                ->connectTimeout(3)
                ->withHeaders([
                    'User-Agent' => 'WebSearchBot/1.0 (+https://web-search.org/bot.html)',
                    'Accept' => 'image/webp,image/png,image/jpeg,image/*;q=0.8',
                ])
                ->get($imageUrl);

            if (!$response->successful() || strlen($response->body()) < 500) {
                return null;
            }

            $rawBytes = $response->body();
            $imageInfo = @getimagesizefromstring($rawBytes);
            if (!$imageInfo) {
                return null;
            }

            $origWidth = $imageInfo[0];
            $origHeight = $imageInfo[1];
            $mimeType = $imageInfo['mime'] ?? 'image/jpeg';

            // Filter out tiny icons, tracker pixels, and spacers
            if ($origWidth < 60 || $origHeight < 60) {
                return null;
            }

            // Downscale image
            $processed = $this->downscaleImage($rawBytes, $origWidth, $origHeight, 800, 600);
            $thumbnail = $this->downscaleImage($rawBytes, $origWidth, $origHeight, 360, 260);

            if (!$processed) {
                return null;
            }

            $hash = md5($imageUrl);
            $ext = 'webp';
            $imagePath = "images/{$hash}.{$ext}";
            $thumbPath = "thumbnails/{$hash}.{$ext}";

            Storage::disk($this->disk)->put($imagePath, $processed['data']);
            Storage::disk($this->disk)->put($thumbPath, $thumbnail ? $thumbnail['data'] : $processed['data']);

            $aspectRatio = $origHeight > 0 ? round($origWidth / $origHeight, 2) : 1.0;

            return WebImage::create([
                'id' => (string) Str::uuid(),
                'web_page_id' => $pageId,
                'domain_id' => $domainId,
                'domain' => strtolower($domain),
                'page_url' => $pageUrl,
                'image_url' => $imageUrl,
                'local_path' => Storage::url($imagePath),
                'thumbnail_path' => Storage::url($thumbPath),
                'alt_text' => $altText ? substr(trim($altText), 0, 500) : null,
                'title' => $title ? substr(trim($title), 0, 255) : null,
                'width' => $processed['width'],
                'height' => $processed['height'],
                'mime_type' => 'image/webp',
                'size_bytes' => strlen($processed['data']),
                'aspect_ratio' => $aspectRatio,
            ]);
        } catch (\Exception $e) {
            // Log or ignore image download failure
            return null;
        }
    }

    /**
     * Downscale an image keeping aspect ratio and encode to WebP (or JPEG fallback).
     */
    protected function downscaleImage(string $rawBytes, int $origWidth, int $origHeight, int $maxWidth, int $maxHeight): ?array
    {
        $src = @imagecreatefromstring($rawBytes);
        if (!$src) {
            return null;
        }

        // Calculate new constrained dimensions
        $ratio = min($maxWidth / max(1, $origWidth), $maxHeight / max(1, $origHeight), 1.0);
        $newWidth = (int) round($origWidth * $ratio);
        $newHeight = (int) round($origHeight * $ratio);

        $dst = imagecreatetruecolor($newWidth, $newHeight);

        // Preserve alpha transparency for PNG/WebP
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        $transparent = imagecolorallocatealpha($dst, 255, 255, 255, 127);
        imagefilledrectangle($dst, 0, 0, $newWidth, $newHeight, $transparent);

        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);

        ob_start();
        if (function_exists('imagewebp')) {
            imagewebp($dst, null, 80);
        } else {
            imagejpeg($dst, null, 85);
        }
        $data = ob_get_clean();

        imagedestroy($src);
        imagedestroy($dst);

        return [
            'data' => $data,
            'width' => $newWidth,
            'height' => $newHeight,
        ];
    }
}
