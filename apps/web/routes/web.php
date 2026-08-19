<?php

use App\Http\Controllers\ConsoleController;
use App\Http\Controllers\CrawlerController;
use App\Http\Controllers\FaviconController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

// Search Engine Web UI
Route::get('/', [SearchController::class, 'home'])->name('home');
Route::get('/search', [SearchController::class, 'search'])->name('search');
Route::get('/search/random', [SearchController::class, 'random'])->name('search.random');
Route::get('/random-word', [SearchController::class, 'randomWord'])->name('search.random_word');
Route::get('/favicon/{domain}', [FaviconController::class, 'show'])->name('favicon.show');
Route::get('/suggest', [SearchController::class, 'suggest'])->name('suggest');
Route::get('/submit', [SearchController::class, 'submitSite'])->name('submit');
Route::post('/submit', [SearchController::class, 'processSubmitSite'])->name('submit.process');
Route::get('/stats', [SearchController::class, 'stats'])->name('stats');
Route::get('/docs', [SearchController::class, 'docs'])->name('docs');

// Crawler Control Panel
Route::get('/crawler', [CrawlerController::class, 'dashboard'])->name('crawler.dashboard');
Route::post('/crawler/jobs', [CrawlerController::class, 'submitJob'])->name('crawler.jobs.submit');

// Web-Search Console (Google Search Console equivalent)
Route::prefix('console')->name('console.')->group(function () {
    Route::get('/', [ConsoleController::class, 'dashboard'])->name('dashboard');
    Route::get('/inspect', [ConsoleController::class, 'inspect'])->name('inspect');
    Route::post('/inspect/request-indexing', [ConsoleController::class, 'requestIndexing'])->name('inspect.request');
    Route::get('/performance', [ConsoleController::class, 'performance'])->name('performance');
    Route::get('/links', [ConsoleController::class, 'links'])->name('links');
    Route::get('/sitemaps', [ConsoleController::class, 'sitemaps'])->name('sitemaps');
    Route::post('/sitemaps', [ConsoleController::class, 'submitSitemap'])->name('sitemaps.submit');
    Route::post('/verify', [ConsoleController::class, 'verifyDomain'])->name('verify');
});
