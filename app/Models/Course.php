<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = ['title', 'slug', 'school', 'summary', 'content', 'sections', 'image', 'is_published'];

    protected function casts(): array
    {
        return ['is_published' => 'boolean', 'sections' => 'array'];
    }
}
