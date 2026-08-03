<?php

namespace App\Filament\Resources\SitePages\Schemas;

use App\Forms\Components\MediaPicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class SitePageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')->required()->live()->maxLength(255),
                TextInput::make('slug')->required()->unique(ignoreRecord: true)->maxLength(255),
                Toggle::make('use_builder')
                    ->label('Use visual builder on the public page')
                    ->helperText('Leave off to render the matching static GoCare template exactly. Turn on to publish the blocks below.')
                    ->default(false),
                Placeholder::make('builder_help')
                    ->label('Visual page builder')
                    ->content('Add, reorder, and edit blocks below. The preview updates as you edit the page.'),
                Placeholder::make('builder_preview')
                    ->label('Live Preview')
                    ->content(function (Get $get): HtmlString {
                        return new HtmlString(view('filament.site-pages.builder-preview', [
                            'title' => $get('title'),
                            'sections' => $get('sections') ?? [],
                        ])->render());
                    })
                    ->columnSpanFull(),
                Repeater::make('sections')
                    ->label('Page blocks')
                    ->live()
                    ->addActionLabel('Add block')
                    ->schema([
                        Select::make('type')
                            ->options([
                                'content' => 'Content section',
                                'split' => 'Image + text',
                                'callout' => 'Callout',
                                'cta' => 'Call to action',
                            ])
                            ->default('content')
                            ->required()
                            ->live(),
                        Select::make('alignment')
                            ->options(['left' => 'Image left', 'right' => 'Image right'])
                            ->default('right')
                            ->visible(fn (Get $get): bool => $get('type') === 'split'),
                        TextInput::make('heading')->maxLength(255),
                        Textarea::make('body')->rows(6)->columnSpanFull(),
                        MediaPicker::make('image')
                            ->label('Image path or URL')
                            ->placeholder('images/new-images/example.jpg')
                            ->maxLength(255)
                            ->visible(fn (Get $get): bool => in_array($get('type'), ['split'], true)),
                        TextInput::make('link')->label('Button URL')->maxLength(255),
                        TextInput::make('link_label')->label('Button label')->default('Learn More')->maxLength(80),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->reorderable()
                    ->columnSpanFull(),
                Toggle::make('is_published')->default(true),
            ]);
    }
}
