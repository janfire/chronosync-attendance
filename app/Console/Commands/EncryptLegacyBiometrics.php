<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;

class EncryptLegacyBiometrics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'biometrics:encrypt-legacy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Encrypt legacy unencrypted facial encodings in the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Starting legacy data encryption...");

        // Fetch directly from DB to bypass the Model Casts crash
        $records = DB::table('biometric_data')
            ->whereNotNull('facial_encoding')
            ->get();

        $count = 0;
        foreach ($records as $record) {
            $encoding = $record->facial_encoding;

            // Check if it's already encrypted (starts with 'eyJpdi' usually) or if it's raw JSON
            // Raw JSON for our arrays starts with '['
            if (str_starts_with($encoding, '[')) {
                
                // Encrypt it using Laravel's standard encrypter
                $encryptedPayload = Crypt::encryptString($encoding);

                // Save it right back into the database
                DB::table('biometric_data')
                    ->where('id', $record->id)
                    ->update(['facial_encoding' => $encryptedPayload]);

                $count++;
            }
        }

        $this->info("Successfully encrypted {$count} legacy biometric records!");
    }
}
