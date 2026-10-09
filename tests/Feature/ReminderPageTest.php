<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\ServiceRecord;
use App\Models\Subscription;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
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
        $response->assertSee('STNK Motor');
        $response->assertSee('Domain JoyTrack');

        $component = Livewire::test('reminders-index');
        $this->assertCount(1, $component->viewData('services'));
        $this->assertCount(1, $component->viewData('documents'));
        $this->assertCount(1, $component->viewData('subscriptions'));
        $this->assertSame(3, $component->viewData('totalCount'));
        $this->assertSame(2, $component->viewData('overdueCount'));
        $this->assertSame(1, $component->viewData('dueSoonCount'));
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
        $response->assertSee('Dokumen Terlambat');
        $response->assertDontSee('Langganan Aman');

        $component = Livewire::test('reminders-index')->set('status', 'overdue');
        $this->assertCount(1, $component->viewData('documents'));
        $this->assertCount(0, $component->viewData('subscriptions'));
    }

    public function test_reminder_page_shows_empty_state(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);
        $response = $this->get(route('reminders.index'));

        $response->assertStatus(200);
        $response->assertSee(__('Tidak ada pengingat aktif'));
        $this->assertSame(0, Livewire::test('reminders-index')->viewData('totalCount'));
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

    public function test_reminder_page_shows_renew_button_and_modal_for_subscription(): void
    {
        $user = User::factory()->create();
        $subscription = Subscription::factory()->create([
            'user_id' => $user->id,
            'name' => 'Netflix',
            'next_renewal_date' => now()->subDay()->format('Y-m-d'),
            'reminder_days' => 7,
        ]);

        $this->actingAs($user);
        $this->get(route('reminders.index'))->assertStatus(200);

        Livewire::test('reminders-index')
            ->assertSee('openRenew('.(int) $subscription->id.')')
            ->assertSee(__('Perpanjang'));
    }

    public function test_reminder_page_without_subscription_reminders_has_no_renew_modal(): void
    {
        $user = User::factory()->create();
        Document::factory()->create([
            'user_id' => $user->id,
            'name' => 'Dokumen Aman',
            'expiry_date' => now()->addDays(3)->format('Y-m-d'),
            'reminder_days' => 7,
        ]);

        $this->actingAs($user);
        $response = $this->get(route('reminders.index'));

        $response->assertStatus(200);
        $component = Livewire::test('reminders-index');
        $component->assertDontSee('openRenew(');
        $this->assertCount(0, $component->viewData('subscriptions'));
    }

    public function test_renew_redirects_back_to_reminders_when_submitted_from_reminders(): void
    {
        $user = User::factory()->create();
        $subscription = Subscription::factory()->create([
            'user_id' => $user->id,
            'next_renewal_date' => now()->subDays(5)->format('Y-m-d'),
        ]);

        $this->actingAs($user);
        $response = $this->post(route('subscriptions.renew', $subscription), [
            'back' => 'reminders',
        ]);

        $response->assertRedirect(route('reminders.index'));
        $response->assertSessionHas('status');
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
        $reminders = Livewire::test('dashboard-index')->viewData('expiryReminders');
        $this->assertCount(2, $reminders);
        $this->assertEquals('Langganan Terlambat', $reminders->first()['title']);
        $this->assertEquals('overdue', $reminders->first()['status']);
    }
}
