<?php

namespace App\Services;

use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class FacialRecognitionService
{
    public const DEFAULT_TOLERANCE = 0.45;

    /**
     * Extract a 128-d facial encoding from a base64 image using the Python pipeline.
     *
     * @param  string  $base64Image
     * @return array{encoding: array<int, float>, face_location: mixed}
     */
    public function extractEncodingFromBase64(string $base64Image): array
    {
        // Try the persistent server first (Fast Mode)
        try {
            return $this->extractViaServer($base64Image);
        } catch (\Throwable $e) {
            // Log the error but don't stop - fallback to CLI (Slow Mode)
            // In production, you might want to log this to know the server is down
        }

        // Fallback: standard CLI execution (Slow Mode)
        return $this->extractViaCli($base64Image);
    }

    protected function extractViaServer(string $base64Image): array
    {
        $host = config('services.recognition.host', 'http://localhost:5001');
        
        $payload = json_encode([
            'action' => 'extract',
            'image' => $base64Image
        ]);

        $options = [
            'http' => [
                'header'  => "Content-type: application/json\r\nContent-Length: " . strlen($payload) . "\r\n",
                'method'  => 'POST',
                'content' => $payload,
                'timeout' => 5, // 5 seconds timeout
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

    protected function extractViaCli(string $base64Image): array
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
            try {
                $result = Process::timeout(60)
                    ->env($env)
                    ->run([
                        $pythonBin,
                        base_path('scripts/facial_extract.py'),
                        'extract',
                        $tempFile // Pass file path as argument
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
            
        // Use a generator cursor for memory efficiency if dataset is large, 
        // though for typical attendance (<10k users) standard collection is faster due to fewer DB roundtrips.
        // Sticking to get() for speed on reasonable datasets.
        $enrolledFaces = $query->toBase()->get(); // toBase() skips model hydration for raw speed on iteration

        $bestMatch = null;
        $bestBiometric = null;
        $minDistance = $tolerance; // Start with tolerance - anything worse is not a match

        foreach ($enrolledFaces as $biometric) {
            // Manually decode since we used toBase()
            $storedEncoding = json_decode($biometric->facial_encoding);

            if (!is_array($storedEncoding) || count($storedEncoding) !== count($newEncoding)) {
                continue;
            }

            // Inline distance calculation for critical path speed
            // Skip sqrt() for comparison to be faster? 
            // Distance check: sqrt(sum) <= minDistance  <=>  sum <= minDistance^2
            // avoiding sqrt() call in the loop saves CPU cycles.
            
            $sum = 0.0;
            $limitSq = $minDistance * $minDistance;
            
            foreach ($storedEncoding as $index => $value) {
                $diff = (float)$value - (float)($newEncoding[$index] ?? 0.0);
                $sum += $diff * $diff;
                
                // Early exit if we already exceeded the limit (optimization)
                if ($sum > $limitSq) {
                    break; 
                }
            }

            if ($sum <= $limitSq) {
                // We found a new best match (or equal best)
                // Recalculate true distance only when we find a match
                $trueDistance = sqrt($sum);
                
                $minDistance = $trueDistance;
                $bestBiometric = $biometric;
            }
        }

        if ($bestBiometric) {
            // Lazy load the user name only if we found a match (avoids joins/eager loading on all records)
            $userName = $bestBiometric->user_name;
            if (!$userName && $bestBiometric->user_id) {
                $user = \App\Models\User::find($bestBiometric->user_id);
                $userName = $user ? $user->name : 'Unknown';
            }

            Log::info("Facial Match Found: User {$userName} (ID: {$bestBiometric->user_id}) with distance {$minDistance} (Tolerance: {$tolerance})");

            return [
                'user_id' => $bestBiometric->user_id,
                'user_name' => $userName ?? 'Unknown',
                'distance' => $minDistance,
                'confidence' => $this->confidenceFromDistance($minDistance, $tolerance),
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
