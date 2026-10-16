<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Document;
use App\Models\ServiceRecord;
use App\Models\Subscription;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LivewireRemindersTest extends TestCase
{
    use RefreshDatabase;

    public function test_renew_from_reminders_with_transaction(): void
    {
        $user = User::factory()->create();
        $subscription = Subscription::factory()->create([
            'user_id' => $user->id,
            'amount' => 50000,
            'renewal_cycle' => 'monthly',
            'next_renewal_date' => now()->subDay(),
            'reminder_days' => 7,
        ]);
        $account = Account::factory()->create(['user_id' => $user->id, 'current_balance' => 1000000]);
        $this->actingAs($user);

        Livewire::test('reminders-index')
            ->call('openRenew', $subscription->id)
            ->set('renewAccountId', (string) $account->id)
            ->call('saveRenew')
            ->assertRedirect(route('reminders.index'));

        $this->assertSame(
            __('Berlangganan berhasil diperpanjang hingga :date.', ['date' => $subscription->refresh()->next_renewal_date->format('d M Y')]),
            app('session')->get('status'),
        );
        $account->refresh();
        $this->assertEquals(950000, (float) $account->current_balance);
        $this->assertDatabaseCount('subscription_renewals', 1);
    }

    public function test_renew_requires_account_from_reminders(): void
    {
        $user = User::factory()->create();
        $subscription = Subscription::factory()->create([
            'user_id' => $user->id,
            'amount' => 50000,
            'next_renewal_date' => now()->subDay(),
            'reminder_days' => 7,
        ]);
        $this->actingAs($user);

        Livewire::test('reminders-index')
            ->call('openRenew', $subscription->id)
            ->set('renewAccountId', '')
            ->call('saveRenew')
            ->assertHasErrors(['account_id' => __('Akun wajib jika membuat transaksi keuangan.')]);

        $this->assertDatabaseCount('subscription_renewals', 0);
    }

    public function test_renew_is_forbidden_for_other_users(): void
    {
        $user = User::factory()->create();
        $subscription = Subscription::factory()->create(['user_id' => $user->id]);
        $this->actingAs(User::factory()->create());
        $this->withoutExceptionHandling();

        $this->expectException(AuthorizationException::class);
        Livewire::test('reminders-index')->call('openRenew', $subscription->id);
    }

    public function test_status_filter_only_shows_matching_items(): void
    {
        $user = User::factory()->create();
        Document::factory()->create([
            'user_id' => $user->id,
            'name' => 'Dokumen Terlambat',
            'expiry_date' => now()->subDays(2),
        ]);
        Document::factory()->create([
            'user_id' => $user->id,
            'name' => 'Dokumen Segera',
            'expiry_date' => now()->addDays(3),
            'reminder_days' => 7,
        ]);
        $this->actingAs($user);

        $component = Livewire::test('reminders-index');
        $this->assertCount(2, $component->viewData('documents'));
        $this->assertSame(2, $component->viewData('totalCount'));
        $this->assertSame(1, $component->viewData('overdueCount'));
        $this->assertSame(1, $component->viewData('dueSoonCount'));

        $component->call('setStatus', 'overdue');
        $this->assertSame('overdue', $component->instance()->status);
        $this->assertCount(1, $component->viewData('documents'));
        $component->assertSee('Dokumen Terlambat')->assertDontSee('Dokumen Segera');
        $this->assertSame(2, $component->viewData('totalCount'));
        $this->assertSame(1, $component->viewData('overdueCount'));
        $this->assertSame(1, $component->viewData('dueSoonCount'));
    }

    public function test_ok_items_shown_with_aman_status(): void
    {
        $user = User::factory()->create();
        Document::factory()->create([
            'user_id' => $user->id,
            'name' => 'Dokumen Jauh',
            'expiry_date' => now()->addYear(),
            'reminder_days' => 7,
        ]);
        Subscription::factory()->create([
            'user_id' => $user->id,
            'name' => 'Berlangganan Jauh',
            'next_renewal_date' => now()->addMonths(9),
            'reminder_days' => 7,
        ]);
        $this->actingAs($user);

        $component = Livewire::test('reminders-index');
        $this->assertCount(1, $component->viewData('documents'));
        $this->assertCount(1, $component->viewData('subscriptions'));
        $this->assertSame(2, $component->viewData('totalCount'));
        $this->assertSame(0, $component->viewData('overdueCount'));
        $this->assertSame(0, $component->viewData('dueSoonCount'));
        $component->assertSee('Dokumen Jauh')->assertSee('Berlangganan Jauh')->assertSee('Aman');
    }

    public function test_status_filter_ok_shows_only_safe_items(): void
    {
        $user = User::factory()->create();
        Document::factory()->create([
            'user_id' => $user->id,
            'name' => 'Dokumen Terlambat',
            'expiry_date' => now()->subDays(2),
        ]);
        Document::factory()->create([
            'user_id' => $user->id,
            'name' => 'Dokumen Jauh',
            'expiry_date' => now()->addYear(),
            'reminder_days' => 7,
        ]);
        $this->actingAs($user);

        $component = Livewire::test('reminders-index')
            ->call('setStatus', 'ok');

        $this->assertSame('ok', $component->instance()->status);
        $this->assertCount(1, $component->viewData('documents'));
        $component->assertSee('Dokumen Jauh')->assertDontSee('Dokumen Terlambat');
    }

    public function test_ok_service_shown_on_reminders_page(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['user_id' => $user->id, 'current_odometer' => 40000]);
        ServiceRecord::factory()->create([
            'user_id' => $user->id,
            'vehicle_id' => $vehicle->id,
            'service_date' => now()->format('Y-m-d'),
            'next_service_date' => now()->addDays(60)->format('Y-m-d'),
            'next_service_odometer' => 80000,
        ]);
        $this->actingAs($user);

        $component = Livewire::test('reminders-index');
        $this->assertCount(1, $component->viewData('services'));
        $component->assertSee($vehicle->name)->assertSee('Aman');
    }

    public function test_dashboard_still_hides_ok_items(): void
    {
        $user = User::factory()->create();
        Document::factory()->create([
            'user_id' => $user->id,
            'name' => 'Dokumen Jauh',
            'expiry_date' => now()->addYear(),
            'reminder_days' => 7,
        ]);
        Subscription::factory()->create([
            'user_id' => $user->id,
            'name' => 'Berlangganan Jauh',
            'next_renewal_date' => now()->addMonths(9),
            'reminder_days' => 7,
        ]);
        $this->actingAs($user);

        $this->assertCount(0, Livewire::test('dashboard-index')->viewData('expiryReminders'));
    }
}
