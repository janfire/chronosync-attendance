<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a test user for biometric enrollment testing
        User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'employee_number' => 'CS001',
            'password' => Hash::make('password123'),
            'role' => 'staff',
        ]);

        // Create an admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'employee_number' => 'CS002',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);
    }
}
