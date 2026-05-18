<?php

namespace App\Services;

use App\Models\AttendanceLog;
use App\Models\BiometricData;
use Illuminate\Support\Facades\Log;

/**
 * ZKTeco Fingerprint Device Service
 * 
 * Handles communication with ZKTeco fingerprint devices for enrollment and verification.
 * Supports both direct device integration and scheduled sync of attendance logs.
 */
class ZKTecoService
{
    protected $device;
    protected $connected = false;

    public function __construct()
    {
        // Check if ZKTeco package is installed
        if (!config('zkteco.enabled')) {
            return;
        }

        $ip = config('zkteco.device_ip');
        $port = config('zkteco.device_port', 4370);
        
        // Only initialize if package exists
        if (class_exists('\Rats\Zkteco\Lib\ZKTeco')) {
            try {
                $this->device = new \Rats\Zkteco\Lib\ZKTeco($ip, $port);
            } catch (\Exception $e) {
                Log::warning('ZKTeco device initialization failed: ' . $e->getMessage());
            }
        }
    }

    /**
     * Connect to ZKTeco device
     */
    public function connect(): bool
    {
        if (!$this->device) {
            Log::warning('ZKTeco device not initialized');
            return false;
        }

        try {
            $this->connected = $this->device->connect();
            Log::info('ZKTeco device connected successfully');
            return $this->connected;
        } catch (\Exception $e) {
            Log::error('ZKTeco connection failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Disconnect from ZKTeco device
     */
    public function disconnect(): bool
    {
        if (!$this->device || !$this->connected) {
            return false;
        }

        try {
            $result = $this->device->disconnect();
            $this->connected = false;
            return $result;
        } catch (\Exception $e) {
            Log::error('ZKTeco disconnection failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Enroll fingerprint on ZKTeco device
     * 
     * @param int $userId User ID
     * @param string $fingerprintTemplate Base64 encoded fingerprint template
     * @return bool Success status
     */
    public function enrollFingerprint(int $userId, string $fingerprintTemplate): bool
    {
        if (!$this->device) {
            Log::warning('Cannot enroll: ZKTeco device not initialized');
            return false;
        }

        if (!$this->connected) {
            $this->connect();
        }

        try {
            // Store fingerprint template on device
            // Parameters: uid, userid, name, password, role, cardno
            $result = $this->device->setUser(
                $userId,        // uid
                $userId,        // userid  
                '',             // name (optional)
                '',             // password (optional)
                0,              // role (0 = User)
                $fingerprintTemplate  // cardno/template
            );

            Log::info('Fingerprint enrolled on ZKTeco device', [
                'user_id' => $userId,
                'success' => $result
            ]);

            return $result;
        } catch (\Exception $e) {
            Log::error('Fingerprint enrollment failed on device: ' . $e->getMessage(), [
                'user_id' => $userId
            ]);
            return false;
        }
    }

    /**
     * Verify fingerprint against enrolled templates
     * 
     * @param string $fingerprintTemplate Fingerprint to verify
     * @return int|null User ID if match found, null otherwise
     */
    public function verifyFingerprint(string $fingerprintTemplate): ?int
    {
        if (!$this->device) {
            return null;
        }

        if (!$this->connected) {
            $this->connect();
        }

        try {
            // Get all users from device
            $users = $this->device->getUser();
            
            // Device comparison logic would go here
            // This is simplified - actual implementation depends on ZKTeco SDK capabilities
            
            // For now, we'll do database verification instead
            return $this->verifyFingerprintAgainstDatabase($fingerprintTemplate);
            
        } catch (\Exception $e) {
            Log::error('Fingerprint verification failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Verify fingerprint against database templates
     * 
     * @param string $fingerprintTemplate Template to verify
     * @return int|null User ID if match found
     */
    public function verifyFingerprintAgainstDatabase(string $fingerprintTemplate): ?int
    {
        // 1. Precise Match
        $biometric = BiometricData::where('fingerprint_template', $fingerprintTemplate)
            ->whereNotNull('user_id')
            ->first();

        if ($biometric) {
            Log::info('Fingerprint verified: Exact match', ['user_id' => $biometric->user_id]);
            return $biometric->user_id;
        }

        // 2. Fuzzy Match
        $allBiometrics = BiometricData::whereNotNull('fingerprint_template')
            ->whereNotNull('user_id')
            ->select('id', 'user_id', 'fingerprint_template', 'fingerprint_device_id')
            ->get();

        $bestMatchId = null;
        $highestScore = 0;
        
        $inputBin = base64_decode($fingerprintTemplate);

        foreach ($allBiometrics as $bio) {
            $stored = $bio->fingerprint_template;
            
            // Skip WebAuthn credentials (which are JSON objects)
            if (str_starts_with($stored, '{')) continue;

            $storedBin = base64_decode($stored);

            // Simple binary substring match
            if (str_contains($storedBin, $inputBin) || str_contains($inputBin, $storedBin)) {
                 Log::info('Fingerprint verified: Binary substring match', ['user_id' => $bio->user_id]);
                 return $bio->user_id;
            }
            
            // Allow for significant overlap (e.g. 80% of the string matches)
            // This handles cases where one template is truncated
            similar_text($stored, $fingerprintTemplate, $percent);
            
            if ($percent > 10) { // Log potential candidates
                 Log::debug('Fuzzy match candidate', [
                     'user_id' => $bio->user_id,
                     'score' => $percent,
                     'stored_sample' => substr($stored, 0, 20) . '...',
                     'input_sample' => substr($fingerprintTemplate, 0, 20) . '...'
                 ]);
            }

            if ($percent > $highestScore) {
                $highestScore = $percent;
                $bestMatchId = $bio->user_id;
            }
        }
        
        // Lower threshold to 85% given observation of significant prefix differences
        if ($highestScore > 85 && $bestMatchId) {
             Log::info('Fingerprint verified: Fuzzy match', [
                 'user_id' => $bestMatchId,
                 'score' => $highestScore
             ]);
             return $bestMatchId;
        }

        Log::warning('Fingerprint not found. Best score: ' . $highestScore);
        return null;
    }

    /**
     * Get attendance logs from ZKTeco device
     * 
     * @return array Attendance records
     */
    public function getAttendanceLogs(): array
    {
        if (!$this->device) {
            return [];
        }

        if (!$this->connected) {
            $this->connect();
        }

        try {
            $logs = $this->device->getAttendance();
            return $logs ?? [];
        } catch (\Exception $e) {
            Log::error('Failed to get attendance logs from device: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Sync attendance logs from device to database
     * 
     * @return int Number of logs synced
     */
    public function syncAttendanceLogsToDatabase(): int
    {
        $logs = $this->getAttendanceLogs();
        $synced = 0;

        foreach ($logs as $log) {
            try {
                // Check if already synced (using device log ID)
                $deviceLogId = $log['id'] ?? ($log['userId'] . '_' . $log['timestamp']);
                
                $exists = AttendanceLog::where('device_log_id', $deviceLogId)->exists();
                
                if (!$exists) {
                    AttendanceLog::create([
                        'user_id' => $log['userId'] ?? null,
                        'user_name' => $log['userName'] ?? 'Unknown',
                        'action' => ($log['status'] ?? 0) == 0 ? 'clock_in' : 'clock_out',
                        'timestamp' => $log['timestamp'] ?? now(),
                        'device_log_id' => $deviceLogId,
                        'source' => 'fingerprint_zkteco',
                        'network_info' => 'ZKTeco Device Sync',
                    ]);
                    $synced++;
                }
            } catch (\Exception $e) {
                Log::error('Failed to sync attendance log: ' . $e->getMessage(), [
                    'log' => $log
                ]);
            }
        }

        if ($synced > 0) {
            Log::info("Synced {$synced} attendance logs from ZKTeco device");
        }

        return $synced;
    }

    /**
     * Test device connection
     * 
     * @return array Connection status and details
     */
    public function testConnection(): array
    {
        if (!$this->device) {
            return [
                'success' => false,
                'message' => 'ZKTeco device not initialized. Package may not be installed.',
                'details' => [
                    'enabled' => config('zkteco.enabled'),
                    'device_ip' => config('zkteco.device_ip'),
                ]
            ];
        }

        $connected = $this->connect();
        
        if ($connected) {
            try {
                $deviceInfo = [
                    'platform' => $this->device->platform() ?? 'Unknown',
                    'version' => $this->device->version() ?? 'Unknown',
                ];
                
                $this->disconnect();
                
                return [
                    'success' => true,
                    'message' => 'Successfully connected to ZKTeco device',
                    'details' => $deviceInfo
                ];
            } catch (\Exception $e) {
                return [
                    'success' => false,
                    'message' => 'Connected but failed to get device info',
                    'error' => $e->getMessage()
                ];
            }
        }

        return [
            'success' => false,
            'message' => 'Failed to connect to ZKTeco device',
            'details' => [
                'device_ip' => config('zkteco.device_ip'),
                'device_port' => config('zkteco.device_port'),
            ]
        ];
    }

    /**
     * Delete user from ZKTeco device
     * 
     * @param int $userId User ID to delete
     * @return bool Success status
     */
    public function deleteUser(int $userId): bool
    {
        if (!$this->device) {
            return false;
        }

        if (!$this->connected) {
            $this->connect();
        }

        try {
            $result = $this->device->removeUser($userId);
            Log::info('User deleted from ZKTeco device', [
                'user_id' => $userId,
                'success' => $result
            ]);
            return $result;
        } catch (\Exception $e) {
            Log::error('Failed to delete user from device: ' . $e->getMessage(), [
                'user_id' => $userId
            ]);
            return false;
        }
    }
}
