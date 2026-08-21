<?php

namespace App\Filament\Resources\SeoSettings\Schemas;

use App\Forms\Components\MediaPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SeoSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('site_name')->required()->maxLength(255),
                TextInput::make('default_title')->required()->maxLength(255),
                Textarea::make('meta_description')->required()->rows(3)->maxLength(320)->columnSpanFull(),
                Textarea::make('keywords')->rows(2)->helperText('Comma-separated search terms.'),
                TextInput::make('canonical_url')->url()->helperText('Optional site root, for example https://gocareinstitute.ac.ke'),
                MediaPicker::make('og_image')->helperText('Public image path or absolute URL.'),
                TextInput::make('twitter_handle'),
                Select::make('robots')->options([
                    'index,follow' => 'Index and follow',
                    'noindex,follow' => 'Do not index, follow links',
                    'noindex,nofollow' => 'Do not index or follow',
                ])->required(),
                TextInput::make('google_site_verification'),
                TextInput::make('organization_name'),
                MediaPicker::make('organization_logo')->helperText('Public image path or absolute URL.'),
                TextInput::make('organization_phone'),
                TextInput::make('organization_email')->email(),
            ]);
    }
}
