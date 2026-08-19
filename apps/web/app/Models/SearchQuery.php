<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SearchQuery extends Model
{
    use HasFactory;

    protected $fillable = [
        'query',
        'category',
        'results_count',
        'execution_time_ms',
        'ip_hash',
    ];

    protected $casts = [
        'results_count' => 'integer',
        'execution_time_ms' => 'float',
    ];
}
