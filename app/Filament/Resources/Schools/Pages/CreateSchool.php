<?php

namespace App\Filament\Resources\Schools\Pages;

use App\Filament\Concerns\ManagesPageSeo;
use App\Filament\Resources\Schools\SchoolResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSchool extends CreateRecord
{
    use ManagesPageSeo;

    protected static string $resource = SchoolResource::class;
}
