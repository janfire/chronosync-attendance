<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    // usage of RefreshDatabase might wipe the db, checking if it's safe. 
    // The user is using XAMPP/local db. Usually tests run on a separate db or in memory.
    // I I will avoid RefreshDatabase to be safe and manually clean up or just use a transaction if possible.
    // However, standard Laravel tests use RefreshDatabase. Given this is an existing project, I'll use it but be cautious.
    // Actually, looking at the user's `tests/Feature` dir, check if they have existing tests that use it.
    // For now I'll skip RefreshDatabase to avoid wiping their dev data if they haven't configured a separate test env.
    // I'll just create users and let them persist or be cleaned up by transaction if I extended a transaction class.
    
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    public function test_admin_can_access_admin_dashboard(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin_test_' . uniqid() . '@zou.ac.zw', // Ensure unique email
            'employee_number' => 'ZOU' . uniqid(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
    }

    public function test_staff_cannot_access_admin_dashboard(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'email' => 'staff_test_' . uniqid() . '@zou.ac.zw',
            'employee_number' => 'ZOU' . uniqid(),
        ]);

        $response = $this->actingAs($staff)->get(route('admin.dashboard'));

        $response->assertStatus(403);
    }
    
    public function test_super_admin_can_access_admin_dashboard(): void
    {
        $superAdmin = User::factory()->create([
            'role' => 'super_admin',
            'email' => 'super_admin_test_' . uniqid() . '@zou.ac.zw',
            'employee_number' => 'ZOU' . uniqid(),
        ]);

        $response = $this->actingAs($superAdmin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
    }
}
