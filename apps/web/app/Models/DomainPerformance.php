<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DomainPerformance extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'domain_id',
        'query',
        'page_url',
        'clicks',
        'impressions',
        'ctr',
        'avg_position',
        'recorded_date',
    ];

    protected $casts = [
        'clicks' => 'integer',
        'impressions' => 'integer',
        'ctr' => 'float',
        'avg_position' => 'float',
        'recorded_date' => 'date',
    ];

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }
}
