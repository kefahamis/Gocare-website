<?php

namespace App\Forms\Components;

use Filament\Forms\Components\Field;

class MediaPicker extends Field
{
    protected string $view = 'filament.forms.components.media-picker';

    public static function make(string $statePath): static
    {
        return parent::make($statePath)
            ->label('Image path')
            ->placeholder('images/new-images/example.jpg')
            ->maxLength(255);
    }
}
