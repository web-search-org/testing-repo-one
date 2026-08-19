<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrawlJob extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'id',
        'seed_url',
        'status',
        'max_depth',
        'max_pages',
        'concurrency',
        'pages_crawled',
        'pages_discovered',
        'pages_indexed',
        'errors_count',
        'error_message',
        'metadata',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'max_depth' => 'integer',
        'max_pages' => 'integer',
        'concurrency' => 'integer',
        'pages_crawled' => 'integer',
        'pages_discovered' => 'integer',
        'pages_indexed' => 'integer',
        'errors_count' => 'integer',
        'metadata' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];
}
