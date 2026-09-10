<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_access_admin(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user);
        $this->get(route('admin.dashboard'))->assertStatus(403);
        $this->get(route('admin.users.index'))->assertStatus(403);
        $this->get(route('admin.settings.edit'))->assertStatus(403);
    }

    public function test_admin_can_access_dashboard(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);
        $this->get(route('admin.dashboard'))->assertStatus(200)->assertSee('Total User');
        $this->get(route('admin.users.index'))->assertStatus(200);
        $this->get(route('admin.settings.edit'))->assertStatus(200);
    }

    public function test_admin_can_see_user_count(): void
    {
        User::factory()->count(3)->create();
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);
        $response = $this->get(route('admin.dashboard'));
        $response->assertViewHas('totalUsers', 4);
        $response->assertViewHas('activeUsers', 4);
    }

    public function test_admin_can_toggle_role(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($admin);
        $this->post(route('admin.users.toggle', $user))->assertRedirect();
        $this->assertDatabaseHas('users', ['id' => $user->id, 'role' => 'admin']);
        $this->post(route('admin.users.toggle', $user))->assertRedirect();
        $this->assertDatabaseHas('users', ['id' => $user->id, 'role' => 'user']);
    }

    public function test_admin_cannot_toggle_self(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);
        $this->post(route('admin.users.toggle', $admin))->assertRedirect();
        $this->assertDatabaseHas('users', ['id' => $admin->id, 'role' => 'admin']);
    }

    public function test_admin_can_update_hero_and_icon(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $icon = UploadedFile::fake()->image('icon.png', 512, 512);

        $response = $this->patch(route('admin.settings.update'), [
            'site_name' => 'JoyTrack Custom',
            'hero_title' => 'Judul Baru',
            'hero_subtitle' => 'Subtitle baru',
            'site_icon' => $icon,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('site_settings', ['key' => 'site_name', 'value' => 'JoyTrack Custom']);
        $this->assertDatabaseHas('site_settings', ['key' => 'hero_title', 'value' => 'Judul Baru']);
        Storage::disk('public')->assertExists('settings/' . $icon->hashName());
        // icons generated
        $this->assertFileExists(public_path('icons/icon-192x192.png'));
    }

    public function test_welcome_shows_dynamic_hero(): void
    {
        \App\Models\SiteSetting::set('hero_title', 'Hero Custom Title');
        $response = $this->get(route('home'));
        $response->assertStatus(200)->assertSee('Hero Custom Title');
    }

    public function test_guest_cannot_access_admin(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }
}
