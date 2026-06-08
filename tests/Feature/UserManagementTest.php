<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_user_management_page()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.users.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.users.index');
    }

    public function test_admin_can_create_user()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $uniqueId = uniqid();

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'New User',
            'email' => "newuser_{$uniqueId}@zou.ac.zw",
            'employee_number' => "EMP{$uniqueId}",
            'role' => 'staff',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'email' => "newuser_{$uniqueId}@zou.ac.zw",
            'role' => 'staff',
        ]);
    }

    public function test_admin_can_update_user()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create([
            'role' => 'staff',
            'email' => 'staff_update_' . uniqid() . '@zou.ac.zw'
        ]);

        $response = $this->actingAs($admin)->put(route('admin.users.update', $user), [
            'name' => 'Updated Name',
            'email' => $user->email,
            'employee_number' => $user->employee_number,
            'role' => 'staff',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_admin_can_delete_user()
    {
        // Must be super_admin to manage admins, but admin can manage staff
        // Let's use super_admin to be safe for general deletion logic if needed,
        // or just admin deleting staff as per UserManagementController logic
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'staff']);
        
        // Mock Mail facade to prevent actual email sending or error if mail config is missing
        // However, UserManagementController catches mail exception, so it should be fine.

        $response = $this->actingAs($admin)->delete(route('admin.users.destroy', $user));

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_admin_can_bulk_delete_users()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $userOne = User::factory()->create(['role' => 'staff']);
        $userTwo = User::factory()->create(['role' => 'staff']);

        $response = $this->actingAs($admin)->post(route('admin.users.bulk-destroy'), [
            'user_ids' => [$userOne->id, $userTwo->id],
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseMissing('users', ['id' => $userOne->id]);
        $this->assertDatabaseMissing('users', ['id' => $userTwo->id]);
    }

    public function test_admin_cannot_bulk_delete_themselves()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $staff = User::factory()->create(['role' => 'staff']);

        $response = $this->actingAs($admin)->post(route('admin.users.bulk-destroy'), [
            'user_ids' => [$admin->id, $staff->id],
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
        $this->assertDatabaseMissing('users', ['id' => $staff->id]);
    }
}
