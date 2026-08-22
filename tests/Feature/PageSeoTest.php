<?php

namespace Tests\Feature;

use App\Models\PageSeo;
use App\Models\SeoSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageSeoTest extends TestCase
{
    use RefreshDatabase;

    private function descriptions(string $html): array
    {
        preg_match_all('~<meta\s+name="description"[^>]*content="([^"]*)"~i', $html, $matches);

        return $matches[1];
    }

    private function title(string $html): ?string
    {
        return preg_match('~<title[^>]*>(.*?)</title>~is', $html, $m) ? trim($m[1]) : null;
    }

    private function canonical(string $html): ?string
    {
        return preg_match('~<link\s+rel="canonical"\s+href="([^"]*)"~i', $html, $m) ? $m[1] : null;
    }

    private function ogUrl(string $html): ?string
    {
        return preg_match('~<meta\s+property="og:url"\s+content="([^"]*)"~i', $html, $m) ? $m[1] : null;
    }

    public function test_path_is_normalised_to_one_spelling(): void
    {
        $this->assertSame('/', PageSeo::normalisePath('/'));
        $this->assertSame('/about', PageSeo::normalisePath('about'));
        $this->assertSame('/about', PageSeo::normalisePath('/About/'));
        $this->assertSame('/faqs', PageSeo::normalisePath('https://gocare.co.ke/faqs/'));
        $this->assertSame('/events', PageSeo::normalisePath('/events?utm=x#top'));
    }

    public function test_page_without_a_record_keeps_its_own_title_and_description(): void
    {
        $html = $this->get('/events')->assertOk()->getContent();

        $this->assertStringContainsString('Events', (string) $this->title($html));

        // The view writes one and the shared partial adds another; only the
        // page's own, more specific one should survive.
        $descriptions = $this->descriptions($html);
        $this->assertCount(1, $descriptions);
        $this->assertStringContainsString('events', strtolower($descriptions[0]));
    }

    public function test_record_overrides_the_title_and_description(): void
    {
        PageSeo::create([
            'path' => '/events',
            'meta_title' => 'Open Days at GoCare',
            'meta_description' => 'Come and see the campus for yourself.',
            'keywords' => 'open day, campus tour',
            'is_active' => true,
        ]);

        $html = $this->get('/events')->assertOk()->getContent();

        $this->assertSame('Open Days at GoCare', $this->title($html));
        $this->assertSame(['Come and see the campus for yourself.'], $this->descriptions($html));
        $this->assertStringContainsString('<meta name="keywords" content="open day, campus tour">', $html);
        $this->assertStringContainsString('content="Open Days at GoCare"', $html);
    }

    public function test_inactive_record_falls_back_to_the_page(): void
    {
        PageSeo::create([
            'path' => '/events',
            'meta_title' => 'Should not be used',
            'is_active' => false,
        ]);

        $html = $this->get('/events')->assertOk()->getContent();

        $this->assertStringNotContainsString('Should not be used', $html);
        $this->assertCount(1, $this->descriptions($html));
    }

    public function test_a_record_may_override_only_some_fields(): void
    {
        PageSeo::create(['path' => '/events', 'meta_title' => 'Only the title', 'is_active' => true]);

        $html = $this->get('/events')->assertOk()->getContent();

        $this->assertSame('Only the title', $this->title($html));
        // Description was left blank, so the page keeps the one it had.
        $this->assertStringContainsString('events', strtolower($this->descriptions($html)[0]));
    }

    public function test_structured_data_is_published_when_valid(): void
    {
        PageSeo::create([
            'path' => '/events',
            'structured_data' => json_encode(['@context' => 'https://schema.org', '@type' => 'Event', 'name' => 'Open Day']),
            'is_active' => true,
        ]);

        $html = $this->get('/events')->assertOk()->getContent();

        $this->assertSame(2, substr_count($html, 'application/ld+json'));
        $this->assertStringContainsString('"@type":"Event"', $html);
    }

    public function test_invalid_structured_data_is_dropped_rather_than_printed(): void
    {
        PageSeo::create([
            'path' => '/events',
            'structured_data' => '{ not valid json',
            'is_active' => true,
        ]);

        $html = $this->get('/events')->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, 'application/ld+json'));
        $this->assertStringNotContainsString('not valid json', $html);
    }

    public function test_record_applies_to_a_page_rendered_through_the_layout(): void
    {
        PageSeo::create(['path' => '/', 'meta_title' => 'GoCare home', 'is_active' => true]);

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertSame('GoCare home', $this->title($html));
    }

    public function test_the_internal_marker_never_reaches_the_browser(): void
    {
        PageSeo::create(['path' => '/events', 'meta_description' => 'A description.', 'is_active' => true]);

        $this->get('/events')->assertOk()->assertDontSee('data-gc-seo', false);
        $this->get('/faqs')->assertOk()->assertDontSee('data-gc-seo', false);
    }

    public function test_canonical_defaults_to_the_page_itself(): void
    {
        $html = $this->get('/events')->assertOk()->getContent();

        $this->assertSame(url('/events'), $this->canonical($html));
    }

    public function test_canonical_accepts_a_path_and_resolves_it_against_the_site(): void
    {
        PageSeo::create([
            'path' => '/blog-acls-training-kenya',
            'canonical_url' => '/blog/acls-training-kenya',
            'is_active' => true,
        ]);

        $html = $this->get('/blog-acls-training-kenya')->assertOk()->getContent();

        $this->assertSame(url('/blog/acls-training-kenya'), $this->canonical($html));
        // og:url must agree with the canonical or the two contradict each other.
        $this->assertSame($this->canonical($html), $this->ogUrl($html));
    }

    public function test_canonical_accepts_a_full_address_unchanged(): void
    {
        PageSeo::create([
            'path' => '/events',
            'canonical_url' => 'https://gocare.co.ke/events',
            'is_active' => true,
        ]);

        $html = $this->get('/events')->assertOk()->getContent();

        $this->assertSame('https://gocare.co.ke/events', $this->canonical($html));
    }

    public function test_canonical_is_ignored_when_the_record_is_inactive(): void
    {
        PageSeo::create([
            'path' => '/events',
            'canonical_url' => '/somewhere-else',
            'is_active' => false,
        ]);

        $html = $this->get('/events')->assertOk()->getContent();

        $this->assertSame(url('/events'), $this->canonical($html));
    }

    public function test_canonical_path_is_built_on_the_configured_site_root(): void
    {
        SeoSetting::current()->update(['canonical_url' => 'https://gocare.co.ke/']);

        PageSeo::create([
            'path' => '/events',
            'canonical_url' => '/open-days',
            'is_active' => true,
        ]);

        $html = $this->get('/events')->assertOk()->getContent();

        $this->assertSame('https://gocare.co.ke/open-days', $this->canonical($html));
    }

    public function test_json_responses_are_left_alone(): void
    {
        $this->getJson('/applications/status/does-not-exist')->assertNotFound();
    }
}
