@extends('admin.layout')

@section('title', 'Scores & Grades')
@section('page-title', 'Performance Analytics')

@push('styles')
<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        box-shadow: 0 8px 32px rgba(31, 38, 135, 0.07);
    }
    .mesh-bg {
        background-color: #f8fafc;
        background-image: 
            radial-gradient(at 0% 0%, rgba(37, 99, 235, 0.05) 0px, transparent 50%),
            radial-gradient(at 100% 0%, rgba(124, 58, 237, 0.05) 0px, transparent 50%);
    }
    .rank-badge {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 0.9rem;
    }
    .rank-1 { background: linear-gradient(135deg, #fbbf24, #d97706); color: white; box-shadow: 0 4px 12px rgba(217, 119, 6, 0.2); }
    .rank-2 { background: linear-gradient(135deg, #94a3b8, #475569); color: white; box-shadow: 0 4px 12px rgba(71, 85, 105, 0.2); }
    .rank-3 { background: linear-gradient(135deg, #b45309, #78350f); color: white; box-shadow: 0 4px 12px rgba(120, 53, 15, 0.2); }
    .rank-norm { background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; }
</style>
@endpush

@section('content')
<div class="space-y-8 mesh-bg min-h-full -m-6 p-6">

    {{-- Header Control Bar --}}
    <div class="glass-card rounded-2xl p-6 border border-gray-200">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div>
                <h3 class="text-xl font-bold text-gray-900 tracking-tight">Performance Leaderboard</h3>
                <p class="text-sm text-gray-500 mt-1">
                    Showing results for 
                    <span class="font-semibold text-emerald-600">
                        @if($periodType === 'weekly')
                            Week of {{ $periodStart->format('M d') }} - {{ $periodEnd->format('M d, Y') }}
                        @else
                            {{ $periodStart->format('F Y') }}
                        @endif
                    </span>
                </p>
            </div>

            <form method="GET" action="{{ route('admin.scores.index') }}" class="flex flex-wrap items-center gap-3">
                <div class="flex bg-gray-100 p-1 rounded-xl border border-gray-200">
                    <button type="submit" name="period" value="weekly" 
                        class="px-4 py-1.5 rounded-lg text-xs font-bold transition-all {{ $periodType === 'weekly' ? 'bg-white text-emerald-600 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                        WEEKLY
                    </button>
                    <button type="submit" name="period" value="monthly" 
                        class="px-4 py-1.5 rounded-lg text-xs font-bold transition-all {{ $periodType === 'monthly' ? 'bg-white text-emerald-600 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                        MONTHLY
                    </button>
                </div>

                <div class="relative">
                    <input type="date" name="date" value="{{ $date->toDateString() }}" onchange="this.form.submit()"
                        class="bg-white border border-gray-200 rounded-xl px-4 py-2 text-sm font-medium focus:ring-2 focus:ring-emerald-500 transition-all outline-none">
                </div>

                <a href="{{ route('admin.scores.export', ['period' => $periodType, 'date' => $date->toDateString()]) }}"
                    class="bg-white border border-gray-200 hover:border-emerald-500 hover:text-emerald-600 text-gray-700 font-bold py-2 px-4 rounded-xl text-sm transition-all flex items-center gap-2">
                    <i class="fas fa-file-export shadow-sm"></i>
                    <span>Export</span>
                </a>
            </form>
        </div>
    </div>

    {{-- Scoring Legend (Modern Pill style) --}}
    <div class="flex flex-wrap items-center gap-4 py-2">
        <span class="text-[10px] uppercase tracking-widest font-black text-gray-400 mr-2">Scoring Guide:</span>
        <div class="flex flex-wrap gap-2">
            <div class="flex items-center gap-1.5 px-3 py-1 bg-white border border-gray-100 rounded-full shadow-sm text-[11px] font-bold">
                <i class="fas fa-bolt text-amber-500"></i> Early <span class="text-green-600">+10</span>
            </div>
            <div class="flex items-center gap-1.5 px-3 py-1 bg-white border border-gray-100 rounded-full shadow-sm text-[11px] font-bold">
                <i class="fas fa-check text-emerald-500"></i> Punctual <span class="text-green-600">+10</span>
            </div>
            <div class="flex items-center gap-1.5 px-3 py-1 bg-white border border-gray-100 rounded-full shadow-sm text-[11px] font-bold">
                <i class="fas fa-clock text-amber-400"></i> On Time <span class="text-amber-600">-1</span>
            </div>
            <div class="flex items-center gap-1.5 px-3 py-1 bg-white border border-gray-100 rounded-full shadow-sm text-[11px] font-bold">
                <i class="fas fa-hourglass-half text-orange-400"></i> Grace <span class="text-orange-600">-2</span>
            </div>
            <div class="flex items-center gap-1.5 px-3 py-1 bg-white border border-gray-100 rounded-full shadow-sm text-[11px] font-bold">
                <i class="fas fa-exclamation-triangle text-red-500"></i> Late <span class="text-red-700">-5</span>
            </div>
        </div>
    </div>

    {{-- Main Leaderboard --}}
    <div class="glass-card rounded-2xl border border-gray-200 shadow-xl overflow-hidden">
        @if($summaries->isEmpty())
            <div class="p-24 text-center">
                <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 border border-gray-100">
                    <i class="fas fa-chart-line text-3xl text-gray-200"></i>
                </div>
                <h4 class="text-gray-900 font-bold">No Data Captured</h4>
                <p class="text-sm text-gray-400 mt-1">Scores will populate as attendance logs arrive.</p>
            </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-left border-b border-gray-100">
                        <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Rank</th>
                        <th class="px-6 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Grade</th>
                        <th class="px-6 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Employee</th>
                        <th class="px-6 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Score %</th>
                        <th class="px-6 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Points</th>
                        <th class="px-6 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Engagement</th>
                        <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($summaries as $i => $s)
                    <tr class="group hover:bg-emerald-50/30 transition-all">
                        <td class="px-8 py-5">
                            <div class="rank-badge {{ $i==0 ? 'rank-1' : ($i==1 ? 'rank-2' : ($i==2 ? 'rank-3' : 'rank-norm')) }}">
                                {{ $i + 1 }}
                            </div>
                        </td>
                        <td class="px-6 py-5">
                            <div class="flex flex-col items-center">
                                <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl text-base font-black border-2 transition-transform group-hover:scale-110 {{ $s->gradeColor() }}">
                                    {{ $s->grade }}
                                </span>
                                <span class="text-[9px] font-bold text-gray-400 mt-1 uppercase tracking-tighter">{{ $s->gradeLabel() }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-5">
                            <div class="flex items-center gap-4">
                                <div class="h-10 w-10 rounded-xl bg-gradient-to-br from-blue-600 to-indigo-700 flex items-center justify-center text-white text-sm font-bold shadow-md">
                                    {{ substr($s->user->name ?? 'U', 0, 1) }}
                                </div>
                                <div class="flex flex-col">
                                    <span class="font-bold text-gray-900 leading-tight">{{ $s->user->name ?? 'Unknown User' }}</span>
                                    <span class="text-xs font-medium text-gray-400 mt-0.5 tracking-wide">{{ $s->user->employee_number ?? 'ID: —' }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-5">
                            <div class="flex flex-col items-center w-24 mx-auto">
                                <span class="text-sm font-black text-gray-900">{{ number_format($s->score_percentage, 1) }}%</span>
                                <div class="w-full bg-gray-100 rounded-full h-1 mt-2 overflow-hidden border border-gray-200/50">
                                    <div class="h-full rounded-full transition-all duration-1000
                                        @if($s->score_percentage>=90) bg-emerald-500
                                        @elseif($s->score_percentage>=75) bg-emerald-500
                                        @elseif($s->score_percentage>=60) bg-amber-500
                                        @else bg-rose-500 @endif"
                                        style="width:{{ min(100,$s->score_percentage) }}%"></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-5 text-center">
                            <div class="inline-block px-3 py-1 bg-gray-50 rounded-lg border border-gray-100">
                                <span class="text-sm font-bold text-gray-800">{{ $s->total_score }}</span>
                                <span class="text-[10px] font-medium text-gray-300 ml-0.5">/{{ $s->max_possible_score }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-5">
                            <div class="flex items-center justify-center gap-3">
                                <div class="flex flex-col items-center">
                                    <span class="text-[10px] font-black text-emerald-600">{{ $s->days_present }}</span>
                                    <i class="fas fa-check-double text-[10px] text-emerald-400"></i>
                                </div>
                                <div class="w-px h-4 bg-gray-200"></div>
                                <div class="flex flex-col items-center">
                                    <span class="text-[10px] font-black {{ $s->days_absent > 0 ? 'text-rose-600' : 'text-gray-300' }}">{{ $s->days_absent }}</span>
                                    <i class="fas fa-user-slash text-[10px] {{ $s->days_absent > 0 ? 'text-rose-400' : 'text-gray-200' }}"></i>
                                </div>
                                <div class="w-px h-4 bg-gray-200"></div>
                                <div class="flex flex-col items-center">
                                    <span class="text-[10px] font-black {{ $s->late_count > 0 ? 'text-amber-600' : 'text-gray-300' }}">{{ $s->late_count }}</span>
                                    <i class="fas fa-running text-[10px] {{ $s->late_count > 0 ? 'text-amber-400' : 'text-gray-200' }}"></i>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-5 text-right">
                            <a href="{{ route('admin.scores.show', $s->user) }}" 
                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-gray-200 text-emerald-600 shadow-sm hover:border-emerald-500 hover:bg-emerald-600 hover:text-white transition-all transform hover:scale-110">
                                <i class="fas fa-arrow-right text-xs"></i>
                            </a>
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


