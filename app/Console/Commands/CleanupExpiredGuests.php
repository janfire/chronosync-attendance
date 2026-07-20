<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanupExpiredGuests extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:cleanup-guests';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Soft delete guest accounts whose estimated period of stay has expired and erase their biometric data.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expiredGuests = User::where('role', UserRole::GUEST)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->get();

        if ($expiredGuests->isEmpty()) {
            $this->info('No expired guests found.');
            return;
        }

        $count = 0;
        foreach ($expiredGuests as $guest) {
            // Delete biometric data
            if ($guest->biometricData) {
                $guest->biometricData->delete();
            }

            // Soft delete user
            $guest->delete();
            $count++;

            Log::info("Soft deleted expired guest account and erased biometric data.", [
                'user_id' => $guest->id,
                'email' => $guest->email
            ]);
        }
        
        // Trigger Python server sync to remove faces from memory
        app(\App\Services\FacialRecognitionService::class)->syncWithPythonServer();

        $this->info("Successfully cleaned up {$count} expired guest(s).");
    }
}
