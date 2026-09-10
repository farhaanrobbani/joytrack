<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Category;
use App\Models\ServiceRecord;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceRecordTest extends TestCase
{
    use RefreshDatabase;

    private function seedServiceCategory(User $user): Category
    {
        return Category::factory()->create(['user_id' => $user->id, 'type' => 'expense', 'name' => 'Servis']);
    }

    public function test_user_can_create_service_without_transaction(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['user_id' => $user->id, 'current_odometer' => 10000]);
        $this->actingAs($user);
        $response = $this->post(route('service-records.store'), [
            'vehicle_id' => $vehicle->id,
            'service_date' => now()->format('Y-m-d'),
            'odometer' => 15000,
            'service_type' => 'Ganti oli',
            'labor_cost' => 100000,
            'parts_cost' => 250000,
            'total_cost' => 350000,
        ]);
        $response->assertRedirect(route('service-records.index'));
        $this->assertDatabaseHas('service_records', ['user_id' => $user->id, 'service_type' => 'Ganti oli', 'total_cost' => 350000]);
        $vehicle->refresh();
        $this->assertEquals(15000, $vehicle->current_odometer);
    }

    public function test_create_service_with_transaction_updates_balance(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['user_id' => $user->id]);
        $account = Account::factory()->create(['user_id' => $user->id, 'current_balance' => 1000000]);
        $this->seedServiceCategory($user);
        $this->actingAs($user);

        $this->post(route('service-records.store'), [
            'vehicle_id' => $vehicle->id,
            'service_date' => now()->format('Y-m-d'),
            'odometer' => 20000,
            'service_type' => 'Servis rutin',
            'labor_cost' => 100000,
            'parts_cost' => 200000,
            'total_cost' => 300000,
            'account_id' => $account->id,
            'create_transaction' => 1,
        ])->assertRedirect(route('service-records.index'));

        $account->refresh();
        $this->assertEquals(700000, (float) $account->current_balance);
        $record = ServiceRecord::first();
        $this->assertNotNull($record->transaction_id);
        $this->assertDatabaseHas('transactions', ['id' => $record->transaction_id, 'amount' => 300000]);
    }

    public function test_next_service_validation(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['user_id' => $user->id]);
        $this->actingAs($user);
        $response = $this->post(route('service-records.store'), [
            'vehicle_id' => $vehicle->id,
            'service_date' => now()->format('Y-m-d'),
            'odometer' => 40000,
            'service_type' => 'Servis rutin',
            'labor_cost' => 50000,
            'parts_cost' => 50000,
            'total_cost' => 100000,
            'next_service_odometer' => 30000, // invalid: smaller
        ]);
        $response->assertSessionHasErrors('next_service_odometer');
    }

    public function test_update_and_delete_reverts_transaction(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['user_id' => $user->id]);
        $account = Account::factory()->create(['user_id' => $user->id, 'current_balance' => 1000000]);
        $this->seedServiceCategory($user);
        $this->actingAs($user);
        $this->post(route('service-records.store'), [
            'vehicle_id' => $vehicle->id, 'service_date' => now()->format('Y-m-d'), 'odometer' => 10000,
            'service_type' => 'Servis rutin', 'labor_cost' => 50000, 'parts_cost' => 50000, 'total_cost' => 100000, 'account_id' => $account->id, 'create_transaction' => 1,
        ]);
        $record = ServiceRecord::first();
        $account->refresh();
        $this->assertEquals(900000, (float) $account->current_balance);

        $this->put(route('service-records.update', $record), [
            'vehicle_id' => $vehicle->id, 'service_date' => now()->format('Y-m-d'), 'odometer' => 10000,
            'service_type' => 'Ganti oli', 'labor_cost' => 100000, 'parts_cost' => 100000, 'total_cost' => 200000, 'account_id' => $account->id,
        ])->assertRedirect(route('service-records.index'));
        $account->refresh();
        $this->assertEquals(800000, (float) $account->current_balance);

        $this->delete(route('service-records.destroy', $record->fresh()))->assertRedirect(route('service-records.index'));
        $account->refresh();
        $this->assertEquals(1000000, (float) $account->current_balance);
    }

    public function test_ownership_isolation(): void
    {
        $u1 = User::factory()->create();
        $u2 = User::factory()->create();
        $v1 = Vehicle::factory()->create(['user_id' => $u1->id]);
        $rec = ServiceRecord::factory()->create(['user_id' => $u1->id, 'vehicle_id' => $v1->id]);
        $this->actingAs($u2);
        $this->get(route('service-records.show', $rec))->assertStatus(403);
    }

    public function test_service_history_filter(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['user_id' => $user->id]);
        ServiceRecord::factory()->create(['user_id' => $user->id, 'vehicle_id' => $vehicle->id, 'service_type' => 'Ganti oli']);
        ServiceRecord::factory()->create(['user_id' => $user->id, 'vehicle_id' => $vehicle->id, 'service_type' => 'Servis rutin']);
        $this->actingAs($user);
        $this->get(route('service-records.index', ['service_type' => 'Ganti oli']))->assertStatus(200);
    }
}
