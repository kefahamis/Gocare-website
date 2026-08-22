<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NormalizeInternalLinks
{
    /**
     * Static files that ship with the app and change with it, so a cached copy
     * has to be invalidated on deploy.
     *
     * @var list<string>
     */
    private const VERSIONED_ASSETS = [
        'style.css',
        'script.js',
        'mobile-nav.js',
        'search.js',
        'search-index.js',
        'accessibility.js',
    ];

    /**
     * Convert links copied from the static site into Laravel route URLs.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! str_contains((string) $response->headers->get('Content-Type'), 'text/html')) {
            return $response;
        }

        $content = $response->getContent();

        if ($content === false) {
            return $response;
        }

        $routeFamilies = 'schools|courses|blog|about|admissions';
        $content = preg_replace_callback(
            '~(?P<quote>["\'])(?:\.\./|/)?(?P<family>'.$routeFamilies.')/(?P<slug>[a-z0-9-]+)(?:\.html)?(?P<suffix>[#?][^"\']*)?(?P=quote)~i',
            static fn (array $match): string => $match['quote'].'/'.$match['family'].'/'.$match['slug'].($match['suffix'] ?? '').$match['quote'],
            $content,
        );

        $content = preg_replace_callback(
            '~(?P<quote>["\'])(?:\.\./|/)?(?P<slug>courses|about|admissions|industry-liaison|contact|blog|apply|faqs|downloads|gallery|events|careers|home-based-care|student-resources|student-support-services|modes-of-study|hostels-and-accommodation)(?:\.html)?(?P<suffix>[#?][^"\']*)?(?P=quote)~i',
            static fn (array $match): string => $match['quote'].'/'.$match['slug'].($match['suffix'] ?? '').$match['quote'],
            $content,
        );

        // The two rules above only know the slugs they were given, which left
        // pages like industrial-attachment.html and alumni-network.html - and
        // every index.html - pointing at files that no longer exist. Routes are
        // extensionless, so any remaining .html link is a static-site leftover.
        // Anchored to href= on purpose: the rules above match a bare quoted
        // slug, which would rewrite an unrelated attribute if one ever matched.
        $content = preg_replace_callback(
            '~(?P<attr>\bhref=)(?P<quote>["\'])(?!https?:|//|mailto:|tel:|data:|#)(?:\.\./|/)?(?P<path>[a-z0-9][a-z0-9/-]*)\.html(?P<suffix>[#?][^"\']*)?(?P=quote)~i',
            static fn (array $match): string => $match['attr'].$match['quote']
                .(strtolower($match['path']) === 'index' ? '/' : '/'.$match['path'])
                .($match['suffix'] ?? '').$match['quote'],
            $content,
        );

        // Nested Laravel routes break relative static assets (e.g. /blog/slug + images/foo.jpg).
        $content = preg_replace_callback(
            '~(?P<attr>\b(?:href|src|action|poster)=)(?P<quote>["\'])(?!https?:|//|mailto:|tel:|data:|#|/)(?P<path>(?:\.\./)*(?:images|docs|js|css|fonts)/[^"\']+)(?P=quote)~i',
            static fn (array $match): string => $match['attr'].$match['quote'].'/'.ltrim(preg_replace('~^(?:\.\./)+~', '', $match['path']), '/').$match['quote'],
            $content,
        );

        // Same six files, absolute and stamped. Without the stamp a returning
        // visitor keeps whatever the browser cached: search-index.js is 400KB
        // of URLs and style.css carries the mobile layout, so a stale copy
        // looks exactly like the bug that was just fixed. Matches the relative
        // and absolute spellings both, and drops any stamp already there so
        // running twice is harmless.
        $assets = implode('|', array_map(
            static fn (string $file): string => preg_quote($file, '~'),
            self::VERSIONED_ASSETS,
        ));

        $content = preg_replace_callback(
            '~(?P<attr>\b(?:href|src)=)(?P<quote>["\'])(?!https?:|//)(?:\.\./)*/?(?P<file>'.$assets.')(?:\?[^"\']*)?(?P=quote)~i',
            fn (array $match): string => $match['attr'].$match['quote'].$this->versioned(strtolower($match['file'])).$match['quote'],
            $content,
        );

        $content = preg_replace_callback(
            '~url\((?P<quote>["\']?)(?!https?:|//|data:|#|/)(?P<path>(?:\.\./)*(?:images|docs|fonts)/[^"\')]+)(?P=quote)\)~i',
            static fn (array $match): string => 'url('.$match['quote'].'/'.ltrim(preg_replace('~^(?:\.\./)+~', '', $match['path']), '/').$match['quote'].')',
            $content,
        );

        $response->setContent($content);

        return $response;
    }

    /**
     * Absolute path to a shipped asset, stamped with its last-modified time.
     *
     * Memoised per request: this runs once per matched attribute and the six
     * files are stat-ed at most once each.
     */
    private function versioned(string $file): string
    {
        static $urls = [];

        if (! array_key_exists($file, $urls)) {
            $path = public_path($file);
            $stamp = is_file($path) ? filemtime($path) : false;
            $urls[$file] = '/'.$file.($stamp ? '?v='.$stamp : '');
        }

        return $urls[$file];
    }
}
