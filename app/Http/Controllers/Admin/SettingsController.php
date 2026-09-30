<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = [
            'gamification_rules' => SystemSetting::get('gamification_rules', $this->getDefaultGamificationRules()),
            'geofences' => SystemSetting::get('geofences', $this->getDefaultGeofences()),
            'biometric_tolerances' => SystemSetting::get('biometric_tolerances', $this->getDefaultBiometricTolerances()),
            'pagination_limits' => SystemSetting::get('pagination_limits', $this->getDefaultPaginationLimits()),
            'expirations' => SystemSetting::get('expirations', $this->getDefaultExpirations()),
        ];

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'gamification_rules' => 'array',
            'geofences' => 'array',
            'biometric_tolerances' => 'array',
            'pagination_limits' => 'array',
            'expirations' => 'array',
        ]);

        if ($request->has('gamification_rules')) {
            SystemSetting::set('gamification_rules', $request->input('gamification_rules'));
        }
        
        if ($request->has('geofences')) {
            // Re-index array to remove any gaps from deleted locations
            $geofences = array_values($request->input('geofences'));
            SystemSetting::set('geofences', $geofences);
        } else {
            // If the array is empty (all removed), we still want to save an empty array
            if ($request->has('geofences_submitted')) {
                 SystemSetting::set('geofences', []);
            }
        }

        if ($request->has('biometric_tolerances')) {
            SystemSetting::set('biometric_tolerances', [
                'default' => (float) $request->input('biometric_tolerances.default'),
                'enrollment' => (float) $request->input('biometric_tolerances.enrollment'),
            ]);
        }

        if ($request->has('pagination_limits')) {
            SystemSetting::set('pagination_limits', [
                'default_rows' => (int) $request->input('pagination_limits.default_rows'),
                'exceptions_rows' => (int) $request->input('pagination_limits.exceptions_rows'),
                'export_limit' => (int) $request->input('pagination_limits.export_limit'),
            ]);
        }

        if ($request->has('expirations')) {
            SystemSetting::set('expirations', [
                'summary_link_minutes' => (int) $request->input('expirations.summary_link_minutes'),
                'otp_minutes' => (int) $request->input('expirations.otp_minutes'),
                'pending_login_minutes' => (int) $request->input('expirations.pending_login_minutes'),
                'default_shift_hours' => (int) $request->input('expirations.default_shift_hours'),
            ]);
        }

        return redirect()->back()->with('success', 'System Settings updated successfully.');
    }

    public function reset()
    {
        SystemSetting::set('gamification_rules', $this->getDefaultGamificationRules());
        SystemSetting::set('geofences', $this->getDefaultGeofences());
        SystemSetting::set('biometric_tolerances', $this->getDefaultBiometricTolerances());
        SystemSetting::set('pagination_limits', $this->getDefaultPaginationLimits());
        SystemSetting::set('expirations', $this->getDefaultExpirations());

        Log::info('System Settings were reset to defaults by admin.', ['user_id' => auth()->id()]);

        return redirect()->back()->with('success', 'System Settings have been reset to their default values.');
    }

    private function getDefaultGamificationRules()
    {
        return [
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
        ];
    }

    private function getDefaultGeofences()
    {
        return [
            [
                'name' => 'ZOU National Centre',
                'lat' => -17.825166,
                'lon' => 31.033510,
                'radius' => 500,
            ],
            [
                'name' => 'ZOU Harare Main Campus',
                'lat' => -17.8273817,
                'lon' => 31.0448893,
                'radius' => 200,
            ],
            [
                'name' => 'ZOU Harare Regional Campus',
                'lat' => -17.821629,
                'lon' => 31.049226,
                'radius' => 500,
            ]
        ];
    }

    private function getDefaultBiometricTolerances()
    {
        return [
            'default' => 0.38,
            'enrollment' => 0.42,
        ];
    }

    private function getDefaultPaginationLimits()
    {
        return [
            'default_rows' => 20,
            'exceptions_rows' => 25,
            'export_limit' => 500,
        ];
    }

    private function getDefaultExpirations()
    {
        return [
            'summary_link_minutes' => 20,
            'otp_minutes' => 10,
            'pending_login_minutes' => 5,
            'default_shift_hours' => 8,
        ];
    }
}
