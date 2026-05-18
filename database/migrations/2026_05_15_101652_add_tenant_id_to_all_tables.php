<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'users',
            'attendance_logs',
            'biometric_data',
            'failed_attendance_logs',
            'attendance_scores',
            'attendance_summaries',
            'system_settings',
            'webauthn_credentials',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'tenant_id')) {
                Schema::table($table, function (Blueprint $table) {
                    // Nullable so existing records don't break; will be backfilled by seeder
                    $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
                    $table->index('tenant_id');
                });
            }
        }
    }

    public function down(): void
    {
        $tables = [
            'users',
            'attendance_logs',
            'biometric_data',
            'failed_attendance_logs',
            'attendance_scores',
            'attendance_summaries',
            'system_settings',
            'webauthn_credentials',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'tenant_id')) {
                Schema::table($table, function (Blueprint $t) use ($table) {
                    $t->dropIndex([$table === 'users' ? 'users_tenant_id_index' : "{$table}_tenant_id_index"]);
                    $t->dropColumn('tenant_id');
                });
            }
        }
    }
};
