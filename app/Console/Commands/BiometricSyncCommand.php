<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\FacialRecognitionService;

class BiometricSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'biometric:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Syncs all database biometric facial templates to the Python in-memory server';

    /**
     * Execute the console command.
     */
    public function handle(FacialRecognitionService $service)
    {
        $this->info('Starting biometric template sync...');
        
        $success = $service->syncWithPythonServer();
        
        if ($success) {
            $this->info('Successfully synced biometric templates with Python server.');
            return Command::SUCCESS;
        } else {
            $this->error('Failed to sync templates. Is the Python server running?');
            return Command::FAILURE;
        }
    }
}
