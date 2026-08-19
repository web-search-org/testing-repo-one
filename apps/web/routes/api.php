<?php

use App\Http\Controllers\Api\V1\CrawlApiController;
use App\Http\Controllers\Api\V1\SearchApiController;
use App\Http\Controllers\Api\V1\StatsApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Search API
    Route::get('/search', [SearchApiController::class, 'search'])->name('api.v1.search');
    Route::get('/suggest', [SearchApiController::class, 'suggest'])->name('api.v1.suggest');

    // Crawler API
    Route::post('/crawl', [CrawlApiController::class, 'submit'])->name('api.v1.crawl.submit');
    Route::get('/crawl/status/{id}', [CrawlApiController::class, 'status'])->name('api.v1.crawl.status');
    Route::post('/crawl/ingest', [CrawlApiController::class, 'ingest'])->name('api.v1.crawl.ingest');

    // Stats & System Health
    Route::get('/stats', [StatsApiController::class, 'stats'])->name('api.v1.stats');
});
