<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LivewireVehiclesTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_vehicle(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test('vehicles-create')
            ->set('name', 'Honda Vario 150')
            ->set('license_plate', 'N 1234 ABC')
            ->set('vehicle_type', 'motor')
            ->set('brand', 'Honda')
            ->set('current_odometer', '15000')
            ->call('save')
            ->assertRedirect(route('vehicles.index'));

        $this->assertSame(__('Kendaraan berhasil dibuat.'), app('session')->get('status'));
        $this->assertDatabaseHas('vehicles', [
            'user_id' => $user->id,
            'name' => 'Honda Vario 150',
            'license_plate' => 'N 1234 ABC',
            'vehicle_type' => 'motor',
            'brand' => 'Honda',
            'current_odometer' => 15000,
            'is_active' => true,
        ]);
    }

    public function test_create_requires_name(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('vehicles-create')
            ->set('current_odometer', '0')
            ->call('save')
            ->assertHasErrors(['name']);

        $this->assertDatabaseCount('vehicles', 0);
    }

    public function test_create_requires_odometer(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('vehicles-create')
            ->set('name', 'Motor')
            ->set('current_odometer', '')
            ->call('save')
            ->assertHasErrors(['current_odometer']);
    }

    public function test_edit_vehicle(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['user_id' => $user->id, 'name' => 'Motor Lama', 'current_odometer' => 10000]);
        $this->actingAs($user);

        Livewire::test('vehicles-edit', ['vehicle' => $vehicle])
            ->set('name', 'Motor Baru')
            ->set('current_odometer', '12500')
            ->call('save')
            ->assertRedirect(route('vehicles.index'));

        $this->assertDatabaseHas('vehicles', [
            'id' => $vehicle->id,
            'name' => 'Motor Baru',
            'current_odometer' => 12500,
        ]);
    }

    public function test_edit_rejects_lower_odometer(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['user_id' => $user->id, 'current_odometer' => 10000]);
        $this->actingAs($user);

        Livewire::test('vehicles-edit', ['vehicle' => $vehicle])
            ->set('current_odometer', '5000')
            ->call('save')
            ->assertHasErrors(['current_odometer' => __('Odometer tidak boleh lebih kecil dari sebelumnya (:value km).', ['value' => '10.000'])]);
    }

    public function test_edit_is_forbidden_for_other_users(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['user_id' => $user->id]);
        $this->actingAs(User::factory()->create());
        $this->withoutExceptionHandling();

        $this->expectException(AuthorizationException::class);
        Livewire::test('vehicles-edit', ['vehicle' => $vehicle]);
    }
}
