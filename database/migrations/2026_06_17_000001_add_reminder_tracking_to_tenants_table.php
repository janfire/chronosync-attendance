<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            // Track when we last sent a "trial ending soon" warning email
            $table->timestamp('trial_reminder_sent_at')->nullable()->after('trial_ends_at');

            // Track when we last sent a "subscription ending soon" warning email
            $table->timestamp('subscription_reminder_sent_at')->nullable()->after('subscription_expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['trial_reminder_sent_at', 'subscription_reminder_sent_at']);
        });
    }
};
