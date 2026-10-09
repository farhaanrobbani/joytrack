<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Category;
use App\Models\ServiceRecord;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LivewireServiceRecordsTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_service_record(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['user_id' => $user->id, 'current_odometer' => 10000]);
        $account = Account::factory()->create(['user_id' => $user->id, 'current_balance' => 1000000]);
        Category::factory()->create(['user_id' => $user->id, 'type' => 'expense', 'name' => 'Kendaraan']);
        $this->actingAs($user);

        Livewire::test('service-records-create')
            ->set('vehicle_id', (string) $vehicle->id)
            ->set('service_date', now()->format('Y-m-d'))
            ->set('odometer', '15000')
            ->set('service_type', 'Ganti oli')
            ->set('workshop', 'Bengkel ABC')
            ->set('labor_cost', '100000')
            ->set('parts_cost', '250000')
            ->set('total_cost', '350000')
            ->set('next_service_odometer', '50000')
            ->set('account_id', (string) $account->id)
            ->set('create_transaction', true)
            ->call('save')
            ->assertRedirect(route('service-records.index'));

        $this->assertSame(__('Catatan servis berhasil dibuat.'), app('session')->get('status'));
        $this->assertDatabaseHas('service_records', [
            'user_id' => $user->id,
            'vehicle_id' => $vehicle->id,
            'service_type' => 'Ganti oli',
            'workshop' => 'Bengkel ABC',
            'odometer' => 15000,
            'total_cost' => 350000,
            'next_service_odometer' => 50000,
        ]);
        $account->refresh();
        $this->assertEquals(650000, (float) $account->current_balance);
        $vehicle->refresh();
        $this->assertEquals(15000, $vehicle->current_odometer);
    }

    public function test_create_requires_fields(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('service-records-create')
            ->call('save')
            ->assertHasErrors([
                'vehicle_id',
                'odometer',
                'service_type',
                'total_cost',
            ]);

        $this->assertDatabaseCount('service_records', 0);
    }

    public function test_create_requires_labor_cost_when_cleared(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['user_id' => $user->id]);
        $this->actingAs($user);

        Livewire::test('service-records-create')
            ->set('vehicle_id', (string) $vehicle->id)
            ->set('service_date', now()->format('Y-m-d'))
            ->set('odometer', '15000')
            ->set('service_type', 'Servis rutin')
            ->set('labor_cost', '')
            ->set('parts_cost', '50000')
            ->set('total_cost', '50000')
            ->call('save')
            ->assertHasErrors(['labor_cost']);

        $this->assertDatabaseCount('service_records', 0);
    }

    public function test_create_rejects_lower_next_service_odometer(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['user_id' => $user->id]);
        $this->actingAs($user);

        Livewire::test('service-records-create')
            ->set('vehicle_id', (string) $vehicle->id)
            ->set('service_date', now()->format('Y-m-d'))
            ->set('odometer', '40000')
            ->set('service_type', 'Servis rutin')
            ->set('labor_cost', '50000')
            ->set('parts_cost', '50000')
            ->set('total_cost', '100000')
            ->set('next_service_odometer', '30000')
            ->call('save')
            ->assertHasErrors(['next_service_odometer' => __('Odometer servis berikutnya harus lebih besar dari odometer saat ini.')]);

        $this->assertDatabaseCount('service_records', 0);
    }

    public function test_create_rejects_other_users_vehicle(): void
    {
        $owner = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['user_id' => $owner->id]);
        $this->actingAs(User::factory()->create());

        Livewire::test('service-records-create')
            ->set('vehicle_id', (string) $vehicle->id)
            ->set('service_date', now()->format('Y-m-d'))
            ->set('odometer', '15000')
            ->set('service_type', 'Servis rutin')
            ->set('labor_cost', '50000')
            ->set('parts_cost', '50000')
            ->set('total_cost', '100000')
            ->call('save')
            ->assertHasErrors(['vehicle_id' => __('Kendaraan tidak valid.')]);

        $this->assertDatabaseCount('service_records', 0);
    }

    public function test_edit_service_record(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['user_id' => $user->id]);
        $record = ServiceRecord::factory()->create([
            'user_id' => $user->id,
            'vehicle_id' => $vehicle->id,
            'odometer' => 10000,
            'service_type' => 'Servis rutin',
            'labor_cost' => 50000,
            'parts_cost' => 50000,
            'total_cost' => 100000,
        ]);
        $this->actingAs($user);

        Livewire::test('service-records-edit', ['serviceRecord' => $record])
            ->set('service_type', 'Ganti oli')
            ->set('total_cost', '200000')
            ->call('save')
            ->assertRedirect(route('service-records.index'));

        $this->assertSame(__('Catatan servis berhasil diperbarui.'), app('session')->get('status'));
        $this->assertDatabaseHas('service_records', ['id' => $record->id, 'service_type' => 'Ganti oli', 'total_cost' => 200000]);
    }

    public function test_edit_is_forbidden_for_other_users(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['user_id' => $user->id]);
        $record = ServiceRecord::factory()->create(['user_id' => $user->id, 'vehicle_id' => $vehicle->id]);
        $this->actingAs(User::factory()->create());
        $this->withoutExceptionHandling();

        $this->expectException(AuthorizationException::class);
        Livewire::test('service-records-edit', ['serviceRecord' => $record]);
    }

    public function test_index_shows_only_own_records(): void
    {
        $u1 = User::factory()->create();
        $v1 = Vehicle::factory()->create(['user_id' => $u1->id, 'name' => 'Motor A']);
        ServiceRecord::factory()->create(['user_id' => $u1->id, 'vehicle_id' => $v1->id]);
        $u2 = User::factory()->create();
        $v2 = Vehicle::factory()->create(['user_id' => $u2->id, 'name' => 'Motor B']);
        ServiceRecord::factory()->create(['user_id' => $u2->id, 'vehicle_id' => $v2->id]);
        $this->actingAs($u1);

        Livewire::test('service-records-index')
            ->assertSee('Motor A')
            ->assertDontSee('Motor B');
    }
}
