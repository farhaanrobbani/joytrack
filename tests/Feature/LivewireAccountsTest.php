<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LivewireAccountsTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_account(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test('accounts-create')
            ->set('name', 'Bank Utama')
            ->set('type', 'bank')
            ->set('initial_balance', '500000')
            ->set('description', 'Rekening gaji')
            ->call('save')
            ->assertRedirect(route('accounts.index'));

        $this->assertSame(__('Akun berhasil dibuat.'), app('session')->get('status'));
        $this->assertDatabaseHas('accounts', [
            'user_id' => $user->id,
            'name' => 'Bank Utama',
            'type' => 'bank',
            'initial_balance' => 500000,
            'current_balance' => 500000,
            'description' => 'Rekening gaji',
            'is_active' => true,
        ]);
    }

    public function test_create_requires_name(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('accounts-create')
            ->set('initial_balance', '1000')
            ->call('save')
            ->assertHasErrors(['name']);

        $this->assertDatabaseCount('accounts', 0);
    }

    public function test_create_rejects_negative_balance(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('accounts-create')
            ->set('name', 'Dompet')
            ->set('initial_balance', '-5')
            ->call('save')
            ->assertHasErrors(['initial_balance']);
    }

    public function test_edit_account(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->create(['user_id' => $user->id, 'name' => 'Nama Lama', 'is_active' => true]);
        $this->actingAs($user);

        Livewire::test('accounts-edit', ['account' => $account])
            ->set('name', 'Nama Baru')
            ->set('is_active', false)
            ->call('save')
            ->assertRedirect(route('accounts.index'));

        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
            'name' => 'Nama Baru',
            'is_active' => false,
        ]);
    }

    public function test_edit_requires_name(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->create(['user_id' => $user->id]);
        $this->actingAs($user);

        Livewire::test('accounts-edit', ['account' => $account])
            ->set('name', '')
            ->call('save')
            ->assertHasErrors(['name']);
    }

    public function test_edit_is_forbidden_for_other_users(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->create(['user_id' => $user->id]);
        $this->actingAs(User::factory()->create());
        $this->withoutExceptionHandling();

        $this->expectException(AuthorizationException::class);
        Livewire::test('accounts-edit', ['account' => $account]);
    }
}
