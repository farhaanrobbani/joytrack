<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Document>
 */
class DocumentFactory extends Factory
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
            'vehicle_id' => null,
            'name' => fake()->randomElement(['STNK Mobil', 'SIM A', 'Pajak Kendaraan', 'Asuransi All Risk', 'Paspor']),
            'document_type' => fake()->randomElement(Document::TYPES),
            'expiry_date' => fake()->dateTimeBetween('+1 month', '+12 months')->format('Y-m-d'),
            'reminder_days' => 7,
            'notes' => fake()->optional()->sentence(),
            'is_active' => true,
        ];
    }
}
