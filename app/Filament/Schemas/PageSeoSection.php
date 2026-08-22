<?php

namespace App\Filament\Schemas;

use App\Forms\Components\MediaPicker;
use App\Support\SitePaths;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

/**
 * The SEO block that sits at the foot of a content record's form.
 *
 * Its state lives under the `page_seo` key rather than on the record, because
 * the settings belong to a row in page_seo keyed by URL. ManagesPageSeo loads
 * and saves that row around the form.
 */
class PageSeoSection
{
    public const STATE_PATH = 'page_seo';

    public static function make(): Section
    {
        return Section::make('Search engine optimisation')
            ->description('How this page appears in Google and when its link is shared. Every field is optional — anything left blank uses the site-wide defaults under SEO Settings.')
            ->icon('heroicon-o-magnifying-glass')
            ->collapsed()
            ->collapsible()
            ->statePath(self::STATE_PATH)
            ->columnSpanFull()
            ->schema([
                TextInput::make('meta_title')
                    ->label('Meta title')
                    ->maxLength(255)
                    ->helperText('Replaces the page title in the browser tab and in search results. Aim for under 60 characters.')
                    ->columnSpanFull(),

                Textarea::make('meta_description')
                    ->label('Meta description')
                    ->rows(3)
                    ->maxLength(320)
                    ->helperText('The snippet under the search result. Around 150-160 characters reads best.')
                    ->columnSpanFull(),

                Textarea::make('keywords')
                    ->rows(2)
                    ->helperText('Comma-separated.')
                    ->columnSpanFull(),

                TextInput::make('canonical_url')
                    ->label('Canonical URL')
                    // A string rule, not a closure: Filament evaluates closures
                    // in rules() with its own injection, which cannot supply
                    // Laravel's $attribute. Not ->url() either, since a path is
                    // the more useful answer here.
                    ->rules(['nullable', 'string', 'regex:/^(?:\/|https?:\/\/)/i'])
                    ->validationMessages([
                        'regex' => 'Enter a path starting with / or a full address starting with https://.',
                    ])
                    ->datalist(SitePaths::all())
                    ->placeholder('/blog/acls-training-kenya')
                    ->maxLength(255)
                    ->helperText('Only needed when the same content sits at more than one address — it tells search engines which one to rank. Leave blank and the page points at itself.')
                    ->columnSpanFull(),

                Section::make('Social share image')
                    ->description('Shown when the page is shared on WhatsApp, Facebook or X. Leave blank to use the site-wide image.')
                    ->schema([
                        MediaPicker::make('og_image'),
                    ])
                    ->columnSpanFull(),

                Textarea::make('structured_data')
                    ->label('Structured data (JSON-LD)')
                    ->rows(6)
                    ->rules(['nullable', 'json'])
                    ->helperText('Optional schema.org block for this page, published alongside the site-wide organisation block. Must be valid JSON.')
                    ->columnSpanFull(),
            ]);
    }
}
