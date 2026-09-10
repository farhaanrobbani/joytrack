<?php

namespace Tests\Feature;

use App\Models\ServiceRecord;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\ServiceReminderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceReminderTest extends TestCase
{
    use RefreshDatabase;

    public function test_overdue_by_date(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['user_id' => $user->id, 'current_odometer' => 40000]);
        ServiceRecord::factory()->create([
            'user_id' => $user->id,
            'vehicle_id' => $vehicle->id,
            'service_date' => now()->subDays(30)->format('Y-m-d'),
            'next_service_date' => now()->subDays(5)->format('Y-m-d'),
            'next_service_odometer' => null,
        ]);

        $service = app(ServiceReminderService::class);
        $reminders = $service->getReminders($user->id);
        $this->assertCount(1, $reminders);
        $this->assertEquals('overdue', $reminders->first()['status']);
    }

    public function test_due_soon_by_date(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['user_id' => $user->id, 'current_odometer' => 40000]);
        ServiceRecord::factory()->create([
            'user_id' => $user->id,
            'vehicle_id' => $vehicle->id,
            'service_date' => now()->format('Y-m-d'),
            'next_service_date' => now()->addDays(3)->format('Y-m-d'),
            'next_service_odometer' => null,
        ]);

        $reminders = app(ServiceReminderService::class)->getReminders($user->id);
        $this->assertCount(1, $reminders);
        $this->assertEquals('due_soon', $reminders->first()['status']);
    }

    public function test_overdue_by_odometer(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['user_id' => $user->id, 'current_odometer' => 55000]);
        ServiceRecord::factory()->create([
            'user_id' => $user->id,
            'vehicle_id' => $vehicle->id,
            'service_date' => now()->format('Y-m-d'),
            'odometer' => 50000,
            'next_service_date' => null,
            'next_service_odometer' => 50000,
        ]);

        $reminders = app(ServiceReminderService::class)->getReminders($user->id);
        $this->assertCount(1, $reminders);
        $this->assertEquals('overdue', $reminders->first()['status']);
    }

    public function test_due_soon_by_odometer(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['user_id' => $user->id, 'current_odometer' => 49700]);
        ServiceRecord::factory()->create([
            'user_id' => $user->id,
            'vehicle_id' => $vehicle->id,
            'service_date' => now()->format('Y-m-d'),
            'odometer' => 45000,
            'next_service_date' => null,
            'next_service_odometer' => 50000,
        ]);

        $reminders = app(ServiceReminderService::class)->getReminders($user->id);
        $this->assertCount(1, $reminders);
        $this->assertEquals('due_soon', $reminders->first()['status']);
    }

    public function test_no_reminder_when_far(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['user_id' => $user->id, 'current_odometer' => 40000]);
        ServiceRecord::factory()->create([
            'user_id' => $user->id,
            'vehicle_id' => $vehicle->id,
            'service_date' => now()->format('Y-m-d'),
            'next_service_date' => now()->addDays(60)->format('Y-m-d'),
            'next_service_odometer' => 80000,
        ]);

        $reminders = app(ServiceReminderService::class)->getReminders($user->id);
        $this->assertCount(0, $reminders);
    }

    public function test_dashboard_shows_reminders(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['user_id' => $user->id, 'current_odometer' => 50000]);
        ServiceRecord::factory()->create([
            'user_id' => $user->id,
            'vehicle_id' => $vehicle->id,
            'service_date' => now()->subDays(10)->format('Y-m-d'),
            'next_service_date' => now()->subDays(1)->format('Y-m-d'),
        ]);

        $this->actingAs($user);
        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertViewHas('reminders');
        $this->assertCount(1, $response->viewData('reminders'));
    }

    public function test_vehicle_show_reminder_status(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['user_id' => $user->id, 'current_odometer' => 60000]);
        ServiceRecord::factory()->create([
            'user_id' => $user->id,
            'vehicle_id' => $vehicle->id,
            'service_date' => now()->format('Y-m-d'),
            'next_service_odometer' => 50000,
        ]);

        $this->actingAs($user);
        $this->get(route('vehicles.show', $vehicle))->assertStatus(200)->assertSee(__('Servis Terlambat'));
    }
}
