<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $admin = User::factory()->create([
            'name' => 'Admin KWSP',
            'email' => 'admin@kwsp.my',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
        ]);

        \App\Models\Wallet::create([
            'user_id' => $admin->id,
            'currency' => 'MYR',
            'balance' => 5000.00,
        ]);

        \App\Models\InvestmentPlan::create([
            'name' => 'Starter',
            'description' => 'Min RM 100',
            'min_amount' => 100,
            'roi_daily_percent' => 2.0,
            'duration_days' => 30,
            'sort_order' => 10,
        ]);

        \App\Models\InvestmentPlan::create([
            'name' => 'Pro',
            'description' => 'Min RM 1000',
            'min_amount' => 1000,
            'roi_daily_percent' => 5.0,
            'duration_days' => 30,
            'sort_order' => 20,
        ]);
    }
}
