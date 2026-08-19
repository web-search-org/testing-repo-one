<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebLink extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'id',
        'source_page_id',
        'source_url',
        'source_domain',
        'target_page_id',
        'target_url',
        'target_domain',
        'anchor_text',
        'is_external',
        'rel',
    ];

    protected $casts = [
        'is_external' => 'boolean',
    ];

    public function sourcePage(): BelongsTo
    {
        return $this->belongsTo(WebPage::class, 'source_page_id');
    }

    public function targetPage(): BelongsTo
    {
        return $this->belongsTo(WebPage::class, 'target_page_id');
    }
}
