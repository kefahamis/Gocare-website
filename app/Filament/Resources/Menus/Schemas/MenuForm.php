<?php

namespace App\Filament\Resources\Menus\Schemas;

use App\Forms\Components\MenuBuilder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class MenuForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->required()->maxLength(120),
                Select::make('location')
                    ->required()
                    ->options([
                        'primary' => 'Primary Navigation (header)',
                        'mobile' => 'Mobile Menu',
                        'footer' => 'Footer',
                    ])
                    ->helperText('Where this menu is displayed on the public site.'),
                Textarea::make('description')->rows(2)->maxLength(500),
                Toggle::make('is_active')->default(true)->label('Active'),
                MenuBuilder::make('items')
                    ->label('Menu Items')
                    ->menuLocation(fn (Get $get): string => $get('location') ?? 'primary')
                    ->columnSpanFull(),
            ]);
    }
}
