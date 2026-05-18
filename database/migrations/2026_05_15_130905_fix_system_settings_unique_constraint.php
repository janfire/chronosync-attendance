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
        Schema::table('system_settings', function (Blueprint $table) {
            // Drop the old global unique constraint on 'key'
            $table->dropUnique('system_settings_key_unique');
            
            // Add a new composite unique constraint on ['key', 'tenant_id']
            $table->unique(['key', 'tenant_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            $table->dropUnique(['key', 'tenant_id']);
            $table->unique('key');
        });
    }
};
