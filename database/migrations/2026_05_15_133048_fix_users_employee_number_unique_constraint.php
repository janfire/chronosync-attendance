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
        Schema::table('users', function (Blueprint $table) {
            // Drop the old global unique constraint on 'employee_number'
            $table->dropUnique('users_employee_number_unique');
            
            // Add a new composite unique constraint on ['employee_number', 'tenant_id']
            // This allows different companies to have an "ADMIN-001"
            $table->unique(['employee_number', 'tenant_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['employee_number', 'tenant_id']);
            $table->unique('employee_number');
        });
    }
};
