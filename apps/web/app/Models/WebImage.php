<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebImage extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'id',
        'web_page_id',
        'domain_id',
        'domain',
        'page_url',
        'image_url',
        'local_path',
        'thumbnail_path',
        'alt_text',
        'title',
        'width',
        'height',
        'mime_type',
        'size_bytes',
        'aspect_ratio',
    ];

    public function webPage(): BelongsTo
    {
        return $this->belongsTo(WebPage::class);
    }

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }
}
