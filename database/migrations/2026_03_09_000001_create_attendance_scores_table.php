<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('attendance_date');

            $table->foreignId('clock_in_log_id')->nullable()->constrained('attendance_logs')->onDelete('set null');
            $table->foreignId('clock_out_log_id')->nullable()->constrained('attendance_logs')->onDelete('set null');

            $table->time('clock_in_time')->nullable();
            $table->time('clock_out_time')->nullable();

            $table->enum('clock_in_status', ['early', 'punctual', 'on_time', 'grace', 'late', 'absent'])->default('absent');
            $table->enum('clock_out_status', ['normal', 'left_early', 'early_departure', 'late_departure', 'absent'])->default('absent');

            $table->integer('clock_in_points')->default(0);
            $table->integer('clock_out_points')->default(0);
            $table->integer('total_points')->default(0);

            $table->timestamps();

            $table->unique(['user_id', 'attendance_date']);
            $table->index(['user_id', 'attendance_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_scores');
    }
};
