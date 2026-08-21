<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlogPost extends Model
{
    protected $fillable = ['title', 'slug', 'excerpt', 'content', 'sections', 'image', 'is_published', 'published_at'];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'sections' => 'array',
            'published_at' => 'datetime',
        ];
    }
}
