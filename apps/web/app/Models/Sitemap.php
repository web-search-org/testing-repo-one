<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sitemap extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'domain_id',
        'url',
        'status',
        'total_urls',
        'indexed_urls',
        'error_message',
        'last_crawled_at',
    ];

    protected $casts = [
        'total_urls' => 'integer',
        'indexed_urls' => 'integer',
        'last_crawled_at' => 'datetime',
    ];

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }
}
