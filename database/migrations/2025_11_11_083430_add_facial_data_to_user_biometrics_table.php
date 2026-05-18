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
            $table->json('facial_encoding')->nullable()->after('facial_data');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('biometric_data', function (Blueprint $table) {
            $table->dropColumn('facial_encoding');
        });
    }
};
