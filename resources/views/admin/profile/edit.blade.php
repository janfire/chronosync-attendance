@extends('admin.layout')

@section('title', 'Edit Profile')
@section('page-title', 'Edit My Profile')

@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <form method="POST" action="{{ route('profile.update') }}" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Personal Information Section -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-user-circle text-emerald-600 mr-2"></i>
                        Personal Information
                    </h3>
                    
                    <div class="space-y-4">
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
                                placeholder="Enter your full name"
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
                                placeholder="your.email@example.com"
                            >
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">Must be a @example.com email address</p>
                        </div>

                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                            <p class="text-sm text-gray-600">
                                <strong>Employee Number:</strong> {{ $user->employee_number }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">Employee number cannot be changed. Contact an administrator if you need to update it.</p>
                        </div>

                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                            <p class="text-sm text-gray-600">
                                <strong>Role:</strong> 
                                <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full 
                                    @if($user->role === 'super_admin') bg-purple-100 text-purple-800
                                    @elseif($user->role === 'admin') bg-emerald-100 text-emerald-800
                                    @elseif($user->role === 'general_user') bg-green-100 text-green-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ $user->getRoleLabel() }}
                                </span>
                            </p>
                            <p class="text-xs text-gray-500 mt-1">Role cannot be changed. Contact an administrator if you need a role change.</p>
                        </div>
                    </div>
                </div>

                <!-- Password Change Section -->
                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-lock text-emerald-600 mr-2"></i>
                        Change Password
                    </h3>
                    <p class="text-sm text-gray-600 mb-4">Leave password fields blank if you don't want to change your password.</p>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">
                                Current Password
                            </label>
                            <input 
                                type="password" 
                                id="current_password" 
                                name="current_password"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent @error('current_password') border-red-500 @enderror"
                                placeholder="Enter your current password"
                            >
                            @error('current_password')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

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
                                placeholder="Enter new password (min 8 characters)"
                            >
                            @error('password')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">Password must be at least 8 characters long</p>
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

                <!-- Form Actions -->
                <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                    <a href="{{ route('profile.show') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors">
                        <i class="fas fa-save mr-2"></i>Update Profile
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection



