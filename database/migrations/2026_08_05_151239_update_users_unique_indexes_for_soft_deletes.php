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

        if ($driver === 'pgsql') {
            // Drop existing unique constraints
            DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_email_unique');
            DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_employee_number_tenant_id_unique');
            
            // Drop existing unique indexes if they were created as indexes instead of constraints
            DB::statement('DROP INDEX IF EXISTS users_email_unique');
            DB::statement('DROP INDEX IF EXISTS users_employee_number_tenant_id_unique');

            // Create partial unique indexes that ignore soft-deleted rows
            DB::statement('CREATE UNIQUE INDEX users_email_unique ON users (email) WHERE deleted_at IS NULL');
            DB::statement('CREATE UNIQUE INDEX users_employee_number_tenant_id_unique ON users (employee_number, tenant_id) WHERE deleted_at IS NULL');
        } elseif ($driver === 'mysql') {
            // MySQL does not support partial unique indexes easily without generated columns.
            // But since PostgreSQL is used in production here, we focus on pgsql.
            // If they happen to use sqlite for testing, sqlite supports it since version 3.9.0
        } elseif ($driver === 'sqlite') {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique('users_email_unique');
                $table->dropUnique('users_employee_number_tenant_id_unique');
            });
            DB::statement('CREATE UNIQUE INDEX users_email_unique ON users (email) WHERE deleted_at IS NULL');
            DB::statement('CREATE UNIQUE INDEX users_employee_number_tenant_id_unique ON users (employee_number, tenant_id) WHERE deleted_at IS NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS users_email_unique');
            DB::statement('DROP INDEX IF EXISTS users_employee_number_tenant_id_unique');

            DB::statement('ALTER TABLE users ADD CONSTRAINT users_email_unique UNIQUE (email)');
            DB::statement('ALTER TABLE users ADD CONSTRAINT users_employee_number_tenant_id_unique UNIQUE (employee_number, tenant_id)');
        } elseif ($driver === 'sqlite') {
            DB::statement('DROP INDEX IF EXISTS users_email_unique');
            DB::statement('DROP INDEX IF EXISTS users_employee_number_tenant_id_unique');

            Schema::table('users', function (Blueprint $table) {
                $table->unique('email', 'users_email_unique');
                $table->unique(['employee_number', 'tenant_id'], 'users_employee_number_tenant_id_unique');
            });
        }
    }
};
