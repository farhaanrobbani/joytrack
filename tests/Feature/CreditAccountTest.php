<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Category;
use App\Models\User;
use App\Services\ExpiryReminderService;
use App\Services\TransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CreditAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_credit_account(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post(route('accounts.store'), [
            'name' => 'Amex Platinum',
            'type' => 'credit',
            'initial_balance' => 0,
            'credit_limit' => 5000000,
            'billing_day' => 1,
            'due_day' => 10,
            'is_active' => true,
        ]);

        $response->assertRedirect(route('accounts.index'));
        $this->assertDatabaseHas('accounts', [
            'user_id' => $user->id,
            'name' => 'Amex Platinum',
            'type' => 'credit',
            'credit_limit' => 5000000,
            'billing_day' => 1,
            'due_day' => 10,
            'current_balance' => 0,
        ]);
    }

    public function test_credit_account_validates_limit_and_due_day(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post(route('accounts.store'), [
            'name' => 'Kartu Paylater',
            'type' => 'credit',
            'initial_balance' => 0,
            'credit_limit' => -100,
            'billing_day' => 32,
            'due_day' => 0,
        ]);

        $response->assertSessionHasErrors(['credit_limit', 'billing_day', 'due_day']);
        $this->assertDatabaseCount('accounts', 0);
    }

    public function test_expense_to_credit_account_makes_balance_negative(): void
    {
        $user = User::factory()->create();
        $credit = Account::factory()->create([
            'user_id' => $user->id,
            'type' => 'credit',
            'initial_balance' => 0,
            'current_balance' => 0,
            'credit_limit' => 2000000,
        ]);
        $expenseCategory = Category::factory()->create(['user_id' => $user->id, 'type' => 'expense']);

        app(TransactionService::class)->create([
            'user_id' => $user->id,
            'account_id' => $credit->id,
            'category_id' => $expenseCategory->id,
            'type' => 'expense',
            'amount' => 500000,
            'transaction_date' => '2026-10-01',
        ]);

        $credit->refresh();
        $this->assertSame(-500000.0, (float) $credit->current_balance);
        $this->assertSame(500000.0, (float) $credit->used_credit);
        $this->assertSame(1500000.0, (float) $credit->available_credit);
    }

    public function test_transfer_from_bank_pays_credit_bill(): void
    {
        $user = User::factory()->create();
        $bank = Account::factory()->create([
            'user_id' => $user->id,
            'type' => 'bank',
            'initial_balance' => 1000000,
            'current_balance' => 1000000,
        ]);
        $credit = Account::factory()->create([
            'user_id' => $user->id,
            'type' => 'credit',
            'initial_balance' => 0,
            'current_balance' => -500000,
            'credit_limit' => 2000000,
        ]);

        app(TransactionService::class)->create([
            'user_id' => $user->id,
            'account_id' => $bank->id,
            'destination_account_id' => $credit->id,
            'type' => 'transfer',
            'amount' => 500000,
            'transaction_date' => '2026-10-05',
        ]);

        $this->assertSame(0.0, (float) $credit->refresh()->current_balance);
        $this->assertSame(500000.0, (float) $bank->refresh()->current_balance);
    }

    public function test_credit_reminder_shows_when_bill_is_unpaid_and_near_due(): void
    {
        $this->travelTo('2026-10-09 10:00:00');
        $user = User::factory()->create();
        Account::factory()->create([
            'user_id' => $user->id,
            'type' => 'credit',
            'name' => 'Amex Platinum',
            'initial_balance' => 0,
            'current_balance' => -750000,
            'due_day' => 12,
        ]);

        $reminders = app(ExpiryReminderService::class)->getCreditReminders($user->id);

        $this->assertCount(1, $reminders);
        $this->assertSame('credit', $reminders[0]['source']);
        $this->assertSame('Tagihan Amex Platinum', $reminders[0]['title']);
        $this->assertSame('due_soon', $reminders[0]['status']);
        $this->assertSame(3, $reminders[0]['days']);
    }

    public function test_credit_reminder_shows_overdue_and_hides_when_paid(): void
    {
        $this->travelTo('2026-10-09 10:00:00');
        $user = User::factory()->create();
        $credit = Account::factory()->create([
            'user_id' => $user->id,
            'type' => 'credit',
            'initial_balance' => 0,
            'current_balance' => -750000,
            'due_day' => 2,
        ]);
        $service = app(ExpiryReminderService::class);

        $this->assertSame('overdue', $service->getCreditReminders($user->id)[0]['status']);

        $credit->update(['current_balance' => 0]);
        $this->assertCount(0, $service->getCreditReminders($user->id));
    }

    public function test_credit_reminder_not_shown_when_due_date_is_far_or_missing(): void
    {
        $this->travelTo('2026-10-09 10:00:00');
        $user = User::factory()->create();
        Account::factory()->create([
            'user_id' => $user->id,
            'type' => 'credit',
            'initial_balance' => 0,
            'current_balance' => -750000,
            'due_day' => 31,
        ]);
        Account::factory()->create([
            'user_id' => $user->id,
            'type' => 'credit',
            'initial_balance' => 0,
            'current_balance' => -750000,
            'due_day' => null,
        ]);

        $this->assertCount(0, app(ExpiryReminderService::class)->getCreditReminders($user->id));
    }

    public function test_reminders_and_dashboard_include_credit_bills(): void
    {
        $this->travelTo('2026-10-09 10:00:00');
        $user = User::factory()->create();
        Account::factory()->create([
            'user_id' => $user->id,
            'type' => 'credit',
            'name' => 'Amex Platinum',
            'initial_balance' => 0,
            'current_balance' => -750000,
            'due_day' => 10,
        ]);
        $this->actingAs($user);

        $component = Livewire::test('reminders-index');
        $this->assertCount(1, $component->viewData('credits'));
        $this->assertSame(1, $component->viewData('totalCount'));
        $component->assertSee('Tagihan Amex Platinum');

        $dashboard = Livewire::test('dashboard-index')->viewData('expiryReminders');
        $this->assertCount(1, $dashboard->where('source', 'credit'));
    }

    public function test_account_show_page_displays_credit_info(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->create([
            'user_id' => $user->id,
            'type' => 'credit',
            'name' => 'Amex Platinum',
            'initial_balance' => 0,
            'current_balance' => -750000,
            'credit_limit' => 5000000,
            'billing_day' => 1,
            'due_day' => 10,
        ]);
        $this->actingAs($user);

        $response = $this->get(route('accounts.show', $account));

        $response->assertOk();
        $response->assertSee('Detail Kartu Kredit / Paylater');
        $response->assertSee('Limit Kredit');
        $response->assertSee('5.000.000', false);
        $response->assertSee('750.000', false);
        $response->assertSee('4.250.000', false);
    }
}
