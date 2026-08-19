<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Favicon extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'id',
        'domain',
        'mime_type',
        'data_base64',
        'original_url',
        'size_bytes',
    ];
}
