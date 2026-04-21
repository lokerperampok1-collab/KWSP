<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InvestmentPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\InvestmentPlan::query()->delete();

        $plans = [
            // BASIC
            ['tier' => 'BASIC', 'name' => 'BASIC 1', 'price' => 500, 'target_return' => 15000, 'duration_days' => 7, 'sort_order' => 1],
            ['tier' => 'BASIC', 'name' => 'BASIC 2', 'price' => 1000, 'target_return' => 31000, 'duration_days' => 7, 'sort_order' => 2],
            ['tier' => 'BASIC', 'name' => 'BASIC 3', 'price' => 1300, 'target_return' => 39000, 'duration_days' => 7, 'sort_order' => 3],
            ['tier' => 'BASIC', 'name' => 'BASIC 4', 'price' => 1500, 'target_return' => 45000, 'duration_days' => 7, 'sort_order' => 4],
            
            // GOLD
            ['tier' => 'GOLD', 'name' => 'GOLD 1', 'price' => 2000, 'target_return' => 70000, 'duration_days' => 7, 'sort_order' => 5],
            ['tier' => 'GOLD', 'name' => 'GOLD 2', 'price' => 3000, 'target_return' => 105000, 'duration_days' => 7, 'sort_order' => 6],
            ['tier' => 'GOLD', 'name' => 'GOLD 3', 'price' => 4000, 'target_return' => 140000, 'duration_days' => 7, 'sort_order' => 7],
            
            // DIAMOND
            ['tier' => 'DIAMOND', 'name' => 'DIAMOND 1', 'price' => 5000, 'target_return' => 200000, 'duration_days' => 7, 'sort_order' => 8],
            ['tier' => 'DIAMOND', 'name' => 'DIAMOND 2', 'price' => 7000, 'target_return' => 280000, 'duration_days' => 7, 'sort_order' => 9],
            ['tier' => 'DIAMOND', 'name' => 'DIAMOND 3', 'price' => 10000, 'target_return' => 400000, 'duration_days' => 7, 'sort_order' => 10],
            
            // VVIP
            ['tier' => 'VVIP', 'name' => 'VVIP LUXURY', 'price' => 15000, 'target_return' => 580000, 'duration_days' => 7, 'sort_order' => 11],
            ['tier' => 'VVIP', 'name' => 'VVIP ELITE', 'price' => 20000, 'target_return' => 600000, 'duration_days' => 7, 'sort_order' => 12],
        ];

        foreach ($plans as $plan) {
            \App\Models\InvestmentPlan::create(array_merge($plan, [
                'status' => true,
                'description' => "Pakej pelaburan {$plan['tier']} dengan pulangan tetap RM " . number_format($plan['target_return'], 0),
            ]));
        }
    }
}
