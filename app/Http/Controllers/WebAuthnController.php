<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\WebAuthnCredential;
use App\Models\AttendanceLog;
use App\Services\LocationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use App\Http\Responses\ApiResponse;

class WebAuthnController extends Controller
{
    protected LocationService $locationService;

    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
    }

    private function getWebAuthn(): \lbuchs\WebAuthn\WebAuthn
    {
        $rpId = request()->getHost(); 
        if (filter_var($rpId, FILTER_VALIDATE_IP) || $rpId === 'localhost') {
            if (filter_var($rpId, FILTER_VALIDATE_IP)) {
                $rpId = null; 
            }
        }
        $formats = ['android-key', 'android-safetynet', 'apple', 'fido-u2f', 'none', 'packed', 'tpm'];
        return new \lbuchs\WebAuthn\WebAuthn('Zou Attendance', $rpId, $formats);
    }

    private function getUserContext(): array
    {
        if (session()->has('pending_registration')) {
            $registrationData = session('pending_registration');
            return [$registrationData['name'] ?? 'Unknown User', null];
        }
        if (\Illuminate\Support\Facades\Auth::check()) {
            $user = \Illuminate\Support\Facades\Auth::user();
            return [$user->name, $user->id];
        }
        return ['Unknown User', null];
    }

    public function registerOptions(Request $request)
    {
        list($userName, $userId) = $this->getUserContext();
        
        if (!$userId && !session()->has('pending_registration')) {
            return ApiResponse::error('User session not found. Please register first.', 401);
        }

        $webauthnUserId = $userId ?: md5(session('pending_registration.email') . session()->getId());

        try {
            $webAuthn = $this->getWebAuthn();
            $args = $webAuthn->getCreateArgs((string)$webauthnUserId, $userName, $userName, 60, true, 'required', true);

            if (isset($args->publicKey)) {
                $args->publicKey->challenge = base64_encode($args->publicKey->challenge);
                $args->publicKey->user->id = base64_encode($args->publicKey->user->id);
                if (empty($args->publicKey->rp->id)) unset($args->publicKey->rp->id);
            } else {
                 if (isset($args->challenge)) $args->challenge = base64_encode($args->challenge);
                 if (isset($args->user->id)) $args->user->id = base64_encode($args->user->id);
            }

            $request->session()->put('webauthn_challenge', base64_encode($webAuthn->getChallenge()));
            return response()->json($args);
        } catch (\Throwable $e) {
            Log::error('WebAuthn options error: ' . $e->getMessage());
            return ApiResponse::error('Failed to generate options: ' . $e->getMessage(), 500);
        }
    }

    public function registerVerify(Request $request)
    {
        list($userName, $userId) = $this->getUserContext();
        
        if (!$userId && !session()->has('pending_registration')) {
            return ApiResponse::error('User session not found', 401);
        }

        try {
            $clientDataJSON = base64_decode($request->clientDataJSON);
            $attestationObject = base64_decode($request->attestationObject);
            $challengeB64 = $request->session()->get('webauthn_challenge');
            $challenge = $challengeB64 ? base64_decode($challengeB64) : null;

            if (!$challenge) return ApiResponse::error('Session timed out.', 419);

            $webAuthn = $this->getWebAuthn();
            $data = $webAuthn->processCreate($clientDataJSON, $attestationObject, $challenge, true, true, false);

            $b64CredentialId = base64_encode($webAuthn->getCredentialId());
            $b64PublicKey = base64_encode($webAuthn->getCredentialPublicKey());

            if ($userId) {
                WebAuthnCredential::create([
                    'user_id' => $userId,
                    'credential_id' => $b64CredentialId,
                    'public_key' => $b64PublicKey,
                    'counter' => $webAuthn->getSignatureCounter() ?? 0,
                ]);
            } else {
                session(['pending_webauthn_credential' => [
                    'credential_id' => $b64CredentialId,
                    'public_key' => $b64PublicKey,
                    'counter' => $webAuthn->getSignatureCounter() ?? 0,
                ]]);
            }

            $request->session()->forget('webauthn_challenge');
            return ApiResponse::success(['status' => 'success'], 'Fingerprint enrolled successfully');
        } catch (\Throwable $e) {
            Log::error('WebAuthn verification failed: ' . $e->getMessage());
            return ApiResponse::error('Verification failed: ' . $e->getMessage(), 422);
        }
    }

    public function loginOptions(Request $request)
    {
        try {
            $webAuthn = $this->getWebAuthn();
            $args = $webAuthn->getGetArgs([], 60, true, true, true, true, 'required');

            if (isset($args->publicKey)) {
                $args->publicKey->challenge = base64_encode($args->publicKey->challenge);
                if (empty($args->publicKey->rpId)) unset($args->publicKey->rpId);
            } else {
                 if (isset($args->challenge)) $args->challenge = base64_encode($args->challenge);
            }

            $request->session()->put('webauthn_challenge', base64_encode($webAuthn->getChallenge()));
            return response()->json($args);
        } catch (\Throwable $e) {
            Log::error('WebAuthn login options error: ' . $e->getMessage());
            return ApiResponse::error('Failed to generate options', 500);
        }
    }

    public function loginVerify(Request $request)
    {
        try {
            $clientDataJSON = base64_decode($request->clientDataJSON);
            $authenticatorData = base64_decode($request->authenticatorData);
            $signature = base64_decode($request->signature);
            $id = base64_decode($request->id);

            $challengeB64 = $request->session()->get('webauthn_challenge');
            $challenge = $challengeB64 ? base64_decode($challengeB64) : null;

            if (!$challenge) return ApiResponse::error('Session timed out.', 419);

            $credential = WebAuthnCredential::where('credential_id', base64_encode($id))->first();
            if (!$credential) return ApiResponse::error('Credential not found. Please register your device first.', 404);

            $webAuthn = $this->getWebAuthn();
            $webAuthn->processGet($clientDataJSON, $authenticatorData, $signature, base64_decode($credential->public_key), $challenge, $credential->counter, 'required');

            $credential->counter = $webAuthn->getSignatureCounter() ?? 0;
            $credential->save();
            $request->session()->forget('webauthn_challenge');

            $user = $credential->user;
            $controller = app()->make(AttendanceController::class);
            return $this->processAttendanceForUser($controller, $user, $request, 'device_biometric');

        } catch (\Throwable $e) {
            Log::error('WebAuthn login failed: ' . $e->getMessage());
            return ApiResponse::error('Authentication failed: ' . $e->getMessage(), 422);
        }
    }

    private function processAttendanceForUser($attendanceController, $user, Request $request, string $source)
    {
        $lastLog = AttendanceLog::where('user_id', $user->id)->whereDate('timestamp', today())->latest('timestamp')->first();
        $action = (!$lastLog || $lastLog->action === 'clock_out') ? 'clock_in' : 'clock_out';
        $userAgent = $request->header('User-Agent');
        $isMobile = preg_match('/(android|bb\d+|meego).+mobile|iphone|ipod/i', $userAgent);

        if ($lastLog && $lastLog->action === 'clock_in') {
            $redirectUrl = URL::signedRoute('attendance.summary', ['user_id' => $user->id, 'prompt_clockout' => 1]);
            return ApiResponse::success([
                'action' => $lastLog->action,
                'user_name' => $user->name,
                'timestamp' => $lastLog->timestamp->format('Y-m-d H:i:s'),
                'method' => 'Device Biometric',
                'redirect_url' => $redirectUrl,
                'is_mobile' => $isMobile
            ], "Welcome back, {$user->name}! Redirecting to your profile...");
        }

        $networkInfo = $request->header('X-Forwarded-For') ?? $request->ip();
        $locationName = $this->locationService->resolveLocation($request->latitude, $request->longitude);

        AttendanceLog::create([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'action' => $action,
            'timestamp' => now(),
            'network_info' => $networkInfo,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'location_accuracy' => $request->accuracy,
            'location_name' => $locationName,
            'source' => $source,
        ]);

        $redirectUrl = URL::signedRoute('attendance.summary', ['user_id' => $user->id]);
        if (\Illuminate\Support\Facades\Auth::check()) \Illuminate\Support\Facades\Auth::guard('web')->logout();

        $message = $action === 'clock_in' ? "Welcome, {$user->name}! You have successfully clocked in." : "Goodbye, {$user->name}! You have successfully clocked out. Have a great day!";

        return ApiResponse::success([
            'action' => $action,
            'user_name' => $user->name,
            'timestamp' => now()->format('Y-m-d H:i:s'),
            'method' => 'Device Biometric',
            'redirect_url' => $redirectUrl,
            'is_mobile' => $isMobile
        ], $message);
    }
}
