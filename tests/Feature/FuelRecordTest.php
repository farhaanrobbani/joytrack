<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Category;
use App\Models\FuelRecord;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FuelRecordTest extends TestCase
{
    use RefreshDatabase;

    private function seedFuelCategory(User $user): Category
    {
        return Category::factory()->create(['user_id' => $user->id, 'type' => 'expense', 'name' => 'Kendaraan']);
    }

    public function test_user_can_create_fuel_without_transaction(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['user_id' => $user->id, 'current_odometer' => 10000]);
        $this->actingAs($user);
        $response = $this->post(route('fuel-records.store'), [
            'vehicle_id' => $vehicle->id,
            'fuel_date' => now()->format('Y-m-d'),
            'odometer' => 15000,
            'liters' => 10,
            'price_per_liter' => 10000,
            'total_cost' => 100000,
        ]);
        $response->assertRedirect(route('fuel-records.index'));
        $this->assertDatabaseHas('fuel_records', ['user_id' => $user->id, 'vehicle_id' => $vehicle->id, 'odometer' => 15000]);
        $vehicle->refresh();
        $this->assertEquals(15000, $vehicle->current_odometer);
    }

    public function test_create_fuel_with_transaction_updates_balance(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['user_id' => $user->id]);
        $account = Account::factory()->create(['user_id' => $user->id, 'current_balance' => 1000000]);
        $this->seedFuelCategory($user);
        $this->actingAs($user);

        $this->post(route('fuel-records.store'), [
            'vehicle_id' => $vehicle->id,
            'fuel_date' => now()->format('Y-m-d'),
            'odometer' => 20000,
            'liters' => 20,
            'price_per_liter' => 10000,
            'total_cost' => 200000,
            'account_id' => $account->id,
            'create_transaction' => 1,
        ])->assertRedirect(route('fuel-records.index'));

        $account->refresh();
        $this->assertEquals(800000, (float) $account->current_balance);
        $fuel = FuelRecord::first();
        $this->assertNotNull($fuel->transaction_id);
        $kendaraan = Category::where('user_id', $user->id)->where('name', 'Kendaraan')->first();
        $this->assertNotNull($kendaraan);
        $this->assertDatabaseHas('transactions', ['id' => $fuel->transaction_id, 'amount' => 200000, 'type' => 'expense', 'category_id' => $kendaraan->id]);
    }

    public function test_fuel_calculation(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['user_id' => $user->id]);
        $this->actingAs($user);
        // liters * price auto? We require total_cost, but test deviation allowed
        $this->post(route('fuel-records.store'), [
            'vehicle_id' => $vehicle->id,
            'fuel_date' => now()->format('Y-m-d'),
            'odometer' => 5000,
            'fuel_type' => 'Pertalite',
            'liters' => 10.5,
            'price_per_liter' => 10000,
            'total_cost' => 105000,
        ]);
        $this->assertDatabaseHas('fuel_records', ['total_cost' => 105000]);
    }

    public function test_fuel_stats(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['user_id' => $user->id]);
        FuelRecord::factory()->create(['user_id' => $user->id, 'vehicle_id' => $vehicle->id, 'odometer' => 10000, 'liters' => 10, 'total_cost' => 100000, 'fuel_date' => now()->format('Y-m-d')]);
        FuelRecord::factory()->create(['user_id' => $user->id, 'vehicle_id' => $vehicle->id, 'odometer' => 10500, 'liters' => 10, 'total_cost' => 100000, 'fuel_date' => now()->format('Y-m-d')]);
        $this->actingAs($user);
        $response = $this->get(route('fuel-records.index', ['vehicle_id' => $vehicle->id]));
        $response->assertStatus(200);
        $response->assertViewHas('stats');
        $stats = $response->viewData('stats');
        $this->assertEquals(20.0, $stats['totalLiters']);
        $this->assertEquals(200000.0, $stats['totalCost']);
        $this->assertEquals(500, $stats['distance']);
        $this->assertEqualsWithDelta(25, $stats['efficiency'], 0.01);
    }

    public function test_update_and_delete_reverts_transaction(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['user_id' => $user->id]);
        $account = Account::factory()->create(['user_id' => $user->id, 'current_balance' => 1000000]);
        $this->seedFuelCategory($user);
        $this->actingAs($user);
        $this->post(route('fuel-records.store'), [
            'vehicle_id' => $vehicle->id, 'fuel_date' => now()->format('Y-m-d'), 'odometer' => 10000,
            'liters' => 10, 'price_per_liter' => 10000, 'total_cost' => 100000, 'account_id' => $account->id, 'create_transaction' => 1,
        ]);
        $fuel = FuelRecord::first();
        $account->refresh();
        $this->assertEquals(900000, (float) $account->current_balance);

        // update with new total_cost and same account -> should adjust balance
        $this->put(route('fuel-records.update', $fuel), [
            'vehicle_id' => $vehicle->id, 'fuel_date' => now()->format('Y-m-d'), 'odometer' => 10000,
            'liters' => 10, 'price_per_liter' => 12000, 'total_cost' => 120000, 'account_id' => $account->id,
        ])->assertRedirect(route('fuel-records.index'));
        $account->refresh();
        $this->assertEquals(880000, (float) $account->current_balance);

        // delete reverts
        $this->delete(route('fuel-records.destroy', $fuel->fresh()))->assertRedirect(route('fuel-records.index'));
        $account->refresh();
        $this->assertEquals(1000000, (float) $account->current_balance);
        $this->assertDatabaseCount('fuel_records', 0);
    }

    public function test_ownership_isolation(): void
    {
        $u1 = User::factory()->create();
        $u2 = User::factory()->create();
        $v1 = Vehicle::factory()->create(['user_id' => $u1->id]);
        $fuel = FuelRecord::factory()->create(['user_id' => $u1->id, 'vehicle_id' => $v1->id]);
        $this->actingAs($u2);
        $this->get(route('fuel-records.show', $fuel))->assertStatus(403);
    }
}
