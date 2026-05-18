<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreatePlatformAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * php artisan admin:create-platform-admin
     */
    protected $signature = 'admin:create-platform-admin
                            {--name= : The name of the platform admin}
                            {--email= : The email address of the platform admin}
                            {--password= : The password for the platform admin}';

    /**
     * The console command description.
     */
    protected $description = 'Create a platform-level administrator account for ChronoSync SaaS management';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('--- ChronoSync Platform Admin Setup ---');
        $this->newLine();

        // Gather details interactively if not passed as options
        $name     = $this->option('name')     ?? $this->ask('Full Name');
        $email    = $this->option('email')    ?? $this->ask('Email Address');
        $password = $this->option('password') ?? $this->secret('Password (min 8 characters)');

        // Basic validation
        if (strlen($password) < 8) {
            $this->error('Password must be at least 8 characters long.');
            return self::FAILURE;
        }

        if (User::withoutTenantScope()->where('email', $email)->exists()) {
            $this->error("A user with the email [{$email}] already exists.");
            return self::FAILURE;
        }

        // Create the platform admin — no tenant_id needed
        $admin = User::withoutTenantScope()->create([
            'name'            => $name,
            'email'           => $email,
            'password'        => Hash::make($password),
            'role'            => UserRole::PLATFORM_ADMIN,
            'employee_number' => 'PLATFORM-ADMIN-001',
            'tenant_id'       => null,
        ]);

        $this->newLine();
        $this->info('✅ Platform Admin created successfully!');
        $this->table(
            ['Field', 'Value'],
            [
                ['Name',            $admin->name],
                ['Email',           $admin->email],
                ['Role',            $admin->role->label()],
                ['Employee Number', $admin->employee_number],
            ]
        );
        $this->newLine();
        $this->comment('This account can now access /superadmin/finance/pending to manage all tenant invoices.');

        return self::SUCCESS;
    }
}
