<?php

namespace App\Filament\Pages;

use App\Models\MailSetting;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Mail;

/**
 * SMTP configuration, editable without a deploy.
 *
 * Settings are only used once "Use these settings" is on, so a half-finished
 * form cannot take outgoing mail down: until then the site keeps running on
 * whatever .env provides.
 */
class MailSettings extends Page
{
    protected string $view = 'filament.pages.mail-settings';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static ?string $navigationLabel = 'Email (SMTP)';

    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 10;

    protected static ?string $title = 'Email (SMTP) settings';

    protected static ?string $slug = 'settings/email';

    /** @var array<string, mixed> */
    public array $data = [];

    public function mount(): void
    {
        $settings = MailSetting::current();

        $this->form->fill([
            'enabled' => $settings->enabled,
            'host' => $settings->host,
            'port' => $settings->port,
            'encryption' => $settings->encryption,
            'username' => $settings->username,
            // Never send the stored password back to the browser. An empty box
            // means "keep what is saved"; typing replaces it.
            'password' => null,
            'from_address' => $settings->from_address,
            'from_name' => $settings->from_name,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Server')
                    ->columns(2)
                    ->schema([
                        Toggle::make('enabled')
                            ->label('Use these settings')
                            ->helperText('Off means the site keeps using the mail settings from the server environment. Turn this on only once a test email has arrived.')
                            ->columnSpanFull(),
                        TextInput::make('host')
                            ->label('SMTP host')
                            ->placeholder('smtp.gmail.com')
                            ->maxLength(255),
                        TextInput::make('port')
                            ->label('Port')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(65535)
                            ->helperText('587 for TLS, 465 for SSL, 25 unencrypted.'),
                        Select::make('encryption')
                            ->label('Encryption')
                            ->options(['tls' => 'TLS', 'ssl' => 'SSL', '' => 'None'])
                            ->native(false),
                        TextInput::make('username')
                            ->label('Username')
                            ->maxLength(255)
                            ->autocomplete(false),
                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->revealable()
                            ->autocomplete(false)
                            ->helperText('Leave blank to keep the saved password. Stored encrypted.'),
                    ]),

                Section::make('Sender')
                    ->columns(2)
                    ->schema([
                        TextInput::make('from_address')
                            ->label('From address')
                            ->email()
                            ->placeholder('admissions@gocare.ac.ke')
                            ->helperText('Most providers reject mail whose From address is not the authenticated account.'),
                        TextInput::make('from_name')
                            ->label('From name')
                            ->placeholder('GoCare Training Institute'),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Save')
                ->action('save'),

            Action::make('test')
                ->label('Send test email')
                ->color('gray')
                ->icon(Heroicon::OutlinedPaperAirplane)
                ->schema([
                    TextInput::make('to')
                        ->label('Send to')
                        ->email()
                        ->required()
                        ->default(fn () => auth()->user()?->email),
                ])
                ->action(fn (array $data) => $this->sendTest($data['to'])),
        ];
    }

    public function save(): void
    {
        $state = $this->form->getState();
        $settings = MailSetting::current();

        // A blank password keeps the stored one, so saving an unrelated change
        // does not wipe the credential the site is running on.
        if (blank($state['password'] ?? null)) {
            unset($state['password']);
        }

        $settings->fill($state)->save();

        $this->form->fill(array_merge($state, ['password' => null]));

        Notification::make()
            ->title('Email settings saved')
            ->body($settings->enabled
                ? 'The site will send through this server from now on.'
                : 'Saved, but still switched off. Send a test email, then turn "Use these settings" on.')
            ->success()
            ->send();
    }

    /**
     * Send through the settings ON THE FORM, not the saved ones.
     *
     * Testing the saved row would mean saving before you know it works, and
     * testing what is live would mean the test cannot tell you anything about
     * the change you just typed.
     */
    public function sendTest(string $to): void
    {
        $state = $this->form->getState();
        $settings = MailSetting::current();

        $password = blank($state['password'] ?? null) ? $settings->password : $state['password'];

        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => $state['host'] ?: config('mail.mailers.smtp.host'),
            'mail.mailers.smtp.port' => $state['port'] ?: config('mail.mailers.smtp.port'),
            'mail.mailers.smtp.username' => $state['username'] ?: null,
            'mail.mailers.smtp.password' => $password,
            'mail.mailers.smtp.scheme' => 'ssl' === ($state['encryption'] ?? null) ? 'smtps' : null,
            'mail.mailers.smtp.encryption' => $state['encryption'] ?: null,
            'mail.from.address' => $state['from_address'] ?: config('mail.from.address'),
            'mail.from.name' => $state['from_name'] ?: config('mail.from.name'),
        ]);

        try {
            // Sent immediately rather than queued: a queued test would report
            // success the moment it was queued and fail silently in a worker,
            // which is the opposite of what a test is for.
            Mail::raw(
                "This is a test from the GoCare admin panel.\n\nIf you are reading this, the SMTP settings work.",
                fn ($message) => $message->to($to)->subject('GoCare SMTP test')
            );

            $settings->forceFill([
                'last_tested_to' => $to,
                'last_tested_at' => now(),
                'last_test_error' => null,
            ])->save();

            Notification::make()
                ->title('Test email sent')
                ->body('Sent to ' . $to . '. If it does not arrive, check the spam folder and the From address.')
                ->success()
                ->send();
        } catch (\Throwable $e) {
            $settings->forceFill([
                'last_tested_to' => $to,
                'last_tested_at' => now(),
                'last_test_error' => $e->getMessage(),
            ])->save();

            // The provider's own words are what identifies a wrong password, a
            // blocked port or an unverified sender. Paraphrasing loses that.
            Notification::make()
                ->title('Test email failed')
                ->body($e->getMessage())
                ->danger()
                ->persistent()
                ->send();
        }
    }
}
