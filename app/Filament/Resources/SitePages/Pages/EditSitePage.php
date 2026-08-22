<?php

namespace App\Filament\Resources\SitePages\Pages;

use App\Filament\Concerns\ManagesPageSeo;
use App\Filament\Resources\SitePages\SitePageResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSitePage extends EditRecord
{
    use ManagesPageSeo;

    protected static string $resource = SitePageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
