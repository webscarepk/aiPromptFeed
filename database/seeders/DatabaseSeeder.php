<?php

namespace Database\Seeders;

use App\Models\User;
use Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Iqrash Ahmad',
            'email' => 'iqrash@gmail.com',
            'password' => Hash::make('iqrash1122'),
            'email_verified_at' => now(),
        ]);
        User::create([
            'name' => 'X Agent',
            'email' => 'xagent891@gmail.com',
            'password' => Hash::make('iqrash1122'),
            'email_verified_at' => now(),
        ]);
    }
}
