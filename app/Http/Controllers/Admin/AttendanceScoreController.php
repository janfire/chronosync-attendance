<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceScore;
use App\Models\AttendanceSummary;
use App\Models\User;
use App\Services\AttendanceScoringService;
use App\Services\AttendanceSummaryService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceScoreController extends Controller
{
    public function __construct(
        private AttendanceScoringService $scoringService,
        private AttendanceSummaryService $summaryService
    ) {}

    public function index(Request $request)
    {
        $periodType = $request->get('period', 'weekly');
        $date       = Carbon::parse($request->get('date', now()->toDateString()));

        $periodStart = $periodType === 'weekly'
            ? $date->copy()->startOfWeek(Carbon::MONDAY)
            : $date->copy()->startOfMonth();

        $periodEnd = $periodType === 'weekly'
            ? $periodStart->copy()->endOfWeek(Carbon::SUNDAY)
            : $date->copy()->endOfMonth();

        // Refresh summaries for all users for this period
        foreach (User::all() as $user) {
            try {
                $periodType === 'weekly'
                    ? $this->summaryService->generateWeeklySummary($user, $periodStart)
                    : $this->summaryService->generateMonthlySummary($user, $date->month, $date->year);
            } catch (\Throwable) {}
        }

        $summaries = AttendanceSummary::with('user')
            ->where('period_type', $periodType)
            ->where('period_start', $periodStart->toDateString())
            ->orderByDesc('score_percentage')
            ->get();

        return view('admin.scores.index', compact('summaries', 'periodType', 'periodStart', 'periodEnd', 'date'));
    }

    public function show(Request $request, User $user)
    {
        $month = (int) $request->get('month', now()->month);
        $year  = (int) $request->get('year', now()->year);

        $monthStart = Carbon::createFromDate($year, $month, 1)->startOfDay();
        $monthEnd   = $monthStart->copy()->endOfMonth();

        $dailyScores = AttendanceScore::where('user_id', $user->id)
            ->whereBetween('attendance_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->orderBy('attendance_date')
            ->get();

        $weeklySummaries = AttendanceSummary::where('user_id', $user->id)
            ->where('period_type', 'weekly')
            ->whereBetween('period_start', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->orderBy('period_start')
            ->get();

        $this->summaryService->generateMonthlySummary($user, $month, $year);
        $monthlySummary = AttendanceSummary::where('user_id', $user->id)
            ->where('period_type', 'monthly')
            ->where('period_start', $monthStart->toDateString())
            ->first();

        return view('admin.scores.show', compact(
            'user', 'dailyScores', 'weeklySummaries', 'monthlySummary', 'month', 'year', 'monthStart', 'monthEnd'
        ));
    }

    public function export(Request $request)
    {
        $periodType  = $request->get('period', 'weekly');
        $date        = Carbon::parse($request->get('date', now()->toDateString()));
        $periodStart = $periodType === 'weekly'
            ? $date->copy()->startOfWeek(Carbon::MONDAY)
            : $date->copy()->startOfMonth();

        $summaries = AttendanceSummary::with('user')
            ->where('period_type', $periodType)
            ->where('period_start', $periodStart->toDateString())
            ->orderByDesc('score_percentage')
            ->get();

        $filename = "scores_{$periodType}_{$periodStart->toDateString()}.csv";
        $headers  = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($summaries) {
            $h = fopen('php://output', 'w');
            fputcsv($h, ['Rank','Employee','Emp No','Present','Absent','Score','Max','%','Grade','Early','Punctual','On Time','Grace','Late','Normal Out','Left Early','Early Dep.']);
            foreach ($summaries as $i => $s) {
                fputcsv($h, [
                    $i + 1,
                    $s->user->name ?? 'N/A',
                    $s->user->employee_number ?? 'N/A',
                    $s->days_present, $s->days_absent,
                    $s->total_score, $s->max_possible_score,
                    number_format($s->score_percentage, 1) . '%',
                    $s->grade,
                    $s->early_count, $s->punctual_count, $s->on_time_count,
                    $s->grace_count, $s->late_count,
                    $s->normal_out_count, $s->left_early_count, $s->early_departure_count,
                ]);
            }
            fclose($h);
        }, 200, $headers);
    }
}
