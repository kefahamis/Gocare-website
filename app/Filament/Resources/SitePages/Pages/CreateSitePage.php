<?php

namespace App\Filament\Resources\SitePages\Pages;

use App\Filament\Concerns\ManagesPageSeo;
use App\Filament\Resources\SitePages\SitePageResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSitePage extends CreateRecord
{
    use ManagesPageSeo;

    protected static string $resource = SitePageResource::class;
}
