<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('subdomain')->unique(); // e.g. "taxease" → taxease.attenda.app
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('industry')->nullable();

            // Subscription
            $table->enum('plan', ['starter', 'business', 'corporate'])->default('starter');
            $table->enum('status', ['trial', 'active', 'suspended', 'cancelled'])->default('trial');
            $table->unsignedInteger('max_employees')->default(20);

            // Billing contact
            $table->string('billing_email')->nullable();
            $table->string('billing_name')->nullable();
            $table->string('billing_phone')->nullable();

            // Payment instructions the tenant sends money to
            $table->string('ecocash_number')->nullable(); // Our EcoCash number for receiving payments
            $table->string('zipit_account')->nullable();  // Our ZIPIT/bank account

            // Dates
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('subscription_starts_at')->nullable();
            $table->timestamp('subscription_expires_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
