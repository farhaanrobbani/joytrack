<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_vehicle_index(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->get(route('vehicles.index'))->assertStatus(200);
    }

    public function test_user_can_create_vehicle(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $response = $this->post(route('vehicles.store'), [
            'name' => 'Honda Vario 150',
            'license_plate' => 'N 1234 ABC',
            'current_odometer' => 45200,
        ]);
        $response->assertRedirect(route('vehicles.index'));
        $this->assertDatabaseHas('vehicles', ['user_id' => $user->id, 'name' => 'Honda Vario 150', 'current_odometer' => 45200]);
    }

    public function test_user_can_update_vehicle(): void
    {
        $user = User::factory()->create();
        $v = Vehicle::factory()->create(['user_id' => $user->id, 'current_odometer' => 10000]);
        $this->actingAs($user);
        $this->patch(route('vehicles.update', $v), [
            'name' => 'Updated Name',
            'current_odometer' => 15000,
        ])->assertRedirect(route('vehicles.index'));
        $this->assertDatabaseHas('vehicles', ['id' => $v->id, 'name' => 'Updated Name', 'current_odometer' => 15000]);
    }

    public function test_odometer_cannot_decrease(): void
    {
        $user = User::factory()->create();
        $v = Vehicle::factory()->create(['user_id' => $user->id, 'current_odometer' => 50000]);
        $this->actingAs($user);
        $response = $this->patch(route('vehicles.update', $v), [
            'name' => $v->name,
            'current_odometer' => 40000,
        ]);
        $response->assertSessionHasErrors('current_odometer');
        $this->assertDatabaseHas('vehicles', ['id' => $v->id, 'current_odometer' => 50000]);
    }

    public function test_user_can_delete_vehicle(): void
    {
        $user = User::factory()->create();
        $v = Vehicle::factory()->create(['user_id' => $user->id]);
        $this->actingAs($user);
        $this->delete(route('vehicles.destroy', $v))->assertRedirect(route('vehicles.index'));
        $this->assertDatabaseCount('vehicles', 0);
    }

    public function test_ownership_isolation(): void
    {
        $u1 = User::factory()->create();
        $u2 = User::factory()->create();
        $v = Vehicle::factory()->create(['user_id' => $u1->id]);
        $this->actingAs($u2);
        $this->get(route('vehicles.show', $v))->assertStatus(403);
        $this->get(route('vehicles.edit', $v))->assertStatus(403);
    }

    public function test_vehicle_detail_shows_stats(): void
    {
        $user = User::factory()->create();
        $v = Vehicle::factory()->create(['user_id' => $user->id]);
        $this->actingAs($user);
        $this->get(route('vehicles.show', $v))->assertStatus(200)->assertSee($v->name);
    }
}
