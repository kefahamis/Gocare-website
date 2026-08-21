<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NormalizeInternalLinks
{
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

        // Nested Laravel routes break relative static assets (e.g. /blog/slug + images/foo.jpg).
        $content = preg_replace_callback(
            '~(?P<attr>\b(?:href|src|action|poster)=)(?P<quote>["\'])(?!https?:|//|mailto:|tel:|data:|#|/)(?P<path>(?:\.\./)*(?:images|docs|js|css|fonts)/[^"\']+)(?P=quote)~i',
            static fn (array $match): string => $match['attr'].$match['quote'].'/'.ltrim(preg_replace('~^(?:\.\./)+~', '', $match['path']), '/').$match['quote'],
            $content,
        );

        $content = preg_replace_callback(
            '~(?P<attr>\b(?:href|src)=)(?P<quote>["\'])(?!https?:|//|mailto:|tel:|data:|#|/)(?P<path>(?:\.\./)*(?:style\.css|script\.js|mobile-nav\.js|search\.js|search-index\.js|accessibility\.js))(?P=quote)~i',
            static fn (array $match): string => $match['attr'].$match['quote'].'/'.basename($match['path']).$match['quote'],
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
}
