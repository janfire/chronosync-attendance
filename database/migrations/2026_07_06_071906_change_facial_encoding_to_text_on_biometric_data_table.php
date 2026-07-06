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
        // We use a raw statement because changing a JSON column to TEXT in Postgres 
        // can sometimes trip up Doctrine DBAL or Laravel's default schema builder.
        DB::statement('ALTER TABLE biometric_data ALTER COLUMN facial_encoding TYPE text USING facial_encoding::text');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE biometric_data ALTER COLUMN facial_encoding TYPE json USING facial_encoding::json');
    }
};
