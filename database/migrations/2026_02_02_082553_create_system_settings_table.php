<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->json('value');
            $table->timestamps();
        });

        // Insert default settings
        DB::table('system_settings')->insert([
            [
                'key' => 'shift_rules',
                'value' => json_encode([
                    'shift_start' => '08:00',
                    'shift_end' => '16:30',
                    'late_after' => '09:00',
                    'early_out_before' => '15:50',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'holidays',
                'value' => json_encode([
                    '2025-01-01', '2025-02-21', '2025-03-29', '2025-04-18',
                    '2025-04-19', '2025-04-21', '2025-05-01', '2025-05-25',
                    '2025-05-26', '2025-08-10', '2025-12-22', '2025-12-25',
                    '2025-12-26'
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
