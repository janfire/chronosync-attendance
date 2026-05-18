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
        $driver = DB::getDriverName();
        
        if ($driver === 'pgsql') {
            // For PostgreSQL, Laravel creates enum as a check constraint
            // Find and drop the existing check constraint
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
            
            // Create new check constraint with all role values
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('super_admin', 'admin', 'general_user', 'staff'))");
        } elseif ($driver === 'sqlite') {
            // For SQLite, drop and recreate the column
            DB::statement("ALTER TABLE users ADD COLUMN role_temp VARCHAR(255) DEFAULT 'staff'");
            DB::statement("UPDATE users SET role_temp = role");
            DB::statement("ALTER TABLE users DROP COLUMN role");
            DB::statement("ALTER TABLE users RENAME COLUMN role_temp TO role");
        } else {
            // For MySQL, modify the enum directly
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin', 'admin', 'general_user', 'staff') DEFAULT 'staff'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum values
        DB::statement("CREATE TYPE user_role_old AS ENUM ('staff', 'admin')");
        
        // Convert current roles back to old values (map super_admin and general_user to admin)
        DB::statement("UPDATE users SET role = 'admin' WHERE role IN ('super_admin', 'general_user')");
        
        DB::statement("ALTER TABLE users ALTER COLUMN role TYPE user_role_old USING role::text::user_role_old");
        
        DB::statement("DROP TYPE IF EXISTS user_role");
        DB::statement("ALTER TYPE user_role_old RENAME TO user_role");
    }
};
