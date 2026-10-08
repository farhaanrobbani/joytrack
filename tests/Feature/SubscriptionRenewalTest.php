<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Subscription;
use App\Models\SubscriptionRenewal;
use App\Models\User;
use App\Services\SubscriptionRenewalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionRenewalTest extends TestCase
{
    use RefreshDatabase;

    private function makeSubscription(User $user, array $overrides = []): Subscription
    {
        return Subscription::factory()->create(array_merge([
            'user_id' => $user->id,
            'renewal_cycle' => 'monthly',
            'amount' => 186000,
            'next_renewal_date' => now()->addDays(20),
            'is_active' => true,
        ], $overrides));
    }

    public function test_renew_shifts_next_date_by_monthly_cycle(): void
    {
        $this->travelTo('2026-01-10 08:00:00');
        $user = User::factory()->create();
        $this->actingAs($user);

        $subscription = $this->makeSubscription($user, ['next_renewal_date' => '2026-03-31']);

        $this->post(route('subscriptions.renew', $subscription))
            ->assertRedirect(route('subscriptions.index'));

        $subscription->refresh();
        $this->assertEquals('2026-04-30', $subscription->next_renewal_date->format('Y-m-d'));
        $renewal = SubscriptionRenewal::where('subscription_id', $subscription->id)->firstOrFail();
        $this->assertEquals('2026-03-31', $renewal->previous_date->format('Y-m-d'));
        $this->assertEquals('2026-04-30', $renewal->new_date->format('Y-m-d'));
    }

    public function test_renew_adds_quarterly_and_yearly_cycles(): void
    {
        $this->travelTo('2026-01-10 08:00:00');
        $user = User::factory()->create();
        $service = app(SubscriptionRenewalService::class);

        $quarterly = $this->makeSubscription($user, [
            'renewal_cycle' => 'quarterly',
            'next_renewal_date' => '2026-03-31',
        ]);
        $service->renew($quarterly, []);
        $this->assertEquals('2026-06-30', $quarterly->fresh()->next_renewal_date->format('Y-m-d'));

        $yearly = $this->makeSubscription($user, [
            'renewal_cycle' => 'yearly',
            'next_renewal_date' => '2026-03-31',
        ]);
        $service->renew($yearly, []);
        $this->assertEquals('2027-03-31', $yearly->fresh()->next_renewal_date->format('Y-m-d'));
    }

    public function test_renew_overdue_counts_from_today(): void
    {
        $this->travelTo('2026-01-10 08:00:00');
        $user = User::factory()->create();
        $this->actingAs($user);

        $subscription = $this->makeSubscription($user, ['next_renewal_date' => '2025-12-01']);

        $this->post(route('subscriptions.renew', $subscription));

        $this->assertEquals('2026-02-10', $subscription->fresh()->next_renewal_date->format('Y-m-d'));
        $renewal = SubscriptionRenewal::where('subscription_id', $subscription->id)->firstOrFail();
        $this->assertEquals('2025-12-01', $renewal->previous_date->format('Y-m-d'));
        $this->assertEquals('2026-02-10', $renewal->new_date->format('Y-m-d'));
    }

    public function test_renew_records_history_and_shows_status_message(): void
    {
        $this->travelTo('2026-01-10 08:00:00');
        $user = User::factory()->create();
        $this->actingAs($user);

        $subscription = $this->makeSubscription($user, ['next_renewal_date' => '2026-02-01']);

        $response = $this->post(route('subscriptions.renew', $subscription), [
            'notes' => 'bayar tahunan',
        ]);

        $response->assertRedirect(route('subscriptions.index'));
        $response->assertSessionHas('status');

        $this->assertDatabaseCount('subscription_renewals', 1);
        $this->assertDatabaseHas('subscription_renewals', [
            'subscription_id' => $subscription->id,
            'user_id' => $user->id,
            'amount' => 186000,
            'notes' => 'bayar tahunan',
        ]);
    }

    public function test_renew_with_transaction_creates_expense_and_updates_balance(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $account = Account::factory()->create([
            'user_id' => $user->id,
            'initial_balance' => 1000000,
            'current_balance' => 1000000,
        ]);
        $subscription = $this->makeSubscription($user);

        $this->post(route('subscriptions.renew', $subscription), [
            'create_transaction' => 1,
            'account_id' => $account->id,
            'amount' => 150000,
            'notes' => 'promo',
        ])->assertRedirect(route('subscriptions.index'));

        $account->refresh();
        $this->assertEquals(850000, (float) $account->current_balance);

        $renewal = SubscriptionRenewal::where('subscription_id', $subscription->id)->firstOrFail();
        $this->assertNotNull($renewal->transaction_id);
        $this->assertEquals($account->id, $renewal->account_id);

        $this->assertDatabaseHas('transactions', [
            'id' => $renewal->transaction_id,
            'user_id' => $user->id,
            'account_id' => $account->id,
            'type' => 'expense',
            'amount' => 150000,
        ]);

        $category = $renewal->transaction->category;
        $this->assertNotNull($category);
        $this->assertEquals('Langganan', $category->name);
        $this->assertEquals('expense', $category->type);
    }

    public function test_renew_without_transaction_skips_expense(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $account = Account::factory()->create([
            'user_id' => $user->id,
            'initial_balance' => 1000000,
            'current_balance' => 1000000,
        ]);
        $subscription = $this->makeSubscription($user);

        $this->post(route('subscriptions.renew', $subscription))->assertRedirect(route('subscriptions.index'));

        $this->assertEquals(1000000, (float) $account->fresh()->current_balance);
        $this->assertDatabaseCount('transactions', 0);

        $renewal = SubscriptionRenewal::where('subscription_id', $subscription->id)->firstOrFail();
        $this->assertNull($renewal->transaction_id);
        $this->assertNull($renewal->account_id);
    }

    public function test_renew_requires_account_when_creating_transaction(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $subscription = $this->makeSubscription($user);

        $this->post(route('subscriptions.renew', $subscription), [
            'create_transaction' => 1,
            'amount' => 186000,
        ])->assertSessionHasErrors('account_id');

        $this->assertDatabaseCount('subscription_renewals', 0);
    }

    public function test_renew_requires_amount_when_subscription_amount_is_null(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $account = Account::factory()->create(['user_id' => $user->id]);
        $subscription = $this->makeSubscription($user, ['amount' => null]);

        $this->post(route('subscriptions.renew', $subscription), [
            'create_transaction' => 1,
            'account_id' => $account->id,
        ])->assertSessionHasErrors('amount');
    }

    public function test_renew_rejects_other_users_account(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $foreignAccount = Account::factory()->create(['user_id' => $other->id]);
        $this->actingAs($user);

        $subscription = $this->makeSubscription($user);

        $this->post(route('subscriptions.renew', $subscription), [
            'create_transaction' => 1,
            'account_id' => $foreignAccount->id,
            'amount' => 186000,
        ])->assertSessionHasErrors('account_id');
    }

    public function test_user_cannot_renew_others_subscription(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $subscription = Subscription::factory()->create(['user_id' => $user1->id]);

        $this->actingAs($user2);

        $this->post(route('subscriptions.renew', $subscription))->assertStatus(403);
        $this->assertDatabaseCount('subscription_renewals', 0);
    }

    public function test_subscription_index_shows_renew_controls(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $subscription = $this->makeSubscription($user);

        $this->get(route('subscriptions.index'))
            ->assertOk()
            ->assertSee('window.JT_SUBSCRIPTIONS')
            ->assertSee('renew-subscription')
            ->assertSee('openRenew(window.JT_SUBSCRIPTIONS['.(int) $subscription->id.'])', false);
    }

    public function test_subscription_edit_shows_renewal_history(): void
    {
        $this->travelTo('2026-01-10 08:00:00');
        $user = User::factory()->create();
        $this->actingAs($user);

        $subscription = $this->makeSubscription($user, ['next_renewal_date' => '2026-02-01']);
        app(SubscriptionRenewalService::class)->renew($subscription, ['notes' => 'awal']);

        $this->get(route('subscriptions.edit', $subscription))
            ->assertOk()
            ->assertSee('Riwayat Perpanjangan')
            ->assertSee('10 Jan 2026')
            ->assertSee('01 Feb 2026')
            ->assertSee('01 Mar 2026')
            ->assertDontSee('Belum ada riwayat perpanjangan.');
    }

    public function test_subscription_edit_shows_empty_history_state(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $subscription = $this->makeSubscription($user);

        $this->get(route('subscriptions.edit', $subscription))
            ->assertOk()
            ->assertSee('Belum ada riwayat perpanjangan.');
    }
}
