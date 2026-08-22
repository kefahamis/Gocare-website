<?php

namespace App\Filament\Resources\Courses\Pages;

use App\Filament\Concerns\ManagesPageSeo;
use App\Filament\Resources\Courses\CourseResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCourse extends CreateRecord
{
    use ManagesPageSeo;

    protected static string $resource = CourseResource::class;
}
