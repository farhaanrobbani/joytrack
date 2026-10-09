<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_requires_auth(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_dashboard_shows_totals(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->create(['user_id' => $user->id, 'initial_balance' => 1000000, 'current_balance' => 1500000]);
        $catInc = Category::factory()->create(['user_id' => $user->id, 'type' => 'income']);
        $catExp = Category::factory()->create(['user_id' => $user->id, 'type' => 'expense']);

        // current month transactions
        Transaction::factory()->create(['user_id' => $user->id, 'account_id' => $account->id, 'category_id' => $catInc->id, 'type' => 'income', 'amount' => 2000000, 'transaction_date' => now()->format('Y-m-d')]);
        Transaction::factory()->create(['user_id' => $user->id, 'account_id' => $account->id, 'category_id' => $catExp->id, 'type' => 'expense', 'amount' => 500000, 'transaction_date' => now()->format('Y-m-d')]);

        $this->actingAs($user);
        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);

        $component = Livewire::test('dashboard-index');
        $this->assertNotNull($component->viewData('totalBalance'));
        $this->assertNotNull($component->viewData('monthlyIncome'));
        $this->assertNotNull($component->viewData('monthlyExpense'));
        $this->assertNotNull($component->viewData('netCashflow'));
        $this->assertNotNull($component->viewData('recentTransactions'));
        $this->assertNotNull($component->viewData('cashflowChart'));
        $this->assertNotNull($component->viewData('expenseByCategory'));
    }

    public function test_dashboard_splits_asset_balance_and_credit_debt(): void
    {
        $user = User::factory()->create();
        Account::factory()->create(['user_id' => $user->id, 'type' => 'bank', 'initial_balance' => 1000000, 'current_balance' => 1000000]);
        Account::factory()->create(['user_id' => $user->id, 'type' => 'cash', 'initial_balance' => 200000, 'current_balance' => 200000]);
        Account::factory()->create(['user_id' => $user->id, 'type' => 'credit', 'initial_balance' => 0, 'current_balance' => -300000]);

        $this->actingAs($user);

        $component = Livewire::test('dashboard-index');
        $this->assertSame(1200000.0, (float) $component->viewData('assetBalance'));
        $this->assertSame(300000.0, (float) $component->viewData('creditDebt'));
        $this->assertSame(900000.0, (float) $component->viewData('totalBalance'));
        $component->assertSee('Saldo Aset')->assertSee('Utang Kartu');
    }

    public function test_dashboard_isolation(): void
    {
        $u1 = User::factory()->create();
        $u2 = User::factory()->create();
        $acc1 = Account::factory()->create(['user_id' => $u1->id, 'current_balance' => 999999]);
        $acc2 = Account::factory()->create(['user_id' => $u2->id, 'current_balance' => 111]);
        $this->actingAs($u1);
        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);
        // totalBalance should reflect only u1
        $this->assertEquals(999999, (float) Livewire::test('dashboard-index')->viewData('totalBalance'));
    }

    public function test_recent_transactions_limit(): void
    {
        $user = User::factory()->create();
        $acc = Account::factory()->create(['user_id' => $user->id]);
        $cat = Category::factory()->create(['user_id' => $user->id, 'type' => 'expense']);
        Transaction::factory()->count(10)->create(['user_id' => $user->id, 'account_id' => $acc->id, 'category_id' => $cat->id, 'type' => 'expense']);
        $this->actingAs($user);
        $this->get(route('dashboard'))->assertStatus(200);
        $this->assertCount(5, Livewire::test('dashboard-index')->viewData('recentTransactions'));
    }
}
