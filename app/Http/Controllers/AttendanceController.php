<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\BiometricData;
use App\Models\FailedAttendanceLog;
use App\Services\FacialRecognitionService;
use App\Services\LocationService;
use App\Services\ZKTecoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Str;
use App\Http\Responses\ApiResponse;
use Illuminate\Support\Facades\URL;

class AttendanceController extends Controller
{
    protected FacialRecognitionService $facialRecognition;
    protected LocationService $locationService;
    protected ZKTecoService $zktecoService;

    public function __construct(
        FacialRecognitionService $facialRecognition,
        LocationService $locationService,
        ZKTecoService $zktecoService
    ) {
        $this->facialRecognition = $facialRecognition;
        $this->locationService = $locationService;
        $this->zktecoService = $zktecoService;
    }
    
    public function showQRCode()
    {
        $qrCodeUrl = route('attendance.clock');
        $qrCode = QrCode::size(300)->generate($qrCodeUrl);

        return view('attendance.qr-code', compact('qrCode'));
    }

    public function showClockPage()
    {
        return view('attendance.clock');
    }

    public function generateQRCode()
    {
        $qrCodeUrl = route('attendance.clock');
        $qrCode = QrCode::size(300)->format('png')->generate($qrCodeUrl);

        return response($qrCode)->header('Content-Type', 'image/png');
    }

    public function verifyAndClock(Request $request)
    {
        try {
            $request->validate([
                'facial_image' => 'required|string',
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
                'accuracy' => 'nullable|numeric',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        }

        try {
            // Send image directly to Python for extraction + 1:N matching!
            $matchResult = $this->facialRecognition->findBestMatchFromImage($request->facial_image);
            
            if (!$matchResult) {
                // Return a proper JSON response with the expected structure
                return response()->json([
                    'success' => false,
                    'message' => 'Face not found in our records. Would you like to register?',
                    'error' => 'Face not recognized',
                    'action_required' => 'registration_prompt'
                ], 200); 
            }
            
            $user = \App\Models\User::find($matchResult['user_id']);

            if (!$user) {
                // Self-healing: The Python server found a match, but the user account is missing (orphaned record).
                // We should delete the orphaned record from the database and force a sync.
                \App\Models\BiometricData::where('user_id', $matchResult['user_id'])->delete();
                
                // Force Python to flush the orphaned record from RAM
                app(\App\Services\FacialRecognitionService::class)->syncWithPythonServer();

                // Return the standard prompt so the user can register a new account
                return response()->json([
                    'success' => false,
                    'message' => 'Face not found in our records. Would you like to register?',
                    'error' => 'Face not recognized',
                    'action_required' => 'registration_prompt'
                ], 200); 
            }

            // Perform common attendance processing
            return $this->processAttendanceForUser($user, $request, 'facial_recognition');

        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 422);
        } catch (\Throwable $e) {
            Log::error('Attendance verification failed: ' . $e->getMessage());
            return ApiResponse::error('Verification failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Verify fingerprint and clock user in/out
     */
    public function verifyFingerprint(Request $request)
    {
        try {
            try {
                $request->validate([
                    'fingerprint_template' => 'required|string',
                    'latitude' => 'nullable|numeric|between:-90,90',
                    'longitude' => 'nullable|numeric|between:-180,180',
                    'accuracy' => 'nullable|numeric',
                ]);
            } catch (\Illuminate\Validation\ValidationException $e) {
                return ApiResponse::validationError($e->errors());
            }

            // Find user by fingerprint 
            // 1. First try database match
            Log::info('Verifying fingerprint', [
                'incoming_length' => strlen($request->fingerprint_template),
                'incoming_sample' => substr($request->fingerprint_template, 0, 20) . '...',
            ]);
            
            $userId = $this->zktecoService->verifyFingerprintAgainstDatabase($request->fingerprint_template);
            
            // 2. If not found in DB, could try device verification (if ZKTeco device was directly connected to server)
            // But usually for kiosk mode, we verify against local DB sync
            
            if (!$userId) {
                // Return registration prompt for fingerprint as well
                return response()->json([
                    'success' => false,
                    'message' => 'Fingerprint not found in our records. Would you like to register?',
                    'error' => 'Fingerprint not recognized',
                    'action_required' => 'registration_prompt'
                ], 200);
            }

            $user = \App\Models\User::find($userId);

            if (!$user) {
                return ApiResponse::error('User account not found', 404);
            }

            return $this->processAttendanceForUser($user, $request, 'fingerprint_webauthn');

        } catch (\Throwable $e) {
            Log::error('Fingerprint verification CRITICAL error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Server Error: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Common attendance processing logic
     */
    private function processAttendanceForUser($user, Request $request, string $source)
    {
        // Fetch the user's latest log once and reuse it across both determineClockAction
        // and validateBusinessRules — previously each method ran its own identical query.
        [$action, $lastLog] = $this->determineClockAction($user);

        // Validate business rules, passing the already-fetched log to avoid re-querying
        $businessRuleCheck = $this->validateBusinessRules($user, $action, $lastLog, $request, $source);
        if ($businessRuleCheck !== null) {
            return $businessRuleCheck;
        }

        // Log attendance
        $this->createAttendanceLog($user, $action, $request, $source);

        // Generate redirect URL to the custom, secure summary profile page
        $isMobile = $this->isMobileDevice($request);
        $redirectUrl = URL::temporarySignedRoute('attendance.summary', now()->addMinutes(20), ['user_id' => $user->id]);

        // Auto-logout if user is logged in (kiosk mode) to protect privacy outside the dashboard
        if (Auth::check()) {
            Auth::guard('web')->logout();
        }

        $message = $action === 'clock_in'
            ? "Welcome, {$user->name}! You have successfully clocked in."
            : "Goodbye, {$user->name}! You have successfully clocked out. Have a great day!";

        return ApiResponse::success([
            'action'       => $action,
            'user_name'    => $user->name,
            'timestamp'    => now()->format('Y-m-d H:i:s'),
            'method'       => $source === 'facial_recognition' ? 'Facial Recognition' : 'Fingerprint',
            'redirect_url' => $redirectUrl,
            'is_mobile'    => $isMobile
        ], $message);
    }

    /**
     * Check if the request is from a mobile device using User-Agent
     */
    private function isMobileDevice(Request $request): bool
    {
        $userAgent = $request->header('User-Agent');
        if (!$userAgent) return false;
        
        // Comprehensive mobile user-agent regex (excluding tablets like iPad if possible, but matching most phones)
        return preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', $userAgent);
    }
    
    /**
     * Show the post-scan attendance summary page
     */
    public function showSummary(Request $request, $user_id)
    {
        if (!$request->hasValidSignature()) {
            abort(401, 'This link has expired or is invalid.');
        }

        $user = \App\Models\User::findOrFail($user_id);
        
        $todayLog = AttendanceLog::where('user_id', $user->id)
            ->whereDate('timestamp', today())
            ->latest('timestamp')
            ->first();

        if (!$todayLog) {
            return redirect()->route('attendance.clock');
        }

        // Calculate hours if clocked out
        $hoursWorked = null;
        if ($todayLog->action === 'clock_out') {
            $clockIn = AttendanceLog::where('user_id', $user->id)
                ->whereDate('timestamp', today())
                ->where('action', 'clock_in')
                ->where('timestamp', '<', $todayLog->timestamp)
                ->latest('timestamp')
                ->first();
                
            if ($clockIn) {
                $hours = $clockIn->timestamp->diffInHours($todayLog->timestamp);
                $minutes = $clockIn->timestamp->diffInMinutes($todayLog->timestamp) % 60;
                $hoursWorked = sprintf('%d hrs %02d mins', $hours, $minutes);
            }
        }

        $isMobile = $this->isMobileDevice($request);
        
        // Pass info to view
        return view('attendance.summary', compact('user', 'todayLog', 'hoursWorked', 'isMobile'));
    }

    /**
     * Find matching user by facial recognition
     * 
     * @return \App\Models\User|\Illuminate\Http\JsonResponse
     */
    private function findMatchingUser(array $inputEncoding)
    {
        // Use the standardized "Best Match" logic from the service
        // We only want enrolled users (onlyEnrolledUsers = true)
        $match = $this->facialRecognition->findBestMatch(
            $inputEncoding, 
            null, // excludeUserId
            null, // excludeBiometricId
            FacialRecognitionService::DEFAULT_TOLERANCE, 
            true // onlyEnrolledUsers
        );

        if (!$match) {
            // IMPORTANT: Return a proper JSON response with the expected structure
            return response()->json([
                'success' => false,
                'message' => 'Face not found in our records. Would you like to register?',
                'error' => 'Face not recognized',
                'action_required' => 'registration_prompt'  // Make sure this is set
            ], 200); // Use 200 to avoid frontend error handling
        }

        $user = \App\Models\User::find($match['user_id']);

        if (!$user) {
            return ApiResponse::error('Matched user record not found in database', 500);
        }

        return $user;
    }

    /**
     * Handle manual clock out from the summary profile page
     */
    public function manualClockOut(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id'
        ]);

        $user = \App\Models\User::find($request->user_id);

        $lastLog = AttendanceLog::where('user_id', $user->id)
            ->whereDate('timestamp', today())
            ->latest('timestamp')
            ->first();

        // Ensure they are actually clocked in
        if (!$lastLog || $lastLog->action !== 'clock_in') {
            return ApiResponse::error('You are not currently clocked in.', 422);
        }

        // Validate business rules (e.g. minimum shift duration)
        // We override rapid clocking check here because this is an intentional manual override
        $action = 'clock_out';
        

        // Log the manual clock out
        $this->createAttendanceLog($user, $action, $request, 'manual_override');

        return ApiResponse::success([
            'action' => $action,
            'user_name' => $user->name,
            'timestamp' => now()->format('Y-m-d H:i:s'),
        ], "Goodbye, {$user->name}! You have successfully clocked out.");
    }

    /**
     * Determine whether to clock in or clock out.
     * Returns both the resolved action AND the raw log record so callers
     * can reuse it without issuing a second identical query.
     *
     * @return array{0: string, 1: \App\Models\AttendanceLog|null}
     */
    private function determineClockAction($user): array
    {
        $lastLog = AttendanceLog::where('user_id', $user->id)
            ->whereDate('timestamp', today())
            ->latest('timestamp')
            ->first();

        $action = (!$lastLog || $lastLog->action === 'clock_out') ? 'clock_in' : 'clock_out';

        return [$action, $lastLog];
    }

    /**
     * Validate business rules (rapid clocking, minimum shift duration).
     * Accepts the pre-fetched $lastLog from determineClockAction to avoid a duplicate query.
     *
     * @return null|\Illuminate\Http\JsonResponse
     */
    private function validateBusinessRules($user, string $action, $lastLog, Request $request, string $source)
    {
        // Prompt for clock out if they scan while already clocked in
        // (This replaces the old "rapid clocking" rule and the "already clocked out" rule)
        if ($lastLog && $lastLog->action === 'clock_in') {
            // Do not log a failure. Instead, seamlessly redirect them to their profile.
            
            $isMobile = $this->isMobileDevice($request);
            $redirectUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute('attendance.summary', now()->addMinutes(20), [
                'user_id' => $user->id,
                'prompt_clockout' => 1
            ]);
            
            return ApiResponse::success([
                'action' => $lastLog->action, // Keep the context of their previous action
                'user_name' => $user->name,
                'timestamp' => $lastLog->timestamp->format('Y-m-d H:i:s'),
                'method' => $source === 'facial_recognition' ? 'Facial Recognition' : 'Fingerprint',
                'redirect_url' => $redirectUrl,
                'is_mobile' => $isMobile
            ], "Welcome back, {$user->name}! Redirecting to your profile...");
        }


        // Prevent re-clocking in after clocking out
        if ($action === 'clock_in' && $lastLog && $lastLog->action === 'clock_out') {
             $this->logFailure($request, 'Already clocked out for the day', $user->id, $action);
             return ApiResponse::error('You have already completed your shift for today. Please contact an administrator if this is an error.', 422);
        }

        return null;
    }

    /**
     * Create attendance log record
     */
    private function createAttendanceLog($user, string $action, Request $request, string $source = 'facial_recognition'): void
    {
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
    }

    public function getAttendanceLogs(Request $request)
    {
        $query = AttendanceLog::with('user');

        if ($request->has('date')) {
            $query->whereDate('timestamp', $request->date);
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $logs = $query->orderBy('timestamp', 'desc')->paginate(20);

        return response()->json($logs);
    }

    private function logFailure(Request $request, string $reason, ?int $userId = null, ?string $action = null)
    {
        try {
            $imagePath = null;
            if ($request->has('facial_image')) {
                $imageData = $request->facial_image;
                if (str_contains($imageData, ',')) {
                    $imageData = explode(',', $imageData)[1];
                }
                
                $fileName = 'failed_' . time() . '_' . Str::random(10) . '.jpg';
                $path = 'attendance/failed/' . $fileName;
                Storage::disk('public')->put($path, base64_decode($imageData));
                $imagePath = $path;
            }

            FailedAttendanceLog::create([
                'user_id' => $userId,
                'action_attempted' => $action,
                'reason' => $reason,
                'captured_face_path' => $imagePath,
                'network_info' => $request->header('X-Forwarded-For') ?? $request->ip(),
                'attempted_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log attendance failure: ' . $e->getMessage());
        }
    }
}
