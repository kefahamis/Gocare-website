<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageLoaderTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_loader_is_injected_on_a_public_page(): void
    {
        $html = $this->get('/events')->assertOk()->getContent();

        $this->assertStringContainsString('id="gc-loader"', $html);
        // The facility: ground, columns, pediment and the windows that light up.
        $this->assertStringContainsString('class="gcb"', $html);
        $this->assertStringContainsString('gcb-roof', $html);
        $this->assertSame(4, substr_count($html, 'gcb-col gcb-col--'));
        $this->assertSame(6, substr_count($html, 'class="gcb-win"'));
    }

    public function test_it_sits_immediately_after_the_body_tag(): void
    {
        $html = $this->get('/about')->assertOk()->getContent();

        // Anything rendered before it would flash unstyled ahead of the panel.
        $this->assertMatchesRegularExpression('~<body[^>]*>\s*<div id="gc-loader"~i', $html);
    }

    public function test_it_appears_once_only(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, 'id="gc-loader"'));
    }

    public function test_a_noscript_fallback_uncovers_the_page(): void
    {
        $html = $this->get('/faqs')->assertOk()->getContent();

        $this->assertStringContainsString('<noscript><style>#gc-loader{display:none !important}</style></noscript>', $html);
    }

    public function test_it_carries_a_label_for_screen_readers(): void
    {
        $html = $this->get('/contact')->assertOk()->getContent();

        $this->assertStringContainsString('role="status"', $html);
        $this->assertStringContainsString('Loading GoCare Training Institute', $html);
    }

    public function test_the_admin_panel_does_not_get_one(): void
    {
        $html = $this->get('/admin/login')->getContent();

        $this->assertStringNotContainsString('id="gc-loader"', $html);
    }

    public function test_json_responses_are_untouched(): void
    {
        $this->getJson('/applications/status/nothing-here')
            ->assertNotFound()
            ->assertHeader('Content-Type', 'application/json');
    }

    public function test_every_stroked_part_is_normalised_for_the_draw(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        // pathLength="100" lets one dasharray drive every path, so the ground
        // line and the pediment draw at the same rate without measuring either.
        preg_match_all('~<(?:path|circle)[^>]*class="gcb[^"]*"[^>]*>~', $html, $m);

        $this->assertNotEmpty($m[0]);

        foreach ($m[0] as $tag) {
            $this->assertStringContainsString('pathLength="100"', $tag, "Missing pathLength: {$tag}");
        }
    }

    public function test_error_pages_do_not_get_one(): void
    {
        // Nothing is loading on a 404; a panel there would only be in the way.
        $html = $this->get('/no-such-page-at-all')->assertNotFound()->getContent();

        $this->assertStringNotContainsString('id="gc-loader"', $html);
    }
}
