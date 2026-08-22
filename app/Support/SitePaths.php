<?php

namespace App\Support;

use App\Models\BlogPost;
use App\Models\Course;
use App\Models\School;
use App\Models\SitePage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Every URL path the site serves a page on.
 *
 * Pages come from two places — Blade views under resources/views/pages and rows
 * in the content tables — so neither list alone is the site. This joins them so
 * an editor picking a page to configure sees all of it.
 */
class SitePaths
{
    /** Route families whose sub-folders map to /{family}/{slug}. */
    private const FAMILIES = ['about', 'admissions', 'courses', 'schools'];

    /** Blade views that are partials or duplicates rather than pages of their own. */
    private const NOT_PAGES = ['index', 'gallery-dynamic', 'industry-liaison-redesign'];

    /**
     * @return array<string, string> path => human label, ordered for a picker
     */
    public static function options(): array
    {
        $paths = [];

        foreach (self::all() as $path) {
            $paths[$path] = $path === '/' ? '/  (home page)' : $path;
        }

        return $paths;
    }

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        static $paths = null;

        if ($paths !== null) {
            return $paths;
        }

        $found = array_merge(
            ['/', '/courses', '/blog', '/contact', '/gallery'],
            self::fromViews(),
            self::fromContent(),
        );

        $found = array_values(array_unique($found));
        sort($found, SORT_NATURAL);

        // Home first; it is the page an editor reaches for most.
        $found = array_values(array_diff($found, ['/']));
        array_unshift($found, '/');

        return $paths = $found;
    }

    /**
     * @return list<string>
     */
    private static function fromViews(): array
    {
        $root = resource_path('views/pages');

        if (! File::isDirectory($root)) {
            return [];
        }

        $paths = [];

        foreach (File::files($root) as $file) {
            $slug = Str::before($file->getFilename(), '.blade.php');

            if (self::isServableSlug($slug) && ! in_array($slug, self::NOT_PAGES, true)) {
                $paths[] = '/'.$slug;
            }
        }

        foreach (self::FAMILIES as $family) {
            $dir = $root.DIRECTORY_SEPARATOR.$family;

            if (! File::isDirectory($dir)) {
                continue;
            }

            foreach (File::files($dir) as $file) {
                $slug = Str::before($file->getFilename(), '.blade.php');

                if (self::isServableSlug($slug)) {
                    $paths[] = '/'.$family.'/'.$slug;
                }
            }
        }

        return $paths;
    }

    /**
     * @return list<string>
     */
    private static function fromContent(): array
    {
        $paths = [];

        // Guard the whole lot: the admin form must still open on a fresh
        // install where these tables have not been migrated yet.
        try {
            foreach (SitePage::query()->pluck('slug') as $slug) {
                if (self::isServableSlug($slug)) {
                    $paths[] = '/'.$slug;
                }
            }

            foreach ([Course::class => 'courses', BlogPost::class => 'blog', School::class => 'schools'] as $model => $family) {
                foreach ($model::query()->pluck('slug') as $slug) {
                    if (self::isServableSlug($slug)) {
                        $paths[] = '/'.$family.'/'.$slug;
                    }
                }
            }
        } catch (\Throwable) {
            return $paths;
        }

        return $paths;
    }

    /**
     * The route patterns only match lowercase slugs, so anything else — the
     * " - old" and " - backup" copies sitting in the views folder — is not a
     * URL this site can serve.
     */
    private static function isServableSlug(string $slug): bool
    {
        return (bool) preg_match('/^[a-z0-9-]+$/', $slug);
    }
}
