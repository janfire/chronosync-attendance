@extends('admin.layout')

@section('title', 'My Profile')
@section('page-title', 'My Profile')

@section('content')
    <div class="max-w-3xl mx-auto">
        <!-- Profile Header -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6 dark:bg-gray-800 dark:border-gray-700">
            <div class="flex items-center space-x-6">
                <div class="h-24 w-24 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-3xl font-bold shadow-lg">
                    {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
                </div>
                <div class="flex-1">
                    <h2 class="text-2xl font-bold text-gray-900 mb-1 dark:text-white">{{ Auth::user()->name }}</h2>
                    <p class="text-gray-600 mb-2 dark:text-gray-300">{{ Auth::user()->email }}</p>
                    <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full 
                        @if(Auth::user()->role === 'super_admin') bg-purple-100 text-purple-800
                        @elseif(Auth::user()->role === 'admin') bg-emerald-100 text-emerald-800
                        @elseif(Auth::user()->role === 'general_user') bg-green-100 text-green-800
                        @else bg-gray-100 text-gray-800
                        @endif dark:bg-gray-800 dark:text-white">
                        {{ Auth::user()->getRoleLabel() }}
                    </span>
                </div>
                <div>
                    <a href="{{ route('profile.edit') }}" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg transition-colors inline-flex items-center space-x-2">
                        <i class="fas fa-edit"></i>
                        <span>Edit Profile</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Profile Information -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Personal Information -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 dark:bg-gray-800 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center dark:text-white">
                    <i class="fas fa-user-circle text-emerald-600 mr-2"></i>
                    Personal Information
                </h3>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Full Name</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ Auth::user()->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email Address</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ Auth::user()->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Employee Number</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ Auth::user()->employee_number }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Role</dt>
                        <dd class="mt-1">
                            <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full 
                                @if(Auth::user()->role === 'super_admin') bg-purple-100 text-purple-800
                                @elseif(Auth::user()->role === 'admin') bg-emerald-100 text-emerald-800
                                @elseif(Auth::user()->role === 'general_user') bg-green-100 text-green-800
                                @else bg-gray-100 text-gray-800
                                @endif dark:bg-gray-800 dark:text-white">
                                {{ Auth::user()->getRoleLabel() }}
                            </span>
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- Account Information -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 dark:bg-gray-800 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center dark:text-white">
                    <i class="fas fa-info-circle text-emerald-600 mr-2"></i>
                    Account Information
                </h3>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Account Created</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ Auth::user()->created_at->format('F j, Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Last Updated</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ Auth::user()->updated_at->format('F j, Y g:i A') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Biometric Enrollment</dt>
                        <dd class="mt-1">
                            @if(Auth::user()->biometricData && Auth::user()->biometricData->facial_status === 'captured')
                                <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1"></i>Enrolled
                                </span>
                            @else
                                <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-white">
                                    <i class="fas fa-times-circle mr-1"></i>Not Enrolled
                                </span>
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="mt-6 bg-white rounded-lg shadow-sm border border-gray-200 p-6 dark:bg-gray-800 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 dark:text-white">Quick Actions</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="{{ route('profile.edit') }}" class="flex items-center space-x-3 p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors dark:bg-gray-900 dark:border-gray-700">
                    <div class="h-10 w-10 rounded-lg bg-emerald-100 flex items-center justify-center">
                        <i class="fas fa-edit text-emerald-600"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">Edit Profile</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Update your information</p>
                    </div>
                </a>
                
                @if(Auth::user()->isPlatformAdmin())
                <a href="{{ route('superadmin.dashboard') }}" class="flex items-center space-x-3 p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors dark:bg-gray-900 dark:border-gray-700">
                    <div class="h-10 w-10 rounded-lg bg-green-100 flex items-center justify-center">
                        <i class="fas fa-chart-line text-green-600"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">Dashboard</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Go to platform dashboard</p>
                    </div>
                </a>
                @else
                <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3 p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors dark:bg-gray-900 dark:border-gray-700">
                    <div class="h-10 w-10 rounded-lg bg-green-100 flex items-center justify-center">
                        <i class="fas fa-chart-line text-green-600"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">Dashboard</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Go to dashboard</p>
                    </div>
                </a>
                @endif
                
                @if(Auth::user()->canManageUsers() && !Auth::user()->isPlatformAdmin())
                <a href="{{ route('admin.users.index') }}" class="flex items-center space-x-3 p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors dark:bg-gray-900 dark:border-gray-700">
                    <div class="h-10 w-10 rounded-lg bg-purple-100 flex items-center justify-center">
                        <i class="fas fa-users text-purple-600"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">Manage Users</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">User management</p>
                    </div>
                </a>
                @endif
            </div>
        </div>
    </div>
@endsection



