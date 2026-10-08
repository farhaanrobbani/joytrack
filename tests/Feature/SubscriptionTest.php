<?php

namespace Tests\Feature;

use App\Models\Subscription;
use App\Models\User;
use App\Services\ExpiryReminderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_subscription_index(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get(route('subscriptions.index'))->assertStatus(200);
    }

    public function test_guest_cannot_access_subscriptions(): void
    {
        $this->get(route('subscriptions.index'))->assertRedirect(route('login'));
    }

    public function test_user_can_create_subscription(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post(route('subscriptions.store'), [
            'name' => 'Netflix',
            'amount' => 186000,
            'renewal_cycle' => 'monthly',
            'next_renewal_date' => now()->addDays(20)->format('Y-m-d'),
            'reminder_days' => 7,
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('subscriptions.index'));
        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $user->id,
            'name' => 'Netflix',
            'amount' => 186000,
        ]);
    }

    public function test_user_can_update_subscription(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $subscription = Subscription::factory()->create(['user_id' => $user->id]);

        $response = $this->patch(route('subscriptions.update', $subscription), [
            'name' => 'Netflix Premium',
            'amount' => 200000,
            'renewal_cycle' => $subscription->renewal_cycle,
            'next_renewal_date' => $subscription->next_renewal_date->format('Y-m-d'),
            'reminder_days' => 10,
        ]);

        $response->assertRedirect(route('subscriptions.index'));
        $this->assertDatabaseHas('subscriptions', ['id' => $subscription->id, 'name' => 'Netflix Premium']);
    }

    public function test_user_can_delete_subscription(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $subscription = Subscription::factory()->create(['user_id' => $user->id]);

        $this->delete(route('subscriptions.destroy', $subscription))->assertRedirect(route('subscriptions.index'));
        $this->assertDatabaseCount('subscriptions', 0);
    }

    public function test_user_cannot_edit_others_subscription(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $subscription = Subscription::factory()->create(['user_id' => $user1->id]);

        $this->actingAs($user2);

        $this->get(route('subscriptions.edit', $subscription))->assertStatus(403);
        $this->delete(route('subscriptions.destroy', $subscription))->assertStatus(403);
    }

    public function test_subscription_ownership_isolation(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        Subscription::factory()->create(['user_id' => $user1->id]);
        Subscription::factory()->create(['user_id' => $user2->id]);

        $this->actingAs($user1);
        $response = $this->get(route('subscriptions.index'));

        $response->assertViewHas('subscriptions', fn ($subscriptions) => $subscriptions->count() === 1);
    }

    public function test_store_requires_next_renewal_date(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post(route('subscriptions.store'), [
            'name' => 'Spotify',
            'reminder_days' => 7,
        ])->assertSessionHasErrors('next_renewal_date');
    }

    public function test_subscription_reminder_overdue(): void
    {
        $user = User::factory()->create();
        Subscription::factory()->create([
            'user_id' => $user->id,
            'next_renewal_date' => now()->subDays(2)->format('Y-m-d'),
            'reminder_days' => 7,
        ]);

        $reminders = app(ExpiryReminderService::class)->getSubscriptionReminders($user->id);

        $this->assertCount(1, $reminders);
        $this->assertEquals('overdue', $reminders->first()['status']);
        $this->assertEquals('subscription', $reminders->first()['source']);
    }

    public function test_subscription_reminder_due_soon(): void
    {
        $user = User::factory()->create();
        Subscription::factory()->create([
            'user_id' => $user->id,
            'next_renewal_date' => now()->addDays(3)->format('Y-m-d'),
            'reminder_days' => 7,
        ]);

        $reminders = app(ExpiryReminderService::class)->getSubscriptionReminders($user->id);

        $this->assertCount(1, $reminders);
        $this->assertEquals('due_soon', $reminders->first()['status']);
    }

    public function test_subscription_reminder_ok_when_far(): void
    {
        $user = User::factory()->create();
        Subscription::factory()->create([
            'user_id' => $user->id,
            'next_renewal_date' => now()->addMonths(6)->format('Y-m-d'),
            'reminder_days' => 7,
        ]);

        $this->assertCount(0, app(ExpiryReminderService::class)->getSubscriptionReminders($user->id));
    }

    public function test_inactive_subscription_is_excluded_from_reminders(): void
    {
        $user = User::factory()->create();
        Subscription::factory()->create([
            'user_id' => $user->id,
            'next_renewal_date' => now()->subDay()->format('Y-m-d'),
            'is_active' => false,
        ]);

        $this->assertCount(0, app(ExpiryReminderService::class)->getSubscriptionReminders($user->id));
    }
}
