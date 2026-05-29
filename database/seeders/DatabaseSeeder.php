<?php

namespace Database\Seeders;

use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create the platform-level administrator account (tenant_id = null)
        User::withoutTenantScope()->create([
            'name'            => 'Platform Admin',
            'email'           => 'admin@zou.ac.zw',
            'password'        => Hash::make('forgodsoloved*1'),
            'role'            => UserRole::PLATFORM_ADMIN,
            'employee_number' => 'PLATFORM-ADMIN-001',
            'tenant_id'       => null,
        ]);

        // 2. Create the demo users (initially tenant_id = null)
        $this->call(UserSeeder::class);

        // 3. Create the demo tenant and associate all demo users with it
        $this->call(SaaSSeeder::class);
    }
}
