<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Plan::query()->upsert([
            [
                'name' => 'Free',
                'price' => 0,
                'sms_limit' => 30,
                'description' => 'One-time 3-day trial. Up to 50 SMS per day; allowance auto-renews each calendar day until the trial ends.',
            ],
            [
                'name' => 'Starter',
                'price' => 499,
                'sms_limit' => 200,
                'description' => '200 SMS per day (resets daily).',
            ],
            [
                'name' => 'Growth',
                'price' => 899,
                'sms_limit' => 500,
                'description' => '500 SMS per day (resets daily).',
            ],
        ], uniqueBy: ['name'], update: ['price', 'sms_limit', 'description']);
    }
}
