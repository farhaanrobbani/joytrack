<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            // income
            ['name' => 'Gaji', 'type' => 'income'],
            ['name' => 'Bonus', 'type' => 'income'],
            ['name' => 'Usaha', 'type' => 'income'],
            ['name' => 'Investasi', 'type' => 'income'],
            ['name' => 'Hadiah', 'type' => 'income'],
            ['name' => 'Lainnya', 'type' => 'income'],
            // expense - PRD §7 examples
            ['name' => 'Makanan', 'type' => 'expense'],
            ['name' => 'Transportasi', 'type' => 'expense'],
            ['name' => 'Belanja', 'type' => 'expense'],
            ['name' => 'Tagihan', 'type' => 'expense'],
            ['name' => 'Listrik', 'type' => 'expense'],
            ['name' => 'Internet', 'type' => 'expense'],
            ['name' => 'Pendidikan', 'type' => 'expense'],
            ['name' => 'Kesehatan', 'type' => 'expense'],
            ['name' => 'Hiburan', 'type' => 'expense'],
            ['name' => 'Kendaraan', 'type' => 'expense'],
            ['name' => 'Servis', 'type' => 'expense'],
            ['name' => 'Bahan Bakar', 'type' => 'expense'],
            ['name' => 'Lainnya', 'type' => 'expense'],
        ];

        $users = \App\Models\User::all();

        // If no user exists, seed will be used per-user creation later via observer/service
        // For now seed for each existing user
        foreach ($users as $user) {
            foreach ($defaults as $cat) {
                \App\Models\Category::firstOrCreate(
                    ['user_id' => $user->id, 'name' => $cat['name'], 'type' => $cat['type']],
                    ['is_active' => true]
                );
            }
        }
    }
}
