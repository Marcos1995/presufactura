<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->onboarded()->create([
            'name' => 'Usuario Demo',
            'email' => 'demo@example.com',
            'plan' => 'free',
        ]);
    }
}
