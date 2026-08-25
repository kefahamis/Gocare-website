<?php

namespace App\Filament\Pages;

use App\Models\ContactSetting;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

/**
 * Who gets contact messages, what the notification says, and whether the
 * sender is acknowledged -- all editable without a deploy.
 *
 * Every field falls back to its previous hard-coded value when left blank, so
 * an untouched settings row behaves exactly as the site did before.
 */
class ContactFormSettings extends Page
{
    protected string $view = 'filament.pages.contact-form-settings';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static ?string $navigationLabel = 'Contact form';

    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 20;

    protected static ?string $title = 'Contact form settings';

    protected static ?string $slug = 'settings/contact-form';

    /** @var array<string, mixed> */
    public array $data = [];

    public function mount(): void
    {
        $settings = ContactSetting::current();

        $this->form->fill([
            'enabled' => $settings->enabled,
            'recipients' => $settings->recipients ?: array_filter([config('gocare.notification_email')]),
            'cc' => $settings->cc ?: [],
            'subject' => $settings->subject,
            'success_message' => $settings->success_message,
            'auto_reply' => $settings->auto_reply,
            'auto_reply_subject' => $settings->auto_reply_subject,
            'auto_reply_body' => $settings->auto_reply_body,
            'spam_min_seconds' => $settings->spam_min_seconds,
            'spam_max_per_hour' => $settings->spam_max_per_hour,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Form')
                    ->schema([
                        Toggle::make('enabled')
                            ->label('Accept new messages')
                            ->helperText('Off closes the form to visitors. Messages already received are unaffected.'),
                        TextInput::make('success_message')
                            ->label('Thank-you message')
                            ->placeholder(ContactSetting::DEFAULT_SUCCESS)
                            ->helperText('Shown on the page after a message is sent.')
                            ->maxLength(255),
                    ]),

                Section::make('Notification')
                    ->description('Where a new message is sent. The message is saved to the admin either way, so nothing is lost if mail fails.')
                    ->schema([
                        TagsInput::make('recipients')
                            ->label('Send to')
                            ->placeholder('Add an email address and press Enter')
                            ->nestedRecursiveRules(['email'])
                            ->helperText('Leave empty to fall back to the address configured on the server.'),
                        TagsInput::make('cc')
                            ->label('CC')
                            ->placeholder('Optional')
                            ->nestedRecursiveRules(['email']),
                        TextInput::make('subject')
                            ->label('Subject')
                            ->placeholder(ContactSetting::DEFAULT_SUBJECT)
                            ->maxLength(180),
                    ]),

                Section::make('Spam protection')
                    ->description('A hidden honeypot field always runs and needs no configuration. These two are the tunable defences — both trade false positives against protection.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('spam_min_seconds')
                            ->label('Minimum fill time (seconds)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(120)
                            ->helperText('Submissions faster than this are treated as automated. 0 disables the check. 3 is a sensible floor; a person cannot read and complete this form faster.'),
                        TextInput::make('spam_max_per_hour')
                            ->label('Maximum submissions per hour, per visitor')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(200)
                            ->helperText('0 disables the limit. Counted per IP address, so a shared office connection shares the allowance.'),
                    ]),

                Section::make('Acknowledgement to the sender')
                    ->description('An automatic reply to whoever filled in the form.')
                    ->schema([
                        Toggle::make('auto_reply')
                            ->label('Send an acknowledgement')
                            ->helperText('Off by default. This mails an address typed by an anonymous visitor, so anyone can point it at somebody else — leave it off unless the form is protected against abuse.')
                            ->live(),
                        TextInput::make('auto_reply_subject')
                            ->label('Subject')
                            ->placeholder(ContactSetting::DEFAULT_AUTO_REPLY_SUBJECT)
                            ->maxLength(180)
                            ->visible(fn ($get) => (bool) $get('auto_reply')),
                        Textarea::make('auto_reply_body')
                            ->label('Message')
                            ->rows(8)
                            ->placeholder(ContactSetting::DEFAULT_AUTO_REPLY_BODY)
                            ->helperText('{name} is replaced with the sender\'s name.')
                            ->visible(fn ($get) => (bool) $get('auto_reply')),
                    ]),
            ]);
    }

    public function save(): void
    {
        $state = $this->form->getState();

        // Blank means "use the default", and the model resolves that at read
        // time -- so store null rather than an empty string, which would read
        // back as a deliberately empty subject.
        foreach (['spam_min_seconds', 'spam_max_per_hour'] as $key) {
            $state[$key] = (int) ($state[$key] ?? 0);
        }

        foreach (['subject', 'success_message', 'auto_reply_subject', 'auto_reply_body'] as $key) {
            if (blank($state[$key] ?? null)) {
                $state[$key] = null;
            }
        }

        ContactSetting::current()->fill($state)->save();

        Notification::make()
            ->title('Contact form settings saved')
            ->success()
            ->send();
    }
}
