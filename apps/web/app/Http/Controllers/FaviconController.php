<?php

namespace App\Http\Controllers;

use App\Services\FaviconService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FaviconController extends Controller
{
    public function __construct(
        protected FaviconService $faviconService
    ) {}

    /**
     * Serve favicon directly from local database: GET /favicon/{domain}
     */
    public function show(string $domain): Response
    {
        $domain = strtolower(trim($domain));
        $favicon = $this->faviconService->getOrFetchFavicon($domain);

        $binary = base64_decode($favicon->data_base64);
        $mime = $favicon->mime_type ?: 'image/x-icon';

        return response($binary, 200, [
            'Content-Type' => $mime,
            'Content-Length' => strlen($binary),
            'Cache-Control' => 'public, max-age=604800, immutable',
            'X-Favicon-Source' => 'database',
        ]);
    }
}
