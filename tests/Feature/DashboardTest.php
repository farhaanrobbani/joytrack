<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        $response->assertViewHas('totalBalance');
        $response->assertViewHas('monthlyIncome');
        $response->assertViewHas('monthlyExpense');
        $response->assertViewHas('netCashflow');
        $response->assertViewHas('recentTransactions');
        $response->assertViewHas('cashflowChart');
        $response->assertViewHas('expenseByCategory');
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
        $this->assertEquals(999999, (float) $response->viewData('totalBalance'));
    }

    public function test_recent_transactions_limit(): void
    {
        $user = User::factory()->create();
        $acc = Account::factory()->create(['user_id' => $user->id]);
        $cat = Category::factory()->create(['user_id' => $user->id, 'type' => 'expense']);
        Transaction::factory()->count(10)->create(['user_id' => $user->id, 'account_id' => $acc->id, 'category_id' => $cat->id, 'type' => 'expense']);
        $this->actingAs($user);
        $response = $this->get(route('dashboard'));
        $this->assertCount(5, $response->viewData('recentTransactions'));
    }
}
