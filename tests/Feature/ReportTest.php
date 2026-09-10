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

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_finance_report_requires_auth(): void
    {
        $this->get(route('reports.finance'))->assertRedirect(route('login'));
    }

    public function test_finance_report_shows_totals(): void
    {
        $user = User::factory()->create();
        $acc = Account::factory()->create(['user_id' => $user->id]);
        $catInc = Category::factory()->create(['user_id' => $user->id, 'type' => 'income']);
        $catExp = Category::factory()->create(['user_id' => $user->id, 'type' => 'expense']);
        Transaction::factory()->create(['user_id' => $user->id, 'account_id' => $acc->id, 'category_id' => $catInc->id, 'type' => 'income', 'amount' => 500000, 'transaction_date' => now()->format('Y-m-d')]);
        Transaction::factory()->create(['user_id' => $user->id, 'account_id' => $acc->id, 'category_id' => $catExp->id, 'type' => 'expense', 'amount' => 200000, 'transaction_date' => now()->format('Y-m-d')]);

        $this->actingAs($user);
        $response = $this->get(route('reports.finance', ['preset' => 'month']));
        $response->assertStatus(200);
        $response->assertViewHas(['totalIncome', 'totalExpense', 'netCashflow', 'expenseByCategory']);
        $this->assertEquals(500000, (float) $response->viewData('totalIncome'));
        $this->assertEquals(200000, (float) $response->viewData('totalExpense'));
    }

    public function test_finance_date_range_filter(): void
    {
        $user = User::factory()->create();
        $acc = Account::factory()->create(['user_id' => $user->id]);
        $cat = Category::factory()->create(['user_id' => $user->id, 'type' => 'income']);
        Transaction::factory()->create(['user_id' => $user->id, 'account_id' => $acc->id, 'category_id' => $cat->id, 'type' => 'income', 'amount' => 100000, 'transaction_date' => '2026-01-15']);
        Transaction::factory()->create(['user_id' => $user->id, 'account_id' => $acc->id, 'category_id' => $cat->id, 'type' => 'income', 'amount' => 200000, 'transaction_date' => '2026-02-15']);

        $this->actingAs($user);
        $response = $this->get(route('reports.finance', ['start_date' => '2026-01-01', 'end_date' => '2026-01-31', 'preset' => 'custom']));
        $response->assertStatus(200);
        $this->assertEquals(100000, (float) $response->viewData('totalIncome'));
    }

    public function test_vehicle_report(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['user_id' => $user->id]);
        FuelRecord::factory()->create(['user_id' => $user->id, 'vehicle_id' => $vehicle->id, 'fuel_date' => now()->format('Y-m-d'), 'total_cost' => 150000, 'liters' => 10]);
        ServiceRecord::factory()->create(['user_id' => $user->id, 'vehicle_id' => $vehicle->id, 'service_date' => now()->format('Y-m-d'), 'total_cost' => 300000]);

        $this->actingAs($user);
        $response = $this->get(route('reports.vehicle', ['preset' => 'month']));
        $response->assertStatus(200);
        $response->assertViewHas(['fuelStats', 'serviceStats', 'totalVehicleCost', 'perVehicle']);
        $this->assertEquals(150000, (float) $response->viewData('fuelStats')['total']);
        $this->assertEquals(300000, (float) $response->viewData('serviceStats')['total']);
        $this->assertEquals(450000, (float) $response->viewData('totalVehicleCost'));
    }

    public function test_report_isolation(): void
    {
        $u1 = User::factory()->create();
        $u2 = User::factory()->create();
        $acc1 = Account::factory()->create(['user_id' => $u1->id]);
        $cat1 = Category::factory()->create(['user_id' => $u1->id, 'type' => 'income']);
        Transaction::factory()->create(['user_id' => $u1->id, 'account_id' => $acc1->id, 'category_id' => $cat1->id, 'type' => 'income', 'amount' => 999999, 'transaction_date' => now()->format('Y-m-d')]);

        $this->actingAs($u2);
        $response = $this->get(route('reports.finance', ['preset' => 'month']));
        $response->assertStatus(200);
        $this->assertEquals(0, (float) $response->viewData('totalIncome'));
    }
}
