<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'domain_id',
        'url',
        'domain',
        'title',
        'description',
        'keywords',
        'headings',
        'body_text',
        'category',
        'language',
        'content_hash',
        'canonical_url',
        'favicon_url',
        'og_image',
        'page_rank',
        'http_status',
        'response_time_ms',
        'in_links_count',
        'out_links_count',
        'is_indexed',
        'crawled_at',
    ];

    protected $casts = [
        'keywords' => 'array',
        'headings' => 'array',
        'page_rank' => 'float',
        'response_time_ms' => 'float',
        'http_status' => 'integer',
        'in_links_count' => 'integer',
        'out_links_count' => 'integer',
        'is_indexed' => 'boolean',
        'crawled_at' => 'datetime',
    ];

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }
}
