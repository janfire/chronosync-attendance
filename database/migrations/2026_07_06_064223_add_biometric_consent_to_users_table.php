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
            $table->boolean('biometric_consent_granted')->default(false);
            $table->timestamp('biometric_consent_timestamp')->nullable();
            $table->string('biometric_consent_ip')->nullable();
            $table->string('policy_version_agreed')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'biometric_consent_granted',
                'biometric_consent_timestamp',
                'biometric_consent_ip',
                'policy_version_agreed',
            ]);
        });
    }
};
