<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->enum('period_type', ['weekly', 'monthly']);
            $table->date('period_start');
            $table->date('period_end');

            $table->integer('total_score')->default(0);
            $table->integer('max_possible_score')->default(0);
            $table->decimal('score_percentage', 5, 2)->default(0);
            $table->string('grade', 2)->default('F');

            $table->integer('days_present')->default(0);
            $table->integer('days_absent')->default(0);

            $table->integer('early_count')->default(0);
            $table->integer('punctual_count')->default(0);
            $table->integer('on_time_count')->default(0);
            $table->integer('grace_count')->default(0);
            $table->integer('late_count')->default(0);

            $table->integer('normal_out_count')->default(0);
            $table->integer('left_early_count')->default(0);
            $table->integer('early_departure_count')->default(0);

            $table->timestamps();

            $table->unique(['user_id', 'period_type', 'period_start']);
            $table->index(['user_id', 'period_type', 'period_start']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_summaries');
    }
};
