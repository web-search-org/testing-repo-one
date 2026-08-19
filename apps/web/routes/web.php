<?php

use App\Http\Controllers\CrawlerController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

// Search Engine Web UI
Route::get('/', [SearchController::class, 'home'])->name('home');
Route::get('/search', [SearchController::class, 'search'])->name('search');
Route::get('/suggest', [SearchController::class, 'suggest'])->name('suggest');
Route::get('/stats', [SearchController::class, 'stats'])->name('stats');
Route::get('/docs', [SearchController::class, 'docs'])->name('docs');

// Crawler Control Panel
Route::get('/crawler', [CrawlerController::class, 'dashboard'])->name('crawler.dashboard');
Route::post('/crawler/jobs', [CrawlerController::class, 'submitJob'])->name('crawler.jobs.submit');
