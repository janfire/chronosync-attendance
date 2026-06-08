<?php

namespace App\Services;

use App\Models\AttendanceLog;
use App\Models\User;
use App\Models\SystemSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportService
{
    protected $shiftRules;
    protected $holidays;

    public function __construct()
    {
        $this->shiftRules = SystemSetting::get('shift_rules', [
            'shift_start' => '08:00',
            'shift_end' => '16:30',
            'late_after' => '09:00',
            'early_out_before' => '15:50',
        ]);

        $holidays = SystemSetting::get('holidays', []);
        $this->holidays = [];
        if (is_array($holidays)) {
            foreach ($holidays as $holiday) {
                if (is_array($holiday) && isset($holiday['date'])) {
                    $this->holidays[] = $holiday['date'];
                } elseif (is_string($holiday)) {
                    $this->holidays[] = $holiday;
                }
            }
        }
    }

    /**
     * Get summary stats for the dashboard.
     */
    public function getSummaryStats($dateRange = null)
    {
        $start = $dateRange['from'] ?? today();
        $end = $dateRange['to'] ?? today();

        $eventsCount = AttendanceLog::whereBetween('timestamp', [$start, $end->endOfDay()])->count();
        $usersCount = User::where('role', 'staff')->count();
        
        $lateRate = $this->calculateLatenessRate($start, $end);

        return [
            'events' => $eventsCount,
            'users' => $usersCount,
            'late_rate' => $lateRate
        ];
    }

    /**
     * Calculate lateness rate for a period.
     */
    private function calculateLatenessRate($start, $end)
    {
        $lateThreshold = $this->shiftRules['late_after'] ?? '09:00';
        
        $totalClockIns = AttendanceLog::where('action', 'clock_in')
            ->whereBetween('timestamp', [$start, $end->endOfDay()])
            ->count();

        if ($totalClockIns === 0) return 0;

        $lateClockIns = AttendanceLog::where('action', 'clock_in')
            ->whereBetween('timestamp', [$start, $end->endOfDay()])
            ->whereTime('timestamp', '>', $lateThreshold)
            ->count();

        return round(($lateClockIns / $totalClockIns) * 100, 1);
    }

    /**
     * Get Top 5 Compliant Users.
     * Compliant = Most days present + high hours.
     */
    public function getTop5Compliant($month = null)
    {
        $start = $month ? Carbon::parse($month)->startOfMonth() : now()->startOfMonth();
        $end = $month ? Carbon::parse($month)->endOfMonth() : now()->endOfMonth();

        $users = User::where('role', 'staff')->get();
        $stats = [];

        foreach ($users as $user) {
            $userStats = $this->getUserMonthlyDetails($user->id, $start, $end);
            $stats[] = array_merge(['user' => $user], $userStats);
        }

        // Sort by days present (desc) then hours (desc)
        usort($stats, function($a, $b) {
            if ($a['days_present'] === $b['days_present']) {
                return $b['total_hours'] <=> $a['total_hours'];
            }
            return $b['days_present'] <=> $a['days_present'];
        });

        return array_slice($stats, 0, 5);
    }

    /**
     * Get Top 5 Punctual Users.
     * Punctual = Smallest average minutes after shift start.
     */
    public function getTop5Punctual($month = null)
    {
        $start = $month ? Carbon::parse($month)->startOfMonth() : now()->startOfMonth();
        $end = $month ? Carbon::parse($month)->endOfMonth() : now()->endOfMonth();
        $shiftStart = Carbon::parse($this->shiftRules['shift_start']);

        $logs = AttendanceLog::where('action', 'clock_in')
            ->whereBetween('timestamp', [$start, $end])
            ->get();

        $userAverages = $logs->groupBy('user_id')->map(function($userLogs) {
            $totalMinutes = $userLogs->sum(function($log) {
                return $log->timestamp->hour * 60 + $log->timestamp->minute;
            });
            return $totalMinutes / $userLogs->count();
        });

        $stats = [];
        foreach ($userAverages as $userId => $avgMinutes) {
            $user = User::find($userId);
            if (!$user) continue;

            $shiftStartMinutes = $shiftStart->hour * 60 + $shiftStart->minute;
            $diff = max(0, $avgMinutes - $shiftStartMinutes);

            $stats[] = [
                'user' => $user,
                'avg_diff' => round($diff, 1)
            ];
        }

        usort($stats, fn($a, $b) => $a['avg_diff'] <=> $b['avg_diff']);
        return array_slice($stats, 0, 5);
    }

    /**
     * Get Top 5 Active Outside Hours.
     */
    public function getTop5OutsideHours($month = null)
    {
        $start = $month ? Carbon::parse($month)->startOfMonth() : now()->startOfMonth();
        $end = $month ? Carbon::parse($month)->endOfMonth() : now()->endOfMonth();
        $shiftStart = $this->shiftRules['shift_start'];
        $shiftEnd = $this->shiftRules['shift_end'];

        $logs = AttendanceLog::whereBetween('timestamp', [$start, $end])
            ->where(function($q) use ($shiftStart, $shiftEnd) {
                $q->whereTime('timestamp', '<', $shiftStart)
                  ->orWhereTime('timestamp', '>', $shiftEnd);
            })
            ->select('user_id', DB::raw('count(*) as outside_count'))
            ->groupBy('user_id')
            ->orderBy('outside_count', 'desc')
            ->take(5)
            ->get();

        foreach ($logs as $log) {
            $log->user = User::find($log->user_id);
        }

        return $logs;
    }

    /**
     * Calculate "Clipped Hours" for a user in a given period.
     */
    public function getUserMonthlyDetails($userId, $start, $end)
    {
        $logs = AttendanceLog::where('user_id', $userId)
            ->whereBetween('timestamp', [$start, $end])
            ->orderBy('timestamp')
            ->get()
            ->groupBy(fn($log) => $log->timestamp->format('Y-m-d'));

        $totalClippedMinutes = 0;
        $daysPresent = 0;
        $lateDays = 0;
        $earlyLeaves = 0;
        $signIns = [];
        $signOuts = [];

        $shiftStart = Carbon::parse($this->shiftRules['shift_start']);
        $shiftEnd = Carbon::parse($this->shiftRules['shift_end']);
        $lateThreshold = Carbon::parse($this->shiftRules['late_after']);
        $earlyThreshold = Carbon::parse($this->shiftRules['early_out_before']);

        foreach ($logs as $date => $dayLogs) {
            $carbonDate = Carbon::parse($date);
            
            // Skip weekends and holidays
            if ($carbonDate->isWeekend() || in_array($date, $this->holidays)) {
                continue;
            }

            $clockIn = $dayLogs->where('action', 'clock_in')->first();
            $clockOut = $dayLogs->where('action', 'clock_out')->last();

            if ($clockIn) {
                $daysPresent++;
                $signIns[] = $clockIn->timestamp->format('H:i');
                
                if ($clockIn->timestamp->format('H:i:s') > $lateThreshold->format('H:i:s')) {
                    $lateDays++;
                }

                if ($clockOut) {
                    $signOuts[] = $clockOut->timestamp->format('H:i');
                    
                    if ($clockOut->timestamp->format('H:i:s') < $earlyThreshold->format('H:i:s')) {
                        $earlyLeaves++;
                    }

                    // CLIIPPING LOGIC
                    $actualIn = $clockIn->timestamp;
                    $actualOut = $clockOut->timestamp;

                    // Clip In to Shift Start
                    $effectiveIn = $actualIn->format('H:i:s') < $shiftStart->format('H:i:s') 
                        ? $actualIn->copy()->setTimeFrom($shiftStart) 
                        : $actualIn;

                    // Clip Out to Shift End
                    $effectiveOut = $actualOut->format('H:i:s') > $shiftEnd->format('H:i:s') 
                        ? $actualOut->copy()->setTimeFrom($shiftEnd) 
                        : $actualOut;

                    if ($effectiveOut > $effectiveIn) {
                        $totalClippedMinutes += $effectiveIn->diffInMinutes($effectiveOut);
                    }
                }
            }
        }

        return [
            'total_hours' => round($totalClippedMinutes / 60, 1),
            'days_present' => $daysPresent,
            'late_count' => $lateDays,
            'early_leaves' => $earlyLeaves,
            'avg_sign_in' => count($signIns) ? $this->averageTime($signIns) : 'N/A',
            'avg_sign_out' => count($signOuts) ? $this->averageTime($signOuts) : 'N/A',
        ];
    }

    private function averageTime($times)
    {
        $totalSeconds = 0;
        foreach ($times as $time) {
            list($h, $m) = explode(':', $time);
            $totalSeconds += $h * 3600 + $m * 60;
        }
        $avgSeconds = $totalSeconds / count($times);
        return sprintf('%02d:%02d', floor($avgSeconds / 3600), floor(($avgSeconds % 3600) / 60));
    }

    /**
     * Get Weekly Top 5 Late Comers.
     */
    public function getTop5LateComersWeekly()
    {
        $start = now()->startOfWeek();
        $end = now()->endOfWeek();
        $lateThreshold = $this->shiftRules['late_after'] ?? '09:00';

        $results = AttendanceLog::where('action', 'clock_in')
            ->whereBetween('timestamp', [$start, $end])
            ->whereTime('timestamp', '>', $lateThreshold)
            ->select('user_id', DB::raw('count(*) as late_count'))
            ->groupBy('user_id')
            ->orderBy('late_count', 'desc')
            ->take(5)
            ->get();

        foreach ($results as $res) {
            $res->user = User::find($res->user_id);
        }

        return $results;
    }

    /**
     * Get Weekly Top 5 Early Sign-outs.
     */
    public function getTop5EarlySignOutsWeekly()
    {
        $start = now()->startOfWeek();
        $end = now()->endOfWeek();
        $earlyThreshold = $this->shiftRules['early_out_before'] ?? '15:50';

        $results = AttendanceLog::where('action', 'clock_out')
            ->whereBetween('timestamp', [$start, $end])
            ->whereTime('timestamp', '<', $earlyThreshold)
            ->select('user_id', DB::raw('count(*) as early_count'))
            ->groupBy('user_id')
            ->orderBy('early_count', 'desc')
            ->take(5)
            ->get();

        foreach ($results as $res) {
            $res->user = User::find($res->user_id);
        }

        return $results;
    }
}
