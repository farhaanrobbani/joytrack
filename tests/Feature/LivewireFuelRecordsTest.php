<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Category;
use App\Models\FuelRecord;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LivewireFuelRecordsTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_fuel_record(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['user_id' => $user->id, 'current_odometer' => 10000]);
        $account = Account::factory()->create(['user_id' => $user->id, 'current_balance' => 1000000]);
        Category::factory()->create(['user_id' => $user->id, 'type' => 'expense', 'name' => 'Kendaraan']);
        $this->actingAs($user);

        Livewire::test('fuel-records-create')
            ->set('vehicle_id', (string) $vehicle->id)
            ->set('fuel_date', now()->format('Y-m-d'))
            ->set('odometer', '15000')
            ->set('fuel_type', 'Pertalite')
            ->set('liters', '20')
            ->set('price_per_liter', '10000')
            ->set('total_cost', '200000')
            ->set('account_id', (string) $account->id)
            ->set('create_transaction', true)
            ->call('save')
            ->assertRedirect(route('fuel-records.index'));

        $this->assertSame(__('Catatan BBM berhasil dibuat.'), app('session')->get('status'));
        $this->assertDatabaseHas('fuel_records', [
            'user_id' => $user->id,
            'vehicle_id' => $vehicle->id,
            'fuel_type' => 'Pertalite',
            'odometer' => 15000,
            'total_cost' => 200000,
        ]);
        $account->refresh();
        $this->assertEquals(800000, (float) $account->current_balance);
        $vehicle->refresh();
        $this->assertEquals(15000, $vehicle->current_odometer);
    }

    public function test_create_requires_fields(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('fuel-records-create')
            ->call('save')
            ->assertHasErrors([
                'vehicle_id',
                'odometer',
                'liters',
                'price_per_liter',
                'total_cost',
            ]);

        $this->assertDatabaseCount('fuel_records', 0);
    }

    public function test_create_rejects_other_users_vehicle(): void
    {
        $owner = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['user_id' => $owner->id]);
        $this->actingAs(User::factory()->create());

        Livewire::test('fuel-records-create')
            ->set('vehicle_id', (string) $vehicle->id)
            ->set('fuel_date', now()->format('Y-m-d'))
            ->set('odometer', '15000')
            ->set('liters', '10')
            ->set('price_per_liter', '10000')
            ->set('total_cost', '100000')
            ->call('save')
            ->assertHasErrors(['vehicle_id' => __('Kendaraan tidak valid.')]);

        $this->assertDatabaseCount('fuel_records', 0);
    }

    public function test_create_rejects_lower_odometer(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['user_id' => $user->id]);
        FuelRecord::factory()->create(['user_id' => $user->id, 'vehicle_id' => $vehicle->id, 'odometer' => 20000]);
        $this->actingAs($user);

        Livewire::test('fuel-records-create')
            ->set('vehicle_id', (string) $vehicle->id)
            ->set('fuel_date', now()->format('Y-m-d'))
            ->set('odometer', '15000')
            ->set('liters', '10')
            ->set('price_per_liter', '10000')
            ->set('total_cost', '100000')
            ->call('save')
            ->assertHasErrors([
                'odometer' => __('Odometer tidak boleh lebih kecil dari sebelumnya (:value km).', ['value' => '20.000']),
            ]);
    }

    public function test_create_transaction_requires_account(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['user_id' => $user->id]);
        $this->actingAs($user);

        Livewire::test('fuel-records-create')
            ->set('vehicle_id', (string) $vehicle->id)
            ->set('fuel_date', now()->format('Y-m-d'))
            ->set('odometer', '15000')
            ->set('liters', '10')
            ->set('price_per_liter', '10000')
            ->set('total_cost', '100000')
            ->set('create_transaction', true)
            ->call('save')
            ->assertHasErrors(['account_id' => __('Akun wajib jika membuat transaksi keuangan.')]);

        $this->assertDatabaseCount('fuel_records', 0);
    }

    public function test_edit_fuel_record(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['user_id' => $user->id]);
        $fuel = FuelRecord::factory()->create([
            'user_id' => $user->id,
            'vehicle_id' => $vehicle->id,
            'odometer' => 10000,
            'liters' => 10,
            'total_cost' => 100000,
        ]);
        $this->actingAs($user);

        Livewire::test('fuel-records-edit', ['fuelRecord' => $fuel])
            ->set('total_cost', '120000')
            ->call('save')
            ->assertRedirect(route('fuel-records.index'));

        $this->assertSame(__('Catatan BBM berhasil diperbarui.'), app('session')->get('status'));
        $this->assertDatabaseHas('fuel_records', ['id' => $fuel->id, 'total_cost' => 120000]);
    }

    public function test_edit_is_forbidden_for_other_users(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['user_id' => $user->id]);
        $fuel = FuelRecord::factory()->create(['user_id' => $user->id, 'vehicle_id' => $vehicle->id]);
        $this->actingAs(User::factory()->create());
        $this->withoutExceptionHandling();

        $this->expectException(AuthorizationException::class);
        Livewire::test('fuel-records-edit', ['fuelRecord' => $fuel]);
    }

    public function test_index_shows_only_own_records(): void
    {
        $u1 = User::factory()->create();
        $v1 = Vehicle::factory()->create(['user_id' => $u1->id, 'name' => 'Motor A']);
        FuelRecord::factory()->create(['user_id' => $u1->id, 'vehicle_id' => $v1->id]);
        $u2 = User::factory()->create();
        $v2 = Vehicle::factory()->create(['user_id' => $u2->id, 'name' => 'Motor B']);
        FuelRecord::factory()->create(['user_id' => $u2->id, 'vehicle_id' => $v2->id]);
        $this->actingAs($u1);

        Livewire::test('fuel-records-index')
            ->assertSee('Motor A')
            ->assertDontSee('Motor B');
    }
}
