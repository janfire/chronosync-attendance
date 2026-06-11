<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (\Illuminate\Support\Facades\Schema::hasTable('tenants')) {
            $tenant = \App\Models\Tenant::firstOrCreate(
                ['subdomain' => 'mminc'],
                [
                    'company_name' => 'MM Inc',
                    'email' => 'contact@mminc.test',
                    'plan' => 'business',
                    'status' => 'active',
                    'trial_ends_at' => now()->addDays(14),
                    'subscription_expires_at' => now()->addDays(30),
                ]
            );
            app()->instance('current_tenant', $tenant);
        }
    }
}
