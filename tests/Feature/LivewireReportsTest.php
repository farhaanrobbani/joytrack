<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Category;
use App\Models\FuelRecord;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LivewireReportsTest extends TestCase
{
    use RefreshDatabase;

    public function test_finance_component_computes_totals(): void
    {
        $user = User::factory()->create();
        $acc = Account::factory()->create(['user_id' => $user->id]);
        $catInc = Category::factory()->create(['user_id' => $user->id, 'type' => 'income']);
        $catExp = Category::factory()->create(['user_id' => $user->id, 'type' => 'expense']);
        Transaction::factory()->create(['user_id' => $user->id, 'account_id' => $acc->id, 'category_id' => $catInc->id, 'type' => 'income', 'amount' => 500000, 'transaction_date' => now()->format('Y-m-d')]);
        Transaction::factory()->create(['user_id' => $user->id, 'account_id' => $acc->id, 'category_id' => $catExp->id, 'type' => 'expense', 'amount' => 200000, 'transaction_date' => now()->format('Y-m-d')]);

        $this->actingAs($user);

        $component = Livewire::test('reports-finance');
        $this->assertEquals(500000, (float) $component->viewData('totalIncome'));
        $this->assertEquals(200000, (float) $component->viewData('totalExpense'));
        $this->assertEquals(300000, (float) $component->viewData('netCashflow'));
    }

    public function test_finance_component_isolates_users(): void
    {
        $u1 = User::factory()->create();
        $u2 = User::factory()->create();
        $acc = Account::factory()->create(['user_id' => $u1->id]);
        $cat = Category::factory()->create(['user_id' => $u1->id, 'type' => 'income']);
        Transaction::factory()->create(['user_id' => $u1->id, 'account_id' => $acc->id, 'category_id' => $cat->id, 'type' => 'income', 'amount' => 999999, 'transaction_date' => now()->format('Y-m-d')]);

        $this->actingAs($u2);

        $component = Livewire::test('reports-finance');
        $this->assertEquals(0, (float) $component->viewData('totalIncome'));
    }

    public function test_fuel_component_computes_stats(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['user_id' => $user->id]);
        FuelRecord::factory()->create(['user_id' => $user->id, 'vehicle_id' => $vehicle->id, 'fuel_date' => now()->format('Y-m-d'), 'total_cost' => 150000, 'liters' => 10]);
        FuelRecord::factory()->create(['user_id' => $user->id, 'vehicle_id' => $vehicle->id, 'fuel_date' => now()->format('Y-m-d'), 'total_cost' => 100000, 'liters' => 5]);

        $this->actingAs($user);

        $stats = Livewire::test('reports-fuel')->viewData('stats');
        $this->assertEquals(250000, (float) $stats['total']);
        $this->assertEquals(15, (float) $stats['liters']);
        $this->assertEquals(2, (int) $stats['count']);
    }

    public function test_fuel_component_custom_date_range_filter(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['user_id' => $user->id]);
        FuelRecord::factory()->create(['user_id' => $user->id, 'vehicle_id' => $vehicle->id, 'fuel_date' => '2026-01-15', 'total_cost' => 100000]);
        FuelRecord::factory()->create(['user_id' => $user->id, 'vehicle_id' => $vehicle->id, 'fuel_date' => '2026-02-15', 'total_cost' => 200000]);

        $this->actingAs($user);

        $stats = Livewire::test('reports-fuel', [
            'preset' => 'custom',
            'startDate' => '2026-01-01',
            'endDate' => '2026-01-31',
        ])->viewData('stats');
        $this->assertEquals(100000, (float) $stats['total']);
    }
}
