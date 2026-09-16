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
        <!-- Events Card -->
        <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm transition-all duration-300 hover:shadow-md hover:-translate-y-0.5 group">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Total Logs</p>
                    <h3 class="text-3xl font-black text-gray-900 tracking-tight group-hover:text-emerald-600 transition-colors">{{ number_format($stats['events']) }}</h3>
                </div>
                <div class="p-3 bg-emerald-50 rounded-xl text-emerald-600 group-hover:bg-emerald-100 transition-colors">
                    <i class="fas fa-fingerprint text-xl"></i>
                </div>
            </div>
            <div class="flex items-center space-x-1.5 text-xs text-gray-400 font-medium">
                <span class="inline-flex items-center px-1.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-semibold text-[10px]">Active Range</span>
                <span>within date filters</span>
            </div>
        </div>

        <!-- Users Card -->
        <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm transition-all duration-300 hover:shadow-md hover:-translate-y-0.5 group">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Staff Members</p>
                    <h3 class="text-3xl font-black text-gray-900 tracking-tight group-hover:text-indigo-600 transition-colors">{{ number_format($stats['users']) }}</h3>
                </div>
                <div class="p-3 bg-indigo-50 rounded-xl text-indigo-600 group-hover:bg-indigo-100 transition-colors">
                    <i class="fas fa-users text-xl"></i>
                </div>
            </div>
            <div class="flex items-center space-x-1.5 text-xs text-gray-400 font-medium">
                <span class="inline-flex items-center px-1.5 py-0.5 rounded-full bg-indigo-50 text-indigo-700 font-semibold text-[10px]">Active Staff</span>
                <span>monitored accounts</span>
            </div>
        </div>

        <!-- Late Rate Card with SVG Radial ring -->
        <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm transition-all duration-300 hover:shadow-md hover:-translate-y-0.5 group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Late Check-In Rate</p>
                    <h3 class="text-3xl font-black text-gray-900 tracking-tight group-hover:text-red-500 transition-colors">{{ $stats['late_rate'] }}%</h3>
                    <p class="text-xs text-red-500 mt-2 font-semibold">After shift start threshold</p>
                </div>
                <div class="relative w-16 h-16 flex-shrink-0">
                    <svg class="w-full h-full transform -rotate-90">
                        <!-- Background circle -->
                        <circle cx="32" cy="32" r="28" stroke-width="4" stroke="#f3f4f6" fill="transparent" />
                        <!-- Foreground circle -->
                        <circle cx="32" cy="32" r="28" stroke-width="4" stroke="url(#lateRateGradient)" fill="transparent"
                                stroke-dasharray="175.9"
                                stroke-dashoffset="{{ 175.9 - (175.9 * min($stats['late_rate'], 100) / 100) }}"
                                stroke-linecap="round"
                                class="transition-all duration-1000 ease-out" />
                    </svg>
                    <!-- Gradient definition -->
                    <svg class="w-0 h-0 absolute">
                        <defs>
                            <linearGradient id="lateRateGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#ef4444" />
                                <stop offset="100%" stop-color="#f97316" />
                            </linearGradient>
                        </defs>
                    </svg>
                    <span class="absolute inset-0 flex items-center justify-center text-[10px] font-black text-red-600">{{ $stats['late_rate'] }}%</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Consolidated Tabbed Panel for Top Performers -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden transition-all duration-300 hover:shadow-md">
        <!-- Tabs Header -->
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50 flex flex-col xl:flex-row xl:items-center justify-between gap-4">
            <div>
                <h3 class="font-extrabold text-gray-900 text-lg">Top Performers & Activity Leaders</h3>
                <p class="text-xs text-gray-500">Key metrics for staff performance and attendance exceptions this month.</p>
            </div>
            <!-- Tab buttons -->
            <div class="flex flex-wrap gap-1 bg-gray-100/80 p-1 rounded-xl self-start">
                <button onclick="switchTab('compliant')" id="tabBtn-compliant" class="tab-btn px-4 py-2 rounded-lg text-xs font-bold transition-all text-gray-900 bg-white shadow-sm">
                    <i class="fas fa-award mr-1.5 text-emerald-600"></i>Compliance
                </button>
                <button onclick="switchTab('punctual')" id="tabBtn-punctual" class="tab-btn px-4 py-2 rounded-lg text-xs font-bold transition-all text-gray-500 hover:text-gray-900">
                    <i class="fas fa-bolt mr-1.5 text-yellow-500"></i>Punctuality
                </button>
                <button onclick="switchTab('outside')" id="tabBtn-outside" class="tab-btn px-4 py-2 rounded-lg text-xs font-bold transition-all text-gray-500 hover:text-gray-900">
                    <i class="fas fa-moon mr-1.5 text-purple-500"></i>After-Hours
                </button>
                <button onclick="switchTab('weekly-late')" id="tabBtn-weekly-late" class="tab-btn px-4 py-2 rounded-lg text-xs font-bold transition-all text-gray-500 hover:text-gray-900">
                    <i class="fas fa-clock mr-1.5 text-red-500"></i>Weekly Late
                </button>
                <button onclick="switchTab('weekly-early')" id="tabBtn-weekly-early" class="tab-btn px-4 py-2 rounded-lg text-xs font-bold transition-all text-gray-500 hover:text-gray-900">
                    <i class="fas fa-door-open mr-1.5 text-orange-500"></i>Weekly Early
                </button>
            </div>
        </div>

        <!-- Compliance Tab Content -->
        <div id="tabContent-compliant" class="tab-content">
            @if(count($topCompliant) > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50/50 text-[10px] font-black text-gray-400 uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-3">Rank / User</th>
                            <th class="px-6 py-3">Days</th>
                            <th class="px-6 py-3">Late Count</th>
                            <th class="px-6 py-3">Early Leaves</th>
                            <th class="px-6 py-3 text-right">Avg Hours Range</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($topCompliant as $data)
                        <tr class="hover:bg-gray-50/30 transition-colors">
                            <td class="px-6 py-4 flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    @if($loop->iteration == 1)
                                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-gradient-to-r from-yellow-400 to-amber-500 text-[10px] font-bold text-white shadow-sm ring-2 ring-yellow-200">1</span>
                                    @elseif($loop->iteration == 2)
                                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-gradient-to-r from-slate-300 to-slate-400 text-[10px] font-bold text-white shadow-sm ring-2 ring-slate-200">2</span>
                                    @elseif($loop->iteration == 3)
                                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-gradient-to-r from-amber-600 to-amber-700 text-[10px] font-bold text-white shadow-sm ring-2 ring-amber-400">3</span>
                                    @else
                                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-gray-100 text-[10px] font-bold text-gray-500">4</span>
                                    @endif
                                </div>
                                <div class="h-8 w-8 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center text-xs font-bold font-mono">
                                    {{ substr($data['user']->name, 0, 1) }}
                                </div>
                                <div>
                                    <span class="font-bold text-gray-900">{{ $data['user']->name }}</span>
                                    <div class="text-[10px] text-gray-400">ID: {{ $data['user']->employee_number }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 font-bold text-emerald-600">{{ $data['days_present'] }} days</td>
                            <td class="px-6 py-4 {{ $data['late_count'] > 0 ? 'text-red-500 font-semibold' : 'text-gray-400' }}">{{ $data['late_count'] }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $data['early_leaves'] }}</td>
                            <td class="px-6 py-4 text-right text-xs text-gray-500 font-medium">{{ $data['avg_sign_in'] }} - {{ $data['avg_sign_out'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="flex flex-col items-center justify-center py-16 px-4 text-center">
                <div class="h-16 w-16 bg-gray-50 rounded-2xl flex items-center justify-center mb-4 text-gray-400 border border-gray-100">
                    <i class="fas fa-award text-2xl text-emerald-500"></i>
                </div>
                <h4 class="font-bold text-gray-800 text-sm">No compliance data</h4>
                <p class="text-xs text-gray-400 max-w-sm mt-1">No attendance records meet compliance standards for this month.</p>
            </div>
            @endif
        </div>

        <!-- Punctuality Tab Content -->
        <div id="tabContent-punctual" class="tab-content hidden">
            @if(count($topPunctual) > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50/50 text-[10px] font-black text-gray-400 uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-3">Rank / User</th>
                            <th class="px-6 py-3 text-right">Avg Min After Start</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($topPunctual as $data)
                        <tr class="hover:bg-gray-50/30 transition-colors">
                            <td class="px-6 py-4 flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    @if($loop->iteration == 1)
                                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-gradient-to-r from-yellow-400 to-amber-500 text-[10px] font-bold text-white shadow-sm ring-2 ring-yellow-200">1</span>
                                    @elseif($loop->iteration == 2)
                                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-gradient-to-r from-slate-300 to-slate-400 text-[10px] font-bold text-white shadow-sm ring-2 ring-slate-200">2</span>
                                    @elseif($loop->iteration == 3)
                                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-gradient-to-r from-amber-600 to-amber-700 text-[10px] font-bold text-white shadow-sm ring-2 ring-amber-400">3</span>
                                    @else
                                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-gray-100 text-[10px] font-bold text-gray-500">4</span>
                                    @endif
                                </div>
                                <div class="h-8 w-8 rounded-lg bg-yellow-50 text-yellow-800 flex items-center justify-center text-xs font-bold font-mono">
                                    {{ substr($data['user']->name, 0, 1) }}
                                </div>
                                <div>
                                    <span class="font-bold text-gray-900">{{ $data['user']->name }}</span>
                                    <div class="text-[10px] text-gray-400 font-mono">{{ $data['user']->employee_number }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="px-3 py-1 bg-green-50 text-green-700 border border-green-200 rounded-full font-bold text-xs">
                                    +{{ $data['avg_diff'] }}m
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="flex flex-col items-center justify-center py-16 px-4 text-center">
                <div class="h-16 w-16 bg-gray-50 rounded-2xl flex items-center justify-center mb-4 text-gray-400 border border-gray-100">
                    <i class="fas fa-stopwatch text-2xl text-yellow-500"></i>
                </div>
                <h4 class="font-bold text-gray-800 text-sm">No punctuality records</h4>
                <p class="text-xs text-gray-400 max-w-sm mt-1">No punctuality logs available for this period.</p>
            </div>
            @endif
        </div>

        <!-- After Hours Tab Content -->
        <div id="tabContent-outside" class="tab-content hidden">
            @if(count($topOutside) > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50/50 text-[10px] font-black text-gray-400 uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-3">Rank / User</th>
                            <th class="px-6 py-3 text-right">Events Outside Shift</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($topOutside as $log)
                        <tr class="hover:bg-gray-50/30 transition-colors">
                            <td class="px-6 py-4 flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    @if($loop->iteration == 1)
                                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-gradient-to-r from-yellow-400 to-amber-500 text-[10px] font-bold text-white shadow-sm ring-2 ring-yellow-200">1</span>
                                    @elseif($loop->iteration == 2)
                                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-gradient-to-r from-slate-300 to-slate-400 text-[10px] font-bold text-white shadow-sm ring-2 ring-slate-200">2</span>
                                    @elseif($loop->iteration == 3)
                                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-gradient-to-r from-amber-600 to-amber-700 text-[10px] font-bold text-white shadow-sm ring-2 ring-amber-400">3</span>
                                    @else
                                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-gray-100 text-[10px] font-bold text-gray-500">4</span>
                                    @endif
                                </div>
                                <div class="h-8 w-8 rounded-lg bg-purple-50 text-purple-700 flex items-center justify-center text-xs font-bold font-mono">
                                    {{ substr($log->user->name, 0, 1) }}
                                </div>
                                <div>
                                    <span class="font-bold text-gray-900">{{ $log->user->name }}</span>
                                    <div class="text-[10px] text-gray-400 font-mono">{{ $log->user->employee_number }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="px-3 py-1 bg-purple-50 text-purple-700 border border-purple-200 rounded-full font-bold text-xs">
                                    {{ $log->outside_count }} logs
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="flex flex-col items-center justify-center py-16 px-4 text-center">
                <div class="h-16 w-16 bg-gray-50 rounded-2xl flex items-center justify-center mb-4 text-gray-400 border border-gray-100">
                    <i class="fas fa-moon text-2xl text-purple-500"></i>
                </div>
                <h4 class="font-bold text-gray-800 text-sm">No after-hours logs</h4>
                <p class="text-xs text-gray-400 max-w-sm mt-1">No check-in or check-out logs were registered outside normal work shifts.</p>
            </div>
            @endif
        </div>

        <!-- Weekly Late Tab Content -->
        <div id="tabContent-weekly-late" class="tab-content hidden">
            @if(count($topLateComersWeekly) > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50/50 text-[10px] font-black text-gray-400 uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-3">Rank / User</th>
                            <th class="px-6 py-3 text-right">Late Days This Week</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($topLateComersWeekly as $res)
                        <tr class="hover:bg-gray-50/30 transition-colors">
                            <td class="px-6 py-4 flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    @if($loop->iteration == 1)
                                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-gradient-to-r from-yellow-400 to-amber-500 text-[10px] font-bold text-white shadow-sm ring-2 ring-yellow-200">1</span>
                                    @elseif($loop->iteration == 2)
                                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-gradient-to-r from-slate-300 to-slate-400 text-[10px] font-bold text-white shadow-sm ring-2 ring-slate-200">2</span>
                                    @elseif($loop->iteration == 3)
                                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-gradient-to-r from-amber-600 to-amber-700 text-[10px] font-bold text-white shadow-sm ring-2 ring-amber-400">3</span>
                                    @else
                                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-gray-100 text-[10px] font-bold text-gray-500">4</span>
                                    @endif
                                </div>
                                <div class="h-8 w-8 rounded-lg bg-red-50 text-red-700 flex items-center justify-center text-xs font-bold font-mono">
                                    {{ substr($res->user->name, 0, 1) }}
                                </div>
                                <div>
                                    <span class="font-bold text-gray-900">{{ $res->user->name }}</span>
                                    <div class="text-[10px] text-gray-400 font-mono">{{ $res->user->employee_number }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="px-3 py-1 bg-red-50 text-red-700 border border-red-200 rounded-full font-bold text-xs">
                                    {{ $res->late_count }} times
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="flex flex-col items-center justify-center py-16 px-4 text-center">
                <div class="h-16 w-16 bg-green-50 rounded-2xl flex items-center justify-center mb-4 text-green-500 border border-green-100">
                    <i class="fas fa-check-circle text-2xl"></i>
                </div>
                <h4 class="font-bold text-gray-800 text-sm">Perfect Punctuality</h4>
                <p class="text-xs text-gray-400 max-w-sm mt-1">Excellent! No late arrivals recorded this week.</p>
            </div>
            @endif
        </div>

        <!-- Weekly Early Tab Content -->
        <div id="tabContent-weekly-early" class="tab-content hidden">
            @if(count($topEarlySignOutsWeekly) > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50/50 text-[10px] font-black text-gray-400 uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-3">Rank / User</th>
                            <th class="px-6 py-3 text-right">Early Leaves This Week</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($topEarlySignOutsWeekly as $res)
                        <tr class="hover:bg-gray-50/30 transition-colors">
                            <td class="px-6 py-4 flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    @if($loop->iteration == 1)
                                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-gradient-to-r from-yellow-400 to-amber-500 text-[10px] font-bold text-white shadow-sm ring-2 ring-yellow-200">1</span>
                                    @elseif($loop->iteration == 2)
                                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-gradient-to-r from-slate-300 to-slate-400 text-[10px] font-bold text-white shadow-sm ring-2 ring-slate-200">2</span>
                                    @elseif($loop->iteration == 3)
                                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-gradient-to-r from-amber-600 to-amber-700 text-[10px] font-bold text-white shadow-sm ring-2 ring-amber-400">3</span>
                                    @else
                                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-gray-100 text-[10px] font-bold text-gray-500">4</span>
                                    @endif
                                </div>
                                <div class="h-8 w-8 rounded-lg bg-orange-50 text-orange-800 flex items-center justify-center text-xs font-bold font-mono">
                                    {{ substr($res->user->name, 0, 1) }}
                                </div>
                                <div>
                                    <span class="font-bold text-gray-900">{{ $res->user->name }}</span>
                                    <div class="text-[10px] text-gray-400 font-mono">{{ $res->user->employee_number }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="px-3 py-1 bg-orange-50 text-orange-700 border border-orange-200 rounded-full font-bold text-xs">
                                    {{ $res->early_count }} times
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="flex flex-col items-center justify-center py-16 px-4 text-center">
                <div class="h-16 w-16 bg-green-50 rounded-2xl flex items-center justify-center mb-4 text-green-500 border border-green-100">
                    <i class="fas fa-check-circle text-2xl"></i>
                </div>
                <h4 class="font-bold text-gray-800 text-sm">Full Shifts Completed</h4>
                <p class="text-xs text-gray-400 max-w-sm mt-1">Excellent! No early clock-outs registered this week.</p>
            </div>
            @endif
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
                    @foreach($monthlyDetails as $data)
                    <tr class="hover:bg-emerald-50/30 transition-colors cursor-pointer" onclick="window.location='{{ route('admin.staff.show', $data['user']->uuid) }}'">
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
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function switchTab(tabId) {
        // Hide all tab contents
        $('.tab-content').addClass('hidden');
        // Show selected tab content
        $('#tabContent-' + tabId).removeClass('hidden');

        // Reset tab buttons classes
        $('.tab-btn').removeClass('bg-white shadow-sm text-gray-900').addClass('text-gray-500 hover:text-gray-900');

        // Set active tab button classes
        $('#tabBtn-' + tabId).removeClass('text-gray-500 hover:text-gray-900').addClass('bg-white shadow-sm text-gray-900');
    }

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
                },
                "emptyTable": "No attendance data found for this month."
            },
            "dom": '<"admin-dt-toolbar"lf>rt<"admin-dt-footer"ip>'
        });
    });
</script>
@endpush


