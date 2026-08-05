<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{


    public function index(Request $request)
    {
        // Check if user can manage users
        if (!Auth::user()->canManageUsers()) {
            abort(403, 'You do not have permission to access this page.');
        }

        $baseQuery = User::where('role', '!=', UserRole::PLATFORM_ADMIN);

        // Search functionality
        if ($request->has('search') && $request->get('search')) {
            $search = $request->get('search');
            $baseQuery->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('employee_number', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->has('role') && $request->get('role')) {
            $baseQuery->where('role', $request->get('role'));
        }

        // Calculate statistics efficiently using database queries
        $stats = [
            'total_users' => User::where('role', '!=', UserRole::PLATFORM_ADMIN)->count(),
            'super_admins' => User::where('role', 'super_admin')->count(),
            'admins' => User::where('role', 'admin')->count(),
            'general_users' => User::where('role', 'general_user')->count(),
            'staff' => User::where('role', 'staff')->count(),
        ];

        // Get paginated users with optimized relationships
        $users = (clone $baseQuery)->with([
                'biometricData',
                'attendanceLogs' => fn($q) => $q->latest()->limit(10) // Only recent 10 logs
            ])
            ->withCount('attendanceLogs') // Add count for total logs
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.users.index', [
            'users' => $users,
            'roles' => UserRole::toSelectArray(),
            'search' => $request->get('search'),
            'roleFilter' => $request->get('role'),
            'stats' => $stats,
        ]);
    }

    public function create()
    {
        // Check if user can manage users
        if (!Auth::user()->canManageUsers()) {
            abort(403, 'You do not have permission to create users.');
        }

        $currentUser = Auth::user();
        
        // Determine available roles based on current user's role
        $availableRoles = $this->getAvailableRoles($currentUser);

        return view('admin.users.create', [
            'roles' => $availableRoles,
        ]);
    }

    public function store(Request $request)
    {
        // Check if user can manage users
        if (!Auth::user()->canManageUsers()) {
            abort(403, 'You do not have permission to create users.');
        }

        $currentUser = Auth::user();
        
        // Validate role permissions
        $availableRoles = array_keys($this->getAvailableRoles($currentUser));
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'employee_number' => 'required|string|unique:users,employee_number',
            'password' => 'required|min:8|confirmed',
            'role' => ['required', Rule::in($availableRoles)],
        ], [
            'email.unique' => 'This email is already registered',
            'employee_number.unique' => 'This employee number is already in use',
        ]);

        // Create user - password will be automatically hashed by model's 'hashed' cast
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'employee_number' => $validated['employee_number'],
            'password' => $validated['password'], // Model will hash automatically
            'role' => $validated['role'],
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', "User '{$user->name}' created successfully with role: {$user->role->value}");
    }

    public function edit(User $user)
    {
        // Check if user can manage users
        if (!Auth::user()->canManageUsers()) {
            abort(403, 'You do not have permission to edit users.');
        }

        $currentUser = Auth::user();
        
        // Check if current user can edit this user
        if (!$this->canManageUser($currentUser, $user)) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', 'You do not have permission to edit this user.');
        }

        $availableRoles = $this->getAvailableRoles($currentUser);

        return view('admin.users.edit', [
            'user' => $user,
            'roles' => $availableRoles,
        ]);
    }

    public function update(Request $request, User $user)
    {
        // Check if user can manage users
        if (!Auth::user()->canManageUsers()) {
            abort(403, 'You do not have permission to update users.');
        }

        $currentUser = Auth::user();
        
        // Check if current user can edit this user
        if (!$this->canManageUser($currentUser, $user)) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', 'You do not have permission to edit this user.');
        }

        $availableRoles = array_keys($this->getAvailableRoles($currentUser));

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'employee_number' => ['required', 'string', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|min:8|confirmed',
            'role' => ['required', Rule::in($availableRoles)],
        ], [
            'email.unique' => 'This email is already registered',
            'employee_number.unique' => 'This employee number is already in use',
        ]);

        // Prevent downgrading the last super admin
        if ($user->role === UserRole::SUPER_ADMIN && $validated['role'] !== UserRole::SUPER_ADMIN->value) {
            $superAdminCount = User::where('role', UserRole::SUPER_ADMIN)->count();
            if ($superAdminCount <= 1) {
                return redirect()
                    ->back()
                    ->with('error', 'You cannot change the role of the last super admin in this workspace.');
            }
        }

        // Update user
        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'employee_number' => $validated['employee_number'],
            'role' => $validated['role'],
        ]);

        // Update password if provided (model will hash automatically)
        if (!empty($validated['password'])) {
            $user->update([
                'password' => $validated['password'],
            ]);
        }

        return redirect()
            ->route('admin.users.index')
            ->with('success', "User '{$user->name}' updated successfully.");
    }

    public function destroy(User $user)
    {
        if (!Auth::user()->canManageUsers()) {
            abort(403, 'You do not have permission to delete users.');
        }

        $currentUser = Auth::user();
        $result = $this->attemptDeleteUser($currentUser, $user);

        if ($result !== true) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', $result);
        }

        return redirect()
            ->route('admin.users.index')
            ->with('success', "User '{$user->name}' deleted successfully.");
    }

    public function bulkDestroy(Request $request)
    {
        if (!Auth::user()->canManageUsers()) {
            abort(403, 'You do not have permission to delete users.');
        }

        $validated = $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $currentUser = Auth::user();
        $users = User::whereIn('id', $validated['user_ids'])
            ->where('role', '!=', UserRole::PLATFORM_ADMIN)
            ->get();

        $deletedNames = [];
        $skipped = [];

        DB::transaction(function () use ($users, $currentUser, &$deletedNames, &$skipped) {
            foreach ($users as $user) {
                $userName = $user->name;
                $result = $this->attemptDeleteUser($currentUser, $user);

                if ($result === true) {
                    $deletedNames[] = $userName;
                } else {
                    $skipped[] = "{$userName}: {$result}";
                }
            }
        });

        if (count($deletedNames) === 0) {
            $message = $skipped[0] ?? 'No users were deleted.';
            return redirect()
                ->route('admin.users.index')
                ->with('error', $message);
        }

        $successMessage = count($deletedNames) === 1
            ? "User '{$deletedNames[0]}' deleted successfully."
            : count($deletedNames) . ' users deleted successfully.';

        if (count($skipped) > 0) {
            $successMessage .= ' Skipped ' . count($skipped) . ' user(s) due to permissions or restrictions.';
        }

        return redirect()
            ->route('admin.users.index')
            ->with('success', $successMessage);
    }

    /**
     * @return true|string True on success, or an error message string.
     */
    private function attemptDeleteUser(User $currentUser, User $user): bool|string
    {
        if ($user->role === UserRole::PLATFORM_ADMIN) {
            return 'Platform admin accounts cannot be deleted.';
        }

        if ($user->role === UserRole::SUPER_ADMIN) {
            $superAdminCount = User::where('role', UserRole::SUPER_ADMIN)->count();
            if ($superAdminCount <= 1) {
                return 'You cannot delete the last super admin of this workspace.';
            }
        }

        if ($user->id === $currentUser->id) {
            return 'You cannot delete your own account.';
        }

        if (!$this->canManageUser($currentUser, $user)) {
            return 'You do not have permission to delete this user.';
        }

        try {
            $userData = [
                'name' => $user->name,
                'email' => $user->email,
                'employee_number' => $user->employee_number,
                'role' => $user->role->value,
            ];

            \Illuminate\Support\Facades\Mail::to('masiyatino9@gmail.com')
                ->send(new \App\Mail\UserDeletionAlert($userData, $currentUser->name));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send user deletion email: ' . $e->getMessage());
        }

        // Detach/Delete biometric data so the face is freed up for a new account
        $user->biometricData()->delete();

        $user->delete();

        return true;
    }

    /**
     * Get available roles based on current user's role
     */
    private function getAvailableRoles(User $currentUser): array
    {
        $managableRoles = $currentUser->role->managableRoles();
        
        $result = [];
        foreach ($managableRoles as $role) {
            $result[$role->value] = $role->label();
        }
        
        return $result;
    }

    /**
     * Check if current user can manage target user
     */
    private function canManageUser(User $currentUser, User $targetUser): bool
    {
        return $currentUser->role->canManage($targetUser->role);
    }
}

