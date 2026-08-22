<?php

namespace App\Filament\Concerns;

use App\Filament\Schemas\PageSeoSection;
use App\Models\PageSeo;

/**
 * Loads and saves the SEO block that PageSeoSection puts on a record's form.
 *
 * The settings are a row in page_seo keyed by URL, not columns on the record,
 * so they are read before the form fills and written after the record saves —
 * on create that has to be afterwards, because the path is derived from the
 * slug the form just stored.
 */
trait ManagesPageSeo
{
    /** @var array<string, mixed> */
    protected array $pageSeoState = [];

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $existing = $this->record?->pageSeo();

        $data[PageSeoSection::STATE_PATH] = [
            'meta_title' => $existing?->meta_title,
            'meta_description' => $existing?->meta_description,
            'keywords' => $existing?->keywords,
            'canonical_url' => $existing?->canonical_url,
            'og_image' => $existing?->og_image,
            'structured_data' => $existing?->structured_data,
        ];

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->extractPageSeo($data);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->extractPageSeo($data);
    }

    protected function afterCreate(): void
    {
        $this->persistPageSeo();
    }

    protected function afterSave(): void
    {
        $this->persistPageSeo();
    }

    /**
     * Lift the SEO block out of the record's own data.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function extractPageSeo(array $data): array
    {
        $this->pageSeoState = (array) ($data[PageSeoSection::STATE_PATH] ?? []);

        unset($data[PageSeoSection::STATE_PATH]);

        return $data;
    }

    private function persistPageSeo(): void
    {
        $record = $this->record;

        if (! $record || ! method_exists($record, 'seoPath')) {
            return;
        }

        $path = PageSeo::normalisePath($record->seoPath());

        $values = [
            'meta_title' => $this->blankToNull($this->pageSeoState['meta_title'] ?? null),
            'meta_description' => $this->blankToNull($this->pageSeoState['meta_description'] ?? null),
            'keywords' => $this->blankToNull($this->pageSeoState['keywords'] ?? null),
            'canonical_url' => $this->blankToNull($this->pageSeoState['canonical_url'] ?? null),
            'og_image' => $this->blankToNull($this->pageSeoState['og_image'] ?? null),
            'structured_data' => $this->blankToNull($this->pageSeoState['structured_data'] ?? null),
        ];

        $existing = PageSeo::query()->where('path', $path)->first();

        // An all-blank block means "use the site defaults". Don't leave an
        // empty row behind for it, and clear one that is no longer needed.
        if (collect($values)->every(fn ($value) => $value === null)) {
            $existing?->delete();

            return;
        }

        if ($existing) {
            $existing->update($values);

            return;
        }

        PageSeo::create($values + ['path' => $path, 'is_active' => true]);
    }

    private function blankToNull(mixed $value): ?string
    {
        $value = is_string($value) ? trim($value) : $value;

        return blank($value) ? null : (string) $value;
    }
}
