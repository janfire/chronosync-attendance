<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\User;
use App\Models\BiometricData;
use App\Services\AdminDashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    // Middleware is applied in routes/web.php
    // No need for constructor middleware in Laravel 11

    protected \App\Services\AnalyticsService $analyticsService;
    protected \App\Services\AdminDashboardService $adminDashboardService;

    public function __construct(\App\Services\AnalyticsService $analyticsService, \App\Services\AdminDashboardService $adminDashboardService)
    {
        $this->analyticsService = $analyticsService;
        $this->adminDashboardService = $adminDashboardService;
    }

    public function userGuide()
    {
        return view('admin.guide');
    }

    public function dashboard()
    {
        $tenantId = Auth::user()->tenant_id;

        // Cache dashboard stats per tenant for 60 seconds.
        // These are read-only counts that change at most once per scan — no need
        // to recalculate on every page load. Flush automatically every minute.
        $stats = Cache::remember("dashboard_stats_{$tenantId}", 60, function () {
            return [
                'total_employees'    => User::whereIn('role', ['staff', 'guest'])->count(),
                'today_attendance'   => AttendanceLog::whereDate('timestamp', today())
                    ->where('action', 'clock_in')
                    ->distinct('user_id')
                    ->count('user_id'),
                'absents'            => $this->getAbsentCount(),
                'currently_clocked_in' => $this->getCurrentlyClockedInCount(),
                'pending_issues'     => $this->getPendingIssuesCount(),
                'pending_exceptions' => \App\Models\AttendanceException::where('status', 'pending')->count(),
            ];
        });

        // Recent activity does NOT get cached — it should always be live
        $recentActivity = AttendanceLog::with('user')
            ->whereDate('timestamp', today())
            ->orderBy('timestamp', 'desc')
            ->limit(20)
            ->get();

        // Recent failed attempts (security alerts)
        $failedAttempts = \App\Models\FailedAttendanceLog::with('user')
            ->whereDate('attempted_at', today())
            ->orderBy('attempted_at', 'desc')
            ->limit(5)
            ->get();

        $adminMetrics = $this->adminDashboardService->getMetrics();
        return view('admin.dashboard', compact('stats', 'recentActivity', 'failedAttempts', 'adminMetrics'));

    }

    public function hrInsights(Request $request)
    {
        $userId = $request->get('user_id');
        $date = $request->get('date', today()->toDateString());
        $action = $request->get('action');

        $selectedEmployee = null;
        $employeeStats = null;

        if ($userId) {
            $selectedEmployee = User::find($userId);
            if ($selectedEmployee) {
                $employeeStats = $this->analyticsService->getEmployeeAnalytics($userId);
            }
        }

        $employees = User::whereIn('role', ['staff', 'guest'])->orderBy('name')->get();

        $teamStats = [
            'avg_daily_hours' => $this->analyticsService->calculateAverageDailyHours(),
            'lateness_rate' => $this->analyticsService->getLatenessRate(),
            'weekly_hours_trend' => $this->analyticsService->getWeeklyHoursTrend(),
            'peak_traffic' => $this->analyticsService->getPeakTrafficTimes(),
            'total_week_hours' => $this->analyticsService->calculateTotalHours('week'),
        ];

        // Prepare logs with filters
        $query = AttendanceLog::with('user');

        if ($date) {
            $query->whereDate('timestamp', $date);
        }

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if ($action) {
            $query->where('action', $action);
        }

        $logs = $query->orderBy('timestamp', 'desc')->limit(500)->get(); // Cap at 500 — DataTables handles client-side filtering

        return view('admin.insights', compact(
            'teamStats', 
            'employees', 
            'selectedEmployee', 
            'employeeStats', 
            'logs', 
            'date'
        ));
    }

    public function employees(Request $request)
    {
        $query = User::whereIn('role', ['staff', 'guest'])
            ->with('biometricData', 'attendanceLogs');

        // Search functionality
        if ($request->has('search') && $request->get('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('employee_number', 'like', "%{$search}%");
            });
        }

        // Filter by enrollment status
        if ($request->has('enrollment_status')) {
            $status = $request->get('enrollment_status');
            if ($status === 'enrolled') {
                $query->whereHas('biometricData', function($q) {
                    $q->where('facial_status', 'captured');
                });
            } elseif ($status === 'not_enrolled') {
                $query->whereDoesntHave('biometricData', function($q) {
                    $q->where('facial_status', 'captured');
                });
            }
        }

        $employees = $query->orderBy('created_at', 'desc')->paginate(20);

        // Calculate enrollment stats using direct count queries instead of loading all records.
        // Previously this loaded every staff member into memory just to count two numbers.
        $totalStaff = User::whereIn('role', ['staff', 'guest'])->count();
        $enrolledCount = BiometricData::whereNotNull('user_id')
            ->where('facial_status', 'captured')
            ->distinct('user_id')
            ->count('user_id');
        $notEnrolledCount = $totalStaff - $enrolledCount;

        return view('admin.employees', compact('employees', 'enrolledCount', 'notEnrolledCount'));
    }

    public function showEmployee(User $user)
    {
        if (!$user->isStaff()) {
            return redirect()->route('admin.users.index')->with('error', 'User not found.');
        }

        $user->load('biometricData', 'attendanceLogs');
        
        $recentAttendance = $user->attendanceLogs()
            ->orderBy('timestamp', 'desc')
            ->limit(10)
            ->get();

        $stats = [
            'total_clock_ins' => $user->attendanceLogs()->where('action', 'clock_in')->count(),
            'this_month' => $user->attendanceLogs()
                ->where('action', 'clock_in')
                ->whereMonth('timestamp', now()->month)
                ->whereYear('timestamp', now()->year)
                ->count(),
        ];

        return view('admin.employees.show', compact('user', 'recentAttendance', 'stats'));
    }


    private function getCurrentlyClockedInCount()
    {
        // Get users who clocked in today but haven't clocked out yet
        $clockedIn = DB::table('attendance_logs as al1')
            ->select('al1.user_id')
            ->whereDate('al1.timestamp', today())
            ->where('al1.action', 'clock_in')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('attendance_logs as al2')
                    ->whereColumn('al2.user_id', 'al1.user_id')
                    ->whereDate('al2.timestamp', today())
                    ->where('al2.action', 'clock_out')
                    ->whereColumn('al2.timestamp', '>', 'al1.timestamp');
            })
            ->distinct()
            ->count();

        return $clockedIn;
    }

    private function getPendingIssuesCount()
    {
        // Count missing clock-outs for today
        return DB::table('attendance_logs as al1')
            ->whereDate('al1.timestamp', today())
            ->where('al1.action', 'clock_in')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('attendance_logs as al2')
                    ->whereColumn('al2.user_id', 'al1.user_id')
                    ->whereDate('al2.timestamp', today())
                    ->where('al2.action', 'clock_out')
                    ->whereColumn('al2.timestamp', '>', 'al1.timestamp');
            })
            ->count();
    }

    private function getAbsentCount()
    {
        return User::whereIn('role', ['staff', 'guest'])
            ->whereDoesntHave('attendanceLogs', function ($query) {
                $query->whereDate('timestamp', today())
                      ->where('action', 'clock_in');
            })
            ->count();
    }

    private function getTodayAttendance()
    {
        // Single query: fetch all of today's logs for staff users, grouped by user.
        // This replaces the previous N+1 pattern (1 query per employee × 2 per action = 2N+1 queries).
        $employees = User::whereIn('role', ['staff', 'guest'])->orderBy('name')->get();

        // Load today's first clock-in and last clock-out per user in two bulk queries
        $clockIns = AttendanceLog::whereDate('timestamp', today())
            ->where('action', 'clock_in')
            ->orderBy('timestamp')
            ->get()
            ->keyBy('user_id'); // Keyed by user_id — O(1) lookup per employee

        $clockOuts = AttendanceLog::whereDate('timestamp', today())
            ->where('action', 'clock_out')
            ->orderByDesc('timestamp')
            ->get()
            ->keyBy('user_id');

        $attendance = [];
        foreach ($employees as $employee) {
            $clockIn  = $clockIns->get($employee->id);
            $clockOut = $clockOuts->get($employee->id);

            // Only include clock-out records that came after the clock-in
            if ($clockIn && $clockOut && $clockOut->timestamp <= $clockIn->timestamp) {
                $clockOut = null;
            }

            $attendance[] = [
                'employee'     => $employee,
                'clock_in'     => $clockIn,
                'clock_out'    => $clockOut,
                'status'       => $this->getAttendanceStatus($clockIn, $clockOut),
                'hours_worked' => $this->calculateHours($clockIn, $clockOut),
            ];
        }

        return collect($attendance);
    }

    private function getAttendanceStatus($clockIn, $clockOut)
    {
        if (!$clockIn) {
            return 'absent';
        }

        if (!$clockOut) {
            return 'currently_in';  // Changed from 'incomplete' to 'currently_in'
        }

        return 'clocked_out';  // Changed from 'present'/'late' to 'clocked_out'
    }

    private function calculateHours($clockIn, $clockOut)
    {
        if (!$clockIn || !$clockOut) {
            return null;
        }

        $hours = $clockIn->timestamp->diffInHours($clockOut->timestamp);
        $minutes = $clockIn->timestamp->diffInMinutes($clockOut->timestamp) % 60;

        return sprintf('%d:%02d', $hours, $minutes);
    }
}
