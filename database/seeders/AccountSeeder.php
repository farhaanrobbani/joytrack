<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = \App\Models\User::all();

        foreach ($users as $user) {
            \App\Models\Account::create([
                'user_id' => $user->id,
                'name' => 'Cash',
                'type' => 'cash',
                'initial_balance' => 500000.00,
                'current_balance' => 500000.00,
                'description' => 'Uang tunai dompet',
                'is_active' => true,
            ]);

            \App\Models\Account::create([
                'user_id' => $user->id,
                'name' => 'BCA',
                'type' => 'bank',
                'initial_balance' => 2500000.00,
                'current_balance' => 2500000.00,
                'description' => 'Rekening utama BCA',
                'is_active' => true,
            ]);
        }
    }
}
