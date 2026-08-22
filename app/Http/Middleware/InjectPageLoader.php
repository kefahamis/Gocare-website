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
            <svg viewBox="0 0 240 132" preserveAspectRatio="xMidYMid meet" focusable="false" aria-hidden="true">
              <g class="gcb">
                <path class="gcb-ground" pathLength="100" d="M14,120 H226"></path>
                <path class="gcb-wing gcb-wing--l" pathLength="100" d="M22,106 V74 H60"></path>
                <path class="gcb-wing gcb-wing--r" pathLength="100" d="M218,106 V74 H180"></path>
                <path class="gcb-steps" pathLength="100" d="M44,113 H196 M52,106 H188"></path>
                <path class="gcb-col gcb-col--1" pathLength="100" d="M72,105 V61"></path>
                <path class="gcb-col gcb-col--2" pathLength="100" d="M96,105 V61"></path>
                <path class="gcb-col gcb-col--3" pathLength="100" d="M144,105 V61"></path>
                <path class="gcb-col gcb-col--4" pathLength="100" d="M168,105 V61"></path>
                <path class="gcb-lintel" pathLength="100" d="M54,59 H186"></path>
                <path class="gcb-roof" pathLength="100" d="M50,53 L120,20 L190,53"></path>
                <circle class="gcb-clock" cx="120" cy="38" r="6" pathLength="100"></circle>
                <rect class="gcb-win" x="26" y="84" width="8" height="12" rx="1.5"></rect>
                <rect class="gcb-win" x="37" y="84" width="8" height="12" rx="1.5"></rect>
                <rect class="gcb-win" x="48" y="84" width="8" height="12" rx="1.5"></rect>
                <rect class="gcb-win" x="184" y="84" width="8" height="12" rx="1.5"></rect>
                <rect class="gcb-win" x="195" y="84" width="8" height="12" rx="1.5"></rect>
                <rect class="gcb-win" x="206" y="84" width="8" height="12" rx="1.5"></rect>
                <rect class="gcb-door" x="106" y="78" width="28" height="28" rx="2"></rect>
              </g>
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
