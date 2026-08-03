<?php

namespace App\Filament\Resources\ContactMessages\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ContactMessageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->required(),
                TextInput::make('email')->email()->required(),
                TextInput::make('phone'),
                TextInput::make('subject'),
                Textarea::make('message')->required()->rows(8)->columnSpanFull(),
                Select::make('status')->options(['new' => 'New', 'read' => 'Read', 'replied' => 'Replied'])->required(),
            ]);
    }
}
