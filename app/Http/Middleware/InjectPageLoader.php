<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Put the page loader into the markup of every public page.
 *
 * It goes in here rather than in the views because the site is ~130 standalone
 * Blade files with a <head> each; editing them all to add a loader would be a
 * change of the same size as the feature. Injecting after <body> also means it
 * is in the first paint, where a script-inserted overlay would flash the page
 * first and cover it afterwards.
 *
 * Styles live in public/style.css (already render-blocking, so the panel is
 * never seen unstyled). Only the markup and its few lines of script are here.
 */
class InjectPageLoader
{
    /** Prefixes that get the site chrome but should not get the loader. */
    private const SKIP_PREFIXES = ['admin', 'livewire', 'filament', 'up'];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $this->shouldInject($request, $response)) {
            return $response;
        }

        $content = $response->getContent();

        if ($content === false || str_contains($content, 'id="gc-loader"')) {
            return $response;
        }

        // After the opening <body ...> tag, so it is the first thing painted.
        $updated = preg_replace(
            '~(<body\b[^>]*>)~i',
            '$1'."\n".$this->markup(),
            $content,
            1,
            $count,
        );

        if ($count > 0 && $updated !== null) {
            $response->setContent($updated);
        }

        return $response;
    }

    private function shouldInject(Request $request, Response $response): bool
    {
        if (! str_contains((string) $response->headers->get('Content-Type'), 'text/html')) {
            return false;
        }

        // A redirect or an error page has nothing worth waiting for.
        if ($response->getStatusCode() !== 200) {
            return false;
        }

        // Livewire updates arrive as fragments; only a full document qualifies.
        if ($request->headers->has('X-Livewire') || $request->ajax()) {
            return false;
        }

        foreach (self::SKIP_PREFIXES as $prefix) {
            if ($request->is($prefix) || $request->is($prefix.'/*')) {
                return false;
            }
        }

        return true;
    }

    private function markup(): string
    {
        $logo = '/images/gocare-institute-logo-white.png';

        return <<<HTML
        <div id="gc-loader" role="status" aria-label="Loading">
          <span class="gc-loader-sr">Loading GoCare Training Institute</span>
          <div class="gc-loader-inner" aria-hidden="true">
            <img src="{$logo}" alt="" width="148" height="48" decoding="async" fetchpriority="high">
            <svg viewBox="0 0 240 60" preserveAspectRatio="xMidYMid meet" focusable="false" aria-hidden="true">
              <path class="gc-loader-base" d="M0,32 H240"></path>
              <path class="gc-loader-line" d="M0,32 L72,32 L82,10 L94,54 L104,24 L112,32 L240,32"></path>
            </svg>
            <p class="gc-loader-word">Train with the experts</p>
          </div>
        </div>
        <script>
        (function () {
          var panel = document.getElementById('gc-loader');
          if (!panel) return;

          var root = document.documentElement;
          root.className += ' gc-loading';

          var shown = Date.now();
          var done = false;

          function reveal() {
            if (done) return;
            done = true;
            panel.className += ' gc-loader--done';
            root.className = root.className.replace(/\\s*gc-loading\\b/, '');
            // Leave the DOM once the lift has played out.
            window.setTimeout(function () {
              if (panel && panel.parentNode) panel.parentNode.removeChild(panel);
            }, 900);
          }

          // A minimum on screen so a fast page does not flash the panel, and a
          // ceiling so a slow or broken one never holds the page hostage.
          function finish() {
            var held = Date.now() - shown;
            window.setTimeout(reveal, Math.max(0, 900 - held));
          }

          if (document.readyState === 'complete') finish();
          else window.addEventListener('load', finish);

          window.setTimeout(reveal, 4000);
          // Coming back through history should show the page, not the panel.
          window.addEventListener('pageshow', function (e) { if (e.persisted) reveal(); });
        })();
        </script>
        <noscript><style>#gc-loader{display:none !important}</style></noscript>
        HTML;
    }
}
