<?php

namespace App\Services;

use App\Models\AttendanceLog;
use App\Models\AttendanceScore;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AttendanceScoringService
{
    // ── Clock-In Rules ──────────────────────────────────────────────────────
    // 07:00 – 07:50  Early    +10
    // 07:51 – 08:00  Punctual +10
    // 08:01 – 08:15  On Time  -1
    // 08:16 – 08:30  Grace    -2
    // After 08:30    Late     -5

    // ── Clock-Out Rules ─────────────────────────────────────────────────────
    // Before 16:00   Early Departure -5
    // 16:00 – 16:29  Left Early      -2
    // 16:30 – 17:59  Normal          +5
    // 18:00+         Late Departure   0

    private function parseTime(string $timeStr): int
    {
        $parts = explode(':', $timeStr);
        return (int) $parts[0] * 60 + (int) $parts[1];
    }

    public function classifyClockIn(Carbon $time): array
    {
        $total = $time->hour * 60 + $time->minute;
        $rules = \App\Models\SystemSetting::get('gamification_rules');

        if (!$rules) {
            // Fallback just in case
            $rules = [
                'early_before' => '07:50', 'punctual_before' => '08:00', 'on_time_before' => '08:15', 'grace_before' => '08:30',
                'early_points' => 10, 'punctual_points' => 10, 'on_time_points' => -1, 'grace_points' => -2, 'late_points' => -5
            ];
        }

        return match(true) {
            $total <= $this->parseTime($rules['early_before'])    => ['status' => 'early',    'points' => (int) $rules['early_points']],
            $total <= $this->parseTime($rules['punctual_before']) => ['status' => 'punctual', 'points' => (int) $rules['punctual_points']],
            $total <= $this->parseTime($rules['on_time_before'])  => ['status' => 'on_time',  'points' => (int) $rules['on_time_points']],
            $total <= $this->parseTime($rules['grace_before'])    => ['status' => 'grace',    'points' => (int) $rules['grace_points']],
            default                                               => ['status' => 'late',     'points' => (int) $rules['late_points']],
        };
    }

    public function classifyClockOut(Carbon $time): array
    {
        $total = $time->hour * 60 + $time->minute;
        $rules = \App\Models\SystemSetting::get('gamification_rules');

        if (!$rules) {
            // Fallback
            $rules = [
                'early_departure_before' => '16:00', 'left_early_before' => '16:30', 'normal_departure_before' => '18:00',
                'early_departure_points' => -5, 'left_early_points' => -2, 'normal_departure_points' => 5, 'late_departure_points' => 0
            ];
        }

        return match(true) {
            $total < $this->parseTime($rules['early_departure_before'])  => ['status' => 'early_departure', 'points' => (int) $rules['early_departure_points']],
            $total <= $this->parseTime($rules['left_early_before'])      => ['status' => 'left_early',      'points' => (int) $rules['left_early_points']],
            $total < $this->parseTime($rules['normal_departure_before']) => ['status' => 'normal',          'points' => (int) $rules['normal_departure_points']],
            default                                                      => ['status' => 'late_departure',  'points' => (int) $rules['late_departure_points']],
        };
    }

    public function scoreDay(User $user, Carbon $date): AttendanceScore
    {
        $dateStr = $date->toDateString();

        $clockIn = AttendanceLog::where('user_id', $user->id)
            ->whereDate('timestamp', $dateStr)
            ->where('action', 'clock_in')
            ->latest('timestamp')
            ->first();

        $clockOut = AttendanceLog::where('user_id', $user->id)
            ->whereDate('timestamp', $dateStr)
            ->where('action', 'clock_out')
            ->latest('timestamp')
            ->first();

        $ciResult = $clockIn
            ? $this->classifyClockIn($clockIn->timestamp)
            : ['status' => 'absent', 'points' => 0];

        $coResult = $clockOut
            ? $this->classifyClockOut($clockOut->timestamp)
            : ['status' => 'absent', 'points' => 0];

        $score = AttendanceScore::withoutTenantScope()->updateOrCreate(
            ['user_id' => $user->id, 'attendance_date' => $dateStr],
            [
                'tenant_id'        => $user->tenant_id,
                'clock_in_log_id'  => $clockIn?->id,
                'clock_out_log_id' => $clockOut?->id,
                'clock_in_time'    => $clockIn?->timestamp->format('H:i:s'),
                'clock_out_time'   => $clockOut?->timestamp->format('H:i:s'),
                'clock_in_status'  => $ciResult['status'],
                'clock_out_status' => $coResult['status'],
                'clock_in_points'  => $ciResult['points'],
                'clock_out_points' => $coResult['points'],
                'total_points'     => $ciResult['points'] + $coResult['points'],
            ]
        );

        Log::info("Scored day for user #{$user->id} on {$dateStr}: {$score->total_points} pts");

        return $score;
    }
}
