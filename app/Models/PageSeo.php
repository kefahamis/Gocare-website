<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Per-page SEO overrides, keyed by URL path.
 *
 * Site-wide defaults live in {@see SeoSetting}; a row here overrides them for
 * one page. Anything left blank on the row falls through to the default, so a
 * record that only sets a title is a valid, useful record.
 */
class PageSeo extends Model
{
    /** Where the per-request lookup is remembered. */
    private const REQUEST_CACHE_KEY = 'gocare.page_seo';

    protected $table = 'page_seo';

    protected $fillable = [
        'path',
        'meta_title',
        'meta_description',
        'keywords',
        'og_image',
        'canonical_url',
        'structured_data',
        'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /**
     * Reduce any spelling of a URL to the single form used as the key.
     *
     * "about", "/about/", "/about?x=1#y" and "/ABOUT" all describe one page;
     * storing them separately would mean an editor's override silently not
     * applying because it was saved under a different spelling.
     */
    public static function normalisePath(?string $path): string
    {
        $path = trim((string) $path);

        // Accept a pasted absolute URL and keep only its path.
        if (preg_match('~^https?://~i', $path)) {
            $path = (string) parse_url($path, PHP_URL_PATH);
        }

        $path = explode('#', explode('?', $path)[0])[0];
        $path = '/'.trim($path, '/');

        return $path === '/' ? '/' : rtrim(strtolower($path), '/');
    }

    /**
     * The active override for a path, or null.
     *
     * Answered once per request and remembered on the request itself: the meta
     * partial asks while the view renders and ApplyPageSeo asks again on the
     * finished HTML, and one page should not cost two queries. Deliberately
     * not a static: that outlives the request in a worker or a test run, and
     * would serve one request's answer to the next.
     */
    public static function forPath(?string $path): ?self
    {
        $key = static::normalisePath($path);
        $request = request();
        $resolved = $request->attributes->get(self::REQUEST_CACHE_KEY, []);

        if (! is_array($resolved)) {
            $resolved = [];
        }

        if (! array_key_exists($key, $resolved)) {
            $resolved[$key] = static::query()
                ->where('path', $key)
                ->where('is_active', true)
                ->first();

            $request->attributes->set(self::REQUEST_CACHE_KEY, $resolved);
        }

        return $resolved[$key];
    }

    /**
     * The address this page should tell search engines is the real one.
     *
     * Accepts either a path or a full address, because both are natural to
     * type: a path is resolved against the site root so the same record works
     * on staging and on the live domain, while a full address is left alone
     * for pointing off-site. Null means the page speaks for itself.
     */
    public function canonicalUrl(?string $siteRoot = null): ?string
    {
        $value = trim((string) $this->canonical_url);

        if ($value === '') {
            return null;
        }

        if (preg_match('~^https?://~i', $value)) {
            return rtrim($value, '/') ?: $value;
        }

        $root = rtrim($siteRoot ?: url('/'), '/');

        return $root.'/'.ltrim($value, '/');
    }

    /**
     * The structured-data block, only if it is usable JSON.
     *
     * Invalid JSON is dropped rather than printed: a broken ld+json block is
     * worse for the page than none at all, and the form already warns.
     */
    public function decodedStructuredData(): ?array
    {
        if (blank($this->structured_data)) {
            return null;
        }

        $decoded = json_decode($this->structured_data, true);

        return is_array($decoded) ? $decoded : null;
    }
}
