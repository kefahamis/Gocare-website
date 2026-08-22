<?php

namespace App\Models;

use App\Models\Concerns\HasPageSeo;
use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    use HasPageSeo;

    protected $fillable = ['title', 'slug', 'content', 'sections', 'image', 'source_path', 'is_published'];

    protected function casts(): array
    {
        return ['is_published' => 'boolean', 'sections' => 'array'];
    }

    public function seoPathForSlug(string $slug): string
    {
        return '/schools/'.$slug;
    }
}
