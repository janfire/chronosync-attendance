@extends('admin.layout')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard Overview')

@section('content')
    @if(Auth::user()->isPlatformAdmin())
        {{-- ============================================================
             PLATFORM ADMIN DASHBOARD
             ============================================================ --}}

        {{-- Welcome Banner --}}
        <div class="bg-gradient-to-r from-emerald-600 to-teal-700 rounded-2xl p-8 mb-6 text-white shadow-lg">
            <div class="flex items-center gap-4">
                <div class="h-14 w-14 bg-white/20 rounded-2xl flex items-center justify-center">
                    <i class="fas fa-shield-alt text-white text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold">Platform Admin Workspace</h1>
                    <p class="text-emerald-100 mt-1">Global platform overview — all tenants, subscriptions and billing at a glance.</p>
                </div>
            </div>
        </div>

        {{-- ── Metric Cards ── --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5 mb-8">

            {{-- Total Tenants --}}
            <a href="{{ route('admin.tenants.index') }}"
               class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm hover:shadow-md hover:border-emerald-300 transition-all duration-200 cursor-pointer group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase tracking-wide mb-1">Total Tenants</p>
                        <p class="text-4xl font-bold text-gray-900 dark:text-white group-hover:text-emerald-600 transition-colors">
                            {{ $adminMetrics['total_tenants'] ?? 0 }}
                        </p>
                        <p class="text-xs text-gray-400 mt-2">All registered organisations</p>
                    </div>
                    <div class="h-16 w-16 bg-emerald-50 rounded-2xl flex items-center justify-center group-hover:bg-emerald-100 transition-colors">
                        <i class="fas fa-building text-emerald-600 text-2xl"></i>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                    <span class="text-xs text-emerald-600 font-semibold group-hover:underline">
                        View all tenants <i class="fas fa-arrow-right ml-1"></i>
                    </span>
                </div>
            </a>

            {{-- Active Subscriptions --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm hover:shadow-md transition-all duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase tracking-wide mb-1">Active Subscriptions</p>
                        <p class="text-4xl font-bold text-gray-900 dark:text-white">
                            {{ $adminMetrics['active_subscriptions'] ?? 0 }}
                        </p>
                        <p class="text-xs text-gray-400 mt-2">Paid &amp; up-to-date</p>
                    </div>
                    <div class="h-16 w-16 bg-green-50 rounded-2xl flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                    <span class="text-xs text-green-600 font-semibold">
                        <i class="fas fa-circle text-green-400 mr-1" style="font-size:8px"></i> Subscriptions running normally
                    </span>
                </div>
            </div>

            {{-- Pending Subscriptions --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-orange-200 p-6 shadow-sm hover:shadow-md transition-all duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase tracking-wide mb-1">Pending Subscriptions</p>
                        <p class="text-4xl font-bold text-orange-600">
                            {{ $adminMetrics['pending_subscriptions'] ?? 0 }}
                        </p>
                        <p class="text-xs text-gray-400 mt-2">Awaiting payment / activation</p>
                    </div>
                    <div class="h-16 w-16 bg-orange-50 rounded-2xl flex items-center justify-center">
                        <i class="fas fa-hourglass-half text-orange-500 text-2xl"></i>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-orange-100">
                    <a href="{{ route('superadmin.finance.pending') }}" class="text-xs text-orange-600 font-semibold hover:underline">
                        Review pending invoices <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>

            {{-- Monthly Recurring Revenue --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm hover:shadow-md transition-all duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase tracking-wide mb-1">Monthly Recurring Revenue</p>
                        <p class="text-4xl font-bold text-gray-900 dark:text-white">
                            ${{ number_format($adminMetrics['mrr'] ?? 0, 2) }}
                        </p>
                        <p class="text-xs text-gray-400 mt-2">USD – current billing month</p>
                    </div>
                    <div class="h-16 w-16 bg-blue-50 rounded-2xl flex items-center justify-center">
                        <i class="fas fa-dollar-sign text-blue-600 text-2xl"></i>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                    <span class="text-xs text-blue-600 font-semibold">
                        <i class="fas fa-chart-line mr-1"></i> Revenue this month
                    </span>
                </div>
            </div>

            {{-- New Tenants (7 days) --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm hover:shadow-md transition-all duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase tracking-wide mb-1">New Tenants (7 days)</p>
                        <p class="text-4xl font-bold text-gray-900 dark:text-white">
                            {{ $adminMetrics['new_tenants'] ?? 0 }}
                        </p>
                        <p class="text-xs text-gray-400 mt-2">Recent sign-ups</p>
                    </div>
                    <div class="h-16 w-16 bg-purple-50 rounded-2xl flex items-center justify-center">
                        <i class="fas fa-user-plus text-purple-600 text-2xl"></i>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                    <span class="text-xs text-purple-600 font-semibold">
                        <i class="fas fa-calendar-week mr-1"></i> Last 7 days
                    </span>
                </div>
            </div>

            {{-- System Health --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm hover:shadow-md transition-all duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase tracking-wide mb-1">System Health</p>
                        <p class="text-xl font-bold text-green-600 mt-1">All Services Running</p>
                        <p class="text-xs text-gray-400 mt-2">Queue jobs pending: {{ $adminMetrics['queue_jobs'] ?? 0 }}</p>
                    </div>
                    <div class="h-16 w-16 bg-green-50 rounded-2xl flex items-center justify-center relative">
                        <i class="fas fa-heartbeat text-green-600 text-2xl"></i>
                        <span class="absolute -top-1 -right-1 h-4 w-4 bg-green-500 rounded-full border-2 border-white animate-pulse"></span>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                    <span class="text-xs text-green-600 font-semibold">
                        <i class="fas fa-server mr-1"></i> All systems operational
                    </span>
                </div>
            </div>

        </div>{{-- /metric cards --}}

        {{-- ── Quick Actions ── --}}
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-widest mb-4">Quick Actions</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

            <a href="{{ route('superadmin.finance.pending') }}"
               class="flex items-center gap-5 bg-white dark:bg-gray-800 rounded-2xl border border-emerald-200 p-6 shadow-sm hover:shadow-md hover:border-emerald-400 transition-all duration-200 group">
                <div class="h-14 w-14 bg-emerald-100 rounded-2xl flex items-center justify-center group-hover:bg-emerald-200 transition-colors shrink-0">
                    <i class="fas fa-file-invoice-dollar text-emerald-600 text-xl"></i>
                </div>
                <div>
                    <p class="font-bold text-gray-900 dark:text-white group-hover:text-emerald-700 transition-colors">Finance Operations</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Review tenant billing, confirm payments &amp; manage invoices.</p>
                </div>
                <i class="fas fa-chevron-right text-gray-300 group-hover:text-emerald-500 ml-auto transition-colors"></i>
            </a>

            <a href="{{ route('admin.tenants.index') }}"
               class="flex items-center gap-5 bg-white dark:bg-gray-800 rounded-2xl border border-slate-200 p-6 shadow-sm hover:shadow-md hover:border-slate-400 transition-all duration-200 group">
                <div class="h-14 w-14 bg-slate-100 rounded-2xl flex items-center justify-center group-hover:bg-slate-200 transition-colors shrink-0">
                    <i class="fas fa-sitemap text-slate-600 text-xl"></i>
                </div>
                <div>
                    <p class="font-bold text-gray-900 dark:text-white group-hover:text-slate-700 transition-colors">Tenant Management</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Browse all registered tenants and manage their status.</p>
                </div>
                <i class="fas fa-chevron-right text-gray-300 group-hover:text-slate-500 ml-auto transition-colors"></i>
            </a>

        </div>{{-- /quick actions --}}

  </div>
    @else
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4 mb-6">
            <!-- Total Employees Card -->
            <a href="{{ route('admin.users.index') }}" class="stat-card bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 hover:shadow-md hover:border-emerald-300 transition-all duration-200 cursor-pointer group">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1 font-medium">Total Employees</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white group-hover:text-emerald-600 transition-colors">{{ $stats['total_employees'] }}</p>
                        <p class="text-xs text-gray-400 mt-1">All registered staff</p>
                    </div>
                    <div class="h-14 w-14 bg-emerald-100 rounded-xl flex items-center justify-center group-hover:bg-emerald-200 transition-colors">
                        <i class="fas fa-users text-emerald-600 text-xl"></i>
                    </div>
                </div>
                <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-700">
                    <span class="text-xs text-emerald-600 font-medium group-hover:underline">View all <i class="fas fa-arrow-right ml-1"></i></span>
                </div>
            </a>

            <!-- Today's Attendance Card -->
            <a href="{{ route('admin.insights') }}?date={{ today()->toDateString() }}" class="stat-card bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 hover:shadow-md hover:border-green-300 transition-all duration-200 cursor-pointer group">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1 font-medium">Today's Attendance</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white group-hover:text-green-600 transition-colors">{{ $stats['today_attendance'] }}</p>
                        <p class="text-xs text-gray-400 mt-1">Checked in today</p>
                    </div>
                    <div class="h-14 w-14 bg-green-100 rounded-xl flex items-center justify-center group-hover:bg-green-200 transition-colors">
                        <i class="fas fa-calendar-check text-green-600 text-xl"></i>
                    </div>
                </div>
                <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-700">
                    <span class="text-xs text-green-600 font-medium group-hover:underline">View report <i class="fas fa-arrow-right ml-1"></i></span>
                </div>
            </a>

            <!-- Currently In Card -->
            <div class="stat-card bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 hover:shadow-md transition-all duration-200">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1 font-medium">Currently In</p>
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

            <!-- Missing Check-outs Card -->
            <div class="stat-card bg-white dark:bg-gray-800 rounded-xl shadow-sm border {{ $stats['pending_issues'] > 0 ? 'border-orange-300' : 'border-gray-200' }} p-5 hover:shadow-md transition-all duration-200">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1 font-medium">Missing Check-outs</p>
                        <p class="text-3xl font-bold {{ $stats['pending_issues'] > 0 ? 'text-orange-600' : 'text-gray-900 dark:text-white' }}">{{ $stats['pending_issues'] }}</p>
                        <p class="text-xs text-gray-400 mt-1">{{ $stats['pending_issues'] > 0 ? 'Require attention' : 'All clear' }}</p>
                    </div>
                    <div class="h-14 w-14 {{ $stats['pending_issues'] > 0 ? 'bg-orange-100' : 'bg-gray-100' }} rounded-xl flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle {{ $stats['pending_issues'] > 0 ? 'text-orange-600' : 'text-gray-400' }} text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Absents Card -->
            <div class="stat-card bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 hover:shadow-md transition-all duration-200">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1 font-medium">Absents</p>
                        <p class="text-3xl font-bold text-rose-600">{{ $stats['absents'] }}</p>
                        <p class="text-xs text-gray-400 mt-1">Not checked in today</p>
                    </div>
                    <div class="h-14 w-14 bg-rose-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-user-slash text-rose-600 text-xl"></i>
                    </div>
                </div>
            </div>

        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <!-- Recent Activity (Takes 2/3 of space on desktop) -->
            <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden flex flex-col">
                <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800">
                    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-500 text-white shadow-sm">
                                <i class="fas fa-history"></i>
                            </span>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Live Activity Feed</h3>
                                <p class="text-xs text-gray-500">Recent employee clock-ins, clock-outs, and attendance locations.</p>
                            </div>
                        </div>
                        <a href="{{ route('admin.insights') }}" class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700 hover:bg-emerald-100 transition-colors">
                            <span>View Data Log</span>
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                    </div>
                </div>
                <div class="p-4 space-y-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="text-sm text-gray-600">Showing the latest activity for your active staff members.</div>
                        <div class="flex items-center gap-2 text-sm text-gray-600">
                            <i class="fas fa-clock"></i>
                            Updated {{ now()->diffForHumans() }}
                        </div>
                    </div>
                    <div class="overflow-x-auto rounded-2xl border border-gray-100 shadow-sm">
                        <table id="activityTable" class="min-w-full admin-data-table display text-sm text-left text-gray-500" style="width:100%">
                            <thead class="text-[11px] text-gray-600 uppercase tracking-[0.15em] bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-3 text-left">User</th>
                                    <th class="px-4 py-3 text-left">Action</th>
                                    <th class="px-4 py-3 text-left">Time</th>
                                    <th class="px-4 py-3 text-left">Location</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentActivity as $log)
                                <tr class="bg-white dark:bg-gray-800 border-b hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white whitespace-nowrap">
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
            </div>
        </div>
    </div>

    @endif
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
                "search": "",
                "searchPlaceholder": "Search activity...",
                "lengthMenu": "Show _MENU_ entries",
                "paginate": {
                    "previous": "<i class='fas fa-chevron-left'></i>",
                    "next": "<i class='fas fa-chevron-right'></i>"
                }
            },
            "dom": '<"admin-dt-toolbar"lf>rt<"admin-dt-footer"ip>'
        });

    });
</script>
@endpush


