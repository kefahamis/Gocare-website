<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use App\Support\MediaLibraryState;
use Livewire\WithFileUploads;

class MediaLibrary extends Page
{
    use MediaLibraryState;
    use WithFileUploads;

    protected string $view = 'filament.pages.media-library';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static ?string $navigationLabel = 'Media Library';

    protected static string|\UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 90;

    protected static ?string $title = 'Media Library';

    protected static ?string $slug = 'media';

    protected function getHeaderActions(): array
    {
        return [];
    }
}
