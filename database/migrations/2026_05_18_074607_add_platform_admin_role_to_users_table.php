<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add the 'platform_admin' value to the users role enum.
     * Also make tenant_id nullable so a platform admin can exist
     * without belonging to any specific tenant.
     */
    public function up(): void
    {
        // 1. Make tenant_id nullable on users (platform admin has no tenant)
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->nullable()->change();
        });

        // 2. Add platform_admin to the role column (driver-aware)
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('platform_admin','super_admin','admin','general_user','staff') DEFAULT 'staff'");
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
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('platform_admin','super_admin','admin','general_user','staff'))");
        } elseif ($driver === 'sqlite') {
            // SQLite doesn't enforce enums — the VARCHAR column already accepts any string value.
            // No change needed; the PHP Enum class enforces valid values at the application layer.
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        // Remove any platform_admin users before reverting the enum
        DB::table('users')->where('role', 'platform_admin')->delete();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin','admin','general_user','staff') DEFAULT 'staff'");
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
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('super_admin','admin','general_user','staff'))");
        }
    }
};

