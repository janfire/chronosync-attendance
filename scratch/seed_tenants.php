<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Define defaults directly to avoid invoking controller which might need request
$defaults = [
    'gamification_rules' => [
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
    'geofences' => [
        ['name' => 'ZOU National Centre', 'lat' => -17.825166, 'lon' => 31.033510, 'radius' => 500],
        ['name' => 'ZOU Harare Main Campus', 'lat' => -17.8273817, 'lon' => 31.0448893, 'radius' => 200],
        ['name' => 'ZOU Harare Regional Campus', 'lat' => -17.821629, 'lon' => 31.049226, 'radius' => 500]
    ],
    'biometric_tolerances' => ['default' => 0.38, 'enrollment' => 0.42],
    'pagination_limits' => ['default_rows' => 20, 'exceptions_rows' => 25, 'export_limit' => 500],
    'expirations' => ['summary_link_minutes' => 20, 'otp_minutes' => 10, 'pending_login_minutes' => 5, 'default_shift_hours' => 8]
];

$tenants = \App\Models\Tenant::all();
foreach ($tenants as $tenant) {
    foreach ($defaults as $key => $value) {
        if (!\App\Models\SystemSetting::where('tenant_id', $tenant->id)->where('key', $key)->exists()) {
            \App\Models\SystemSetting::create(['tenant_id' => $tenant->id, 'key' => $key, 'value' => $value]);
        }
    }
}
echo "Seeded existing tenants!\n";
