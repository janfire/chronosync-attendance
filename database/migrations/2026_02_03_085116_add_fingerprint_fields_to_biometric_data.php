<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('biometric_data', function (Blueprint $table) {
            // Only add the two missing columns (fingerprint_status and fingerprint_captured_at already exist)
            if (!Schema::hasColumn('biometric_data', 'fingerprint_template')) {
                $table->text('fingerprint_template')->nullable()->after('facial_captured_at');
            }
            if (!Schema::hasColumn('biometric_data', 'fingerprint_device_id')) {
                $table->string('fingerprint_device_id', 50)->nullable()->after('fingerprint_captured_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('biometric_data', function (Blueprint $table) {
            if (Schema::hasColumn('biometric_data', 'fingerprint_template')) {
                $table->dropColumn('fingerprint_template');
            }
            if (Schema::hasColumn('biometric_data', 'fingerprint_device_id')) {
                $table->dropColumn('fingerprint_device_id');
            }
        });
    }
};
