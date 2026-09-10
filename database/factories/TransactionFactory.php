<?php

namespace Database\Factories;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(['income', 'expense']);

        return [
            'user_id' => \App\Models\User::factory(),
            'account_id' => \App\Models\Account::factory(),
            'category_id' => \App\Models\Category::factory(),
            'type' => $type,
            'amount' => fake()->randomFloat(2, 10000, 5000000),
            'transaction_date' => fake()->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
            'description' => fake()->sentence(3),
            'notes' => null,
        ];
    }

    public function income(): static
    {
        return $this->state(fn () => ['type' => 'income']);
    }

    public function expense(): static
    {
        return $this->state(fn () => ['type' => 'expense']);
    }

    public function transfer(): static
    {
        return $this->state(fn () => [
            'type' => 'transfer',
            'category_id' => null,
            'transfer_group_id' => fake()->uuid(),
        ]);
    }
}
