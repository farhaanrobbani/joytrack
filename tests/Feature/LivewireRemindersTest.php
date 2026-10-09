<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Document;
use App\Models\Subscription;
use App\Models\User;
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

        $component = Livewire::test('reminders-index')->set('status', 'overdue');
        $this->assertCount(1, $component->viewData('documents'));
        $component->assertSee('Dokumen Terlambat')->assertDontSee('Dokumen Segera');
    }
}
