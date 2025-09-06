<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Shipper One',
            'email' => 'shipper1@example.com',
            'password' => bcrypt('password'),
            'role' => 'shipper',
        ]);

        User::factory()->create([
            'name' => 'Carrier One',
            'email' => 'carrier1@example.com',
            'password' => bcrypt('password'),
            'role' => 'carrier',
        ]);

        User::factory()->count(5)->shipper()->create();
        User::factory()->count(10)->carrier()->create();
    }
}
