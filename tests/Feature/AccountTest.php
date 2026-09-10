<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_account_index(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('accounts.index'));
        $response->assertStatus(200);
    }

    public function test_user_can_create_account(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $accountData = [
            'name' => 'Bank BCA',
            'type' => 'bank',
            'initial_balance' => 5000000,
            'description' => 'Rekening utama',
            'is_active' => true,
        ];

        $response = $this->post(route('accounts.store'), $accountData);

        $response->assertRedirect(route('accounts.index'));
        $this->assertDatabaseCount('accounts', 1);
        $this->assertDatabaseHas('accounts', [
            'user_id' => $user->id,
            'name' => 'Bank BCA',
            'type' => 'bank',
            'current_balance' => 5000000,
        ]);
    }

    public function test_user_can_update_account(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $account = Account::factory()->create(['user_id' => $user->id]);

        $response = $this->patch(route('accounts.update', $account), [
            'name' => 'Bank BCA Updated',
            'type' => $account->type,
            'description' => 'Deskripsi baru',
        ]);

        $response->assertRedirect(route('accounts.index'));
        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
            'name' => 'Bank BCA Updated',
        ]);
    }

    public function test_user_can_delete_account(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $account = Account::factory()->create(['user_id' => $user->id]);

        $response = $this->delete(route('accounts.destroy', $account));

        $response->assertRedirect(route('accounts.index'));
        $this->assertDatabaseCount('accounts', 0);
    }

    public function test_user_cannot_view_others_account(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $account = Account::factory()->create(['user_id' => $user1->id]);

        $this->actingAs($user2);

        $response = $this->get(route('accounts.show', $account));
        $response->assertStatus(403);
    }

    public function test_account_ownership_isolation(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Account::factory()->create(['user_id' => $user1->id]);
        Account::factory()->create(['user_id' => $user1->id]);
        Account::factory()->create(['user_id' => $user2->id]);

        $this->actingAs($user1);
        $response = $this->get(route('accounts.index'));

        $response->assertViewHas('accounts', function ($accounts) {
            return $accounts->count() === 2;
        });
    }
}
