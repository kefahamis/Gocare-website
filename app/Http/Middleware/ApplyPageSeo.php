<?php

namespace App\Http\Middleware;

use App\Models\PageSeo;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Apply the per-page SEO record to tags the Blade partial cannot reach.
 *
 * Most of this site is standalone Blade views that write their own <title> and
 * <meta name="description"> near the top of <head>, long before
 * components/seo.blade.php is included at the bottom. A partial cannot unwrite
 * those, so the two tags that matter most to SEO are settled here, on the
 * finished HTML — the same post-processing NormalizeInternalLinks already does
 * for links and assets.
 */
class ApplyPageSeo
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! str_contains((string) $response->headers->get('Content-Type'), 'text/html')) {
            return $response;
        }

        $content = $response->getContent();

        if ($content === false || ! str_contains($content, '</head>')) {
            return $response;
        }

        $page = PageSeo::forPath($request->getPathInfo());

        $content = $this->applyTitle($content, $page?->meta_title);
        $content = $this->resolveDescriptions($content);

        $response->setContent($content);

        return $response;
    }

    /**
     * Replace the <title> the view hard-coded, when an editor has set one.
     */
    private function applyTitle(string $content, ?string $title): string
    {
        if (blank($title)) {
            return $content;
        }

        $escaped = e($title);

        $replaced = preg_replace(
            '~<title\b[^>]*>.*?</title>~is',
            '<title>'.$escaped.'</title>',
            $content,
            1,
            $count,
        );

        // A view with no <title> at all still deserves the configured one.
        if ($count === 0) {
            return preg_replace('~</head>~i', '<title>'.$escaped.'</title>'."\n".'</head>', $content, 1) ?? $content;
        }

        return $replaced ?? $content;
    }

    /**
     * Leave exactly one <meta name="description"> standing.
     *
     * The static views emit their own and the partial adds another, so every
     * page currently ships two. Which one wins depends on where it came from:
     * an editor's override outranks everything, and failing that a description
     * written for this page beats the site-wide fallback.
     */
    private function resolveDescriptions(string $content): string
    {
        $pattern = '~[ \t]*<meta\s+name="description"[^>]*>\R?~i';

        if (! preg_match_all($pattern, $content, $matches)) {
            return $content;
        }

        $tags = $matches[0];

        if (count($tags) === 1) {
            return $this->stripMarker($content);
        }

        $fromPage = null;
        $fromDefault = null;
        $fromView = null;

        foreach ($tags as $tag) {
            if (str_contains($tag, 'data-gc-seo="page"')) {
                $fromPage = $tag;
            } elseif (str_contains($tag, 'data-gc-seo="default"')) {
                $fromDefault = $tag;
            } elseif ($fromView === null) {
                $fromView = $tag;
            }
        }

        $keep = $fromPage ?? $fromView ?? $fromDefault ?? $tags[0];

        // Drop every description, then put the winner back where the first one
        // stood, so it keeps its place near the top of <head>.
        $first = true;
        $content = preg_replace_callback(
            $pattern,
            function () use (&$first, $keep): string {
                if ($first) {
                    $first = false;

                    return $keep;
                }

                return '';
            },
            $content,
        ) ?? $content;

        return $this->stripMarker($content);
    }

    /**
     * The marker is plumbing between the partial and this class; it has no
     * business reaching the browser.
     */
    private function stripMarker(string $content): string
    {
        return preg_replace('~\s+data-gc-seo="(?:page|default)"~i', '', $content) ?? $content;
    }
}
