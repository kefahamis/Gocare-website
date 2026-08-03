<?php

namespace Tests\Feature;

use App\Filament\Resources\Menus\Pages\EditMenu;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MenuBuilderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_menus_are_seeded_for_all_locations(): void
    {
        $this->assertDatabaseHas('menus', ['location' => 'primary', 'is_active' => true]);
        $this->assertDatabaseHas('menus', ['location' => 'mobile']);
        $this->assertDatabaseHas('menus', ['location' => 'footer']);

        $this->assertCount(7, Menu::forLocation('primary')->items);
        $this->assertCount(12, Menu::forLocation('mobile')->items);
        $this->assertCount(3, Menu::forLocation('footer')->items);
    }

    public function test_frontend_renders_primary_menu_from_database(): void
    {
        $menu = Menu::where('location', 'primary')->firstOrFail();
        $items = $menu->items;
        $items[] = ['label' => 'Test Nav Link', 'url' => '/contact', 'description' => null, 'icon' => null, 'color' => null, 'mega' => null, 'target' => null, 'children' => []];
        $menu->update(['items' => $items]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Test Nav Link')
            ->assertSee('About GoCare');
    }

    public function test_mega_menu_children_render_from_database(): void
    {
        $menu = Menu::where('location', 'primary')->firstOrFail();
        $menu->update(['items' => [
            [
                'label' => 'Mega Parent', 'url' => '/about', 'mega' => 'standard',
                'children' => [
                    ['label' => 'Mega Child One', 'url' => '/about', 'description' => 'Child description'],
                    ['label' => 'Mega Child Two', 'url' => '/about'],
                ],
            ],
        ]]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Mega Parent')
            ->assertSee('Mega Child One')
            ->assertSee('Mega Child Two');
    }

    public function test_mobile_menu_renders_from_database(): void
    {
        $menu = Menu::where('location', 'mobile')->firstOrFail();
        $menu->update(['items' => [
            ['label' => 'Drawer Item', 'url' => '/about'],
        ]]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Drawer Item')
            ->assertSee('Start Your Journey');
    }

    public function test_footer_renders_columns_from_database(): void
    {
        $menu = Menu::where('location', 'footer')->firstOrFail();
        $menu->update(['items' => [
            [
                'label' => 'Footer Column', 'url' => null,
                'children' => [
                    ['label' => 'Column Link', 'url' => '/about'],
                ],
            ],
        ]]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Footer Column')
            ->assertSee('Column Link');
    }

    public function test_default_items_fallback_when_no_menu_record(): void
    {
        Menu::query()->delete();

        $this->get('/')
            ->assertOk()
            ->assertSee('About GoCare')
            ->assertSee('Admissions')
            ->assertSee('Download Prospectus');
    }

    public function test_inactive_menu_is_hidden_from_frontend(): void
    {
        Menu::where('location', 'primary')->update(['is_active' => false]);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('About GoCare');
    }

    public function test_admin_can_access_menus_resource(): void
    {
        $user = User::factory()->create([
            'name' => 'GoCare Administrator',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user)->get('/admin/menus')->assertOk();
        $this->actingAs($user)->get('/admin/menus/create')->assertOk();
        $this->actingAs($user)->get('/admin/menus/'.Menu::where('location', 'primary')->firstOrFail()->id.'/edit')->assertOk();
    }

    public function test_admin_can_save_builder_items_through_the_form(): void
    {
        $user = User::factory()->create([
            'name' => 'GoCare Administrator',
            'email_verified_at' => now(),
        ]);

        $menu = Menu::where('location', 'primary')->firstOrFail();

        $items = [
            [
                'label' => 'Builder Root', 'url' => '/about', 'description' => null,
                'icon' => null, 'color' => null, 'mega' => null, 'target' => null,
                'children' => [
                    ['label' => 'Builder Child', 'url' => '/about', 'description' => 'Child copy'],
                ],
            ],
        ];

        Livewire::actingAs($user)
            ->test(EditMenu::class, ['record' => $menu->getRouteKey()])
            ->fillForm(['items' => $items])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame($items, $menu->fresh()->items);
        $this->assertDatabaseHas('menus', ['id' => $menu->id]);
    }

    public function test_builder_renders_on_the_menu_edit_page(): void
    {
        $user = User::factory()->create([
            'name' => 'GoCare Administrator',
            'email_verified_at' => now(),
        ]);

        $menu = Menu::where('location', 'primary')->firstOrFail();

        $this->actingAs($user)
            ->get('/admin/menus/'.$menu->id.'/edit')
            ->assertOk()
            ->assertSee('Menu structure')
            ->assertSee('Live preview')
            ->assertSee('Add item');
    }
}
