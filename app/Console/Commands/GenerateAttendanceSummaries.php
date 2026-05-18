<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\AttendanceSummaryService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateAttendanceSummaries extends Command
{
    protected $signature = 'attendance:summarize
                            {--period=weekly : weekly or monthly}
                            {--date= : Reference date Y-m-d, defaults to today}';

    protected $description = 'Generate attendance score summaries for all users.';

    public function __construct(private AttendanceSummaryService $summaryService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $period = $this->option('period');
        $date   = $this->option('date') ? Carbon::parse($this->option('date')) : Carbon::now();

        if (!in_array($period, ['weekly', 'monthly'])) {
            $this->error('Use --period=weekly or --period=monthly');
            return self::FAILURE;
        }

        $users = User::all();
        $bar   = $this->output->createProgressBar($users->count());
        $bar->start();

        foreach ($users as $user) {
            try {
                if ($period === 'weekly') {
                    $this->summaryService->generateWeeklySummary($user, $date->copy()->startOfWeek(Carbon::MONDAY));
                } else {
                    $this->summaryService->generateMonthlySummary($user, $date->month, $date->year);
                }
            } catch (\Throwable $e) {
                $this->newLine();
                $this->error("Failed user#{$user->id}: {$e->getMessage()}");
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("✅ {$period} summaries done for {$users->count()} users.");

        return self::SUCCESS;
    }
}
