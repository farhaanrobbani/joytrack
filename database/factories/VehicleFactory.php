<?php

namespace Database\Factories;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vehicle>
 */
class VehicleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'name' => fake()->randomElement(['Honda Vario', 'Yamaha NMAX', 'Toyota Avanza', 'Honda Beat']) . ' ' . fake()->numberBetween(125, 150),
            'license_plate' => strtoupper(fake()->bothify('? #### ???')),
            'brand' => fake()->randomElement(['Honda', 'Yamaha', 'Toyota', 'Suzuki']),
            'model' => fake()->word(),
            'year' => fake()->numberBetween(2015, 2026),
            'color' => fake()->randomElement(['Hitam', 'Putih', 'Merah', 'Silver']),
            'vehicle_type' => fake()->randomElement(['motor', 'mobil']),
            'current_odometer' => fake()->numberBetween(0, 80000),
            'is_active' => true,
        ];
    }
}
