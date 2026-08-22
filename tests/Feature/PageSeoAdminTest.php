<?php

namespace Tests\Feature;

use App\Filament\Resources\BlogPosts\Pages\EditBlogPost;
use App\Filament\Resources\Courses\Pages\CreateCourse;
use App\Filament\Resources\Schools\Pages\EditSchool;
use App\Filament\Resources\SitePages\Pages\EditSitePage;
use App\Models\BlogPost;
use App\Models\Course;
use App\Models\PageSeo;
use App\Models\School;
use App\Models\SitePage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The SEO block lives at the foot of each content record's form rather than in
 * a menu of its own, so these check it round-trips through those forms.
 */
class PageSeoAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_the_block_saves_against_the_records_own_url(): void
    {
        $school = School::create(['title' => 'Medical', 'slug' => 'medical-health-sciences', 'source_path' => 'schools/medical-health-sciences.html', 'is_published' => true]);

        Livewire::test(EditSchool::class, ['record' => $school->getRouteKey()])
            ->fillForm([
                'page_seo' => [
                    'meta_title' => 'School of Medical & Health Sciences | GoCare',
                    'meta_description' => 'Clinical programmes accredited by TVETA.',
                ],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $seo = PageSeo::where('path', '/schools/medical-health-sciences')->first();

        $this->assertNotNull($seo, 'The block should have written a row for the school URL.');
        $this->assertSame('School of Medical & Health Sciences | GoCare', $seo->meta_title);
        $this->assertTrue($seo->is_active);
    }

    public function test_saved_values_load_back_into_the_form(): void
    {
        $page = SitePage::create(['title' => 'About', 'slug' => 'about', 'source_path' => 'about.html', 'is_published' => true]);
        PageSeo::create(['path' => '/about', 'meta_title' => 'About GoCare', 'keywords' => 'college, nairobi', 'is_active' => true]);

        Livewire::test(EditSitePage::class, ['record' => $page->getRouteKey()])
            ->assertFormSet([
                'page_seo' => [
                    'meta_title' => 'About GoCare',
                    'meta_description' => null,
                    'keywords' => 'college, nairobi',
                    'canonical_url' => null,
                    'og_image' => null,
                    'structured_data' => null,
                ],
            ]);
    }

    public function test_the_home_page_record_maps_to_the_root_url(): void
    {
        $home = SitePage::create(['title' => 'Home', 'slug' => 'home', 'source_path' => 'index.html', 'is_published' => true]);

        Livewire::test(EditSitePage::class, ['record' => $home->getRouteKey()])
            ->fillForm(['page_seo' => ['meta_title' => 'GoCare Training Institute']])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('page_seo', ['path' => '/', 'meta_title' => 'GoCare Training Institute']);
    }

    public function test_clearing_every_field_removes_the_row(): void
    {
        $school = School::create(['title' => 'Medical', 'slug' => 'medical-health-sciences', 'source_path' => 'schools/medical-health-sciences.html', 'is_published' => true]);
        PageSeo::create(['path' => '/schools/medical-health-sciences', 'meta_title' => 'Old title', 'is_active' => true]);

        Livewire::test(EditSchool::class, ['record' => $school->getRouteKey()])
            ->fillForm(['page_seo' => ['meta_title' => '', 'meta_description' => '', 'keywords' => '', 'canonical_url' => '', 'og_image' => '', 'structured_data' => '']])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseMissing('page_seo', ['path' => '/schools/medical-health-sciences']);
    }

    public function test_a_bad_canonical_is_rejected_by_the_embedded_block(): void
    {
        $school = School::create(['title' => 'Medical', 'slug' => 'medical-health-sciences', 'source_path' => 'schools/medical-health-sciences.html', 'is_published' => true]);

        Livewire::test(EditSchool::class, ['record' => $school->getRouteKey()])
            ->fillForm(['page_seo' => ['canonical_url' => 'not-a-url']])
            ->call('save')
            ->assertHasFormErrors(['page_seo.canonical_url']);
    }

    public function test_the_block_works_on_create_where_the_path_is_only_known_after_saving(): void
    {
        Livewire::test(CreateCourse::class)
            ->fillForm([
                'title' => 'Certificate in Caregiving',
                'slug' => 'certificate-in-caregiving-level-4',
                'sections' => [],
                'page_seo' => ['meta_title' => 'Caregiving Level 4 | GoCare'],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('page_seo', [
            'path' => '/courses/certificate-in-caregiving-level-4',
            'meta_title' => 'Caregiving Level 4 | GoCare',
        ]);
    }

    public function test_renaming_a_slug_carries_the_settings_across(): void
    {
        $post = BlogPost::create(['title' => 'ACLS', 'slug' => 'acls-training-kenya', 'is_published' => true]);
        PageSeo::create(['path' => '/blog/acls-training-kenya', 'meta_title' => 'ACLS in Kenya', 'is_active' => true]);

        Livewire::test(EditBlogPost::class, ['record' => $post->getRouteKey()])
            ->fillForm(['slug' => 'acls-training-nairobi'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseMissing('page_seo', ['path' => '/blog/acls-training-kenya']);
        $this->assertDatabaseHas('page_seo', ['path' => '/blog/acls-training-nairobi', 'meta_title' => 'ACLS in Kenya']);
    }

    public function test_renaming_onto_an_occupied_path_keeps_the_records_own_settings(): void
    {
        $post = BlogPost::create(['title' => 'ACLS', 'slug' => 'acls-training-kenya', 'is_published' => true]);
        PageSeo::create(['path' => '/blog/acls-training-kenya', 'meta_title' => 'The post being renamed', 'is_active' => true]);
        PageSeo::create(['path' => '/blog/already-taken', 'meta_title' => 'Left over from before', 'is_active' => true]);

        Livewire::test(EditBlogPost::class, ['record' => $post->getRouteKey()])
            ->fillForm(['slug' => 'already-taken'])
            ->call('save')
            ->assertHasNoFormErrors();

        // The editor had this post's SEO on screen while renaming it, so that
        // is what the new URL should carry.
        $this->assertDatabaseHas('page_seo', ['path' => '/blog/already-taken', 'meta_title' => 'The post being renamed']);
        // And the old URL no longer exists, so neither should its row.
        $this->assertDatabaseMissing('page_seo', ['path' => '/blog/acls-training-kenya']);
        $this->assertSame(1, PageSeo::count());
    }

    public function test_there_is_no_standalone_page_seo_resource(): void
    {
        // SEO is edited in the block at the foot of a record's form. A second
        // place to edit the same rows would only be somewhere for them to drift.
        $this->assertFalse(
            class_exists(\App\Filament\Resources\PageSeos\PageSeoResource::class),
            'The standalone Page SEO resource should be gone.'
        );

        $this->assertDirectoryDoesNotExist(app_path('Filament/Resources/PageSeos'));
    }

    public function test_settings_saved_by_the_block_still_reach_the_page(): void
    {
        $school = School::create(['title' => 'Medical', 'slug' => 'medical-health-sciences', 'source_path' => 'schools/medical-health-sciences.html', 'is_published' => true]);

        Livewire::test(EditSchool::class, ['record' => $school->getRouteKey()])
            ->fillForm(['page_seo' => ['meta_title' => 'School of Medical & Health Sciences | GoCare']])
            ->call('save')
            ->assertHasNoFormErrors();

        $html = $this->get('/schools/medical-health-sciences')->assertOk()->getContent();

        $this->assertStringContainsString('<title>School of Medical &amp; Health Sciences | GoCare</title>', $html);
    }
}
