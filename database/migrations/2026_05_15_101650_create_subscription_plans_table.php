<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');                        // Starter, Business, Corporate
            $table->string('slug')->unique();              // starter, business, corporate
            $table->decimal('price_usd', 8, 2);           // 15.00, 35.00, 75.00
            $table->unsignedInteger('max_employees');      // 20, 75, 0 (0 = unlimited)
            $table->unsignedInteger('trial_days')->default(14);
            $table->json('features');                      // List of feature strings
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
