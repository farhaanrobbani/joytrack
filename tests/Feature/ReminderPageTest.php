<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\ServiceRecord;
use App\Models\Subscription;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReminderPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_reminder_page_requires_auth(): void
    {
        $this->get(route('reminders.index'))->assertRedirect(route('login'));
    }

    public function test_reminder_page_shows_all_sections(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['user_id' => $user->id, 'current_odometer' => 40000]);
        ServiceRecord::factory()->create([
            'user_id' => $user->id,
            'vehicle_id' => $vehicle->id,
            'service_date' => now()->format('Y-m-d'),
            'next_service_date' => now()->subDay()->format('Y-m-d'),
            'next_service_odometer' => null,
        ]);
        Document::factory()->create([
            'user_id' => $user->id,
            'name' => 'STNK Motor',
            'expiry_date' => now()->addDays(3)->format('Y-m-d'),
            'reminder_days' => 7,
        ]);
        Subscription::factory()->create([
            'user_id' => $user->id,
            'name' => 'Domain JoyTrack',
            'next_renewal_date' => now()->subDays(10)->format('Y-m-d'),
            'reminder_days' => 7,
        ]);

        $this->actingAs($user);
        $response = $this->get(route('reminders.index'));

        $response->assertStatus(200);
        $response->assertViewHas('services', fn ($s) => $s->count() === 1);
        $response->assertViewHas('documents', fn ($d) => $d->count() === 1);
        $response->assertViewHas('subscriptions', fn ($s) => $s->count() === 1);
        $response->assertViewHas('totalCount', 3);
        $response->assertViewHas('overdueCount', 2);
        $response->assertViewHas('dueSoonCount', 1);
        $response->assertSee('STNK Motor');
        $response->assertSee('Domain JoyTrack');
    }

    public function test_reminder_page_status_filter_overdue(): void
    {
        $user = User::factory()->create();
        Document::factory()->create([
            'user_id' => $user->id,
            'name' => 'Dokumen Terlambat',
            'expiry_date' => now()->subDays(2)->format('Y-m-d'),
        ]);
        Subscription::factory()->create([
            'user_id' => $user->id,
            'name' => 'Langganan Aman',
            'next_renewal_date' => now()->addDays(2)->format('Y-m-d'),
            'reminder_days' => 7,
        ]);

        $this->actingAs($user);
        $response = $this->get(route('reminders.index', ['status' => 'overdue']));

        $response->assertStatus(200);
        $response->assertViewHas('documents', fn ($d) => $d->count() === 1);
        $response->assertViewHas('subscriptions', fn ($s) => $s->count() === 0);
        $response->assertSee('Dokumen Terlambat');
        $response->assertDontSee('Langganan Aman');
    }

    public function test_reminder_page_shows_empty_state(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);
        $response = $this->get(route('reminders.index'));

        $response->assertStatus(200);
        $response->assertViewHas('totalCount', 0);
        $response->assertSee(__('Tidak ada pengingat aktif'));
    }

    public function test_reminder_page_has_navigation_to_manage_items(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);
        $response = $this->get(route('reminders.index'));

        $response->assertStatus(200);
        $response->assertSee(route('documents.index'));
        $response->assertSee(route('subscriptions.index'));
        $response->assertSee(route('documents.create'));
        $response->assertSee(route('subscriptions.create'));
        $response->assertSee(__('+ Tambah Dokumen'));
        $response->assertSee(__('+ Tambah Berlangganan'));
    }

    public function test_sidebar_shows_reminder_section_links(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);
        $response = $this->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee(route('reminders.index'));
        $response->assertSee(route('documents.index'));
        $response->assertSee(route('subscriptions.index'));
    }

    public function test_dashboard_expiry_reminders_sorted_overdue_first(): void
    {
        $user = User::factory()->create();
        Document::factory()->create([
            'user_id' => $user->id,
            'name' => 'Dokumen Segera',
            'expiry_date' => now()->addDays(2)->format('Y-m-d'),
            'reminder_days' => 7,
        ]);
        Subscription::factory()->create([
            'user_id' => $user->id,
            'name' => 'Langganan Terlambat',
            'next_renewal_date' => now()->subDays(3)->format('Y-m-d'),
        ]);

        $this->actingAs($user);
        $response = $this->get(route('dashboard'));

        $response->assertStatus(200);
        $reminders = $response->viewData('expiryReminders');
        $this->assertCount(2, $reminders);
        $this->assertEquals('Langganan Terlambat', $reminders->first()['title']);
        $this->assertEquals('overdue', $reminders->first()['status']);
    }
}
