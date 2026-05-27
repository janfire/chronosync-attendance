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
        Schema::create('attendance_exceptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('type', ['missed_punch', 'incorrect_time', 'manual_correction', 'other'])->default('missed_punch');
            $table->text('note')->nullable();
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('handled_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_exceptions');
    }
};
