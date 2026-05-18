@extends('admin.layout')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard Overview')

@section('content')
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Total Employees Card -->
        <a href="{{ route('admin.users.index') }}" class="stat-card bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md hover:border-emerald-300 transition-all duration-200 cursor-pointer group">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-xs text-gray-500 mb-1 font-medium">Total Employees</p>
                    <p class="text-3xl font-bold text-gray-900 group-hover:text-emerald-600 transition-colors">{{ $stats['total_employees'] }}</p>
                    <p class="text-xs text-gray-400 mt-1">All registered staff</p>
                </div>
                <div class="h-14 w-14 bg-emerald-100 rounded-xl flex items-center justify-center group-hover:bg-emerald-200 transition-colors">
                    <i class="fas fa-users text-emerald-600 text-xl"></i>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-gray-100">
                <span class="text-xs text-emerald-600 font-medium group-hover:underline">View all <i class="fas fa-arrow-right ml-1"></i></span>
            </div>
        </a>

        <!-- Today's Attendance Card -->
        <a href="{{ route('admin.insights') }}?date={{ today()->toDateString() }}" class="stat-card bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md hover:border-green-300 transition-all duration-200 cursor-pointer group">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-xs text-gray-500 mb-1 font-medium">Today's Attendance</p>
                    <p class="text-3xl font-bold text-gray-900 group-hover:text-green-600 transition-colors">{{ $stats['today_attendance'] }}</p>
                    <p class="text-xs text-gray-400 mt-1">Checked in today</p>
                </div>
                <div class="h-14 w-14 bg-green-100 rounded-xl flex items-center justify-center group-hover:bg-green-200 transition-colors">
                    <i class="fas fa-calendar-check text-green-600 text-xl"></i>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-gray-100">
                <span class="text-xs text-green-600 font-medium group-hover:underline">View report <i class="fas fa-arrow-right ml-1"></i></span>
            </div>
        </a>

        <!-- Currently In Card -->
        <div class="stat-card bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition-all duration-200">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-xs text-gray-500 mb-1 font-medium">Currently In</p>
                    <p class="text-3xl font-bold text-purple-600">{{ $stats['currently_clocked_in'] }}</p>
                    <p class="text-xs text-gray-400 mt-1">On site now</p>
                </div>
                <div class="h-14 w-14 bg-purple-100 rounded-xl flex items-center justify-center relative">
                    <i class="fas fa-user-clock text-purple-600 text-xl"></i>
                    @if($stats['currently_clocked_in'] > 0)
                        <span class="absolute -top-1 -right-1 h-4 w-4 bg-green-500 rounded-full border-2 border-white animate-pulse"></span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Pending Issues Card -->
        <div class="stat-card bg-white rounded-xl shadow-sm border {{ $stats['pending_issues'] > 0 ? 'border-orange-300' : 'border-gray-200' }} p-5 hover:shadow-md transition-all duration-200">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-xs text-gray-500 mb-1 font-medium">Pending Issues</p>
                    <p class="text-3xl font-bold {{ $stats['pending_issues'] > 0 ? 'text-orange-600' : 'text-gray-900' }}">{{ $stats['pending_issues'] }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ $stats['pending_issues'] > 0 ? 'Require attention' : 'All clear' }}</p>
                </div>
                <div class="h-14 w-14 {{ $stats['pending_issues'] > 0 ? 'bg-orange-100' : 'bg-gray-100' }} rounded-xl flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle {{ $stats['pending_issues'] > 0 ? 'text-orange-600' : 'text-gray-400' }} text-xl"></i>
                </div>
            </div>
        </div>

    </div>



    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Activity (Takes 2/3 of space on desktop) -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between bg-gray-50/50">
                <h3 class="text-lg font-bold text-gray-900 flex items-center">
                    <i class="fas fa-history text-emerald-500 mr-2"></i>Live Activity Feed
                </h3>
                <a href="{{ route('admin.insights') }}" class="text-sm text-emerald-600 hover:underline font-medium">
                    View Data Log <i class="fas fa-external-link-alt ml-1"></i>
                </a>
            </div>
            <div class="p-4">
                <div class="overflow-x-auto">
                    <table id="activityTable" class="w-full text-sm text-left text-gray-500 display" style="width:100%">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 rounded-l-lg">User</th>
                                <th class="px-4 py-3">Action</th>
                                <th class="px-4 py-3">Time</th>
                                <th class="px-4 py-3 rounded-r-lg">Location</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentActivity as $log)
                            <tr class="bg-white border-b hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap">
                                    {{ $log->user_name }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2.5 py-1 text-[10px] font-bold tracking-widest uppercase rounded border {{ $log->action == 'clock_in' ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-rose-200 bg-rose-50 text-rose-700' }}">
                                        {{ str_replace('_', ' ', $log->action) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3" data-order="{{ $log->timestamp->timestamp }}">
                                    {{ $log->timestamp->format('H:i') }}
                                    <span class="text-xs text-gray-400 block">{{ $log->timestamp->diffForHumans() }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    {{ $log->location_name ?? 'Main Site' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Security & Health (Takes 1/3) -->
        <div class="space-y-6">
            <!-- Summary Stats -->
            <div class="bg-gradient-to-br from-gray-900 to-slate-800 rounded-2xl p-6 text-white shadow-xl">
                <h4 class="text-xs font-black uppercase tracking-[0.2em] text-slate-400 mb-4">Daily Health</h4>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-slate-300">Arrivals Today</span>
                        <span class="text-lg font-bold">{{ $stats['today_attendance'] }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-slate-300">Currently Active</span>
                        <span class="text-lg font-bold text-emerald-400">{{ $stats['currently_clocked_in'] }}</span>
                    </div>
                </div>
                <div class="mt-6 pt-6 border-t border-slate-700">
                    <p class="text-[10px] text-slate-500 italic text-center">Last updated: {{ now()->format('H:i:s') }}</p>
                </div>
            </div>

            <!-- Security Alerts Mini-Feed -->
            <div class="bg-white rounded-xl shadow-sm border border-orange-200 overflow-hidden">
                <div class="px-4 py-3 bg-orange-50 border-b border-orange-100 flex items-center justify-between">
                    <h3 class="text-xs font-black uppercase text-orange-800 tracking-wider">Security Alerts</h3>
                    <span class="animate-pulse h-2 w-2 bg-orange-500 rounded-full"></span>
                </div>
                <div class="divide-y divide-orange-50">
                    @forelse($failedAttempts->take(3) as $attempt)
                        <div class="p-4 bg-orange-50/20">
                            <div class="flex items-start space-x-3">
                                @if($attempt->captured_face_path)
                                    <img src="{{ asset('storage/' . $attempt->captured_face_path) }}" class="h-10 w-10 rounded-lg object-cover ring-2 ring-white">
                                @else
                                    <div class="h-10 w-10 rounded-lg bg-orange-200 flex items-center justify-center text-orange-600">
                                        <i class="fas fa-user-secret"></i>
                                    </div>
                                @endif
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-bold text-gray-900 truncate">{{ $attempt->reason }}</p>
                                    <p class="text-[10px] text-gray-500">{{ $attempt->attempted_at->diffForHumans() }}</p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-6 text-center text-gray-400">
                            <p class="text-[10px]">No security threats detected.</p>
                        </div>
                    @endforelse
                </div>
                @if($failedAttempts->count() > 3)
                    <div class="px-4 py-2 bg-gray-50 text-center border-t border-gray-100">
                        <button class="text-[10px] font-bold text-gray-500 hover:text-gray-700 uppercase">View All Alerts</button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Security Alerts & Failed Attempts Feed -->
    <div class="mt-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <div class="h-8 w-8 rounded-xl bg-rose-50 text-rose-500 border border-rose-100 flex items-center justify-center mr-3 shadow-sm">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    Security Event Log
                </h3>
                <span class="px-3 py-1 text-[10px] font-bold uppercase tracking-wider bg-white border border-gray-200 text-gray-500 rounded-lg shadow-sm">
                    Recent Activity
                </span>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($failedAttempts as $attempt)
                    <div class="p-5 hover:bg-slate-50/80 transition-colors group relative flex flex-col md:flex-row md:items-center gap-4">
                        <!-- Left: Image or Icon -->
                        <div class="flex-shrink-0 relative">
                            @if($attempt->captured_face_path)
                                <div class="overflow-hidden rounded-xl h-14 w-14 border border-rose-200 shadow-sm bg-white p-0.5">
                                    <img src="{{ asset('storage/' . $attempt->captured_face_path) }}" 
                                         alt="Captured Face" 
                                         class="h-full w-full rounded-lg object-cover transform group-hover:scale-110 transition-transform duration-300">
                                </div>
                            @else
                                <div class="h-14 w-14 rounded-xl bg-gray-50 border border-gray-200 flex items-center justify-center text-gray-400">
                                    <i class="fas fa-user-secret text-xl opacity-50"></i>
                                </div>
                            @endif
                            <div class="absolute -top-1 -right-1 h-3 w-3 bg-rose-500 border-2 border-white rounded-full"></div>
                        </div>
                        
                        <!-- Middle: Details -->
                        <div class="flex-1 min-w-0 pr-12">
                            <div class="flex items-center gap-2 mb-1.5">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wide bg-rose-50 text-rose-600 border border-rose-100">
                                    {{ $attempt->reason }}
                                </span>
                                @if($attempt->action_attempted)
                                    <span class="text-xs text-gray-500 font-medium hidden sm:inline-block">&bull; {{ ucfirst($attempt->action_attempted) }} Attempt</span>
                                @endif
                            </div>
                            
                            @if($attempt->user)
                                <h4 class="text-sm font-bold text-gray-900 truncate">{{ $attempt->user->name }}</h4>
                                <p class="text-[11px] text-gray-500 font-mono mt-0.5 truncate">ID: {{ $attempt->user->employee_number }}</p>
                            @else
                                <h4 class="text-sm font-bold text-gray-600 italic">Unidentified Identity</h4>
                                <p class="text-[11px] text-gray-400 mt-0.5">System could not match biometrics</p>
                            @endif
                        </div>
                        
                        <!-- Right: Metadata & Actions -->
                        <div class="flex flex-row md:flex-col items-center md:items-end justify-between md:justify-center gap-2 mt-3 md:mt-0 shrink-0">
                            <div class="text-left md:text-right">
                                <div class="text-sm font-bold text-gray-900">{{ $attempt->attempted_at->format('H:i') }}</div>
                                <div class="text-[10px] text-gray-400 font-medium">{{ $attempt->attempted_at->diffForHumans() }}</div>
                            </div>
                            <div class="text-[10px] font-mono text-gray-400 px-2 py-1 bg-white rounded border border-gray-200 shadow-sm flex items-center gap-1.5">
                                <i class="fas fa-network-wired text-gray-300"></i>{{ $attempt->network_info }}
                            </div>
                        </div>

                        <!-- Hover Action Button -->
                        <div class="absolute right-5 top-1/2 -translate-y-1/2 opacity-0 group-hover:opacity-100 transition-all duration-200 hidden lg:flex items-center gap-2 transform translate-x-2 group-hover:translate-x-0 bg-white/90 backdrop-blur px-3 py-4 rounded-xl border border-gray-100 shadow-sm">
                            <button class="bg-white hover:bg-gray-50 hover:text-rose-600 border border-gray-200 text-gray-600 shadow-sm rounded-lg px-3 py-1.5 text-xs font-bold transition-colors">
                                Review Event
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="p-16 text-center flex flex-col items-center bg-gray-50/30">
                        <div class="h-16 w-16 bg-emerald-50 text-emerald-500 rounded-2xl flex items-center justify-center mb-4 border border-emerald-100 shadow-sm">
                            <i class="fas fa-shield-check text-2xl"></i>
                        </div>
                        <h4 class="text-gray-900 font-bold mb-1">No Security Alerts</h4>
                        <p class="text-sm text-gray-500 max-w-sm">Your system is currently secure. No failed biometric attempts have been logged recently.</p>
                    </div>
                @endforelse
            </div>
            @if($failedAttempts->count() > 0)
            <div class="px-6 py-3 bg-gray-50 border-t border-gray-100 text-center">
                <a href="#" class="text-xs font-bold text-gray-500 hover:text-gray-800 uppercase tracking-wider transition-colors">
                    View Full Security Log
                </a>
            </div>
            @endif
        </div>
    </div>
@endsection

@push('styles')
<style>
    .stat-card {
        transition: all 0.2s ease;
    }
    .stat-card:hover {
        transform: translateY(-2px);
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        $('#activityTable').DataTable({
            "pageLength": 5,
            "lengthMenu": [[5, 10, 25, -1], [5, 10, 25, "All"]],
            "order": [[ 2, "desc" ]], // Order by Time column by default
            "language": {
                "search": "<i class='fas fa-search text-gray-400'></i>",
                "searchPlaceholder": "Search activity...",
                "paginate": {
                    "previous": "<i class='fas fa-chevron-left'></i>",
                    "next": "<i class='fas fa-chevron-right'></i>"
                }
            },
            "dom": '<"flex items-center justify-between mb-4"lf>rt<"flex items-center justify-between mt-4"ip>'
        });


    });
</script>
@endpush


