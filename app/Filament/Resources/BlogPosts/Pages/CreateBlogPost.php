<?php

namespace App\Filament\Resources\BlogPosts\Pages;

use App\Filament\Concerns\ManagesPageSeo;
use App\Filament\Resources\BlogPosts\BlogPostResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBlogPost extends CreateRecord
{
    use ManagesPageSeo;

    protected static string $resource = BlogPostResource::class;
}
