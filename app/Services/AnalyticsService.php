<?php

namespace App\Services;

use App\Models\AttendanceLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    /**
     * Calculate total hours worked for a given period.
     * Assumes a simple Clock In -> Clock Out pair for each day.
     */
    public function calculateTotalHours($period = 'week')
    {
        $query = AttendanceLog::query();
        
        $startDate = match($period) {
            'day' => today(),
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            default => now()->startOfWeek(),
        };

        $logs = $query->where('timestamp', '>=', $startDate)
                      ->orderBy('user_id')
                      ->orderBy('timestamp')
                      ->get()
                      ->groupBy('user_id');

        $totalMinutes = 0;

        foreach ($logs as $userId => $userLogs) {
            // Group by day to handle multiple shifts in a day if needed, 
            // though basic logic is first in, last out or pairs.
            // Let's use a robust pairing strategy:
            $dailyLogs = $userLogs->groupBy(function($log) {
                return $log->timestamp->format('Y-m-d');
            });

            foreach ($dailyLogs as $day => $dayLogs) {
                $clockIn = $dayLogs->where('action', 'clock_in')->first();
                $clockOut = $dayLogs->where('action', 'clock_out')->last(); // Take the last clock out

                if ($clockIn && $clockOut && $clockOut->timestamp > $clockIn->timestamp) {
                    $totalMinutes += $clockIn->timestamp->diffInMinutes($clockOut->timestamp);
                }
            }
        }

        return round($totalMinutes / 60, 1);
    }

    /**
     * Calculate average daily hours per employee for the current week.
     */
    public function calculateAverageDailyHours()
    {
        $totalHours = $this->calculateTotalHours('week');
        $activeEmployees = User::where('role', 'staff')->count(); // Or filtered by those who attended
        
        // Days elapsed in the current working week (Mon-Fri)
        $daysElapsed = min(now()->dayOfWeekIso, 5); 

        if ($activeEmployees == 0 || $daysElapsed == 0) return 0;

        return round($totalHours / ($activeEmployees * $daysElapsed), 1);
    }

    /**
     * Get peak traffic times (histogram of clock-ins by hour).
     */
    public function getPeakTrafficTimes()
    {
        $logs = AttendanceLog::where('action', 'clock_in')
            ->where('timestamp', '>=', now()->subDays(30)) // Last 30 days
            ->get();

        $data = $logs->groupBy(function($log) {
            return (int) $log->timestamp->format('H');
        })->map(function($group) {
            return $group->count();
        })->toArray();

        // Fill missing hours
        $result = [];
        for ($i = 6; $i <= 20; $i++) { // From 6 AM to 8 PM
            $result[$i] = $data[$i] ?? 0;
        }

        return $result;
    }

    /**
     * Calculate late arrival rate (percentage of clock-ins after 9:00 AM).
     */
    public function getLatenessRate()
    {
        $totalClockIns = AttendanceLog::where('action', 'clock_in')
            ->where('timestamp', '>=', now()->startOfMonth())
            ->count();

        if ($totalClockIns == 0) return 0;

        $lateClockIns = AttendanceLog::where('action', 'clock_in')
            ->where('timestamp', '>=', now()->startOfMonth())
            ->whereTime('timestamp', '>', '09:00:00')
            ->count();

        return round(($lateClockIns / $totalClockIns) * 100, 1);
    }

    /**
     * Get Weekly Work Hours Trend (last 7 days).
     */
    public function getWeeklyHoursTrend()
    {
        $days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $days[$date->format('D')] = $this->calculateTotalHoursForDate($date);
        }
        return $days;
    }

    private function calculateTotalHoursForDate($date)
    {
        $logs = AttendanceLog::whereDate('timestamp', $date)
            ->get()
            ->groupBy('user_id');

        $totalMinutes = 0;
        foreach ($logs as $userLogs) {
            $clockIn = $userLogs->where('action', 'clock_in')->first();
            $clockOut = $userLogs->where('action', 'clock_out')->last();

            if ($clockIn && $clockOut && $clockOut->timestamp > $clockIn->timestamp) {
                $totalMinutes += $clockIn->timestamp->diffInMinutes($clockOut->timestamp);
            }
        }

        return round($totalMinutes / 60, 1);
    }

    // ========================================
    // PER-EMPLOYEE ANALYTICS
    // ========================================

    /**
     * Get comprehensive analytics for a specific employee.
     */
    public function getEmployeeAnalytics($userId, $period = 'month')
    {
        $startDate = match($period) {
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            '3months' => now()->subMonths(3),
            default => now()->startOfMonth(),
        };

        return [
            'avg_arrival_time' => $this->getEmployeeAvgArrivalTime($userId, $startDate),
            'avg_departure_time' => $this->getEmployeeAvgDepartureTime($userId, $startDate),
            'avg_daily_hours' => $this->getEmployeeAvgDailyHours($userId, $startDate),
            'total_hours' => $this->getEmployeeTotalHours($userId, $startDate),
            'days_worked' => $this->getEmployeeDaysWorked($userId, $startDate),
            'late_count' => $this->getEmployeeLateCount($userId, $startDate),
            'daily_trend' => $this->getEmployeeDailyTrend($userId, 7), // Last 7 days
        ];
    }

    private function getEmployeeAvgArrivalTime($userId, $startDate)
    {
        $clockIns = AttendanceLog::where('user_id', $userId)
            ->where('action', 'clock_in')
            ->where('timestamp', '>=', $startDate)
            ->get();

        if ($clockIns->isEmpty()) return 'N/A';

        $totalSeconds = 0;
        foreach ($clockIns as $log) {
            $totalSeconds += $log->timestamp->hour * 3600 + $log->timestamp->minute * 60;
        }

        $avgSeconds = $totalSeconds / $clockIns->count();
        $hours = floor($avgSeconds / 3600);
        $minutes = floor(($avgSeconds % 3600) / 60);

        return sprintf('%02d:%02d', $hours, $minutes);
    }

    private function getEmployeeAvgDepartureTime($userId, $startDate)
    {
        $clockOuts = AttendanceLog::where('user_id', $userId)
            ->where('action', 'clock_out')
            ->where('timestamp', '>=', $startDate)
            ->get();

        if ($clockOuts->isEmpty()) return 'N/A';

        $totalSeconds = 0;
        foreach ($clockOuts as $log) {
            $totalSeconds += $log->timestamp->hour * 3600 + $log->timestamp->minute * 60;
        }

        $avgSeconds = $totalSeconds / $clockOuts->count();
        $hours = floor($avgSeconds / 3600);
        $minutes = floor(($avgSeconds % 3600) / 60);

        return sprintf('%02d:%02d', $hours, $minutes);
    }

    private function getEmployeeAvgDailyHours($userId, $startDate)
    {
        $logs = AttendanceLog::where('user_id', $userId)
            ->where('timestamp', '>=', $startDate)
            ->orderBy('timestamp')
            ->get()
            ->groupBy(function($log) {
                return $log->timestamp->format('Y-m-d');
            });

        if ($logs->isEmpty()) return 0;

        $totalMinutes = 0;
        $validDays = 0;

        foreach ($logs as $dayLogs) {
            $clockIn = $dayLogs->where('action', 'clock_in')->first();
            $clockOut = $dayLogs->where('action', 'clock_out')->last();

            if ($clockIn && $clockOut && $clockOut->timestamp > $clockIn->timestamp) {
                $totalMinutes += $clockIn->timestamp->diffInMinutes($clockOut->timestamp);
                $validDays++;
            }
        }

        return $validDays > 0 ? round($totalMinutes / $validDays / 60, 1) : 0;
    }

    private function getEmployeeTotalHours($userId, $startDate)
    {
        $logs = AttendanceLog::where('user_id', $userId)
            ->where('timestamp', '>=', $startDate)
            ->orderBy('timestamp')
            ->get()
            ->groupBy(function($log) {
                return $log->timestamp->format('Y-m-d');
            });

        $totalMinutes = 0;

        foreach ($logs as $dayLogs) {
            $clockIn = $dayLogs->where('action', 'clock_in')->first();
            $clockOut = $dayLogs->where('action', 'clock_out')->last();

            if ($clockIn && $clockOut && $clockOut->timestamp > $clockIn->timestamp) {
                $totalMinutes += $clockIn->timestamp->diffInMinutes($clockOut->timestamp);
            }
        }

        return round($totalMinutes / 60, 1);
    }

    private function getEmployeeDaysWorked($userId, $startDate)
    {
        return AttendanceLog::where('user_id', $userId)
            ->where('action', 'clock_in')
            ->where('timestamp', '>=', $startDate)
            ->distinct('timestamp')
            ->count(DB::raw('DATE(timestamp)'));
    }

    private function getEmployeeLateCount($userId, $startDate)
    {
        return AttendanceLog::where('user_id', $userId)
            ->where('action', 'clock_in')
            ->where('timestamp', '>=', $startDate)
            ->whereTime('timestamp', '>', '09:00:00')
            ->count();
    }

    private function getEmployeeDailyTrend($userId, $days = 7)
    {
        $trend = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $logs = AttendanceLog::where('user_id', $userId)
                ->whereDate('timestamp', $date)
                ->get();

            $clockIn = $logs->where('action', 'clock_in')->first();
            $clockOut = $logs->where('action', 'clock_out')->last();

            $hours = 0;
            if ($clockIn && $clockOut && $clockOut->timestamp > $clockIn->timestamp) {
                $hours = round($clockIn->timestamp->diffInMinutes($clockOut->timestamp) / 60, 1);
            }

            $trend[$date->format('D')] = $hours;
        }

        return $trend;
    }
}
