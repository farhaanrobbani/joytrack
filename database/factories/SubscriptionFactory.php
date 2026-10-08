<?php

namespace Database\Factories;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subscription>
 */
class SubscriptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->randomElement(['Netflix', 'Spotify', 'Domain .com', 'Cloud Hosting', 'Gym']),
            'amount' => fake()->randomFloat(2, 15000, 500000),
            'next_renewal_date' => fake()->dateTimeBetween('+1 month', '+12 months')->format('Y-m-d'),
            'reminder_days' => 7,
            'notes' => fake()->optional()->sentence(),
            'is_active' => true,
        ];
    }
}
