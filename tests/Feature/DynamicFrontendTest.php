<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\SitePage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DynamicFrontendTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_courses_listing_renders_from_database(): void
    {
        $this->get('/courses')
            ->assertOk()
            ->assertSee('Explore Our Programmes');
    }

    public function test_course_detail_renders_from_database(): void
    {
        $this->get('/courses/amca-usa-certification')
            ->assertOk()
            ->assertSee('AMCA', false);
    }

    public function test_blog_listing_renders_from_database(): void
    {
        $this->get('/blog')
            ->assertOk()
            ->assertSee('Blogs &amp; Articles', false);
    }

    public function test_blog_post_renders_from_database(): void
    {
        $this->get('/blog/acls-training-kenya')
            ->assertOk();
    }

    public function test_blog_post_builder_renders_typed_blocks(): void
    {
        $post = BlogPost::where('slug', 'home-based-care-level-3')->firstOrFail();

        $post->update([
            'use_builder' => true,
            'sections' => [
                ['type' => 'callout', 'body' => 'Admissions are now open for the next intake.'],
                ['type' => 'split', 'heading' => 'Learn by doing', 'body' => 'Hands-on clinical practice in modern facilities.', 'image' => 'images/news-bg.jpg', 'alignment' => 'left', 'link' => 'apply', 'link_label' => 'Apply now'],
                ['type' => 'cta', 'heading' => 'Start today', 'body' => 'Join GoCare and become certified.', 'link' => 'apply', 'link_label' => 'Apply Now'],
            ],
        ]);

        $this->get('/blog/home-based-care-level-3')
            ->assertOk()
            ->assertSee('bp-callout', false)
            ->assertSee('bp-split', false)
            ->assertSee('bp-cta', false)
            ->assertSee('Learn by doing')
            ->assertSee('Apply now')
            ->assertSee('Start today');
    }

    public function test_blog_post_static_template_used_by_default(): void
    {
        $this->get('/blog/home-based-care-level-3')
            ->assertOk()
            ->assertSee('Who This Course Is For')
            ->assertSee('5 min read')
            ->assertDontSee('gc-edit', false)
            ->assertDontSee('bp-split', false);
    }

    public function test_school_page_renders_from_database(): void
    {
        $this->get('/schools/medical-health-sciences')
            ->assertOk();
    }

    public function test_site_pages_render_from_database(): void
    {
        $this->get('/about')->assertOk()->assertSee('Welcome to GoCare', false);
        $this->get('/faqs')->assertOk();
    }

    public function test_admin_edit_is_reflected_on_frontend(): void
    {
        $page = SitePage::where('slug', 'faqs')->firstOrFail();

        $page->update([
            'use_builder' => true,
            'sections' => [
                ['heading' => 'Frequently Asked Questions', 'body' => 'Original body copy'],
                ['heading' => 'Updated Section Heading', 'body' => 'Freshly edited content from the backend'],
            ],
        ]);

        $this->get('/faqs')
            ->assertOk()
            ->assertSee('Updated Section Heading')
            ->assertSee('Freshly edited content from the backend');
    }

    public function test_typed_page_builder_blocks_render_with_template_styles(): void
    {
        $page = SitePage::where('slug', 'faqs')->firstOrFail();

        $page->update([
            'use_builder' => true,
            'sections' => [
                ['type' => 'callout', 'heading' => 'Important update', 'body' => 'Admissions are now open.'],
                ['type' => 'split', 'heading' => 'Meet our team', 'body' => 'Learn with experienced practitioners.', 'image' => 'images/news-bg.jpg', 'alignment' => 'left', 'link' => 'contact', 'link_label' => 'Contact us'],
            ],
        ]);

        $this->get('/faqs')
            ->assertOk()
            ->assertSee('gc-sec--callout', false)
            ->assertSee('gc-sec--split', false)
            ->assertSee('Meet our team')
            ->assertSee('Contact us');
    }

    public function test_static_page_template_is_used_by_default(): void
    {
        $this->get('/about')
            ->assertOk()
            ->assertSee('about-page-sec', false)
            ->assertSee('about-grid', false)
            ->assertDontSee('gc-edit', false);
    }

    public function test_static_school_links_use_laravel_routes(): void
    {
        $this->get('/schools/medical-health-sciences')
            ->assertOk()
            ->assertSee('href="/schools/medical-health-sciences"', false)
            ->assertDontSee('href="../schools/medical-health-sciences"', false);
    }

    public function test_functional_pages_stay_static(): void
    {
        $this->get('/apply')->assertOk();
        $this->get('/contact')->assertOk();
        $this->get('/application-form')->assertOk();
    }

    public function test_homepage_unchanged(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Home');
    }

    public function test_unknown_page_returns_404(): void
    {
        $this->get('/this-page-does-not-exist')->assertNotFound();
    }
}
