<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->onboarded()->create([
            'name' => 'Usuario Free',
            'email' => 'free@example.com',
            'plan' => 'free',
        ]);

        User::factory()->onboarded()->create([
            'name' => 'Usuario Pro',
            'email' => 'pro@example.com',
            'plan' => 'pro',
        ]);
    }
}
