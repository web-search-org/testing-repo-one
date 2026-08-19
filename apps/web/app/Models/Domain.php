<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Domain extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'protocol',
        'favicon_url',
        'domain_rank',
        'total_pages',
        'crawl_status',
        'last_crawled_at',
    ];

    protected $casts = [
        'domain_rank' => 'float',
        'total_pages' => 'integer',
        'last_crawled_at' => 'datetime',
    ];

    public function webPages(): HasMany
    {
        return $this->hasMany(WebPage::class);
    }
}
