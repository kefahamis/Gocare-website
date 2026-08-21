<?php

namespace App\Filament\Resources\BlogPosts\Schemas;

use App\Forms\Components\MediaPicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BlogPostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')->required()->live()->maxLength(255),
                TextInput::make('slug')->required()->unique(ignoreRecord: true)->maxLength(255),
                MediaPicker::make('image')
                    ->label('Hero image path or URL')
                    ->placeholder('images/new-images/example.jpg')
                    ->maxLength(255),
                Textarea::make('excerpt')->rows(3)->columnSpanFull(),
                Textarea::make('content')
                    ->label('Post content')
                    ->rows(18)
                    ->columnSpanFull(),
                Toggle::make('is_published')->default(true),
                DateTimePicker::make('published_at'),
            ]);
    }
}
