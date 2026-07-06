<?php

namespace App\Services;

use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class FacialRecognitionService
{
    /**
     * Matching tolerance used during attendance scanning.
     * Tightened from 0.45 → 0.38 to significantly reduce look-alike false positives.
     * Independent benchmarks show genuine same-person pairs rarely exceed 0.35 in
     * controlled indoor/frontal conditions; different-person pairs rarely fall below 0.42.
     */
    public const DEFAULT_TOLERANCE = 0.38;

    /**
     * Slightly wider tolerance used during enrollment duplicate checks.
     * Allows catching look-alikes attempting to register under a different name,
     * while still being tighter than the original 0.45 default.
     */
    public const ENROLLMENT_TOLERANCE = 0.42;

    /**
     * Minimum confidence percentage (as a 0–1 fraction) a match must reach
     * before it is accepted, even if it passes the distance threshold.
     * Prevents borderline distances (e.g. 0.37 at threshold 0.38 = 2.6% confidence)
     * from being treated as reliable matches.
     */
    public const MIN_CONFIDENCE_FLOOR = 0.15;

    /**
     * Extract a 128-d facial encoding from a base64 image using the Python pipeline.
     *
     * @param  string  $base64Image
     * @param  bool    $isEnrollment  When true, uses high-quality settings (CNN model, 10 jitters).
     *                                When false (scanning), uses fast settings (HOG model, 1 jitter).
     * @return array{encoding: array<int, float>, face_location: mixed}
     */
    public function extractEncodingFromBase64(string $base64Image, bool $isEnrollment = false): array
    {
        // Try the persistent server first (Fast Mode)
        try {
            return $this->extractViaServer($base64Image, $isEnrollment);
        } catch (\Throwable $e) {
            // Log the error but don't stop - fallback to CLI (Slow Mode)
            Log::warning('Recognition server unavailable, falling back to CLI.', ['error' => $e->getMessage()]);
        }

        // Fallback: standard CLI execution (Slow Mode)
        return $this->extractViaCli($base64Image, $isEnrollment);
    }

    protected function extractViaServer(string $base64Image, bool $isEnrollment = false): array
    {
        $host = config('services.recognition.host', 'http://localhost:5001');
        
        $payload = json_encode([
            'action'        => 'extract',
            'image'         => $base64Image,
            'is_enrollment' => $isEnrollment, // Signals Python to use high-quality settings
        ]);

        // Enrollment takes longer due to CNN + 10-jitter; allow extra time
        // On a CPU without GPU acceleration, this can easily take 45+ seconds.
        $timeout = $isEnrollment ? 120 : 10;
        set_time_limit(120);
        $options = [
            'http' => [
                'header'  => "Content-type: application/json\r\nContent-Length: " . strlen($payload) . "\r\n",
                'method'  => 'POST',
                'content' => $payload,
                'timeout' => $timeout,
                'ignore_errors' => true,
            ]
        ];

        $context  = stream_context_create($options);
        $result = @file_get_contents($host, false, $context);

        if ($result === false) {
            throw new \RuntimeException('Could not connect to recognition server');
        }

        $output = json_decode($result, true) ?? [];

        if (empty($output['success'])) {
            throw new \RuntimeException($output['error'] ?? 'Server returned error');
        }

        return [
            'encoding' => array_map('floatval', $output['facial_encoding']),
            'face_location' => $output['face_location'] ?? null,
        ];
    }

    public function syncWithPythonServer(): bool
    {
        $host = config('services.recognition.host', 'http://localhost:5001');
        
        $enrolledFaces = \App\Models\BiometricData::select(['user_id', 'facial_encoding'])
            ->whereNotNull('facial_encoding')
            ->whereNotNull('user_id')
            ->where('facial_status', 'captured')
            ->get();
            
        $templates = [];
        foreach ($enrolledFaces as $face) {
            $templates[(string)$face->user_id] = $face->facial_encoding;
        }
        
        $tenantId = app()->bound('current_tenant') ? app('current_tenant')->id : 'default';

        $payload = json_encode([
            'action' => 'sync',
            'tenant_id' => $tenantId,
            'templates' => $templates
        ]);
        
        $options = [
            'http' => [
                'header'  => "Content-type: application/json\r\nContent-Length: " . strlen($payload) . "\r\n",
                'method'  => 'POST',
                'content' => $payload,
                'timeout' => 15,
                'ignore_errors' => true,
            ]
        ];
        
        $context  = stream_context_create($options);
        $result = @file_get_contents($host, false, $context);
        
        if ($result === false) {
            Log::error('Failed to sync biometric templates with Python server');
            return false;
        }
        
        Log::info('Successfully synced templates with Python server');
        return true;
    }

    public function findBestMatchFromImage(string $base64Image, float $tolerance = self::DEFAULT_TOLERANCE)
    {
        $host = config('services.recognition.host', 'http://localhost:5001');
        
        $tenantId = app()->bound('current_tenant') ? app('current_tenant')->id : 'default';

        $payload = json_encode([
            'action'    => 'recognize',
            'tenant_id' => $tenantId,
            'image'     => $base64Image,
            'tolerance' => $tolerance
        ]);

        $options = [
            'http' => [
                'header'  => "Content-type: application/json\r\nContent-Length: " . strlen($payload) . "\r\n",
                'method'  => 'POST',
                'content' => $payload,
                'timeout' => 5,
                'ignore_errors' => true,
            ]
        ];

        $context  = stream_context_create($options);
        $result = @file_get_contents($host, false, $context);

        if ($result === false) {
            throw new \RuntimeException('Could not connect to recognition server for matching');
        }

        $output = json_decode($result, true) ?? [];

        if (isset($output['error']) && $output['error'] === 'FACE_DATABASE_EMPTY') {
            // Auto-sync and retry!
            Log::info('Python memory is empty. Auto-syncing templates...');
            $this->syncWithPythonServer();
            
            // Retry the recognition once
            $result = @file_get_contents($host, false, $context);
            $output = json_decode($result, true) ?? [];
            
            // If still empty after sync, the tenant truly has no faces enrolled.
            if (isset($output['error']) && $output['error'] === 'FACE_DATABASE_EMPTY') {
                return null;
            }
        }

        if (empty($output['success'])) {
            throw new \RuntimeException($output['error'] ?? 'Server returned error during match');
        }

        if (!$output['match']) {
            return null; // No match found
        }
        
        return [
            'user_id' => $output['user_id'],
            'distance' => $output['distance']
        ];
    }

    protected function extractViaCli(string $base64Image, bool $isEnrollment = false): array
    {
        // Use temporary file instead of pipe to avoid "Broken pipe" errors on Windows/Linux
        // when the process exits early or buffers fill up.
        $tempFile = tempnam(sys_get_temp_dir(), 'face_');
        if ($tempFile === false) {
             throw new \RuntimeException('Could not create temporary file for facial processing.');
        }

        try {
            // Decode the base64 string to binary image data BEFORE writing to file
            // Otherwise Python tries to read text as an image and fails with "cannot identify image file"
            $binaryData = $this->decodeImage($base64Image);
            file_put_contents($tempFile, $binaryData);
            
            $pythonBin = $this->pythonBinary();
            $userSitePackages = $this->getUserSitePackagesPath();
            
            // Set environment variables to help Python find packages
            $env = [];
            if ($userSitePackages) {
                // Set PYTHONPATH to include user site-packages
                $env['PYTHONPATH'] = $userSitePackages;
            }
            
            // Always set APPDATA for Windows (Python needs it for user site-packages detection)
            if (strtoupper(PHP_OS_FAMILY) === 'WINDOWS') {
                $appdata = getenv('APPDATA');
                if (!$appdata) {
                    // Fallback: construct from USERPROFILE
                    $userProfile = getenv('USERPROFILE');
                    if ($userProfile) {
                        $appdata = $userProfile . '\\AppData\\Roaming';
                    }
                }
                if ($appdata) {
                    $env['APPDATA'] = $appdata;
                }
            }
            
            // Pass temp file path as argument
            // Increased timeout to 60 seconds to allow for Python cold start (importing libraries)
            // Enrollment takes much longer (CNN + 10-jitter); bump timeout accordingly
            $timeout = $isEnrollment ? 120 : 60;

            try {
                $result = Process::timeout($timeout)
                    ->env($env)
                    ->run([
                        $pythonBin,
                        base_path('scripts/facial_extract.py'),
                        'extract',
                        $tempFile,                          // Pass file path as argument
                        $isEnrollment ? 'enroll' : 'scan', // Mode flag for Python
                    ]);
            } catch (\Illuminate\Process\Exceptions\ProcessTimedOutException $e) {
                throw new \RuntimeException('The facial recognition system is taking too long to respond. Please try again or check if the implementation is efficient enough.');
            }

            $output = json_decode($result->output(), true) ?? [];

            if (!$result->successful() || empty($output['success'])) {
                $errorMsg = $output['error'] ?? $result->errorOutput();
                if (empty($errorMsg)) {
                    // Fallback if both are empty, check stdout
                     $errorMsg = $result->output();
                }

                // Beautify common infrastructure errors for the user
                if (Str::contains($errorMsg, ['Failed to import', 'Module not found', 'No module named'])) {
                    Log::error("Facial Recognition Dependency Error: " . $errorMsg);
                    throw new \RuntimeException("Facial recognition service is currently unavailable. Please contact support.");
                }

                $message = $errorMsg ?: 'Unknown processing failure';
                throw new \RuntimeException("Facial processing failed: {$message}");
            }

            $encoding = $output['facial_encoding'] ?? null;
            if (!is_array($encoding) || count($encoding) !== 128) {
                throw new \RuntimeException('Invalid facial encoding returned by processor.');
            }

            return [
                'encoding' => array_map('floatval', $encoding),
                'face_location' => $output['face_location'] ?? null,
            ];
        } catch (\RuntimeException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new \RuntimeException("Facial processing failed: {$e->getMessage()}", 0, $e);
        } finally {
            // Clean up temp file
            if (file_exists($tempFile)) {
                @unlink($tempFile);
            }
        }
    }

    /**
     * Calculate Euclidean distance between two encodings.
     */
    public function calculateDistance(array $known, array $candidate): float
    {
        if (count($known) !== count($candidate)) {
            throw new \InvalidArgumentException('Facial encodings must have the same length.');
        }

        $sum = 0.0;
        foreach ($known as $index => $value) {
            $diff = (float)$value - (float)($candidate[$index] ?? 0.0);
            $sum += $diff * $diff;
        }

        return sqrt($sum);
    }

    /**
     * Convert a distance into a normalized confidence value (0-1).
     */
    public function confidenceFromDistance(float $distance, float $tolerance = self::DEFAULT_TOLERANCE): float
    {
        if ($tolerance <= 0) {
            return 0.0;
        }

        $confidence = 1 - ($distance / $tolerance);
        return max(0.0, min(1.0, round($confidence, 4)));
    }

    public function isMatch(float $distance, float $tolerance = self::DEFAULT_TOLERANCE): bool
    {
        return $distance <= $tolerance;
    }

    /**
     * Check if a facial encoding already exists for another user.
     * Returns the user ID if a duplicate is found, null otherwise.
     *
     * @param array $newEncoding The facial encoding to check
     * @param int|null $excludeUserId User ID to exclude from the check (e.g., current user being enrolled)
     * @param int|null $excludeBiometricId Biometric ID to exclude from the check (e.g., current biometric record being created)
     * @param float $tolerance Distance tolerance for matching
     * @param bool $onlyEnrolledUsers If true, only check against biometric records with assigned user_id (enrolled users)
     * @return array|null Returns ['user_id' => int, 'user_name' => string, 'distance' => float] if duplicate found, null otherwise
     */
    /**
     * Find the best matching user from the enrolled database.
     * Returns the match details including the user ID, name, distance, and confidence.
     * 
     * @param array $newEncoding The facial encoding to check
     * @param int|null $excludeUserId User ID to exclude (e.g. for duplicate checks)
     * @param int|null $excludeBiometricId Biometric ID to exclude
     * @param float $tolerance Distance tolerance for matching
     * @param bool $onlyEnrolledUsers If true, only checks against records with an assigned User ID
     * @return array|null
     */
    public function findBestMatch(array $newEncoding, ?int $excludeUserId = null, ?int $excludeBiometricId = null, float $tolerance = self::DEFAULT_TOLERANCE, bool $onlyEnrolledUsers = false): ?array
    {
        // Optimization: Only select necessary columns to reduce memory usage and hydration time
        $query = \App\Models\BiometricData::select(['id', 'user_id', 'user_name', 'facial_encoding'])
            ->whereNotNull('facial_encoding')
            ->where('facial_status', 'captured');

        if ($onlyEnrolledUsers) {
            $query->whereNotNull('user_id');
        }

        if ($excludeUserId) {
            $query->where('user_id', '!=', $excludeUserId);
        }

        if ($excludeBiometricId) {
            $query->where('id', '!=', $excludeBiometricId);
        }
            
        // Use standard get() so Eloquent model casts (like encrypted:array) are automatically applied
        $enrolledFaces = $query->get(); 

        $bestBiometric = null;
        $minDistance = $tolerance; // Start with tolerance — anything worse is not a match

        foreach ($enrolledFaces as $biometric) {
            // facial_encoding is automatically decrypted and cast to an array by Eloquent
            $storedEncoding = $biometric->facial_encoding;

            if (!is_array($storedEncoding) || count($storedEncoding) !== count($newEncoding)) {
                continue;
            }

            // Inline distance calculation for critical path speed.
            // Distance check: sqrt(sum) <= minDistance  <=>  sum <= minDistance²
            // Avoids sqrt() on every iteration — only called when a candidate match is found.
            $sum = 0.0;
            $limitSq = $minDistance * $minDistance;

            foreach ($storedEncoding as $index => $value) {
                $diff = (float)$value - (float)($newEncoding[$index] ?? 0.0);
                $sum += $diff * $diff;

                // Early exit if we already exceeded the squared limit (optimization)
                if ($sum > $limitSq) {
                    break;
                }
            }

            if ($sum <= $limitSq) {
                // Candidate passed the distance gate — compute the true distance
                $trueDistance = sqrt($sum);

                // Dual-gate: also require the match to exceed the minimum confidence floor.
                // This prevents borderline matches (e.g. distance=0.37, tolerance=0.38 → 2.6% confidence)
                // from being accepted as reliable identifications.
                $confidence = $this->confidenceFromDistance($trueDistance, $tolerance);
                if ($confidence >= self::MIN_CONFIDENCE_FLOOR) {
                    $minDistance    = $trueDistance;
                    $bestBiometric  = $biometric;
                }
            }
        }

        if ($bestBiometric) {
            // Lazy-load user name only on a confirmed match (avoids joins/eager loading on all records)
            $userName = $bestBiometric->user_name;
            if (!$userName && $bestBiometric->user_id) {
                $user = \App\Models\User::find($bestBiometric->user_id);
                $userName = $user ? $user->name : 'Unknown';
            }

            $confidence = $this->confidenceFromDistance($minDistance, $tolerance);
            Log::info("Facial Match Found: User {$userName} (ID: {$bestBiometric->user_id}) — distance={$minDistance}, confidence={$confidence}, tolerance={$tolerance}");

            return [
                'user_id'    => $bestBiometric->user_id,
                'user_name'  => $userName ?? 'Unknown',
                'distance'   => $minDistance,
                'confidence' => $confidence,
            ];
        }

        return null;
    }

    /**
     * Alias for findBestMatch to maintain backward compatibility.
     */
    public function findDuplicateFace(array $newEncoding, ?int $excludeUserId = null, ?int $excludeBiometricId = null, float $tolerance = self::DEFAULT_TOLERANCE, bool $onlyEnrolledUsers = false): ?array
    {
        return $this->findBestMatch($newEncoding, $excludeUserId, $excludeBiometricId, $tolerance, $onlyEnrolledUsers);
    }

    /**
     * Strip the data URI prefix and decode the base64 payload.
     * Note: This method is no longer used for facial encoding extraction,
     * but kept for potential future use.
     */
    protected function decodeImage(string $base64Image): string
    {
        $clean = preg_replace('#^data:image/\w+;base64,#i', '', $base64Image);
        $binary = base64_decode($clean, true);

        if ($binary === false) {
            throw new \RuntimeException('Invalid image data supplied.');
        }

        return $binary;
    }

    protected function pythonBinary(): string
    {
        if (strtoupper(PHP_OS_FAMILY) === 'WINDOWS') {
            // Try to find Python executable
            $pythonPath = shell_exec('where python 2>nul');
            if ($pythonPath) {
                $pythonPath = trim(explode("\n", $pythonPath)[0]);
                if (file_exists($pythonPath)) {
                    return $pythonPath;
                }
            }
            return 'python';
        }

        // On Linux, try to use the project's venv if it exists
        $venvPath = base_path('venv/bin/python');
        if (file_exists($venvPath)) {
            return $venvPath;
        }

        $dotVenvPath = base_path('.venv/bin/python');
        if (file_exists($dotVenvPath)) {
            return $dotVenvPath;
        }

        return 'python3';
    }

    protected function getUserSitePackagesPath(): ?string
    {
        if (strtoupper(PHP_OS_FAMILY) !== 'WINDOWS') {
            return null;
        }

        // Get Python version
        $pythonBin = $this->pythonBinary();
        $versionOutput = shell_exec("{$pythonBin} --version 2>&1");
        
        if (preg_match('/Python (\d+)\.(\d+)/', $versionOutput, $matches)) {
            $major = $matches[1];
            $minor = $matches[2];
            
            // Try multiple methods to get APPDATA
            $appdata = getenv('APPDATA');
            if (!$appdata) {
                // Fallback: Try to get from user profile
                $userProfile = getenv('USERPROFILE');
                if ($userProfile) {
                    $appdata = $userProfile . '\\AppData\\Roaming';
                }
            }
            
            if ($appdata) {
                $userSitePackages = $appdata . "\\Python\\Python{$major}{$minor}\\site-packages";
                
                if (is_dir($userSitePackages)) {
                    return $userSitePackages;
                }
            }
            
            // Last resort removed for portability
        }

        return null;
    }
}
