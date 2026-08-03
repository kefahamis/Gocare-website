<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlogPost extends Model
{
    protected $fillable = ['title', 'slug', 'excerpt', 'content', 'sections', 'image', 'use_builder', 'is_published', 'published_at'];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'use_builder' => 'boolean',
            'sections' => 'array',
            'published_at' => 'datetime',
        ];
    }
}
