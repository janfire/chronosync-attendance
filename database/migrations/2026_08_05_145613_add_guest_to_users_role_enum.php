<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('platform_admin','super_admin','admin','general_user','staff','guest') DEFAULT 'staff'");
        } elseif ($driver === 'pgsql') {
            // Drop existing role check constraint
            $constraints = DB::select("
                SELECT conname
                FROM pg_constraint
                WHERE conrelid = 'users'::regclass
                AND contype = 'c'
                AND pg_get_constraintdef(oid) LIKE '%role%'
            ");
            foreach ($constraints as $constraint) {
                DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS {$constraint->conname}");
            }
            // Add the constraint back WITH 'guest'
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('platform_admin','super_admin','admin','general_user','staff','guest'))");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        // Optionally remove guest users if we are reverting
        DB::table('users')->where('role', 'guest')->delete();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('platform_admin','super_admin','admin','general_user','staff') DEFAULT 'staff'");
        } elseif ($driver === 'pgsql') {
            $constraints = DB::select("
                SELECT conname
                FROM pg_constraint
                WHERE conrelid = 'users'::regclass
                AND contype = 'c'
                AND pg_get_constraintdef(oid) LIKE '%role%'
            ");
            foreach ($constraints as $constraint) {
                DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS {$constraint->conname}");
            }
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('platform_admin','super_admin','admin','general_user','staff'))");
        }
    }
};
