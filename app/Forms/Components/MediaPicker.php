<?php

namespace App\Forms\Components;

use Filament\Forms\Components\Field;
use Filament\Forms\Components\Concerns\CanBeLengthConstrained;
use Filament\Forms\Components\Concerns\HasPlaceholder;

class MediaPicker extends Field
{
    use CanBeLengthConstrained;
    use HasPlaceholder;

    protected string $view = 'filament.forms.components.media-picker';

    public static function make(?string $name = null): static
    {
        return parent::make($name)
            ->label('Image path')
            ->placeholder('images/new-images/example.jpg')
            ->maxLength(255);
    }
}
