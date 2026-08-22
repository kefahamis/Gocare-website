<?php

namespace App\Filament\Resources\Courses\Schemas;

use App\Filament\Schemas\PageSeoSection;
use App\Forms\Components\MediaPicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CourseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')->required()->maxLength(255),
                TextInput::make('slug')->required()->unique(ignoreRecord: true)->maxLength(255),
                TextInput::make('school')->maxLength(255),
                MediaPicker::make('image')->maxLength(255),
                Textarea::make('summary')->rows(3)->columnSpanFull(),
                Repeater::make('sections')
                    ->schema([
                        TextInput::make('heading')->required()->maxLength(255),
                        Textarea::make('body')->required()->rows(8),
                        MediaPicker::make('image')->maxLength(255),
                        TextInput::make('link')->maxLength(255),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->reorderable()
                    ->columnSpanFull(),
                Toggle::make('is_published')->default(true),
                PageSeoSection::make(),
            ]);
    }
}
