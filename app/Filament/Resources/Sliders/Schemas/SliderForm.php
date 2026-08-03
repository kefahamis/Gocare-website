<?php

namespace App\Filament\Resources\Sliders\Schemas;

use App\Models\Slider;
use Filament\Forms\Components\BaseFileUpload;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SliderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')->required()->maxLength(255),
                TextInput::make('accent_title')->maxLength(255),
                TextInput::make('title_suffix')->maxLength(255),
                Textarea::make('description')->rows(3),
                Textarea::make('highlight')->rows(2),
                TagsInput::make('points')->separator(','),
                FileUpload::make('image')
                    ->disk('public')
                    ->directory('sliders')
                    ->image()
                    ->required()
                    ->fetchFileInformation(false)
                    ->getUploadedFileUsing(static function (BaseFileUpload $component, string $file, string|array|null $storedFileNames): ?array {
                        if (str_starts_with($file, 'images/')) {
                            $path = public_path($file);

                            return [
                                'name' => basename($file),
                                'size' => is_file($path) ? filesize($path) : 0,
                                'type' => is_file($path) ? (mime_content_type($path) ?: null) : null,
                                'url' => Slider::publicAssetUrl($file),
                            ];
                        }

                        return $component->getUploadedFile($file, $storedFileNames);
                    }),
                TextInput::make('alt_text')->maxLength(255),
                TextInput::make('primary_label')->maxLength(80),
                TextInput::make('primary_url')->maxLength(255),
                Select::make('primary_style')->options(['primary' => 'Orange primary', 'ghost' => 'Ghost', 'white' => 'White'])->default('primary'),
                TextInput::make('secondary_label')->maxLength(80),
                TextInput::make('secondary_url')->maxLength(255),
                Select::make('secondary_style')->options(['primary' => 'Orange primary', 'ghost' => 'Ghost', 'white' => 'White'])->default('white'),
                TextInput::make('sort_order')->numeric()->required()->default(0),
                Toggle::make('open_new_tab'),
                Toggle::make('is_published')->default(true),
            ]);
    }
}
