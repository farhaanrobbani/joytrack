<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Category;
use App\Models\FuelRecord;
use App\Models\ServiceRecord;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_export_transactions_csv(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->create(['user_id' => $user->id]);
        $cat = Category::factory()->create(['user_id' => $user->id, 'type' => 'income']);
        Transaction::factory()->create(['user_id' => $user->id, 'account_id' => $account->id, 'category_id' => $cat->id, 'type' => 'income']);
        $this->actingAs($user);
        $response = $this->get(route('export.transactions'));
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('Tanggal', $response->streamedContent());
    }

    public function test_export_finance_csv(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $response = $this->get(route('export.finance', ['preset' => 'month']));
        $response->assertStatus(200);
        $this->assertStringContainsString('Ringkasan Keuangan', $response->streamedContent());
    }

    public function test_export_vehicle_csv(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['user_id' => $user->id]);
        FuelRecord::factory()->create(['user_id' => $user->id, 'vehicle_id' => $vehicle->id, 'fuel_date' => now()->format('Y-m-d')]);
        $this->actingAs($user);
        $response = $this->get(route('export.vehicle', ['preset' => 'month']));
        $response->assertStatus(200);
        $this->assertStringContainsString('Laporan Kendaraan', $response->streamedContent());
    }

    public function test_export_fuel_csv(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['user_id' => $user->id]);
        FuelRecord::factory()->create(['user_id' => $user->id, 'vehicle_id' => $vehicle->id, 'fuel_date' => now()->format('Y-m-d')]);
        $this->actingAs($user);
        $response = $this->get(route('export.fuel'));
        $response->assertStatus(200);
        $this->assertStringContainsString('Tanggal', $response->streamedContent());
    }

    public function test_export_service_csv(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['user_id' => $user->id]);
        ServiceRecord::factory()->create(['user_id' => $user->id, 'vehicle_id' => $vehicle->id, 'service_date' => now()->format('Y-m-d')]);
        $this->actingAs($user);
        $response = $this->get(route('export.service'));
        $response->assertStatus(200);
        $this->assertStringContainsString('Tanggal', $response->streamedContent());
    }

    public function test_export_finance_pdf(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $response = $this->get(route('export.finance.pdf', ['preset' => 'month']));
        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_export_vehicle_pdf(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $response = $this->get(route('export.vehicle.pdf', ['preset' => 'month']));
        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_export_isolation(): void
    {
        $u1 = User::factory()->create();
        $u2 = User::factory()->create();
        $account = Account::factory()->create(['user_id' => $u1->id]);
        $cat = Category::factory()->create(['user_id' => $u1->id, 'type' => 'income']);
        Transaction::factory()->create(['user_id' => $u1->id, 'account_id' => $account->id, 'category_id' => $cat->id, 'type' => 'income', 'amount' => 999999, 'transaction_date' => now()->format('Y-m-d')]);

        $this->actingAs($u2);
        $response = $this->get(route('export.transactions'));
        $response->assertStatus(200);
        $this->assertStringNotContainsString('999999', $response->streamedContent());
    }
}
