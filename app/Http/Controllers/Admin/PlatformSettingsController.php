<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GlobalSetting;
use Illuminate\Http\Request;

class PlatformSettingsController extends Controller
{
    public function index()
    {
        $settings = [
            'maintenance_mode' => GlobalSetting::get('maintenance_mode', false),
            'maintenance_message' => GlobalSetting::get('maintenance_message', 'The system is currently undergoing scheduled maintenance. Please check back later.'),
            'default_trial_days' => GlobalSetting::get('default_trial_days', 14),
            'allow_new_registrations' => GlobalSetting::get('allow_new_registrations', true),
        ];

        return view('admin.superadmin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'maintenance_mode' => 'nullable|boolean',
            'maintenance_message' => 'nullable|string',
            'default_trial_days' => 'required|integer|min:0',
            'allow_new_registrations' => 'nullable|boolean',
        ]);

        GlobalSetting::set('maintenance_mode', $request->has('maintenance_mode'));
        GlobalSetting::set('maintenance_message', $validated['maintenance_message'] ?? '');
        GlobalSetting::set('default_trial_days', $validated['default_trial_days']);
        GlobalSetting::set('allow_new_registrations', $request->has('allow_new_registrations'));

        return redirect()->back()->with('success', 'Platform settings updated successfully.');
    }
}
