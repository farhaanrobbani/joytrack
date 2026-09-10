<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
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
            'name' => fake()->words(2, true),
            'type' => fake()->randomElement(['income', 'expense']),
            'icon' => null,
            'is_active' => true,
        ];
    }

    public function income(): static
    {
        return $this->state(fn (array $attrs) => ['type' => 'income']);
    }

    public function expense(): static
    {
        return $this->state(fn (array $attrs) => ['type' => 'expense']);
    }
}
