<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->index(['tenant_id', 'role']);
        });

        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->index(['tenant_id', 'action', 'timestamp']);
        });

        Schema::table('biometric_data', function (Blueprint $table) {
            $table->index(['tenant_id', 'facial_status']);
        });

        Schema::table('failed_attendance_logs', function (Blueprint $table) {
            $table->index(['tenant_id', 'attempted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'role']);
        });

        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'action', 'timestamp']);
        });

        Schema::table('biometric_data', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'facial_status']);
        });

        Schema::table('failed_attendance_logs', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'attempted_at']);
        });
    }
};
