<?php

namespace App\Models;

use App\Models\Concerns\HasPageSeo;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasPageSeo;

    protected $fillable = ['title', 'slug', 'school', 'summary', 'content', 'sections', 'image', 'is_published'];

    protected function casts(): array
    {
        return ['is_published' => 'boolean', 'sections' => 'array'];
    }

    public function seoPathForSlug(string $slug): string
    {
        return '/courses/'.$slug;
    }
}
