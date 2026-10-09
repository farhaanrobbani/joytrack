<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Services\TransactionService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LivewireTransactionsFormTest extends TestCase
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

    public function test_create_valid_transaction(): void
    {
        [$user, $account, $income, $expense] = $this->setupUser();
        $this->actingAs($user);

        Livewire::test('transactions-create', ['type' => 'income'])
            ->set('account_id', (string) $account->id)
            ->set('category_id', (string) $income->id)
            ->set('amount', '150000')
            ->set('transaction_date', '2026-09-15')
            ->set('description', 'Gaji September')
            ->call('save')
            ->assertRedirect(route('transactions.index'));

        $this->assertSame(__('Transaksi berhasil dibuat.'), app('session')->get('status'));
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'account_id' => $account->id,
            'category_id' => $income->id,
            'type' => 'income',
            'amount' => 150000,
            'description' => 'Gaji September',
        ]);
        $account->refresh();
        $this->assertEquals(1150000, (float) $account->current_balance);
    }

    public function test_create_requires_amount(): void
    {
        [$user, $account, $income, $expense] = $this->setupUser();
        $this->actingAs($user);

        Livewire::test('transactions-create')
            ->set('account_id', (string) $account->id)
            ->set('category_id', (string) $expense->id)
            ->set('transaction_date', '2026-09-15')
            ->call('save')
            ->assertHasErrors(['amount']);

        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_create_transfer_requires_destination(): void
    {
        [$user, $account, $income, $expense] = $this->setupUser();
        $this->actingAs($user);

        Livewire::test('transactions-create', ['type' => 'transfer'])
            ->set('account_id', (string) $account->id)
            ->set('amount', '50000')
            ->set('transaction_date', '2026-09-15')
            ->call('save')
            ->assertHasErrors(['destination_account_id']);

        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_create_rejects_other_users_account(): void
    {
        [$user, $account, $income, $expense] = $this->setupUser();
        $otherUser = User::factory()->create();
        $otherAccount = Account::factory()->create(['user_id' => $otherUser->id, 'initial_balance' => 0, 'current_balance' => 0]);
        $this->actingAs($user);

        Livewire::test('transactions-create')
            ->set('account_id', (string) $otherAccount->id)
            ->set('category_id', (string) $expense->id)
            ->set('amount', '10000')
            ->set('transaction_date', '2026-09-15')
            ->call('save')
            ->assertHasErrors(['account_id' => __('Akun tidak valid.')]);
    }

    public function test_create_rejects_mismatched_category(): void
    {
        [$user, $account, $income, $expense] = $this->setupUser();
        $this->actingAs($user);

        Livewire::test('transactions-create')
            ->set('type', 'income')
            ->set('account_id', (string) $account->id)
            ->set('category_id', (string) $expense->id)
            ->set('amount', '10000')
            ->set('transaction_date', '2026-09-15')
            ->call('save')
            ->assertHasErrors(['category_id' => __('Kategori tidak sesuai dengan jenis transaksi.')]);
    }

    public function test_changing_type_resets_category(): void
    {
        [$user, $account, $income, $expense] = $this->setupUser();
        $this->actingAs($user);

        Livewire::test('transactions-create')
            ->set('category_id', (string) $expense->id)
            ->set('type', 'transfer')
            ->assertSet('category_id', '')
            ->assertSet('type', 'transfer');
    }

    public function test_edit_updates_transaction(): void
    {
        [$user, $account, $income, $expense] = $this->setupUser();
        $tx = app(TransactionService::class)->create([
            'user_id' => $user->id,
            'account_id' => $account->id,
            'category_id' => $expense->id,
            'type' => 'expense',
            'amount' => 100000,
            'transaction_date' => '2026-09-01',
        ]);
        $this->actingAs($user);

        Livewire::test('transactions-edit', ['transaction' => $tx])
            ->set('amount', '250000')
            ->set('description', 'Diperbarui')
            ->call('save')
            ->assertRedirect(route('transactions.index'));

        $this->assertDatabaseHas('transactions', [
            'id' => $tx->id,
            'amount' => 250000,
            'description' => 'Diperbarui',
        ]);
        $account->refresh();
        $this->assertEquals(750000, (float) $account->current_balance);
    }

    public function test_edit_requires_category_for_income(): void
    {
        [$user, $account, $income, $expense] = $this->setupUser();
        $tx = Transaction::factory()->create([
            'user_id' => $user->id,
            'account_id' => $account->id,
            'category_id' => $income->id,
            'type' => 'income',
            'amount' => 100000,
            'transaction_date' => '2026-09-01',
        ]);
        $this->actingAs($user);

        Livewire::test('transactions-edit', ['transaction' => $tx])
            ->set('category_id', '')
            ->call('save')
            ->assertHasErrors(['category_id']);
    }

    public function test_edit_is_forbidden_for_other_users(): void
    {
        [$user, $account, $income, $expense] = $this->setupUser();
        $tx = Transaction::factory()->create([
            'user_id' => $user->id,
            'account_id' => $account->id,
            'category_id' => $expense->id,
            'type' => 'expense',
        ]);
        $this->actingAs(User::factory()->create());
        $this->withoutExceptionHandling();

        $this->expectException(AuthorizationException::class);
        Livewire::test('transactions-edit', ['transaction' => $tx]);
    }
}
