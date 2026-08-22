<?php

namespace App\Models;

use App\Models\Concerns\HasPageSeo;
use Illuminate\Database\Eloquent\Model;

class SitePage extends Model
{
    use HasPageSeo;

    protected $fillable = ['title', 'slug', 'content', 'sections', 'image', 'source_path', 'is_published'];

    protected function casts(): array
    {
        return ['is_published' => 'boolean', 'sections' => 'array'];
    }

    /**
     * Site pages publish at the root, except the home page which is "/".
     */
    public function seoPathForSlug(string $slug): string
    {
        return $slug === 'home' ? '/' : '/'.$slug;
    }
}
