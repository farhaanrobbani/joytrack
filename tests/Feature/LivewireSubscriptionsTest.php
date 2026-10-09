<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LivewireSubscriptionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_subscription(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test('subscriptions-create')
            ->set('name', 'Netflix')
            ->set('amount', '49000')
            ->set('renewal_cycle', 'monthly')
            ->set('next_renewal_date', now()->addDays(10)->format('Y-m-d'))
            ->set('reminder_days', '7')
            ->set('notes', 'Paket premium')
            ->call('save')
            ->assertRedirect(route('subscriptions.index'));

        $this->assertSame(__('Berlangganan berhasil dibuat.'), app('session')->get('status'));
        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $user->id,
            'name' => 'Netflix',
            'renewal_cycle' => 'monthly',
            'reminder_days' => 7,
            'is_active' => true,
        ]);
    }

    public function test_create_requires_name_and_next_renewal_date(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('subscriptions-create')
            ->call('save')
            ->assertHasErrors(['name', 'next_renewal_date']);

        $this->assertDatabaseCount('subscriptions', 0);
    }

    public function test_edit_subscription(): void
    {
        $user = User::factory()->create();
        $subscription = Subscription::factory()->create([
            'user_id' => $user->id,
            'name' => 'Spotify',
            'amount' => 54999,
        ]);
        $this->actingAs($user);

        Livewire::test('subscriptions-edit', ['subscription' => $subscription])
            ->set('name', 'Spotify Duo')
            ->set('amount', '69999')
            ->set('renewal_cycle', 'yearly')
            ->call('save')
            ->assertRedirect(route('subscriptions.index'));

        $this->assertSame(__('Berlangganan berhasil diperbarui.'), app('session')->get('status'));
        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscription->id,
            'name' => 'Spotify Duo',
            'renewal_cycle' => 'yearly',
        ]);
    }

    public function test_edit_is_forbidden_for_other_users(): void
    {
        $user = User::factory()->create();
        $subscription = Subscription::factory()->create(['user_id' => $user->id]);
        $this->actingAs(User::factory()->create());
        $this->withoutExceptionHandling();

        $this->expectException(AuthorizationException::class);
        Livewire::test('subscriptions-edit', ['subscription' => $subscription]);
    }

    public function test_renew_from_index_with_transaction(): void
    {
        $user = User::factory()->create();
        $subscription = Subscription::factory()->create([
            'user_id' => $user->id,
            'amount' => 50000,
            'renewal_cycle' => 'monthly',
            'next_renewal_date' => now()->addDays(10),
        ]);
        $account = Account::factory()->create(['user_id' => $user->id, 'current_balance' => 1000000]);
        $this->actingAs($user);

        Livewire::test('subscriptions-index')
            ->call('openRenew', $subscription->id)
            ->set('renewAccountId', (string) $account->id)
            ->call('saveRenew')
            ->assertRedirect(route('subscriptions.index'));

        $this->assertSame(
            __('Berlangganan berhasil diperpanjang hingga :date.', ['date' => $subscription->refresh()->next_renewal_date->format('d M Y')]),
            app('session')->get('status'),
        );
        $account->refresh();
        $this->assertEquals(950000, (float) $account->current_balance);
        $this->assertDatabaseCount('subscription_renewals', 1);
    }

    public function test_renew_requires_account_when_creating_transaction(): void
    {
        $user = User::factory()->create();
        $subscription = Subscription::factory()->create([
            'user_id' => $user->id,
            'amount' => 50000,
            'next_renewal_date' => now()->addDays(5),
        ]);
        $this->actingAs($user);

        Livewire::test('subscriptions-index')
            ->call('openRenew', $subscription->id)
            ->set('renewAccountId', '')
            ->call('saveRenew')
            ->assertHasErrors(['account_id' => __('Akun wajib jika membuat transaksi keuangan.')]);

        $this->assertDatabaseCount('subscription_renewals', 0);
    }

    public function test_renew_requires_amount_when_subscription_amount_is_null(): void
    {
        $user = User::factory()->create();
        $subscription = Subscription::factory()->create([
            'user_id' => $user->id,
            'amount' => null,
            'next_renewal_date' => now()->addDays(5),
        ]);
        $account = Account::factory()->create(['user_id' => $user->id]);
        $this->actingAs($user);

        Livewire::test('subscriptions-index')
            ->call('openRenew', $subscription->id)
            ->set('renewAccountId', (string) $account->id)
            ->call('saveRenew')
            ->assertHasErrors(['amount' => __('Nominal wajib jika membuat transaksi keuangan.')]);

        $this->assertDatabaseCount('subscription_renewals', 0);
    }

    public function test_renew_is_forbidden_for_other_users(): void
    {
        $user = User::factory()->create();
        $subscription = Subscription::factory()->create(['user_id' => $user->id]);
        $this->actingAs(User::factory()->create());
        $this->withoutExceptionHandling();

        $this->expectException(AuthorizationException::class);
        Livewire::test('subscriptions-index')->call('openRenew', $subscription->id);
    }
}
