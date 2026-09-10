<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    private function makeUserWithAccountAndCategory(string $catType = 'income'): array
    {
        $user = User::factory()->create();
        $account = Account::factory()->create(['user_id' => $user->id, 'initial_balance' => 1000000, 'current_balance' => 1000000]);
        $category = Category::factory()->create(['user_id' => $user->id, 'type' => $catType]);
        return [$user, $account, $category];
    }

    public function test_income_increases_balance(): void
    {
        [$user, $account, $cat] = $this->makeUserWithAccountAndCategory('income');
        $this->actingAs($user);

        $this->post(route('transactions.store'), [
            'type' => 'income',
            'account_id' => $account->id,
            'category_id' => $cat->id,
            'amount' => 500000,
            'transaction_date' => now()->format('Y-m-d'),
            'description' => 'Gaji',
        ])->assertRedirect(route('transactions.index'));

        $account->refresh();
        $this->assertEquals(1500000, (float) $account->current_balance);
        $this->assertDatabaseHas('transactions', ['user_id' => $user->id, 'type' => 'income', 'amount' => 500000]);
    }

    public function test_expense_decreases_balance(): void
    {
        [$user, $account, $cat] = $this->makeUserWithAccountAndCategory('expense');
        $this->actingAs($user);

        $this->post(route('transactions.store'), [
            'type' => 'expense',
            'account_id' => $account->id,
            'category_id' => $cat->id,
            'amount' => 200000,
            'transaction_date' => now()->format('Y-m-d'),
        ])->assertRedirect(route('transactions.index'));

        $account->refresh();
        $this->assertEquals(800000, (float) $account->current_balance);
    }

    public function test_transfer_moves_balance(): void
    {
        $user = User::factory()->create();
        $src = Account::factory()->create(['user_id' => $user->id, 'initial_balance' => 1000000, 'current_balance' => 1000000]);
        $dst = Account::factory()->create(['user_id' => $user->id, 'initial_balance' => 500000, 'current_balance' => 500000]);
        $this->actingAs($user);

        $this->post(route('transactions.store'), [
            'type' => 'transfer',
            'account_id' => $src->id,
            'destination_account_id' => $dst->id,
            'amount' => 300000,
            'transaction_date' => now()->format('Y-m-d'),
        ])->assertRedirect(route('transactions.index'));

        $src->refresh(); $dst->refresh();
        $this->assertEquals(700000, (float) $src->current_balance);
        $this->assertEquals(800000, (float) $dst->current_balance);
        $this->assertDatabaseHas('transactions', ['type' => 'transfer', 'account_id' => $src->id, 'destination_account_id' => $dst->id]);
    }

    public function test_transfer_not_counted_as_income_expense(): void
    {
        // ensure transfer excluded from income/expense sum logic (we just check type)
        $user = User::factory()->create();
        $src = Account::factory()->create(['user_id' => $user->id]);
        $dst = Account::factory()->create(['user_id' => $user->id]);
        $this->actingAs($user);
        $this->post(route('transactions.store'), [
            'type' => 'transfer', 'account_id' => $src->id, 'destination_account_id' => $dst->id, 'amount' => 10000, 'transaction_date' => now()->format('Y-m-d'),
        ]);
        $this->assertEquals(0, Transaction::where('user_id', $user->id)->where('type', 'income')->count());
        $this->assertEquals(0, Transaction::where('user_id', $user->id)->where('type', 'expense')->count());
        $this->assertEquals(1, Transaction::where('user_id', $user->id)->where('type', 'transfer')->count());
    }

    public function test_update_reverts_and_applies(): void
    {
        [$user, $account, $catIncome] = $this->makeUserWithAccountAndCategory('income');
        $catExpense = Category::factory()->create(['user_id' => $user->id, 'type' => 'expense']);
        $this->actingAs($user);

        $this->post(route('transactions.store'), [
            'type' => 'income', 'account_id' => $account->id, 'category_id' => $catIncome->id, 'amount' => 100000, 'transaction_date' => now()->format('Y-m-d'),
        ]);
        $tx = Transaction::first();
        $account->refresh();
        $this->assertEquals(1100000, (float) $account->current_balance);

        // update to expense 200k -> should revert +100k then -200k = 800k
        $this->patch(route('transactions.update', $tx), [
            'type' => 'expense', 'account_id' => $account->id, 'category_id' => $catExpense->id, 'amount' => 200000, 'transaction_date' => now()->format('Y-m-d'),
        ])->assertRedirect(route('transactions.index'));

        $account->refresh();
        $this->assertEquals(800000, (float) $account->current_balance);
    }

    public function test_delete_reverts_balance(): void
    {
        [$user, $account, $cat] = $this->makeUserWithAccountAndCategory('expense');
        $this->actingAs($user);
        $this->post(route('transactions.store'), [
            'type' => 'expense', 'account_id' => $account->id, 'category_id' => $cat->id, 'amount' => 150000, 'transaction_date' => now()->format('Y-m-d'),
        ]);
        $tx = Transaction::first();
        $account->refresh();
        $this->assertEquals(850000, (float) $account->current_balance);

        $this->delete(route('transactions.destroy', $tx))->assertRedirect(route('transactions.index'));
        $account->refresh();
        $this->assertEquals(1000000, (float) $account->current_balance);
        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_transaction_filters(): void
    {
        [$user, $account, $catInc] = $this->makeUserWithAccountAndCategory('income');
        $catExp = Category::factory()->create(['user_id' => $user->id, 'type' => 'expense']);
        $this->actingAs($user);
        Transaction::factory()->create(['user_id' => $user->id, 'account_id' => $account->id, 'category_id' => $catInc->id, 'type' => 'income', 'amount' => 100, 'transaction_date' => '2026-09-01']);
        Transaction::factory()->create(['user_id' => $user->id, 'account_id' => $account->id, 'category_id' => $catExp->id, 'type' => 'expense', 'amount' => 50, 'transaction_date' => '2026-09-05']);

        $this->get(route('transactions.index', ['type' => 'income']))->assertStatus(200);
        // pagination and filter not crashing is main; also test search
        $this->get(route('transactions.index', ['search' => 'nonexistent']))->assertStatus(200);
    }

    public function test_pagination(): void
    {
        [$user, $account, $cat] = $this->makeUserWithAccountAndCategory('expense');
        $this->actingAs($user);
        Transaction::factory()->count(20)->create(['user_id' => $user->id, 'account_id' => $account->id, 'category_id' => $cat->id, 'type' => 'expense']);
        $response = $this->get(route('transactions.index'));
        $response->assertStatus(200);
        // paginator exists - 15 per page
        $this->assertTrue($response->viewData('transactions')->hasPages() || $response->viewData('transactions')->count() === 15);
    }

    public function test_ownership_isolation(): void
    {
        $u1 = User::factory()->create();
        $u2 = User::factory()->create();
        $acc1 = Account::factory()->create(['user_id' => $u1->id]);
        $cat1 = Category::factory()->create(['user_id' => $u1->id, 'type' => 'expense']);
        $tx = Transaction::factory()->create(['user_id' => $u1->id, 'account_id' => $acc1->id, 'category_id' => $cat1->id]);

        $this->actingAs($u2);
        $this->get(route('transactions.show', $tx))->assertStatus(403);
        $this->get(route('transactions.edit', $tx))->assertStatus(403);
    }

    public function test_cannot_use_other_users_account(): void
    {
        $u1 = User::factory()->create();
        $u2 = User::factory()->create();
        $accOther = Account::factory()->create(['user_id' => $u2->id]);
        $cat = Category::factory()->create(['user_id' => $u1->id, 'type' => 'expense']);
        $this->actingAs($u1);
        $response = $this->post(route('transactions.store'), [
            'type' => 'expense', 'account_id' => $accOther->id, 'category_id' => $cat->id, 'amount' => 10000, 'transaction_date' => now()->format('Y-m-d'),
        ]);
        $response->assertSessionHasErrors('account_id');
    }
}
