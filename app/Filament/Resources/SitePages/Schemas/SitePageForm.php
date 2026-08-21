<?php

namespace App\Filament\Resources\SitePages\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SitePageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')->required()->live()->maxLength(255),
                TextInput::make('slug')->required()->unique(ignoreRecord: true)->maxLength(255),
                Textarea::make('content')
                    ->label('Page content')
                    ->rows(18)
                    ->columnSpanFull(),
                Toggle::make('is_published')->default(true),
            ]);
    }
}
