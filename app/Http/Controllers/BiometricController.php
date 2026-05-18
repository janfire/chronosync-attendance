<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiResponse;
use App\Models\BiometricData;
use App\Models\User;
use App\Services\FacialRecognitionService;
use App\Services\ZKTecoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class BiometricController extends Controller
{
    protected FacialRecognitionService $facialRecognition;
    protected ZKTecoService $zktecoService;

    public function __construct(
        FacialRecognitionService $facialRecognition,
        ZKTecoService $zktecoService
    ) {
        $this->facialRecognition = $facialRecognition;
        $this->zktecoService = $zktecoService;
    }

    public function showEnrollment()
    {
        // Check if there's pending registration data OR if user is logged in
        if (!session()->has('pending_registration') && !Auth::check()) {
            return redirect()->route('register')->with('error', 'Please complete registration or login first.');
        }

        // Force HTTPS for ngrok
        if (request()->secure() || str_contains(request()->url(), 'ngrok-free.dev')) {
            URL::forceScheme('https');
        }

        return view('auth.biometric-enrollment');
    }

    public function storeFacialData(Request $request)
    {
        $request->validate([
            'facial_image' => 'required|string',
        ]);

        try {
            Log::info('Facial enrollment started');

            // Extract facial encoding from image
            $encodingResult = $this->facialRecognition->extractEncodingFromBase64($request->facial_image);
            $facialEncoding = $encodingResult['encoding'];

            // Determine user context
            [$userName, $existingUserId] = $this->getUserContext();

            // Check for duplicate enrollment
            $duplicateCheck = $this->checkDuplicateEnrollment($facialEncoding, $existingUserId, $userName);
            if ($duplicateCheck !== null) {
                return $duplicateCheck; // Return error response
            }

            // Handle enrollment based on user context
            if (Auth::check()) {
                $this->handleAuthenticatedUserFacialEnrollment($facialEncoding);
            } else {
                $this->handlePendingRegistrationFacialEnrollment($facialEncoding, $userName);
            }

            return ApiResponse::success([
                'encoding_dimensions' => count($facialEncoding),
            ], 'Facial data enrolled successfully');
            
        } catch (\RuntimeException $e) {
            Log::warning('Facial enrollment validation failed: ' . $e->getMessage());
            return ApiResponse::error($e->getMessage(), 422);
        } catch (\Throwable $e) {
            Log::error('Facial enrollment failed: ' . $e->getMessage());
            return ApiResponse::error('Automatic processing failed: ' . $e->getMessage(), 500);
        }
    }

    private function getUserContext(): array
    {
        // PRIORITY FIX: Check for pending registration FIRST.
        // This allows an Admin to be logged in but still enroll a NEW user.
        if (session()->has('pending_registration')) {
            $registrationData = session('pending_registration');
            return [$registrationData['name'] ?? 'Unknown User', null];
        }

        // Only fall back to Auth::user() if there is NO pending registration
        if (Auth::check()) {
            $user = Auth::user();
            return [$user->name, $user->id];
        }
        
        return ['Unknown User', null];
    }

    /**
     * Check if face is already enrolled for another user
     * 
     * @return null|JsonResponse
     */
    private function checkDuplicateEnrollment(array $facialEncoding, ?int $existingUserId, string $userName)
    {
        $duplicateFace = $this->facialRecognition->findDuplicateFace(
            $facialEncoding,
            $existingUserId, // Exclude current user if logged in
            null,
            \App\Services\FacialRecognitionService::DEFAULT_TOLERANCE,
            true // Only enrolled users
        );
        
        if ($duplicateFace) {
            Log::warning('Duplicate face enrollment attempt blocked', [
                'attempted_user' => $userName,
                'existing_user' => $duplicateFace['user_name'],
                'distance' => $duplicateFace['distance'],
            ]);
            
            return ApiResponse::error(
                "This face is already enrolled for another user ({$duplicateFace['user_name']}). Each person can only enroll once.",
                409,
                ['duplicate_detected' => true, 'existing_user' => $duplicateFace['user_name']]
            );
        }

        return null;
    }

    /**
     * Handle facial enrollment for authenticated users
     */
    private function handleAuthenticatedUserFacialEnrollment(array $facialEncoding): void
    {
        $user = Auth::user();
        $biometric = BiometricData::where('user_id', $user->id)->first();

        $enrollmentData = [
            'facial_encoding' => $facialEncoding,
            'facial_status' => 'captured',
            'facial_captured_at' => now(),
        ];

        if ($biometric) {
            $biometric->update($enrollmentData);
        } else {
            BiometricData::create(array_merge($enrollmentData, [
                'user_id' => $user->id,
                'user_name' => $user->name,
            ]));
        }
        
        Log::info('Facial enrollment completed for authenticated user', ['user_id' => $user->id]);
    }

    /**
     * Handle facial enrollment for pending registration
     */
    private function handlePendingRegistrationFacialEnrollment(array $facialEncoding, string $userName): void
    {
        $existingBiometric = BiometricData::where('facial_status', 'captured')
            ->whereNull('user_id')
            ->first();

        $enrollmentData = [
            'facial_encoding' => $facialEncoding,
            'facial_captured_at' => now(),
            'user_name' => $userName,
        ];

        if ($existingBiometric) {
            $existingBiometric->update($enrollmentData);
            session(['biometric_id' => $existingBiometric->id]);
        } else {
            $biometric = BiometricData::create(array_merge($enrollmentData, [
                'user_id' => null,
                'facial_status' => 'captured',
            ]));
            session(['biometric_id' => $biometric->id]);
        }
        
        Log::info('Facial enrollment stored for pending registration');
    }


    /**
     * Get WebAuthn instance
     */
    private function getWebAuthn(): \lbuchs\WebAuthn\WebAuthn
    {
        $rpId = request()->getHost(); 
        
        // WebAuthn spec: RP ID must be a "valid domain string". IP addresses are not allowed.
        // If accessing via 127.0.0.1 or localhost, we can often omit the ID or ensure it matches.
        if (filter_var($rpId, FILTER_VALIDATE_IP) || $rpId === 'localhost') {
            // For local development, omitting the ID (or setting to host) is usually safest.
            // But browsers like Chrome strictly forbid IP addresses in the 'id' field.
            // We'll let the browser use the default origin behavior if it's an IP.
            if (filter_var($rpId, FILTER_VALIDATE_IP)) {
                $rpId = null; 
            }
        }

        $formats = ['android-key', 'android-safetynet', 'apple', 'fido-u2f', 'none', 'packed', 'tpm'];
        return new \lbuchs\WebAuthn\WebAuthn('Zou Attendance', $rpId, $formats);
    }

    /**
     * Get WebAuthn registration options (Step 1 of Enrollment)
     */
    public function getRegistrationOptions(Request $request)
    {
        list($userName, $userId) = $this->getUserContext();
        
        // If no user and no pending registration, then they are truly not authenticated
        if (!$userId && !session()->has('pending_registration')) {
            return ApiResponse::error('User session not found. Please register first.', 401);
        }

        // Use a unique ID for the authenticator even if not yet saved in DB
        // If userId is null, use a stable hash of the email or session
        $webauthnUserId = $userId ?: md5(session('pending_registration.email') . session()->getId());

        try {
            $webAuthn = $this->getWebAuthn();
            
            // Existing IDs to exclude (prevent duplicate enrollment on same authenticator)
            $excludeIds = []; 

            $args = $webAuthn->getCreateArgs(
                (string)$webauthnUserId, 
                $userName, 
                $userName, 
                60, // timeout
                false, // requireResidentKey (Arg 5)
                'required', // requireUserVerification (Arg 6) - strictly force a scan
                true // crossPlatformAttachment (Arg 7: true = cross-platform)
            );

            // Force direct attestation to potentially force more interaction/device info
            if (isset($args->publicKey)) {
                $args->publicKey->attestation = 'direct';
            }

            Log::debug('WebAuthn Registration Options Generated', [
                'args' => json_encode($args),
                'attestation_mode' => $args->publicKey->attestation ?? 'default'
            ]);

            // Base64 encode binary fields for JSON transport
            // Note: lbuchs/webauthn returns an object structure that matches the WebAuthn API
            // content, but binary fields need to be encoded for JSON.
            
            // Check structure (it might return the whole options object or just publicKey)
            // It usually returns an object that HAS a publicKey property, or IS the publicKey property?
            // Documentation implies it helps construct the creation options.
            
            // According to common usage of this library:
            // $args is an stdClass with publicKey property.
            
            // However, safe traversing:
            if (isset($args->publicKey)) {
                $args->publicKey->challenge = base64_encode($args->publicKey->challenge);
                $args->publicKey->user->id = base64_encode($args->publicKey->user->id);
                
                // If rpId was null (e.g. on an IP address), remove it from the JS object
                // so the browser uses the default origin behavior.
                if (empty($args->publicKey->rp->id)) {
                    unset($args->publicKey->rp->id);
                }
                
                if (isset($args->publicKey->excludeCredentials)) {
                    foreach ($args->publicKey->excludeCredentials as &$cred) {
                        $cred->id = base64_encode($cred->id);
                    }
                }
            } else {
                 // Maybe it returns the publicKey object directly?
                 if (isset($args->challenge)) {
                     $args->challenge = base64_encode($args->challenge);
                 }
                 if (isset($args->user->id)) {
                     $args->user->id = base64_encode($args->user->id);
                 }
            }

            // Save challenge to session (it is already binary in the object before we encoded it? 
            // Wait, we encoded it in place. So session has encoded? 
            // The library call `getChallenge()` returns the binary challenge stored internally.
            
            // Save challenge to session as base64 to prevent corruption by database driver
            $request->session()->put('webauthn_challenge', base64_encode($webAuthn->getChallenge()));

            return response()->json($args);

        } catch (\Throwable $e) {
            Log::error('WebAuthn options error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return ApiResponse::error('Failed to generate registration options: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Verify WebAuthn registration (Step 2 of Enrollment)
     */
    public function verifyRegistration(Request $request)
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

            if (!$challenge) {
                return ApiResponse::error('Session timed out. Please try again.', 419);
            }

            Log::debug('WebAuthn Verify Registration Data Received', [
                'clientDataJSON_len' => strlen($clientDataJSON),
                'attestationObject_len' => strlen($attestationObject),
                'challenge_len' => strlen($challenge)
            ]);

            $data = $webAuthn->processCreate(
                $clientDataJSON, 
                $attestationObject, 
                $challenge, 
                true, // requireUserVerification (match the options!)
                true, // requireUserPresence
                false // requireWifi
            );

            Log::debug('WebAuthn processCreate result', ['data' => json_encode($data)]);

            // $data contains credentialId, credentialPublicKey, certificate, etc.
            
            // Some authenticators might not provide all fields, or they might be named differently
            // Use an array to check properties safely
            $dataArr = (array)$data;
            
            // The library property might be 'aaguid' or 'AAGUID' depending on version
            $aaguid = null;
            if (isset($dataArr['aaguid'])) $aaguid = $dataArr['aaguid'];
            elseif (isset($dataArr['AAGUID'])) $aaguid = $dataArr['AAGUID'];

            $credentialData = [
                'id' => $data->credentialId ?? $dataArr['credentialId'] ?? null,
                'key' => $data->credentialPublicKey ?? $dataArr['credentialPublicKey'] ?? null,
                'counter' => $data->signatureCounter ?? $dataArr['signatureCounter'] ?? 0,
                'aaguid' => $aaguid,
                'fmt' => $data->attestationFormat ?? $dataArr['attestationFormat'] ?? 'none',
                'type' => 'webauthn'
            ];

            // Get or create biometric record
            if ($userId) {
                $biometric = BiometricData::firstOrCreate(['user_id' => $userId]);
            } else {
                // For pending registration, look for existing biometric for this session
                $biometricId = session('biometric_id');
                $biometric = $biometricId ? BiometricData::find($biometricId) : null;
                
                if (!$biometric) {
                    $biometric = BiometricData::create([
                        'user_name' => $userName,
                        'facial_status' => 'not_enrolled',
                    ]);
                }
            }
            
            $biometric->update([
                'fingerprint_template' => json_encode($credentialData),
                'fingerprint_status' => 'captured',
                'fingerprint_captured_at' => now(),
                'fingerprint_device_id' => 'webauthn_' . ($aaguid ? bin2hex($aaguid) : bin2hex($credentialData['id'] ?? 'unknown')),
            ]);

            // Save ID to session for status polling if unauthenticated
            session(['biometric_id' => $biometric->id]);
            
            // Clear challenge
            $request->session()->forget('webauthn_challenge');

            Log::info('WebAuthn fingerprint enrolled successfully', ['userName' => $userName, 'userId' => $userId]);

            return ApiResponse::success([
                'fingerprint_status' => 'captured',
                'enrolled_at' => now(),
            ], 'Fingerprint enrolled successfully');

        } catch (\Throwable $e) {
            Log::error('WebAuthn verification failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return ApiResponse::error('Verification failed: ' . $e->getMessage(), 422);
        }
    }

    private function isJson($string) {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    // Reverted fingerprint methods

    // Add facial verification method for future attendance check-in
    public function verifyFacialData(Request $request)
    {
        $request->validate([
            'facial_image' => 'required|string',
        ]);

        $user = Auth::user();

        $biometric = BiometricData::where('user_id', $user->id)->first();

        if (!$biometric || !$biometric->facial_encoding) {
            return response()->json(['error' => 'No facial data enrolled'], 400);
        }

        try {
            $encodingResult = $this->facialRecognition->extractEncodingFromBase64($request->facial_image);
            $distance = $this->facialRecognition->calculateDistance($biometric->facial_encoding, $encodingResult['encoding']);
            $match = $this->facialRecognition->isMatch($distance);

            return response()->json([
                'success' => $match,
                'match' => $match,
                'confidence' => $this->facialRecognition->confidenceFromDistance($distance),
            ], $match ? 200 : 400);
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            Log::error('Facial verification failed: ' . $e->getMessage());
            return response()->json(['error' => 'Verification failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get all fingerprint templates for client-side matching
     */
    public function getTemplates(Request $request)
    {
        // Security: Ensure this is only accessible from trusted clients (e.g. kiosk)
        // For now, relies on public access for the kiosk, but ideally should be token-protected.

        $templates = BiometricData::whereNotNull('fingerprint_template')
            ->whereNotNull('user_id')
            ->select('user_id', 'fingerprint_template')
            ->get();

        return ApiResponse::success($templates, 'Templates retrieved successfully');
    }

    public function completeEnrollment(Request $request)
    {
        Log::info('completeEnrollment called', [
            'has_session' => session()->has('pending_registration'),
            'authenticated' => Auth::check(), 
        ]);

        // Check for pending registration FIRST
        // This allows Admins to complete enrollment for OTHERS
        if (session()->has('pending_registration')) {
            // Proceed to standard flow below...
        } elseif (Auth::check()) {
            // Only handle as "Authenticated User Enrollment" (Self-Enrollment) 
            // if there is NO pending registration for someone else.
            return $this->handleAuthenticatedUserEnrollment($request);
        }

        // Validate pending registration exists
        if (!session()->has('pending_registration')) {
            Log::warning('No pending registration in session');
            return redirect()->route('register')->with('error', 'No pending registration found.');
        }

        // Validate biometric enrollment is complete
        $biometric = $this->validateBiometricEnrollment();
        if ($biometric instanceof \Illuminate\Http\RedirectResponse) {
            return $biometric; // Return redirect if validation failed
        }

        // Check for duplicate face before creating user
        $duplicateCheck = $this->checkForDuplicateFace($biometric);
        if ($duplicateCheck instanceof \Illuminate\Http\RedirectResponse) {
            return $duplicateCheck; // Return redirect if duplicate found
        }

        // Create user and link biometric data
        $user = $this->createUserFromPendingRegistration($biometric);
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user; // Return redirect if creation failed
        }

        // Complete registration and prepare for kiosk mode
        return $this->finalizeEnrollment($request, $user);
    }

    /**
     * Handle enrollment completion for already authenticated users
     */
    private function handleAuthenticatedUserEnrollment(Request $request)
    {
        $user = Auth::user();
        
        // Log out the user to ensure kiosk mode readiness
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('attendance.clock')
            ->with('success', 'Facial enrollment completed successfully! You can now use the kiosk to clock in.');
    }

    /**
     * Validate that biometric enrollment is complete
     * 
     * @return BiometricData|\Illuminate\Http\RedirectResponse
     */
    private function validateBiometricEnrollment()
    {
        $biometric = BiometricData::where('facial_status', 'captured')
            ->whereNull('user_id')
            ->first();

        Log::info('Biometric data check', [
            'found' => $biometric ? true : false,
            'has_encoding' => $biometric && $biometric->facial_encoding ? true : false,
            'biometric_id' => $biometric ? $biometric->id : null,
        ]);

        if (!$biometric || !$biometric->facial_encoding) {
            Log::warning('Biometric enrollment incomplete', [
                'biometric_exists' => $biometric ? true : false,
                'has_encoding' => $biometric && $biometric->facial_encoding ? true : false,
            ]);
            return redirect()->route('biometric.enrollment')
                ->with('error', 'Please complete facial recognition enrollment.');
        }

        return $biometric;
    }

    /**
     * Check if the face is already enrolled for another user
     * 
     * @return null|\Illuminate\Http\RedirectResponse
     */
    private function checkForDuplicateFace(BiometricData $biometric)
    {
        $duplicateFace = $this->facialRecognition->findDuplicateFace(
            $biometric->facial_encoding, 
            null, // excludeUserId
            $biometric->id, // excludeBiometricId
            \App\Services\FacialRecognitionService::DEFAULT_TOLERANCE,
            true // onlyEnrolledUsers
        );
        
        if ($duplicateFace) {
            Log::error('Duplicate face detected during enrollment completion', [
                'pending_user' => $biometric->user_name,
                'existing_user_id' => $duplicateFace['user_id'],
                'existing_user_name' => $duplicateFace['user_name'],
            ]);
            
            // Clean up pending biometric data
            $biometric->delete();
            session()->forget('pending_registration');
            
            return redirect()->route('register')->with('error', 
                "This face is already enrolled for another user ({$duplicateFace['user_name']}). Each person can only register once. Please contact support if you believe this is an error."
            );
        }

        return null;
    }

    /**
     * Create user from pending registration data and link biometric
     * 
     * @return User|\Illuminate\Http\RedirectResponse
     */
    private function createUserFromPendingRegistration(BiometricData $biometric)
    {
        $registrationData = session('pending_registration');

        if (!$registrationData) {
            Log::error('No registration data found in session during enrollment completion');
            return redirect()->route('register')->with('error', 'Registration data lost. Please register again.');
        }

        try {
            Log::info('Attempting to create user', [
                'email' => $registrationData['email'] ?? 'missing',
                'name' => $registrationData['name'] ?? 'missing',
                'employee_number' => $registrationData['employee_number'] ?? 'missing',
            ]);

            // Create user - password will be automatically hashed by User model
            $user = User::create($registrationData);

            // Verify user was saved
            if (!User::find($user->id)) {
                throw new \Exception('User was created but could not be retrieved from database');
            }

            // Link biometric data to user
            $biometric->update(['user_id' => $user->id]);
            
            // Verify biometric was linked
            $updatedBiometric = BiometricData::find($biometric->id);
            if (!$updatedBiometric || $updatedBiometric->user_id !== $user->id) {
                throw new \Exception('Biometric data was not properly linked to user');
            }

            Log::info('User created and biometric linked successfully', [
                'user_id' => $user->id,
                'biometric_id' => $biometric->id,
            ]);

            return $user;

        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Database error creating user', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);
            return redirect()->route('register')->with('error', 'Database error: ' . $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Failed to create user during enrollment completion', [
                'error' => $e->getMessage(),
            ]);
            return redirect()->route('register')->with('error', 'Failed to create account: ' . $e->getMessage());
        }
    }

    /**
     * Finalize enrollment by clearing session and preparing for kiosk mode
     */
    private function finalizeEnrollment(Request $request, User $user)
    {
        // Clear the pending registration session
        session()->forget('pending_registration');

        // KIOSK MODE: Do NOT log the user in
        // Ensure we are logged out to prepare for the next user
        if (Auth::check()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        // Fire the registered event
        event(new \Illuminate\Auth\Events\Registered($user));

        return redirect()->route('attendance.clock')
            ->with('success', 'Registration completed successfully! You can now use the kiosk to clock in.');
    }

    /**
     * Enroll fingerprint for a user
     */
    public function enrollFingerprint(Request $request)
    {
        $request->validate([
            'fingerprint_template' => 'required|string',
            'device_id' => 'nullable|string',
        ]);

        try {
        $user = Auth::user();
        $biometric = null;

        // AUTH CHECK:
        // 1. If logged in, find/create biometric record for user
        if ($user) {
            $biometric = BiometricData::firstOrCreate(['user_id' => $user->id]);
        } 
        // 2. If NOT logged in, check for pending biometric session (from facial step)
        elseif (session()->has('biometric_id')) {
            $biometric = BiometricData::find(session('biometric_id'));
        }

        if (!$biometric) {
            return ApiResponse::error('User not authenticated and no pending enrollment found', 401);
        }

        // Check if fingerprint already enrolled for another user (duplicate detection)
        // We exclude the current biometric record ID to allow updates
        $existingFingerprint = BiometricData::where('fingerprint_template', $request->fingerprint_template)
            ->where('id', '!=', $biometric->id) 
            ->whereNotNull('user_id')
            ->first();

        if ($existingFingerprint) {
            return ApiResponse::error(
                'This fingerprint is already enrolled for another user',
                409,
                ['duplicate_detected' => true]
            );
        }

        // Update the biometric record
        $biometric->update([
            'fingerprint_template' => $request->fingerprint_template,
            'fingerprint_status' => 'captured',
            'fingerprint_captured_at' => now(),
            'fingerprint_device_id' => $request->device_id,
        ]);

        // Try to enroll on ZKTeco device if enabled (Optional: typically for syncing)
        if ($user && config('zkteco.enabled') && config('zkteco.allow_zkteco')) {
             $this->zktecoService->enrollFingerprint($user->id, $request->fingerprint_template);
        }          
            Log::info('Fingerprint enrolled successfully', [
                'user_id' => $biometric->user_id, // Use biometric->user_id as $user might be null
                'device_id' => $request->device_id,
                'template_sample' => substr($request->fingerprint_template, 0, 50) . '...'
            ]);

            return ApiResponse::success([
                'fingerprint_status' => $biometric->fingerprint_status,
                'enrolled_at' => $biometric->fingerprint_captured_at,
            ], 'Fingerprint enrolled successfully');

        } catch (\Exception $e) {
            Log::error('Fingerprint enrollment failed: ' . $e->getMessage());
            return ApiResponse::error('Fingerprint enrollment failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get biometric enrollment status for current user
     */
    public function getEnrollmentStatus()
    {
        $user = Auth::user();
        $biometric = null;

        if ($user) {
            $biometric = BiometricData::where('user_id', $user->id)->first();
        } else {
            // Check for pending registration biometric
            $biometricId = session('biometric_id');
            if ($biometricId) {
                $biometric = BiometricData::find($biometricId);
            }
        }

        if (!$biometric) {
            return ApiResponse::success([
                'facial_enrolled' => false,
                'fingerprint_enrolled' => false,
                'biometric_complete' => false,
            ]);
        }

        $facialEnrolled = !empty($biometric->facial_encoding) && $biometric->facial_status === 'captured';
        $fingerprintEnrolled = !empty($biometric->fingerprint_template) && $biometric->fingerprint_status === 'captured';

        return ApiResponse::success([
            'facial_enrolled' => $facialEnrolled,
            'fingerprint_enrolled' => $fingerprintEnrolled,
            'biometric_complete' => $facialEnrolled || $fingerprintEnrolled,
            'both_enrolled' => $facialEnrolled && $fingerprintEnrolled,
            'enrollment_details' => [
                'facial_captured_at' => $biometric->facial_captured_at,
                'fingerprint_captured_at' => $biometric->fingerprint_captured_at,
            ]
        ]);
    }

    /**
     * Delete fingerprint enrollment
     */
    public function deleteFingerprint()
    {
        $user = Auth::user();
        
        if (!$user) {
            return ApiResponse::error('User not authenticated', 401);
        }

        $biometric = BiometricData::where('user_id', $user->id)->first();

        if (!$biometric || empty($biometric->fingerprint_template)) {
            return ApiResponse::error('No fingerprint enrollment found', 404);
        }

        // Delete from ZKTeco device if enabled
        if (config('zkteco.enabled') && config('zkteco.allow_zkteco')) {
            $this->zktecoService->deleteUser($user->id);
        }

        // Clear fingerprint data
        $biometric->update([
            'fingerprint_template' => null,
            'fingerprint_status' => 'not_enrolled',
            'fingerprint_captured_at' => null,
            'fingerprint_device_id' => null,
        ]);

        Log::info('Fingerprint enrollment deleted', ['user_id' => $user->id]);

        return ApiResponse::success([], 'Fingerprint enrollment deleted successfully');
    }

    /**
     * Delete facial enrollment data for a specific user (Admin only)
     */
    public function deleteFacialDataForUser(User $user)
    {
        $biometric = \App\Models\BiometricData::where('user_id', $user->id)->first();

        if (!$biometric || empty($biometric->facial_encoding)) {
            return redirect()->back()->with('error', 'No facial data found for this user.');
        }

        // Clear facial data
        $biometric->update([
            'facial_encoding' => null,
            'facial_status' => 'not_enrolled',
            'facial_captured_at' => null,
        ]);

        \Illuminate\Support\Facades\Log::info('Facial enrollment deleted by admin', ['target_user_id' => $user->id, 'admin_id' => \Illuminate\Support\Facades\Auth::id()]);

        return redirect()->back()->with('success', "Facial data for '{$user->name}' deleted successfully.");
    }
}
