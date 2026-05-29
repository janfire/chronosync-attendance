@extends('admin.layout')

@section('title', 'Advanced Reports')
@section('page-title', 'Advanced Attendance Reports')

@section('content')
<div class="space-y-6">
    <!-- Top Controls -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
        <div class="flex items-center space-x-4">
            <form method="GET" action="{{ route('admin.reports.index') }}" class="flex items-center space-x-2">
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Range</label>
                    <div class="flex items-center space-x-2">
                        <input type="date" name="from" value="{{ $dateFrom->toDateString() }}" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
                        <span class="text-gray-400">to</span>
                        <input type="date" name="to" value="{{ $dateTo->toDateString() }}" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>
                <div class="self-end">
                    <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700 transition-colors">
                        Refresh
                    </button>
                </div>
            </form>
            
            <div class="h-10 w-px bg-gray-200"></div>

            <form method="GET" action="{{ route('admin.reports.index') }}" class="flex items-center space-x-2">
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Month Focus</label>
                    <input type="month" name="month" value="{{ $month }}" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500" onchange="this.form.submit()">
                </div>
            </form>
        </div>

        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.reports.settings') }}" class="flex items-center space-x-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium transition-colors">
                <i class="fas fa-cog"></i>
                <span>Shift & Rules</span>
            </a>
            <a href="{{ route('admin.reports.export', ['month' => $month]) }}" class="flex items-center space-x-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium transition-colors">
                <i class="fas fa-file-export"></i>
                <span>Export Monthly</span>
            </a>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm font-bold text-gray-500 uppercase tracking-wider">Events</p>
                <div class="p-2 bg-emerald-50 rounded-lg text-emerald-600">
                    <i class="fas fa-fingerprint"></i>
                </div>
            </div>
            <h3 class="text-3xl font-black text-gray-900">{{ $stats['events'] }}</h3>
            <p class="text-xs text-gray-400 mt-2">In selected date range</p>
        </div>
        <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm font-bold text-gray-500 uppercase tracking-wider">Users</p>
                <div class="p-2 bg-indigo-50 rounded-lg text-indigo-600">
                    <i class="fas fa-users"></i>
                </div>
            </div>
            <h3 class="text-3xl font-black text-gray-900">{{ $stats['users'] }}</h3>
            <p class="text-xs text-gray-400 mt-2">Active staff members</p>
        </div>
        <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm font-bold text-gray-500 uppercase tracking-wider">Late Rate</p>
                <div class="p-2 bg-red-50 rounded-lg text-red-600">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
            <h3 class="text-3xl font-black text-gray-900">{{ $stats['late_rate'] }}%</h3>
            <p class="text-xs text-red-500 mt-2 font-medium">After system threshold</p>
        </div>
    </div>

    <!-- Top 5 Grids -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top 5 Compliant -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                <h3 class="font-bold text-gray-900">Top 5 — Compliant</h3>
                <p class="text-[10px] text-gray-500 uppercase tracking-widest font-bold">Best attendance & within hours</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 text-[10px] font-black text-gray-400 uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-3">User</th>
                            <th class="px-6 py-3">Days</th>
                            <th class="px-6 py-3">Late</th>
                            <th class="px-6 py-3">Early Leave</th>
                            <th class="px-6 py-3">Avg In/Out</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($topCompliant as $data)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <span class="font-bold text-gray-900">{{ $data['user']->name }}</span>
                                <div class="text-[10px] text-gray-400">ID: {{ $data['user']->employee_number }}</div>
                            </td>
                            <td class="px-6 py-4 font-bold text-emerald-600">{{ $data['days_present'] }}</td>
                            <td class="px-6 py-4 {{ $data['late_count'] > 0 ? 'text-red-500' : 'text-gray-400' }}">{{ $data['late_count'] }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $data['early_leaves'] }}</td>
                            <td class="px-6 py-4 text-[10px] text-gray-500">{{ $data['avg_sign_in'] }} - {{ $data['avg_sign_out'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Top 5 Punctual -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                <h3 class="font-bold text-gray-900">Top 5 — Most Punctual</h3>
                <p class="text-[10px] text-gray-500 uppercase tracking-widest font-bold">Closest to shift start</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 text-[10px] font-black text-gray-400 uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-3">User</th>
                            <th class="px-6 py-3 text-right">Avg Min After Start</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($topPunctual as $data)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <span class="font-bold text-gray-900">{{ $data['user']->name }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="px-2 py-1 bg-green-100 text-green-700 rounded-lg font-bold text-xs">
                                    +{{ $data['avg_diff'] }}m
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <!-- Top 5 Active Outside Hours -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                <h3 class="font-bold text-gray-900">Top 5 — Active Outside Work Hours</h3>
                <p class="text-[10px] text-gray-500 uppercase tracking-widest font-bold">Events outside shift hours</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 text-[10px] font-black text-gray-400 uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-3">User</th>
                            <th class="px-6 py-3 text-right">Events</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($topOutside as $log)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <span class="font-bold text-gray-900">{{ $log->user->name }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="px-2 py-1 bg-purple-100 text-purple-700 rounded-lg font-bold text-xs">
                                    {{ $log->outside_count }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Weekly Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Weekly Late Comers -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                <h3 class="font-bold text-gray-900">Weekly — Top 5 Late Comers</h3>
                <p class="text-[10px] text-gray-500 uppercase tracking-widest font-bold">Current week lateness count</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 text-[10px] font-black text-gray-400 uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-3">User</th>
                            <th class="px-6 py-3 text-right">Late Days</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($topLateComersWeekly as $res)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <span class="font-bold text-gray-900">{{ $res->user->name }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="text-red-600 font-bold">{{ $res->late_count }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Weekly Early Sign-outs -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                <h3 class="font-bold text-gray-900">Weekly — Top 5 Early Sign-outs</h3>
                <p class="text-[10px] text-gray-500 uppercase tracking-widest font-bold">Current week early leaves</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 text-[10px] font-black text-gray-400 uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-3">User</th>
                            <th class="px-6 py-3 text-right">Early Leaves</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($topEarlySignOutsWeekly as $res)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <span class="font-bold text-gray-900">{{ $res->user->name }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="text-orange-600 font-bold">{{ $res->early_count }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h3 class="font-extrabold text-gray-900 text-lg">Total Attendance Hours per User (Monthly)</h3>
                <p class="text-xs text-gray-500">Hours are clipped to shift window and exclude weekends & listed holidays.</p>
            </div>
            <div class="px-3 py-1 bg-emerald-50 text-emerald-700 rounded-full text-xs font-bold uppercase tracking-tighter">
                {{ Carbon\Carbon::parse($month)->format('F Y') }}
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm admin-data-table display" id="reportsTable">
                <thead class="bg-gray-50 text-[10px] font-black text-gray-400 uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-4">User Details</th>
                        <th class="px-6 py-4">Days Counted</th>
                        <th class="px-6 py-4">Hours (Clipped)</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($monthlyDetails as $data)
                    <tr class="hover:bg-emerald-50/30 transition-colors cursor-pointer" onclick="window.location='{{ route('admin.staff.show', $data['user']->id) }}'">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="h-8 w-8 rounded-lg bg-gray-100 flex items-center justify-center text-xs font-bold text-gray-500 mr-3">
                                    {{ substr($data['user']->name, 0, 1) }}
                                </div>
                                <div>
                                    <div class="font-bold text-gray-900">{{ $data['user']->name }}</div>
                                    <span class="text-[10px] text-gray-400 uppercase font-black">{{ $data['user']->employee_number }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 font-medium text-gray-600">{{ $data['days_present'] }} days</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-2">
                                <span class="text-lg font-black text-gray-900">{{ $data['total_hours'] }}</span>
                                <span class="text-[10px] text-gray-400 font-bold uppercase">hours</span>
                            </div>
                            <div class="text-[10px] text-gray-400 italic">Avg: {{ $data['avg_sign_in'] }} - {{ $data['avg_sign_out'] }}</div>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <i class="fas fa-chevron-right text-gray-300"></i>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-gray-400 italic">No attendance data found for this month.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#reportsTable').DataTable({
            "pageLength": 10,
            "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
            "order": [[ 2, "desc" ]], // Order by Hours column
            "language": {
                "search": "",
                "searchPlaceholder": "Search reports...",
                "lengthMenu": "Show _MENU_ rows",
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


