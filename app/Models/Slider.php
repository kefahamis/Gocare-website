<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Slider extends Model
{
    protected $fillable = [
        'title', 'accent_title', 'title_suffix', 'description', 'highlight', 'points', 'image', 'alt_text',
        'primary_label', 'primary_url', 'primary_style',
        'secondary_label', 'secondary_url', 'secondary_style',
        'open_new_tab', 'sort_order', 'is_published',
    ];

    protected function casts(): array
    {
        return [
            'points' => 'array',
            'open_new_tab' => 'boolean',
            'is_published' => 'boolean',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function getImageUrlAttribute(): string
    {
        if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://')) {
            return $this->image;
        }

        if (str_starts_with($this->image, 'images/')) {
            return static::publicAssetUrl($this->image);
        }

        return Storage::url($this->image);
    }

    public static function publicAssetUrl(string $path): string
    {
        return asset(static::encodePath($path));
    }

    public static function encodePath(string $path): string
    {
        return implode('/', array_map('rawurlencode', explode('/', $path)));
    }
}
