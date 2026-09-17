<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;

class SyncYearlyHolidays extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'holidays:sync-yearly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically fetch and sync public holidays for the current year';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $year = now()->year;
        $url = "https://date.nager.at/api/v3/PublicHolidays/{$year}/ZW";

        $this->info("Fetching holidays for {$year} from {$url}...");

        try {
            $response = Http::timeout(10)->get($url);

            if ($response->successful()) {
                $data = $response->json();
                
                $holidays = array_map(function($h) {
                    return [
                        'date' => $h['date'],
                        'name' => $h['name'] ?? $h['localName'] ?? 'Holiday'
                    ];
                }, $data);

                // Sort by date
                usort($holidays, function($a, $b) {
                    return $a['date'] <=> $b['date'];
                });

                SystemSetting::set('holidays', $holidays);
                
                $this->info("Successfully synced " . count($holidays) . " holidays for {$year}.");
                return Command::SUCCESS;
            }

            $this->error("API Error: " . $response->status());
            return Command::FAILURE;
            
        } catch (\Exception $e) {
            $this->error("Connection failed: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
