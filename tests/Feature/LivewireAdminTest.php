<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LivewireAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_component_counts_users(): void
    {
        User::factory()->count(3)->create();
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin);

        $component = Livewire::test('admin-dashboard');
        $this->assertSame(4, $component->viewData('totalUsers'));
        $this->assertSame(4, $component->viewData('activeUsers'));
        $this->assertSame(1, $component->viewData('adminUsers'));
    }

    public function test_users_component_filters_by_search(): void
    {
        User::factory()->create(['name' => 'Alice Wonder', 'email' => 'alice@example.com']);
        User::factory()->create(['name' => 'Bob Builder', 'email' => 'bob@example.com']);
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin);

        $users = Livewire::test('admin-users-index')->viewData('users');
        $this->assertSame(3, $users->total());

        $users = Livewire::test('admin-users-index')->set('search', 'Alice')->viewData('users');
        $this->assertSame(1, $users->total());
        $this->assertSame('alice@example.com', $users->first()->email);
    }

    public function test_settings_component_passes_settings_to_view(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin);

        $settings = [
            'site_name' => 'JoyTrack',
            'hero_title' => 'Judul',
            'hero_subtitle' => 'Subjudul',
            'site_icon' => null,
        ];

        $component = Livewire::test('admin-settings-edit', ['settings' => $settings]);
        $this->assertSame($settings, $component->viewData('settings'));
    }
}
