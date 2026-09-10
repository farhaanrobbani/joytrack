<?php

namespace Database\Factories;

use App\Models\FuelRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FuelRecord>
 */
class FuelRecordFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $liters = fake()->randomFloat(2, 5, 30);
        $price = fake()->randomFloat(2, 10000, 15000);
        return [
            'user_id' => \App\Models\User::factory(),
            'vehicle_id' => \App\Models\Vehicle::factory(),
            'fuel_date' => fake()->dateTimeBetween('-2 months', 'now')->format('Y-m-d'),
            'odometer' => fake()->numberBetween(1000, 80000),
            'fuel_type' => fake()->randomElement(['Pertalite', 'Pertamax', 'Solar']),
            'liters' => $liters,
            'price_per_liter' => $price,
            'total_cost' => round($liters * $price, 2),
            'station' => fake()->randomElement(['SPBU 1', 'SPBU 2', 'Shell']),
        ];
    }
}
