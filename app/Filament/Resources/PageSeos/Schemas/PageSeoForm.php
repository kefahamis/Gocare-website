<?php

namespace App\Filament\Resources\PageSeos\Schemas;

use App\Forms\Components\MediaPicker;
use App\Models\PageSeo;
use App\Support\SitePaths;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PageSeoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // A datalist rather than a select: it suggests every page the
                // site is known to serve, while still accepting a path that is
                // newer than the list (a view added since, or a landing page).
                TextInput::make('path')
                    ->label('Page URL')
                    ->required()
                    ->datalist(SitePaths::all())
                    ->placeholder('/about')
                    ->unique(ignoreRecord: true)
                    ->dehydrateStateUsing(fn (?string $state): string => PageSeo::normalisePath($state))
                    ->helperText('The page these settings apply to, e.g. /about or /courses/certificate-in-caregiving-level-4. Use / for the home page. One entry per page.')
                    ->columnSpanFull(),

                TextInput::make('meta_title')
                    ->label('Meta title')
                    ->maxLength(255)
                    ->helperText('Replaces the page title in the browser tab and in search results. Aim for under 60 characters. Leave blank to keep the title the page already has.')
                    ->columnSpanFull(),

                Textarea::make('meta_description')
                    ->label('Meta description')
                    ->rows(3)
                    ->maxLength(320)
                    ->helperText('The snippet under the search result. Around 150-160 characters reads best. Leave blank to keep the description the page already has.')
                    ->columnSpanFull(),

                Textarea::make('keywords')
                    ->rows(2)
                    ->helperText('Comma-separated. Falls back to the site-wide keywords when blank.')
                    ->columnSpanFull(),

                // MediaPicker renders its own markup without Filament's field
                // wrapper, so a label or helper text set on it never appears.
                // The section carries the explanation instead.
                Section::make('Social share image')
                    ->description('Shown when the page is shared on WhatsApp, Facebook or X. Leave blank to use the site-wide image.')
                    ->schema([
                        MediaPicker::make('og_image'),
                    ])
                    ->columnSpanFull(),

                TextInput::make('canonical_url')
                    ->label('Canonical URL')
                    ->datalist(SitePaths::all())
                    ->placeholder('/blog/acls-training-kenya')
                    ->maxLength(255)
                    // Deliberately not ->url(): a path is the more useful answer
                    // here and ->url() would reject it. A string rule rather than
                    // a closure, too — Filament evaluates closures in rules() with
                    // its own injection, which cannot supply Laravel's $attribute.
                    ->rules(['nullable', 'string', 'regex:/^(?:\/|https?:\/\/)/i'])
                    ->validationMessages([
                        'regex' => 'Enter a path starting with / or a full address starting with https://.',
                    ])
                    ->helperText('Only needed when the same content sits at more than one address — it tells search engines which one to rank. A path such as /blog/acls-training-kenya, or a full https:// address for somewhere off-site. Leave blank and the page points at itself.')
                    ->columnSpanFull(),

                Textarea::make('structured_data')
                    ->label('Structured data (JSON-LD)')
                    ->rows(8)
                    ->rules(['nullable', 'json'])
                    ->helperText('Optional schema.org block for this page, published alongside the site-wide organisation block. Must be valid JSON.')
                    ->columnSpanFull(),

                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true)
                    ->helperText('Turn off to fall back to the site-wide defaults without deleting these settings.')
                    ->columnSpanFull(),
            ]);
    }
}
