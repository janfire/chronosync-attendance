@extends('admin.layout')

@section('title', 'Edit User')
@section('page-title', 'Edit User')

@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Full Name <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        value="{{ old('name', $user->name) }}"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent @error('name') border-red-500 @enderror"
                        placeholder="Enter full name"
                    >
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        Email Address <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="{{ old('email', $user->email) }}"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent @error('email') border-red-500 @enderror"
                        placeholder="user@example.com"
                    >
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Must be a @example.com email address</p>
                </div>

                <div>
                    <label for="employee_number" class="block text-sm font-medium text-gray-700 mb-2">
                        Employee Number <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="employee_number" 
                        name="employee_number" 
                        value="{{ old('employee_number', $user->employee_number) }}"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent @error('employee_number') border-red-500 @enderror"
                        placeholder="ChronoSync001"
                    >
                    @error('employee_number')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-2">
                        Role <span class="text-red-500">*</span>
                    </label>
                    <select 
                        id="role" 
                        name="role" 
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent @error('role') border-red-500 @enderror"
                    >
                        <option value="">Select Role</option>
                        @foreach($roles as $key => $label)
                            <option value="{{ $key }}" {{ old('role', $user->role) == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('role')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <div class="mt-2 text-xs text-gray-500 space-y-1">
                        <p><strong>Super Admin:</strong> Full system access and user management</p>
                        <p><strong>Admin:</strong> Dashboard access, reports, employee management</p>
                        <p><strong>General User:</strong> Limited access to reports and attendance</p>
                        <p><strong>Staff:</strong> Basic attendance clock-in/out only</p>
                    </div>
                </div>

                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-yellow-800 mb-2">
                        <i class="fas fa-key mr-2"></i>Change Password (Optional)
                    </h4>
                    <p class="text-xs text-yellow-700 mb-4">Leave blank if you don't want to change the password</p>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                                New Password
                            </label>
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                minlength="8"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent @error('password') border-red-500 @enderror"
                                placeholder="Leave blank to keep current password"
                            >
                            @error('password')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                                Confirm New Password
                            </label>
                            <input 
                                type="password" 
                                id="password_confirmation" 
                                name="password_confirmation" 
                                minlength="8"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                                placeholder="Re-enter new password"
                            >
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-4 pt-4 border-t border-gray-200">
                    <a href="{{ route('admin.users.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors">
                        <i class="fas fa-save mr-2"></i>Update User
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection



