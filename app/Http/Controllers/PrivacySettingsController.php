<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\BiometricData;
use Illuminate\Support\Facades\Log;

class PrivacySettingsController extends Controller
{
    public function revokeConsent(Request $request)
    {
        $user = Auth::user();

        // 1. Update user consent to false
        $user->update([
            'biometric_consent_granted' => false,
            'biometric_consent_timestamp' => null,
            'biometric_consent_ip' => null,
            'policy_version_agreed' => null,
        ]);

        // 2. Delete biometric data (facial and fingerprint)
        $biometric = BiometricData::where('user_id', $user->id)->first();
        if ($biometric) {
            $biometric->update([
                'facial_encoding' => null,
                'facial_status' => 'not_enrolled',
                'facial_captured_at' => null,
                'fingerprint_template' => null,
                'fingerprint_status' => 'not_enrolled',
                'fingerprint_captured_at' => null,
                'fingerprint_device_id' => null,
            ]);
        }

        Log::info('User revoked biometric consent and data was deleted', ['user_id' => $user->id]);

        return redirect()->back()->with('success', 'Your biometric consent has been revoked and all related data has been permanently deleted.');
    }
}
