<?php

namespace App\Models;

use App\Models\Concerns\HasPageSeo;
use Illuminate\Database\Eloquent\Model;

class BlogPost extends Model
{
    use HasPageSeo;

    protected $fillable = ['title', 'slug', 'excerpt', 'content', 'sections', 'image', 'is_published', 'published_at'];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'sections' => 'array',
            'published_at' => 'datetime',
        ];
    }

    public function seoPathForSlug(string $slug): string
    {
        return '/blog/'.$slug;
    }
}
