<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class ReportController extends Controller
{
    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Display the Advanced Analytics Dashboard.
     */
    public function index(Request $request)
    {
        $dateFrom = $request->get('from') ? Carbon::parse($request->get('from')) : today();
        $dateTo = $request->get('to') ? Carbon::parse($request->get('to')) : today();
        $month = $request->get('month', now()->format('Y-m'));

        $stats = $this->reportService->getSummaryStats(['from' => $dateFrom, 'to' => $dateTo]);
        
        $topCompliant = $this->reportService->getTop5Compliant($month);
        $topPunctual = $this->reportService->getTop5Punctual($month);
        $topOutside = $this->reportService->getTop5OutsideHours($month);
        $topLateComersWeekly = $this->reportService->getTop5LateComersWeekly();
        $topEarlySignOutsWeekly = $this->reportService->getTop5EarlySignOutsWeekly();

        $users = User::where('role', 'staff')->get();
        $monthlyDetails = [];
        foreach ($users as $user) {
            $monthlyDetails[] = array_merge(
                ['user' => $user],
                $this->reportService->getUserMonthlyDetails($user->id, Carbon::parse($month)->startOfMonth(), Carbon::parse($month)->endOfMonth())
            );
        }

        return view('admin.reports.index', compact(
            'stats',
            'topCompliant',
            'topPunctual',
            'topOutside',
            'topLateComersWeekly',
            'topEarlySignOutsWeekly',
            'monthlyDetails',
            'dateFrom',
            'dateTo',
            'month'
        ));
    }

    public function settings()
    {
        $shiftRules = SystemSetting::get('shift_rules');
        $holidays = SystemSetting::get('holidays', []);
        
        // Normalize legacy format (simple array of dates) to new format (array of objects)
        if (!empty($holidays) && !is_array($holidays[0])) {
             $holidays = array_map(function($date) {
                 return ['date' => $date, 'name' => 'Holiday'];
             }, $holidays);
        }

        // Filter out holidays from previous years
        $currentYear = now()->year;
        $holidays = array_filter($holidays, function($h) use ($currentYear) {
            try {
                return Carbon::parse($h['date'])->year >= $currentYear;
            } catch (\Exception $e) {
                return false;
            }
        });

        // Sort by date
        usort($holidays, function($a, $b) {
            return $a['date'] <=> $b['date'];
        });
        
        $holidays = array_values($holidays);
        
        return view('admin.reports.settings', compact('shiftRules', 'holidays'));
    }

    /**
     * Update report settings.
     */
    public function updateSettings(Request $request)
    {
        $rules = $request->validate([
            'shift_start' => 'required',
            'shift_end' => 'required',
            'late_after' => 'required',
            'early_out_before' => 'required',
        ]);

        $holidaysInput = $request->input('holidays', []);
        $cleanedHolidays = [];

        if (is_array($holidaysInput)) {
            foreach ($holidaysInput as $holiday) {
                if (!empty($holiday['date'])) {
                    $cleanedHolidays[] = [
                        'date' => $holiday['date'],
                        'name' => !empty($holiday['name']) ? $holiday['name'] : 'Holiday',
                    ];
                }
            }
        }
        
        // Sort by date
        usort($cleanedHolidays, function($a, $b) {
            return $a['date'] <=> $b['date'];
        });

        SystemSetting::set('shift_rules', $rules);
        SystemSetting::set('holidays', $cleanedHolidays);

        return back()->with('success', 'Report settings updated successfully.');
    }

    /**
     * Export Monthly Attendance to CSV.
     */
    public function exportMonthly(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));
        $start = Carbon::parse($month)->startOfMonth();
        $end = Carbon::parse($month)->endOfMonth();

        $users = User::where('role', 'staff')->get();
        
        $filename = "attendance_report_{$month}.csv";
        $handle = fopen('php://output', 'w');
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        // CSV Header
        fputcsv($handle, ['User', 'Name', 'Month', 'Hours (within shift)', 'Days Counted', 'Avg Sign In', 'Avg Sign Out']);

        foreach ($users as $user) {
            $details = $this->reportService->getUserMonthlyDetails($user->id, $start, $end);
            fputcsv($handle, [
                $user->employee_number,
                $user->name,
                $start->format('F Y'),
                $details['total_hours'],
                $details['days_present'],
                $details['avg_sign_in'],
                $details['avg_sign_out']
            ]);
        }

        fclose($handle);
        exit();
    }

    /**
     * Fetch holidays from external API (Nager.Date).
     */
    public function fetchHolidays(Request $request)
    {
        $year = $request->get('year', now()->year);
        $url = "https://date.nager.at/api/v3/PublicHolidays/{$year}/ZW";

        try {
            $response = Http::timeout(5)->get($url);

            if ($response->successful()) {
                $data = $response->json();
                // Map to our desired format
                $holidays = array_map(function($h) {
                    return [
                        'date' => $h['date'],
                        'name' => $h['name'] ?? $h['localName'] ?? 'Holiday'
                    ];
                }, $data);
                
                return response()->json(['success' => true, 'holidays' => $holidays]);
            }

            return response()->json(['success' => false, 'message' => 'API Error: ' . $response->status()], 500);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Connection failed: ' . $e->getMessage()], 500);
        }
    }
}
