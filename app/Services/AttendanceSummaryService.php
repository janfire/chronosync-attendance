<?php

namespace App\Services;

use App\Models\AttendanceScore;
use App\Models\AttendanceSummary;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AttendanceSummaryService
{
    const GRADE_A = 90;
    const GRADE_B = 75;
    const GRADE_C = 60;
    const GRADE_D = 45;

    public function computeGrade(float $pct): string
    {
        return match(true) {
            $pct >= self::GRADE_A => 'A',
            $pct >= self::GRADE_B => 'B',
            $pct >= self::GRADE_C => 'C',
            $pct >= self::GRADE_D => 'D',
            default               => 'F',
        };
    }

    public function generateWeeklySummary(User $user, Carbon $weekStart): AttendanceSummary
    {
        return $this->generate($user, 'weekly', $weekStart, $weekStart->copy()->endOfWeek(Carbon::SUNDAY));
    }

    public function generateMonthlySummary(User $user, int $month, int $year): AttendanceSummary
    {
        $start = Carbon::createFromDate($year, $month, 1)->startOfDay();
        return $this->generate($user, 'monthly', $start, $start->copy()->endOfMonth());
    }

    private function generate(User $user, string $type, Carbon $start, Carbon $end): AttendanceSummary
    {
        $scores = AttendanceScore::where('user_id', $user->id)
            ->whereBetween('attendance_date', [$start->toDateString(), $end->toDateString()])
            ->get();

        // Count working days (Mon–Fri)
        $workingDays = 0;
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            if ($cursor->isWeekday()) $workingDays++;
            $cursor->addDay();
        }

        $daysPresent      = $scores->where('clock_in_status', '!=', 'absent')->count();
        $totalScore       = $scores->sum('total_points');
        $maxPossible      = $daysPresent * AttendanceScore::MAX_DAILY_POINTS;
        $pct              = $maxPossible > 0 ? max(0, min(100, round($totalScore / $maxPossible * 100, 2))) : 0;

        $summary = AttendanceSummary::updateOrCreate(
            ['user_id' => $user->id, 'period_type' => $type, 'period_start' => $start->toDateString()],
            [
                'period_end'            => $end->toDateString(),
                'total_score'           => $totalScore,
                'max_possible_score'    => $maxPossible,
                'score_percentage'      => $pct,
                'grade'                 => $this->computeGrade($pct),
                'days_present'          => $daysPresent,
                'days_absent'           => $workingDays - $daysPresent,
                'early_count'           => $scores->where('clock_in_status', 'early')->count(),
                'punctual_count'        => $scores->where('clock_in_status', 'punctual')->count(),
                'on_time_count'         => $scores->where('clock_in_status', 'on_time')->count(),
                'grace_count'           => $scores->where('clock_in_status', 'grace')->count(),
                'late_count'            => $scores->where('clock_in_status', 'late')->count(),
                'normal_out_count'      => $scores->where('clock_out_status', 'normal')->count(),
                'left_early_count'      => $scores->where('clock_out_status', 'left_early')->count(),
                'early_departure_count' => $scores->where('clock_out_status', 'early_departure')->count(),
            ]
        );

        Log::info("Generated {$type} summary user#{$user->id}: grade={$summary->grade} ({$pct}%)");

        return $summary;
    }
}
