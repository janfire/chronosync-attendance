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
                'email'           => $data['email'],
                'password'        => Hash::make($data['password']),
                'role'            => UserRole::SUPER_ADMIN, // The company owner is super_admin for their tenant
                'employee_number' => 'ADMIN-001',
                'tenant_id'       => $tenant->id,
            ]);

            // 3. Seed Default Settings
            SystemSetting::create([
                'key' => 'gamification_rules',
                'value' => [
                    'early_before' => '07:50',
                    'punctual_before' => '08:00',
                    'on_time_before' => '08:15',
                    'grace_before' => '08:30',
                    'early_departure_before' => '16:00',
                    'left_early_before' => '16:30',
                    'normal_departure_before' => '18:00',
                    'early_points' => 10,
                    'punctual_points' => 10,
                    'on_time_points' => -1,
                    'grace_points' => -2,
                    'late_points' => -5,
                    'early_departure_points' => -5,
                    'left_early_points' => -2,
                    'normal_departure_points' => 5,
                    'late_departure_points' => 0,
                ],
                'tenant_id' => $tenant->id,
            ]);

            SystemSetting::create([
                'key' => 'geofences',
                'value' => [
                    ['name' => 'ZOU National Centre', 'lat' => -17.825166, 'lon' => 31.033510, 'radius' => 500],
                    ['name' => 'ZOU Harare Main Campus', 'lat' => -17.8273817, 'lon' => 31.0448893, 'radius' => 200],
                    ['name' => 'ZOU Harare Regional Campus', 'lat' => -17.821629, 'lon' => 31.049226, 'radius' => 500]
                ],
                'tenant_id' => $tenant->id,
            ]);

            SystemSetting::create([
                'key' => 'biometric_tolerances',
                'value' => ['default' => 0.38, 'enrollment' => 0.42],
                'tenant_id' => $tenant->id,
            ]);

            SystemSetting::create([
                'key' => 'pagination_limits',
                'value' => ['default_rows' => 20, 'exceptions_rows' => 25, 'export_limit' => 500],
                'tenant_id' => $tenant->id,
            ]);

            SystemSetting::create([
                'key' => 'expirations',
                'value' => ['summary_link_minutes' => 20, 'otp_minutes' => 10, 'pending_login_minutes' => 5, 'default_shift_hours' => 8],
                'tenant_id' => $tenant->id,
            ]);

            return [
                'tenant' => $tenant,
                'admin'  => $admin
            ];
        });
    }
}
