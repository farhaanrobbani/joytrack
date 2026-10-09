<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LivewireTransactionsIndexTest extends TestCase
{
    use RefreshDatabase;

    private function setupUser(): array
    {
        $user = User::factory()->create();
        $account = Account::factory()->create(['user_id' => $user->id, 'initial_balance' => 1000000, 'current_balance' => 1000000]);
        $incomeCategory = Category::factory()->create(['user_id' => $user->id, 'type' => 'income']);
        $expenseCategory = Category::factory()->create(['user_id' => $user->id, 'type' => 'expense']);

        return [$user, $account, $incomeCategory, $expenseCategory];
    }

    public function test_filter_by_type(): void
    {
        [$user, $account, $income, $expense] = $this->setupUser();
        Transaction::factory()->create(['user_id' => $user->id, 'account_id' => $account->id, 'category_id' => $income->id, 'type' => 'income', 'description' => 'Gaji kantor', 'transaction_date' => '2026-09-01']);
        Transaction::factory()->create(['user_id' => $user->id, 'account_id' => $account->id, 'category_id' => $expense->id, 'type' => 'expense', 'description' => 'Bayar listrik', 'transaction_date' => '2026-09-02']);
        $this->actingAs($user);

        Livewire::test('transactions-index')
            ->set('type', 'income')
            ->assertSee('Gaji kantor')
            ->assertDontSee('Bayar listrik')
            ->set('type', 'expense')
            ->assertSee('Bayar listrik')
            ->assertDontSee('Gaji kantor');
    }

    public function test_search_filters_rows(): void
    {
        [$user, $account, $income, $expense] = $this->setupUser();
        Transaction::factory()->create(['user_id' => $user->id, 'account_id' => $account->id, 'category_id' => $expense->id, 'type' => 'expense', 'description' => 'Beli kopi pagi', 'transaction_date' => '2026-09-01']);
        Transaction::factory()->create(['user_id' => $user->id, 'account_id' => $account->id, 'category_id' => $expense->id, 'type' => 'expense', 'description' => 'Bayar listrik', 'transaction_date' => '2026-09-02']);
        $this->actingAs($user);

        Livewire::test('transactions-index')
            ->set('search', 'kopi')
            ->assertSee('Beli kopi pagi')
            ->assertDontSee('Bayar listrik');
    }

    public function test_filter_by_account(): void
    {
        [$user, $account, $income, $expense] = $this->setupUser();
        $otherAccount = Account::factory()->create(['user_id' => $user->id, 'initial_balance' => 0, 'current_balance' => 0]);
        Transaction::factory()->create(['user_id' => $user->id, 'account_id' => $account->id, 'category_id' => $expense->id, 'type' => 'expense', 'description' => 'Di akun utama', 'transaction_date' => '2026-09-01']);
        Transaction::factory()->create(['user_id' => $user->id, 'account_id' => $otherAccount->id, 'category_id' => $expense->id, 'type' => 'expense', 'description' => 'Di akun kedua', 'transaction_date' => '2026-09-02']);
        $this->actingAs($user);

        Livewire::test('transactions-index')
            ->set('accountId', (string) $otherAccount->id)
            ->assertSee('Di akun kedua')
            ->assertDontSee('Di akun utama');
    }

    public function test_filter_by_date_range(): void
    {
        [$user, $account, $income, $expense] = $this->setupUser();
        Transaction::factory()->create(['user_id' => $user->id, 'account_id' => $account->id, 'category_id' => $expense->id, 'type' => 'expense', 'description' => 'Transaksi awal', 'transaction_date' => '2026-09-01']);
        Transaction::factory()->create(['user_id' => $user->id, 'account_id' => $account->id, 'category_id' => $expense->id, 'type' => 'expense', 'description' => 'Transaksi akhir', 'transaction_date' => '2026-09-20']);
        $this->actingAs($user);

        Livewire::test('transactions-index')
            ->set('dateFrom', '2026-09-10')
            ->assertSee('Transaksi akhir')
            ->assertDontSee('Transaksi awal')
            ->set('dateTo', '2026-09-15')
            ->assertDontSee('Transaksi akhir');
    }

    public function test_reset_filters_clears_state(): void
    {
        [$user, $account, $income, $expense] = $this->setupUser();
        Transaction::factory()->create(['user_id' => $user->id, 'account_id' => $account->id, 'category_id' => $expense->id, 'type' => 'expense']);
        $this->actingAs($user);

        Livewire::test('transactions-index')
            ->set('search', 'kopi')
            ->set('type', 'expense')
            ->set('accountId', (string) $account->id)
            ->set('categoryId', (string) $expense->id)
            ->set('dateFrom', '2026-09-01')
            ->set('dateTo', '2026-09-30')
            ->call('resetFilters')
            ->assertSet('search', '')
            ->assertSet('type', '')
            ->assertSet('accountId', '')
            ->assertSet('categoryId', '')
            ->assertSet('dateFrom', '')
            ->assertSet('dateTo', '');
    }

    public function test_transactions_from_other_users_are_not_shown(): void
    {
        [$user, $account, $income, $expense] = $this->setupUser();
        $otherUser = User::factory()->create();
        $otherAccount = Account::factory()->create(['user_id' => $otherUser->id, 'initial_balance' => 0, 'current_balance' => 0]);
        $otherCategory = Category::factory()->create(['user_id' => $otherUser->id, 'type' => 'expense']);
        Transaction::factory()->create(['user_id' => $otherUser->id, 'account_id' => $otherAccount->id, 'category_id' => $otherCategory->id, 'type' => 'expense', 'description' => 'Rahasia pengguna lain']);
        $this->actingAs($user);

        Livewire::test('transactions-index')
            ->assertDontSee('Rahasia pengguna lain');
    }

    public function test_query_string_hydrates_filters(): void
    {
        [$user, $account, $income, $expense] = $this->setupUser();
        Transaction::factory()->create(['user_id' => $user->id, 'account_id' => $account->id, 'category_id' => $income->id, 'type' => 'income', 'description' => 'Gaji kantor', 'transaction_date' => '2026-09-01']);
        Transaction::factory()->create(['user_id' => $user->id, 'account_id' => $account->id, 'category_id' => $expense->id, 'type' => 'expense', 'description' => 'Bayar listrik', 'transaction_date' => '2026-09-02']);
        $this->actingAs($user);

        $this->get(route('transactions.index', ['type' => 'income']))
            ->assertStatus(200)
            ->assertSee('Gaji kantor')
            ->assertDontSee('Bayar listrik');
    }
}
