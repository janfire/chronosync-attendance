@extends('admin.layout')

@section('title', 'Employee Details')
@section('page-title', 'Employee Details')

@section('content')
    <div class="max-w-6xl mx-auto">
        <!-- Back Button -->
        <div class="mb-4">
            <a href="{{ route('admin.users.index') }}" class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Staff Management
            </a>
        </div>

        <!-- Employee Header Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-start justify-between">
                <div class="flex items-center space-x-6">
                    <div class="h-24 w-24 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white text-3xl font-bold shadow-lg">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 mb-1">{{ $user->name }}</h2>
                        <p class="text-gray-600 mb-2">{{ $user->email }}</p>
                        <div class="flex items-center space-x-4">
                            <span class="text-sm text-gray-500">
                                <i class="fas fa-id-badge mr-1"></i>{{ $user->employee_number }}
                            </span>
                            @if($user->biometricData && $user->biometricData->facial_status == 'captured')
                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1"></i>Enrolled
                                </span>
                            @else
                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                    <i class="fas fa-times-circle mr-1"></i>Not Enrolled
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="flex items-center space-x-2 flex-wrap">
                    <a href="{{ route('admin.insights') }}?user_id={{ $user->id }}" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm transition-colors">
                        <i class="fas fa-chart-line mr-2"></i>View History & Analytics
                    </a>
                    @if(Auth::user()->canManageUsers())
                        <a href="{{ route('admin.users.edit', $user->id) }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm transition-colors">
                            <i class="fas fa-edit mr-2"></i>Edit
                        </a>
                        <a href="{{ route('biometric.enrollment', $user->id) }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm transition-colors">
                            <i class="fas fa-camera mr-2"></i>{{ $user->biometricData && $user->biometricData->facial_status === 'captured' ? 'Re-Enroll Facial Recognition' : 'Facial Recognition Enrollment' }}
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Statistics Cards -->
            <div class="lg:col-span-1 space-y-4">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <h3 class="text-sm font-semibold text-gray-900 mb-4">Statistics</h3>
                    <div class="space-y-4">
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Total Clock-ins</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $stats['total_clock_ins'] }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">This Month</p>
                            <p class="text-2xl font-bold text-emerald-600">{{ $stats['this_month'] }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Member Since</p>
                            <p class="text-sm font-medium text-gray-900">{{ $user->created_at->format('F j, Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Recent Attendance</h3>
                </div>
                <div class="overflow-x-auto">
                    @if($recentAttendance->count() > 0)
                        <div class="divide-y divide-gray-200">
                            @foreach($recentAttendance as $log)
                                <div class="px-6 py-4 hover:bg-gray-50 transition-colors">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-4">
                                            <div class="h-10 w-10 rounded-full {{ $log->action == 'clock_in' ? 'bg-green-100' : 'bg-red-100' }} flex items-center justify-center flex-shrink-0">
                                                <i class="fas fa-{{ $log->action == 'clock_in' ? 'sign-in-alt' : 'sign-out-alt' }} text-{{ $log->action == 'clock_in' ? 'green' : 'red' }}-600"></i>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">
                                                    {{ ucfirst(str_replace('_', ' ', $log->action)) }}
                                                </p>
                                                <div class="flex items-center space-x-2 mt-0.5">
                                                    <p class="text-xs text-gray-500">{{ $log->timestamp->format('M j, Y') }}</p>
                                                    <span class="text-xs text-gray-400">•</span>
                                                    <p class="text-xs text-gray-500">{{ $log->timestamp->format('g:i A') }}</p>
                                                    <span class="text-xs text-gray-400">•</span>
                                                    <p class="text-xs text-gray-500">{{ $log->timestamp->diffForHumans() }}</p>
                                                </div>
                                            </div>
                                        </div>
                                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full {{ $log->action == 'clock_in' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ ucfirst(str_replace('_', ' ', $log->action)) }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <div class="h-16 w-16 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                                    <i class="fas fa-clock text-gray-400 text-2xl"></i>
                                </div>
                                <p class="text-sm font-medium text-gray-900 mb-1">No attendance records</p>
                                <p class="text-xs text-gray-500">This employee hasn't clocked in yet</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection



