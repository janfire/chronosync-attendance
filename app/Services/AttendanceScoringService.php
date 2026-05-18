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

    public function classifyClockIn(Carbon $time): array
    {
        $total = $time->hour * 60 + $time->minute;

        return match(true) {
            $total <= (7 * 60 + 50)  => ['status' => 'early',    'points' => 10],
            $total <= (8 * 60)       => ['status' => 'punctual', 'points' => 10],
            $total <= (8 * 60 + 15)  => ['status' => 'on_time',  'points' => -1],
            $total <= (8 * 60 + 30)  => ['status' => 'grace',    'points' => -2],
            default                  => ['status' => 'late',     'points' => -5],
        };
    }

    public function classifyClockOut(Carbon $time): array
    {
        $total = $time->hour * 60 + $time->minute;

        return match(true) {
            $total < (16 * 60)       => ['status' => 'early_departure', 'points' => -5],
            $total <= (16 * 60 + 29) => ['status' => 'left_early',      'points' => -2],
            $total < (18 * 60)       => ['status' => 'normal',          'points' =>  5],
            default                  => ['status' => 'late_departure',  'points' =>  0],
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

        $score = AttendanceScore::updateOrCreate(
            ['user_id' => $user->id, 'attendance_date' => $dateStr],
            [
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
