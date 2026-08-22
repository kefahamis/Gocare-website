<?php

namespace App\Models\Concerns;

use App\Models\PageSeo;

/**
 * Gives a content record the SEO settings for the URL it is published at.
 *
 * The settings live in {@see PageSeo}, keyed by path rather than by a foreign
 * key, because most of this site's pages are Blade views with no record of
 * their own. A record reaches its own row through the path it publishes at.
 */
trait HasPageSeo
{
    /**
     * Keep the settings pointing at the record when its slug is edited,
     * otherwise renaming a page silently abandons its SEO.
     */
    public static function bootHasPageSeo(): void
    {
        static::updating(function (self $record): void {
            if (! $record->isDirty('slug')) {
                return;
            }

            $was = PageSeo::normalisePath(
                $record->seoPathForSlug((string) $record->getOriginal('slug'))
            );
            $now = PageSeo::normalisePath($record->seoPathForSlug((string) $record->slug));

            if ($was === $now) {
                return;
            }

            // Slugs are unique, so after this rename the old URL serves nothing
            // and its settings are dead. If the new path already has a row it
            // stays put and the form's own save decides what it holds; either
            // way the stale one goes rather than lingering as an orphan.
            if (PageSeo::query()->where('path', $now)->exists()) {
                PageSeo::query()->where('path', $was)->delete();

                return;
            }

            PageSeo::query()->where('path', $was)->update(['path' => $now]);
        });
    }

    /**
     * The URL this record is published at.
     */
    public function seoPath(): string
    {
        return $this->seoPathForSlug((string) $this->slug);
    }

    /**
     * The settings row for this record, if one has been saved.
     */
    public function pageSeo(): ?PageSeo
    {
        return PageSeo::query()
            ->where('path', PageSeo::normalisePath($this->seoPath()))
            ->first();
    }

    /**
     * How this model turns a slug into a path. Implemented per model.
     */
    abstract public function seoPathForSlug(string $slug): string;
}
