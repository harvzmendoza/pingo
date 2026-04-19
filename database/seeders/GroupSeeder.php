<?php

namespace Database\Seeders;

use App\Models\Group;
use Illuminate\Database\Seeder;

class GroupSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Swimming', 'Basketball'] as $name) {
            Group::query()->firstOrCreate(
                ['name' => $name],
            );
        }
    }
}
