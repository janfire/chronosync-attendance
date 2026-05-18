@extends('admin.layout')

@section('title', $user->name . ' — Score Report')
@section('page-title', 'Performance Detail')

@push('styles')
<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        box-shadow: 0 8px 32px rgba(31, 38, 135, 0.05);
    }
    .profile-hero {
        background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
        position: relative;
        overflow: hidden;
    }
    .profile-hero::before {
        content: "";
        position: absolute;
        inset: 0;
        background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        opacity: 0.1;
    }
    .stat-pill {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(4px);
    }
</style>
@endpush

@section('content')
<div class="space-y-6">

    {{-- Breadcrumbs & Actions --}}
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.scores.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-gray-500 hover:text-emerald-600 transition-colors group">
            <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center group-hover:bg-emerald-50 transition-all">
                <i class="fas fa-chevron-left text-[10px]"></i>
            </div>
            <span>Back to Leaderboard</span>
        </a>

        <form method="GET" action="{{ route('admin.scores.show', $user) }}" class="flex items-center gap-2 bg-white p-1 rounded-xl border border-gray-200 shadow-sm">
            <select name="month" onchange="this.form.submit()" class="bg-transparent border-none text-xs font-bold text-gray-700 focus:ring-0 cursor-pointer px-3 pr-8">
                @foreach(range(1,12) as $m)
                    <option value="{{ $m }}" {{ $m===$month ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>
                @endforeach
            </select>
            <div class="w-px h-4 bg-gray-200"></div>
            <select name="year" onchange="this.form.submit()" class="bg-transparent border-none text-xs font-bold text-gray-700 focus:ring-0 cursor-pointer px-3 pr-8">
                @foreach(range(now()->year-1, now()->year+1) as $y)
                    <option value="{{ $y }}" {{ $y===$year ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </form>
    </div>

    {{-- Profile Hero Section --}}
    <div class="profile-hero rounded-3xl p-8 text-white shadow-2xl relative">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-8">
            <div class="flex items-center gap-6">
                <div class="w-24 h-24 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 flex items-center justify-center text-4xl font-black shadow-inner">
                    {{ substr($user->name, 0, 1) }}
                </div>
                <div>
                    <h2 class="text-3xl font-black tracking-tight">{{ $user->name }}</h2>
                    <div class="flex items-center gap-3 mt-2">
                        <span class="px-3 py-1 bg-emerald-400/20 border border-emerald-400/30 rounded-lg text-xs font-bold tracking-widest uppercase">{{ $user->employee_number }}</span>
                        <span class="text-white/60 text-sm font-medium">{{ $user->email }}</span>
                    </div>
                </div>
            </div>

            @if($monthlySummary)
            <div class="flex items-center gap-6 bg-white/5 rounded-2xl p-4 border border-white/10 backdrop-blur-sm">
                <div class="flex flex-col items-center">
                    <span class="text-[10px] uppercase font-black tracking-widest text-white/40 mb-1">M-Grade</span>
                    <div class="w-16 h-16 rounded-xl bg-white flex items-center justify-center text-3xl font-black {{ $monthlySummary->gradeColor() }} shadow-lg ring-4 ring-white/10">
                        {{ $monthlySummary->grade }}
                    </div>
                </div>
                <div class="h-12 w-px bg-white/10"></div>
                <div>
                    <p class="text-2xl font-black">{{ number_format($monthlySummary->score_percentage, 1) }}%</p>
                    <p class="text-[10px] uppercase font-black tracking-widest text-white/40 mt-1">{{ $monthlySummary->gradeLabel() }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Detailed Stats Grid --}}
    @if($monthlySummary)
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="glass-card rounded-2xl p-6 flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <span class="text-[10px] font-black uppercase tracking-widest text-gray-400">Total Score</span>
                <i class="fas fa-chart-line text-emerald-500 bg-emerald-50 p-2 rounded-lg"></i>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-4xl font-black text-gray-900">{{ $monthlySummary->total_score }}</span>
                <span class="text-sm font-bold text-gray-300">/{{ $monthlySummary->max_possible_score }} pts</span>
            </div>
            <div class="mt-auto pt-6">
                <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden border border-gray-100">
                    <div class="h-full rounded-full transition-all duration-1000
                        @if($monthlySummary->score_percentage>=90) bg-emerald-500
                        @elseif($monthlySummary->score_percentage>=75) bg-emerald-500
                        @elseif($monthlySummary->score_percentage>=60) bg-amber-500
                        @else bg-rose-500 @endif"
                        style="width:{{ min(100,$monthlySummary->score_percentage) }}%"></div>
                </div>
                <p class="text-[10px] font-bold text-gray-400 mt-2 uppercase">Completion based on attendance targets</p>
            </div>
        </div>

        <div class="glass-card rounded-2xl p-6">
            <span class="text-[10px] font-black uppercase tracking-widest text-gray-400">Attendance Distribution</span>
            <div class="grid grid-cols-2 gap-4 mt-6">
                <div class="p-4 bg-emerald-50 rounded-2xl border border-emerald-100 flex flex-col items-center">
                    <span class="text-2xl font-black text-emerald-700">{{ $monthlySummary->days_present }}</span>
                    <span class="text-[10px] font-bold text-emerald-600 uppercase mt-1">Present</span>
                    <i class="fas fa-check-circle text-emerald-200 mt-2"></i>
                </div>
                <div class="p-4 bg-rose-50 rounded-2xl border border-rose-100 flex flex-col items-center">
                    <span class="text-2xl font-black text-rose-700">{{ $monthlySummary->days_absent }}</span>
                    <span class="text-[10px] font-bold text-rose-600 uppercase mt-1">Absent</span>
                    <i class="fas fa-times-circle text-rose-200 mt-2"></i>
                </div>
            </div>
        </div>

        <div class="glass-card rounded-2xl p-6">
            <span class="text-[10px] font-black uppercase tracking-widest text-gray-400">Punctuality Analytics</span>
            <div class="space-y-3 mt-4">
                <div class="flex items-center justify-between p-2 hover:bg-gray-50 rounded-lg transition-colors">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-star text-amber-500 text-xs"></i>
                        <span class="text-xs font-bold text-gray-600 uppercase">Early Arrival</span>
                    </div>
                    <span class="text-sm font-black text-emerald-600">{{ $monthlySummary->early_count }}</span>
                </div>
                <div class="flex items-center justify-between p-2 hover:bg-gray-50 rounded-lg transition-colors">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-check-circle text-emerald-500 text-xs"></i>
                        <span class="text-xs font-bold text-gray-600 uppercase">Punctual</span>
                    </div>
                    <span class="text-sm font-black text-emerald-600">{{ $monthlySummary->punctual_count }}</span>
                </div>
                <div class="flex items-center justify-between p-2 hover:bg-gray-50 rounded-lg transition-colors text-gray-400">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-exclamation-circle text-red-500 text-xs"></i>
                        <span class="text-xs font-bold uppercase">Late Arrival</span>
                    </div>
                    <span class="text-sm font-black text-rose-600">{{ $monthlySummary->late_count }}</span>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Weekly Progression --}}
    @if($weeklySummaries->count() > 0)
    <div class="glass-card rounded-3xl overflow-hidden">
        <div class="px-8 py-5 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-sm font-black text-gray-900 uppercase tracking-widest">Weekly Progression</h3>
            <i class="fas fa-wave-square text-blue-200"></i>
        </div>
        <div class="overflow-x-auto text-sm">
            <table class="w-full">
                <thead class="bg-gray-50/50">
                    <tr class="text-left text-[10px] tracking-widest font-black text-gray-400 uppercase">
                        <th class="px-8 py-4">Week Window</th>
                        <th class="px-6 py-4 text-center">Grade</th>
                        <th class="px-6 py-4 text-center">Efficiency</th>
                        <th class="px-6 py-4 text-center">Points</th>
                        <th class="px-6 py-4 text-center">Present</th>
                        <th class="px-6 py-4 text-center">Late</th>
                        <th class="px-8 py-4 text-right">Early Dep.</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($weeklySummaries as $ws)
                    <tr class="hover:bg-emerald-50/20 transition-colors">
                        <td class="px-8 py-4 font-bold text-gray-700">
                            {{ $ws->period_start->format('d M') }} – {{ $ws->period_end->format('d M, Y') }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex justify-center">
                                <span class="w-8 h-8 rounded-lg flex items-center justify-center font-black border {{ $ws->gradeColor() }} bg-white shadow-sm ring-2 ring-transparent transition-all hover:ring-blue-100">
                                    {{ $ws->grade }}
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="font-black text-gray-900">{{ number_format($ws->score_percentage,1) }}%</span>
                        </td>
                        <td class="px-6 py-4 text-center text-gray-500 font-medium">
                            <span class="text-gray-900 font-bold">{{ $ws->total_score }}</span>/{{ $ws->max_possible_score }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="text-emerald-600 font-black">{{ $ws->days_present }}</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="font-black {{ $ws->late_count > 0 ? 'text-rose-600' : 'text-gray-300' }}">{{ $ws->late_count }}</span>
                        </td>
                        <td class="px-8 py-4 text-right">
                            <span class="font-black {{ $ws->early_departure_count > 0 ? 'text-amber-600' : 'text-gray-300' }}">{{ $ws->early_departure_count }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Daily Activity Log --}}
    <div class="glass-card rounded-3xl overflow-hidden shadow-2xl">
        <div class="px-8 py-6 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-sm font-black text-gray-900 uppercase tracking-widest">Daily Activity Log</h3>
            <span class="px-4 py-1.5 bg-gray-100 rounded-full text-[10px] font-black text-gray-500">{{ \Carbon\Carbon::create()->month($month)->format('F Y') }}</span>
        </div>
        
        @if($dailyScores->isEmpty())
            <div class="p-20 text-center text-gray-300">
                <i class="fas fa-calendar-alt text-5xl mb-4 opacity-20"></i>
                <p class="font-bold">No activity recorded for this period</p>
            </div>
        @else
        <div class="overflow-x-auto shadow-inner">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-[10px] tracking-widest font-black text-gray-400 uppercase border-b border-gray-50">
                        <th class="px-8 py-5">Date</th>
                        <th class="px-6 py-5 text-center">Clock In</th>
                        <th class="px-6 py-5">Verification</th>
                        <th class="px-6 py-5 text-center">Points</th>
                        <th class="px-6 py-5 text-center">Clock Out</th>
                        <th class="px-6 py-5">Verification</th>
                        <th class="px-6 py-5 text-center">Points</th>
                        <th class="px-8 py-5 text-right">Performance</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($dailyScores as $score)
                    <tr class="hover:bg-gray-50 transition-colors group">
                        <td class="px-8 py-5">
                            <div class="flex flex-col">
                                <span class="font-black text-gray-900">{{ $score->attendance_date->format('D, d M') }}</span>
                                <span class="text-[10px] text-gray-400 font-bold uppercase tracking-tight">Timeline Entry</span>
                            </div>
                        </td>
                        <td class="px-6 py-5 text-center font-black font-mono text-gray-600">
                            {{ $score->clock_in_time ? \Carbon\Carbon::parse($score->clock_in_time)->format('H:i') : '— —' }}
                        </td>
                        <td class="px-6 py-5">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase ring-1 ring-inset {{ $score->clockInStatusColor() }} transition-all group-hover:shadow-sm">
                                <i class="fas {{ $score->clock_in_points >= 5 ? 'fa-star text-[8px]' : ($score->clock_in_points >= 0 ? 'fa-check text-[8px]' : 'fa-exclamation text-[8px]') }}"></i>
                                {{ $score->clockInStatusLabel() }}
                            </span>
                        </td>
                        <td class="px-6 py-5 text-center font-black {{ $score->clock_in_points >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ $score->clock_in_points > 0 ? '+' : '' }}{{ $score->clock_in_points }}
                        </td>
                        <td class="px-6 py-5 text-center font-black font-mono text-gray-600">
                            {{ $score->clock_out_time ? \Carbon\Carbon::parse($score->clock_out_time)->format('H:i') : '— —' }}
                        </td>
                        <td class="px-6 py-5">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase ring-1 ring-inset {{ $score->clockOutStatusColor() }} transition-all group-hover:shadow-sm">
                                <i class="fas {{ $score->clock_out_points >= 5 ? 'fa-star text-[8px]' : ($score->clock_out_points >= 0 ? 'fa-check text-[8px]' : 'fa-exclamation text-[8px]') }}"></i>
                                {{ $score->clockOutStatusLabel() }}
                            </span>
                        </td>
                        <td class="px-6 py-5 text-center font-black {{ $score->clock_out_points >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ $score->clock_out_points > 0 ? '+' : '' }}{{ $score->clock_out_points }}
                        </td>
                        <td class="px-8 py-5 text-right">
                            <div class="inline-flex flex-col items-end">
                                <span class="text-sm font-black {{ $score->total_points >= 10 ? 'text-emerald-600' : ($score->total_points >= 5 ? 'text-emerald-600' : ($score->total_points >= 0 ? 'text-amber-600' : 'text-rose-600')) }}">
                                    {{ $score->total_points > 0 ? '+' : '' }}{{ $score->total_points }}
                                </span>
                                <div class="w-12 h-1 bg-gray-100 rounded-full mt-1 overflow-hidden">
                                    <div class="h-full rounded-full {{ $score->total_points >= 10 ? 'bg-emerald-400' : ($score->total_points >= 0 ? 'bg-emerald-400' : 'bg-rose-400') }}" 
                                         style="width: {{ min(100, max(0, ($score->total_points + 5) * 6)) }}%"></div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

</div>
@endsection


