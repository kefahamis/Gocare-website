<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_renders_premium_layout_for_admin(): void
    {
        $user = User::factory()->create([
            'name' => 'GoCare Administrator',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/admin');

        $response->assertOk();

        $response->assertSee('Hello, GA.');
        $response->assertSee('Recent Applications');
        $response->assertSee('Our Growth');
        $response->assertSee('filament');
        $response->assertSee('Documentation');
        $response->assertSee('GitHub');
        $response->assertSee('GoCare Admin');
        $response->assertSee('v4.12.5');
    }
}
