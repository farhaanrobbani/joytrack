<?php

namespace Database\Factories;

use App\Models\ServiceRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceRecord>
 */
class ServiceRecordFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $labor = fake()->randomFloat(2, 50000, 500000);
        $parts = fake()->randomFloat(2, 0, 1000000);
        return [
            'user_id' => \App\Models\User::factory(),
            'vehicle_id' => \App\Models\Vehicle::factory(),
            'service_date' => fake()->dateTimeBetween('-3 months', 'now')->format('Y-m-d'),
            'odometer' => fake()->numberBetween(5000, 80000),
            'service_type' => fake()->randomElement(['Servis rutin', 'Ganti oli', 'Ganti kampas rem', 'Servis CVT', 'Perbaikan']),
            'workshop' => fake()->randomElement(['Bengkel ABC', 'Bengkel Resmi', 'Bengkel Jaya']),
            'labor_cost' => $labor,
            'parts_cost' => $parts,
            'total_cost' => $labor + $parts,
            'next_service_date' => fake()->optional()->dateTimeBetween('now', '+6 months')?->format('Y-m-d'),
            'next_service_odometer' => fake()->optional()->numberBetween(80000, 100000),
        ];
    }
}
