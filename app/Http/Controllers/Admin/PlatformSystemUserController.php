<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class PlatformSystemUserController extends Controller
{
    /**
     * Display a listing of the platform users.
     */
    public function index()
    {
        $platformRoles = [
            UserRole::PLATFORM_ADMIN,
            UserRole::PLATFORM_FINANCE,
            UserRole::PLATFORM_DEVELOPER,
            UserRole::PLATFORM_SUPPORT,
        ];

        // Fetch users who are one of the platform roles
        $users = User::withoutGlobalScope('tenant')
            ->whereNull('tenant_id')
            ->whereIn('role', $platformRoles)
            ->get();

        return view('admin.superadmin.users.index', compact('users'));
    }

    /**
     * Store a newly created platform user in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->whereNull('deleted_at')],
            'password' => 'required|string|min:8',
            'role' => ['required', Rule::enum(UserRole::class)],
        ]);

        $role = UserRole::from($request->role);
        
        $validRoles = [
            UserRole::PLATFORM_ADMIN,
            UserRole::PLATFORM_FINANCE,
            UserRole::PLATFORM_DEVELOPER,
            UserRole::PLATFORM_SUPPORT
        ];

        if (!in_array($role, $validRoles)) {
            return back()->with('error', 'Invalid platform role selected.');
        }

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $role,
            'tenant_id' => null, // Platform users have no tenant
            'employee_number' => 'SYS-' . strtoupper(Str::random(6)), // Assign a unique internal identifier
        ]);

        return redirect()->route('superadmin.users.index')->with('success', 'Platform user created successfully.');
    }

    /**
     * Update the specified platform user in storage.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)->whereNull('deleted_at')],
            'password' => 'nullable|string|min:8',
            'role' => ['required', Rule::enum(UserRole::class)],
        ]);

        // Validate role is a platform role
        $role = UserRole::from($request->role);
        $validRoles = [
            UserRole::PLATFORM_ADMIN,
            UserRole::PLATFORM_FINANCE,
            UserRole::PLATFORM_DEVELOPER,
            UserRole::PLATFORM_SUPPORT
        ];

        if (!in_array($role, $validRoles)) {
            return back()->with('error', 'Invalid platform role selected.');
        }

        // Prevent downgrading the only platform admin (basic protection)
        if ($user->role === UserRole::PLATFORM_ADMIN && $role !== UserRole::PLATFORM_ADMIN) {
            $adminCount = User::withoutGlobalScope('tenant')->where('role', UserRole::PLATFORM_ADMIN)->count();
            if ($adminCount <= 1) {
                return back()->with('error', 'Cannot downgrade the last Platform Admin.');
            }
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $role;
        
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('superadmin.users.index')->with('success', 'Platform user updated successfully.');
    }

    /**
     * Remove the specified platform user from storage.
     */
    public function destroy(User $user)
    {
        if (auth()->id() === $user->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        if ($user->role === UserRole::PLATFORM_ADMIN) {
            $adminCount = User::withoutGlobalScope('tenant')->where('role', UserRole::PLATFORM_ADMIN)->count();
            if ($adminCount <= 1) {
                return back()->with('error', 'Cannot delete the last Platform Admin.');
            }
        }

        $user->delete();

        return redirect()->route('superadmin.users.index')->with('success', 'Platform user deleted successfully.');
    }
}
