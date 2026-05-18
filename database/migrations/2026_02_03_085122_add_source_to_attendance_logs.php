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
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->enum('source', ['facial_recognition', 'fingerprint_zkteco', 'fingerprint_webauthn', 'manual'])->default('facial_recognition')->after('location_name');
            $table->string('device_log_id', 100)->nullable()->after('source');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->dropColumn(['source', 'device_log_id']);
        });
    }
};
