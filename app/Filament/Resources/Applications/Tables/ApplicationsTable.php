<?php

namespace App\Filament\Resources\Applications\Tables;

use App\Models\Application;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ApplicationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference')->searchable()->sortable(),
                TextColumn::make('status')->badge(),
                TextColumn::make('phone')->searchable()->toggleable(),
                TextColumn::make('amount')->money('KES')->sortable(),
                TextColumn::make('payment_status')
                    ->label('Payment')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'awaiting_verification' => 'Awaiting verification',
                        default => ucfirst((string) $state),
                    })
                    ->color(fn (?string $state) => match ($state) {
                        'paid' => 'success',
                        'awaiting_verification' => 'warning',
                        'failed' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('mpesa_receipt')
                    ->label('M-Pesa code')
                    ->searchable()
                    ->placeholder('—'),
                TextColumn::make('submitted_at')->dateTime()->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                SelectFilter::make('payment_status')
                    ->label('Payment')
                    ->options([
                        'pending' => 'Pending',
                        'awaiting_verification' => 'Awaiting verification',
                        'paid' => 'Paid',
                        'failed' => 'Failed',
                    ]),
            ])
            ->recordActions([
                // Manual payments carry only a code the applicant typed, so a
                // human has to match it against the M-Pesa statement.
                Action::make('verifyPayment')
                    ->label('Mark as paid')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Confirm this payment was received')
                    ->modalDescription('Check the M-Pesa statement for this code and amount before confirming. This marks the application as paid.')
                    ->visible(fn (Application $record) => $record->payment_status !== 'paid')
                    ->action(fn (Application $record) => $record->update(['payment_status' => 'paid'])),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
