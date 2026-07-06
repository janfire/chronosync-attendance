<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceScore;
use App\Models\User;

class StaffDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        // Get current user's scores for this month
        $scores = AttendanceScore::where('user_id', $user->id)
            ->whereBetween('attendance_date', [$startOfMonth, $endOfMonth])
            ->get();

        $daysPresent = $scores->count();
        $lateCount = $scores->whereIn('clock_in_status', ['late', 'grace'])->count();
        $onTimeCount = $scores->whereIn('clock_in_status', ['early', 'punctual', 'on_time'])->count();
        
        $userTotalPoints = $scores->sum('total_points');

        // Calculate Rank based on total points this month
        $allUserPoints = AttendanceScore::whereBetween('attendance_date', [$startOfMonth, $endOfMonth])
            ->selectRaw('user_id, SUM(total_points) as sum_points')
            ->groupBy('user_id')
            ->orderByDesc('sum_points')
            ->get();

        $totalEmployees = User::count();
        $rankedEmployeesCount = $allUserPoints->count();
        
        $totalEmployees = max($totalEmployees, $rankedEmployeesCount);
        if ($totalEmployees == 0) $totalEmployees = 1;

        $rank = $totalEmployees; // Default to last if no score
        foreach ($allUserPoints as $index => $scoreGroup) {
            if ($scoreGroup->user_id == $user->id) {
                $rank = $index + 1;
                break;
            }
        }

        return view('staff.dashboard', compact(
            'user',
            'daysPresent',
            'lateCount',
            'onTimeCount',
            'userTotalPoints',
            'rank',
            'totalEmployees'
        ));
    }
}
