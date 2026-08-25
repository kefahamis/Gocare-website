<?php

namespace App\Filament\Resources\Applications\Schemas;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class ApplicationForm
{
    /**
     * The submitted form, grouped the way it was asked and labelled the way a
     * person reads it. Keys not listed here still appear, under "Other
     * details": a field added to the application form must never go missing
     * from the admin view just because nobody updated this map.
     *
     * @var array<string, array<string, string>>
     */
    private const GROUPS = [
        'Applicant' => [
            'name' => 'Full name',
            'id' => 'ID / Passport number',
            'dob' => 'Date of birth',
            'gender' => 'Gender',
            'nationality' => 'Nationality',
            'county' => 'County',
            'address' => 'Postal address',
            'email' => 'Email',
            'phone' => 'Phone',
        ],
        'Programme' => [
            'school_sel' => 'School',
            'course' => 'Course',
            'program' => 'Programme',
            'campus' => 'Campus',
            'mode' => 'Study mode',
            'intake_month' => 'Intake month',
            'intake_year' => 'Intake year',
        ],
        'Education' => [
            'qual' => 'Highest qualification',
            'grade' => 'Grade',
            'school' => 'Secondary school',
            'year' => 'Year completed',
            'addqual' => 'Additional qualifications',
        ],
        'Accommodation' => [
            'accommodation' => 'Requires accommodation',
            'room_type' => 'Room type',
            'acc_campus' => 'Accommodation campus',
            'acc_intake' => 'Accommodation intake',
            'acc_notes' => 'Notes',
        ],
        'Payment' => [
            'mpesa_phone' => 'M-Pesa number',
        ],
    ];

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Application')
                    ->columns(2)
                    ->schema([
                        TextInput::make('reference')->required()->disabled(),
                        Select::make('status')
                            ->options([
                                'submitted' => 'Submitted',
                                'reviewing' => 'Reviewing',
                                'accepted' => 'Accepted',
                                'rejected' => 'Rejected',
                            ])
                            ->required(),
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
                    ]),

                ...self::submittedSections(),

                Section::make('Attachments')
                    ->schema([
                        Placeholder::make('attachments')
                            ->hiddenLabel()
                            ->content(fn ($record) => self::documents($record))
                            ->columnSpanFull(),
                    ]),

                Section::make('Raw submission')
                    ->description('Every field exactly as submitted. Useful when a value above looks wrong.')
                    ->collapsed()
                    ->schema([
                        Textarea::make('data')
                            ->hiddenLabel()
                            ->rows(14)
                            ->disabled()
                            ->formatStateUsing(fn ($state) => is_array($state)
                                ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                                : $state)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    /**
     * The uploaded documents, each a download link. The files sit on a private
     * disk, so the link goes through an authenticated admin route rather than
     * exposing a storage URL.
     */
    private static function documents($record): HtmlString
    {
        $documents = $record?->documents()->orderBy('kind')->orderBy('id')->get();

        if (! $documents || $documents->isEmpty()) {
            return new HtmlString('<span class="text-sm text-gray-400">No documents were uploaded with this application.</span>');
        }

        $rows = '';

        foreach ($documents as $document) {
            $rows .= '<div class="flex items-center gap-3 py-1.5 text-sm">'
                . '<span class="w-56 shrink-0 text-gray-500">' . e($document->label()) . '</span>'
                . '<a href="' . e(route('admin.application-documents.show', $document)) . '" class="font-medium text-primary-600 hover:underline">'
                . e($document->original_name) . '</a>'
                . '<span class="text-gray-400">' . e($document->readableSize()) . '</span>'
                . '</div>';
        }

        return new HtmlString('<div>' . $rows . '</div>');
    }

    /**
     * One section per group, plus a catch-all for anything unmapped.
     *
     * @return array<int, Section>
     */
    private static function submittedSections(): array
    {
        $sections = [];

        foreach (self::GROUPS as $title => $fields) {
            $sections[] = Section::make($title)
                ->columns(2)
                ->schema(array_map(
                    fn (string $label, string $key) => Placeholder::make('data_' . $key)
                        ->label($label)
                        ->content(fn ($record) => self::value($record, $key)),
                    $fields,
                    array_keys($fields)
                ));
        }

        $sections[] = Section::make('Other details')
            ->collapsed()
            ->schema([
                Placeholder::make('data_other')
                    ->hiddenLabel()
                    ->content(fn ($record) => self::unmapped($record))
                    ->columnSpanFull(),
            ]);

        return $sections;
    }

    /**
     * A single submitted value, or a visible dash. A blank cell is ambiguous:
     * it could mean "not answered" or "this view is broken".
     */
    private static function value($record, string $key): HtmlString
    {
        $data = is_array($record?->data) ? $record->data : [];
        $value = $data[$key] ?? null;

        if ($value === null || $value === '' || $value === []) {
            return new HtmlString('<span class="text-gray-400">&mdash;</span>');
        }

        if (is_array($value)) {
            $value = implode(', ', array_map('strval', $value));
        }

        return new HtmlString(e((string) $value));
    }

    /**
     * Whatever the map does not cover, so a newly added form field shows up
     * here rather than silently vanishing.
     */
    private static function unmapped($record): HtmlString
    {
        $data = is_array($record?->data) ? $record->data : [];
        $mapped = array_merge(...array_map('array_keys', array_values(self::GROUPS)));
        $rest = array_diff_key($data, array_flip($mapped));
        $rest = array_filter($rest, fn ($v) => $v !== null && $v !== '' && $v !== []);

        if ($rest === []) {
            return new HtmlString('<span class="text-gray-400">Nothing else was submitted.</span>');
        }

        $rows = '';

        foreach ($rest as $key => $value) {
            if (is_array($value)) {
                $value = implode(', ', array_map('strval', $value));
            }

            $rows .= '<div class="flex gap-3 py-1">'
                . '<span class="w-56 shrink-0 text-gray-500">' . e((string) $key) . '</span>'
                . '<span>' . e((string) $value) . '</span>'
                . '</div>';
        }

        return new HtmlString('<div class="text-sm">' . $rows . '</div>');
    }
}
