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

        // User::firstOrCreate(
        //     ['email' => 'test@example.com'],
        //     [
        //         'name' => 'Test User',
        //         'password' => 'password',
        //         'email_verified_at' => now(),
        //     ]
        // );
        $admin = User::create([
            'name' => 'admin',
            'email' => 'admin@gmail.com',
            'password' => 12345678,
            'email_verified_at' => now(),
            'role' => 'admin'
        ]);

        $admin = User::create([
            'name' => 'lina',
            'email' => 'lina@gmail.com',
            'password' => 12345678,
            'email_verified_at' => now(),
            'role' => 'admin'
        ]);

        $purchase = User::create([
            'name' => 'shita',
            'email' => 'shita@gmail.com',
            'password' => 12345678,
            'email_verified_at' => now(),
            'role' => 'purchase'
        ]);

        $sales = User::create([
            'name' => 'mega',
            'email' => 'mega@gmail.com',
            'password' => 12345678,
            'email_verified_at' => now(),
            'role' => 'sales'
        ]);
    }
}
