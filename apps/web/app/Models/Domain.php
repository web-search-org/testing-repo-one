<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Domain extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'protocol',
        'favicon_url',
        'domain_rank',
        'total_pages',
        'crawl_status',
        'verification_token',
        'is_verified',
        'verification_method',
        'verified_at',
        'last_crawled_at',
    ];

    protected $casts = [
        'domain_rank' => 'float',
        'total_pages' => 'integer',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
        'last_crawled_at' => 'datetime',
    ];

    public function webPages(): HasMany
    {
        return $this->hasMany(WebPage::class);
    }

    public function sitemaps(): HasMany
    {
        return $this->hasMany(Sitemap::class);
    }

    public function performances(): HasMany
    {
        return $this->hasMany(DomainPerformance::class);
    }
}
