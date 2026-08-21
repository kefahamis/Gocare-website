<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    protected $fillable = ['title', 'slug', 'content', 'sections', 'image', 'source_path', 'is_published'];

    protected function casts(): array
    {
        return ['is_published' => 'boolean', 'sections' => 'array'];
    }
}
