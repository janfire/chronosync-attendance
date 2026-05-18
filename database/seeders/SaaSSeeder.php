<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SaaSSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Subscription Plans
        $plans = [
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'price_usd' => 15.00,
                'max_employees' => 20,
                'features' => json_encode(['Facial Recognition', 'Basic Reports', 'Mobile Access']),
            ],
            [
                'name' => 'Business',
                'slug' => 'business',
                'price_usd' => 35.00,
                'max_employees' => 75,
                'features' => json_encode(['Facial Recognition', 'Advanced Analytics', 'QR Codes', 'Attendance Scores']),
            ],
            [
                'name' => 'Corporate',
                'slug' => 'corporate',
                'price_usd' => 75.00,
                'max_employees' => 0, // Unlimited
                'features' => json_encode(['Unlimited Employees', 'Biometric Hardware Support', 'API Access', 'Dedicated Support']),
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(['slug' => $plan['slug']], $plan);
        }

        // 2. Create Demo Tenant
        $tenant = Tenant::updateOrCreate(
            ['subdomain' => 'taxease'],
            [
                'company_name' => 'TaxEase ZW',
                'email' => 'contact@taxease.co.zw',
                'plan' => 'business',
                'status' => 'active',
                'max_employees' => 75,
                'trial_ends_at' => now()->addDays(14),
                'subscription_expires_at' => now()->addDays(30),
                'ecocash_number' => '0771234567',
                'zipit_account' => 'Stanbic Bank - 914000123456',
            ]
        );

        // 3. Associate all existing users with this tenant
        // We use DB::table to bypass the trait's global scope during seeding
        DB::table('users')->update(['tenant_id' => $tenant->id]);
        
        // Also associate other tables if they have data
        DB::table('attendance_logs')->update(['tenant_id' => $tenant->id]);
        DB::table('biometric_data')->update(['tenant_id' => $tenant->id]);
        DB::table('failed_attendance_logs')->update(['tenant_id' => $tenant->id]);
        DB::table('attendance_scores')->update(['tenant_id' => $tenant->id]);
        DB::table('attendance_summaries')->update(['tenant_id' => $tenant->id]);
        DB::table('system_settings')->update(['tenant_id' => $tenant->id]);
        DB::table('webauthn_credentials')->update(['tenant_id' => $tenant->id]);

        echo "SaaS Seeding Complete. Demo tenant 'taxease' created and users associated.\n";
    }
}
