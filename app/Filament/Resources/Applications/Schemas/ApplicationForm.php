<?php

namespace App\Filament\Resources\Applications\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ApplicationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('reference')->required()->disabled(),
                Select::make('status')->options(['submitted' => 'Submitted', 'reviewing' => 'Reviewing', 'accepted' => 'Accepted', 'rejected' => 'Rejected'])->required(),
                TextInput::make('phone')->tel(),
                TextInput::make('amount')->numeric()->prefix('KES')->disabled(),
                Select::make('payment_status')
                    ->label('Payment status')
                    ->options([
                        'pending' => 'Pending',
                        'awaiting_verification' => 'Awaiting verification',
                        'paid' => 'Paid',
                        'failed' => 'Failed',
                    ])
                    ->helperText('"Awaiting verification" means the applicant paid manually and typed the code themselves — check the M-Pesa statement before setting this to Paid.'),
                TextInput::make('mpesa_receipt')->label('M-Pesa code'),
                Textarea::make('data')->rows(12)->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT) : $state)->columnSpanFull(),
            ]);
    }
}
