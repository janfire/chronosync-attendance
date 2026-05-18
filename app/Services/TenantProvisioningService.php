<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\User;
use App\Models\SystemSetting;
use App\Enums\UserRole;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class TenantProvisioningService
{
    /**
     * Provision a new tenant and its first admin user.
     */
    public function provision(array $data)
    {
        return DB::transaction(function () use ($data) {
            // 1. Create Tenant
            $tenant = Tenant::create([
                'company_name' => $data['company_name'],
                'subdomain'    => $data['subdomain'],
                'email'        => $data['email'],
                'phone'        => $data['phone'] ?? null,
                'plan'         => $data['plan'] ?? 'starter',
                'status'       => 'trial',
                'trial_ends_at' => now()->addDays(14),
                'max_employees' => 20, // Default for starter
            ]);

            // Bind tenant so subsequent model creations are scoped
            app()->instance('current_tenant', $tenant);

            // 2. Create Admin User
            $admin = User::create([
                'name'            => $data['admin_name'],
                'email'           => $data['admin_email'],
                'password'        => Hash::make($data['password']),
                'role'            => UserRole::SUPER_ADMIN, // The company owner is super_admin for their tenant
                'employee_number' => 'ADMIN-001',
                'tenant_id'       => $tenant->id,
            ]);

            // 3. Seed Default Settings
            SystemSetting::create([
                'key' => 'shift_rules',
                'value' => [
                    'shift_start' => '08:00',
                    'shift_end' => '16:30',
                    'late_after' => '09:00',
                    'early_out_before' => '15:50',
                ],
                'tenant_id' => $tenant->id,
            ]);

            return [
                'tenant' => $tenant,
                'admin'  => $admin
            ];
        });
    }
}
