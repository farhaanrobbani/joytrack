<?php

namespace Database\Factories;

use App\Models\Account;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Account>
 */
class AccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = ['bank', 'cash', 'ewallet', 'savings', 'other'];
        $initialBalance = $this->faker->randomFloat(2, 0, 10000000);

        return [
            'user_id' => \App\Models\User::factory(),
            'name' => $this->faker->word . ' ' . $this->faker->randomElement(['Account', 'Bank', 'Wallet']),
            'type' => $this->faker->randomElement($types),
            'initial_balance' => $initialBalance,
            'current_balance' => $initialBalance,
            'description' => $this->faker->sentence,
            'is_active' => true,
        ];
    }
}
