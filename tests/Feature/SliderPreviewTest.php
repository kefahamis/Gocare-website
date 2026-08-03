<?php

namespace Tests\Feature;

use App\Filament\Resources\Sliders\Pages\EditSlider;
use App\Filament\Resources\Sliders\Pages\ListSliders;
use App\Models\Slider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SliderPreviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_slider_edit_keeps_existing_public_asset_image_in_state(): void
    {
        $this->seed();

        $user = User::factory()->create(['email_verified_at' => now()]);
        $slider = Slider::where('image', 'like', 'images/%')->firstOrFail();

        $test = Livewire::actingAs($user)->test(EditSlider::class, ['record' => $slider->getRouteKey()]);

        $this->assertTrue(in_array($slider->image, array_values($test->get('data.image')), true));
    }

    public function test_slider_edit_resolves_existing_image_preview_url(): void
    {
        $this->seed();

        $user = User::factory()->create(['email_verified_at' => now()]);
        $slider = Slider::where('image', 'like', 'images/%')->firstOrFail();

        $test = Livewire::actingAs($user)->test(EditSlider::class, ['record' => $slider->getRouteKey()]);

        $page = $test->instance();
        $field = collect($page->getSchema('form')->getFlatComponents())
            ->first(fn ($component) => $component->getStatePath(false) === 'image');

        $this->assertNotNull($field);

        $files = $field->getUploadedFiles();
        $this->assertNotNull($files);
        $this->assertCount(1, $files);
        $this->assertSame(Slider::publicAssetUrl($slider->image), array_values($files)[0]['url']);
    }

    public function test_slider_list_table_shows_existing_image(): void
    {
        $this->seed();

        $user = User::factory()->create(['email_verified_at' => now()]);
        $slider = Slider::where('image', 'like', 'images/%')->firstOrFail();

        $this->actingAs($user)
            ->get(ListSliders::getUrl())
            ->assertOk()
            ->assertSee(Slider::publicAssetUrl($slider->image), false);
    }
}
