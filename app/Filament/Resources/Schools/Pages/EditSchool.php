<?php

namespace App\Filament\Resources\Schools\Pages;

use App\Filament\Concerns\ManagesPageSeo;
use App\Filament\Resources\Schools\SchoolResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSchool extends EditRecord
{
    use ManagesPageSeo;

    protected static string $resource = SchoolResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
