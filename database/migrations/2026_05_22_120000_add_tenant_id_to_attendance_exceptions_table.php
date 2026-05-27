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
        Schema::table('attendance_exceptions', function (Blueprint $table) {
            if (!Schema::hasColumn('attendance_exceptions', 'tenant_id')) {
                $table->foreignId('tenant_id')->nullable()->after('approved_by')->constrained('tenants')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_exceptions', function (Blueprint $table) {
            if (Schema::hasColumn('attendance_exceptions', 'tenant_id')) {
                $table->dropConstrainedForeignId('tenant_id');
            }
        });
    }
};
