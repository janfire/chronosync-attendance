@extends('admin.layout')

@section('title', 'Analytics')
@section('page-title', 'HR Analytics')

@section('content')
    <!-- Dashboard Header & Filters -->
    <div class="mb-8">
        <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-white/20 dark:border-gray-700/50 p-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-xl font-bold text-slate-800 dark:text-white tracking-tight">Performance Overview</h2>
                    <p class="text-sm text-slate-500 font-medium mt-1">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-600 text-xs font-bold border border-emerald-100">
                            {{ \Carbon\Carbon::parse($date)->format('F j, Y') }}
                        </span>
                    </p>
                </div>
                
                @if(request()->anyFilled(['user_id', 'date', 'action']))
                    <a href="{{ route('admin.insights') }}" class="group flex items-center text-sm font-semibold text-slate-500 hover:text-red-500 transition-colors bg-slate-50 hover:bg-red-50 px-4 py-2 rounded-xl transition-all">
                        <i class="fas fa-times-circle mr-2 group-hover:rotate-90 transition-transform duration-300"></i> Clear Filters
                    </a>
                @endif
            </div>
            
            <form method="GET" action="{{ route('admin.insights') }}">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <!-- Employee Selection -->
                    <div class="md:col-span-4 lg:col-span-5 relative group">
                        <label class="absolute -top-2 left-3 bg-white dark:bg-gray-800 px-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider group-focus-within:text-emerald-600 transition-colors z-10">Employee</label>
                        <select name="user_id" onchange="this.form.submit()" class="w-full h-[46px] px-4 bg-slate-50/50 dark:bg-gray-900/50 border border-slate-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 text-sm font-medium text-slate-700 dark:text-gray-200 transition-all appearance-none cursor-pointer hover:bg-white dark:hover:bg-gray-900 hover:border-slate-300">
                            <option value="">Viewing Overall Team Data</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" {{ request('user_id') == $emp->id ? 'selected' : '' }}>
                                    {{ $emp->name }}
                                </option>
                            @endforeach
                        </select>
                        <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none text-xs"></i>
                    </div>

                    <!-- Date Selection -->
                    <div class="md:col-span-3 lg:col-span-3 relative group">
                        <label class="absolute -top-2 left-3 bg-white dark:bg-gray-800 px-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider group-focus-within:text-emerald-600 transition-colors z-10">Date</label>
                        <input type="date" name="date" onchange="this.form.submit()" value="{{ $date ?? today()->toDateString() }}" class="w-full h-[46px] px-4 bg-slate-50/50 dark:bg-gray-900/50 border border-slate-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 text-sm font-medium text-slate-700 dark:text-gray-200 transition-all hover:bg-white dark:hover:bg-gray-900 hover:border-slate-300">
                    </div>

                    <!-- Action Filter -->
                    <div class="md:col-span-3 lg:col-span-2 relative group">
                        <label class="absolute -top-2 left-3 bg-white dark:bg-gray-800 px-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider group-focus-within:text-emerald-600 transition-colors z-10">Type</label>
                        <select name="action" onchange="this.form.submit()" class="w-full h-[46px] px-4 bg-slate-50/50 dark:bg-gray-900/50 border border-slate-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 text-sm font-medium text-slate-700 dark:text-gray-200 transition-all appearance-none cursor-pointer hover:bg-white dark:hover:bg-gray-900 hover:border-slate-300">
                            <option value="">All Activity</option>
                            <option value="clock_in" {{ request('action') == 'clock_in' ? 'selected' : '' }}>Clock In</option>
                            <option value="clock_out" {{ request('action') == 'clock_out' ? 'selected' : '' }}>Clock Out</option>
                        </select>
                        <i class="fas fa-filter absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none text-xs"></i>
                    </div>
                    
                    <!-- Review Button (Desktop: Hidden if auto-submit works, but kept for accessibility/mobile) -->
                    <div class="md:col-span-2 lg:col-span-2 md:hidden">
                        <button type="submit" class="w-full h-[46px] bg-slate-900 text-white font-bold rounded-xl shadow-lg shadow-slate-900/20 hover:bg-slate-800 transition-all active:scale-95">
                            Update View
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if(isset($selectedEmployee) && $employeeStats)
        <!-- Premium Employee Card -->
        <div class="mb-10 relative overflow-hidden rounded-3xl bg-[#0F172A] shadow-2xl shadow-indigo-500/10 border border-slate-800 text-white p-8">
            <!-- Background Decoration -->
            <div class="absolute top-0 right-0 -mr-20 -mt-20 w-96 h-96 bg-indigo-500/20 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-80 h-80 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
            
            <div class="relative z-10">
                <div class="flex flex-col md:flex-row items-center md:items-start space-y-6 md:space-y-0 md:space-x-8 mb-8">
                    <!-- Avatar -->
                    <div class="relative group">
                        <div class="h-24 w-24 rounded-2xl bg-gradient-to-br from-indigo-500 to-blue-600 p-[2px] shadow-lg shadow-indigo-500/30">
                            <div class="h-full w-full rounded-2xl bg-[#0F172A] flex items-center justify-center text-3xl font-black text-white">
                                {{ substr($selectedEmployee->name, 0, 1) }}
                            </div>
                        </div>
                        <div class="absolute -bottom-2 -right-2 bg-emerald-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full border-4 border-[#0F172A]">ACTIVE</div>
                    </div>
                    
                    <!-- Info -->
                    <div class="text-center md:text-left flex-1">
                        <h2 class="text-3xl font-bold tracking-tight mb-2">{{ $selectedEmployee->name }}</h2>
                        <div class="flex flex-wrap justify-center md:justify-start gap-3 items-center text-slate-400 text-sm font-medium">
                            <span class="bg-slate-800/50 px-3 py-1 rounded-lg border border-slate-700/50">ID: {{ $selectedEmployee->employee_number }}</span>
                            <span class="w-1 h-1 rounded-full bg-slate-600"></span>
                            <span>{{ $selectedEmployee->role == 'admin' ? 'Administrator' : 'Staff Member' }}</span>
                            <span class="w-1 h-1 rounded-full bg-slate-600"></span>
                            <span class="text-indigo-400">{{ $selectedEmployee->email }}</span>
                        </div>
                    </div>
                    
                    <!-- Quick Action -->
                    <a href="{{ route('admin.staff.show', $selectedEmployee->id) }}" class="px-5 py-2.5 bg-white/10 hover:bg-white/20 border border-white/10 rounded-xl font-semibold text-sm transition-all flex items-center backdrop-blur-md">
                        View Profile <i class="fas fa-arrow-right ml-2 opacity-70"></i>
                    </a>
                </div>
                
                <!-- Stats Grid -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                    <div class="bg-slate-800/40 backdrop-blur-sm rounded-2xl p-5 border border-white/5 hover:border-white/10 transition-colors group">
                        <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-2">Avg Arrival</p>
                        <p class="text-2xl font-bold text-white group-hover:text-indigo-400 transition-colors">{{ $employeeStats['avg_arrival_time'] }}</p>
                    </div>
                    
                    <div class="bg-slate-800/40 backdrop-blur-sm rounded-2xl p-5 border border-white/5 hover:border-white/10 transition-colors group">
                        <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-2">Daily Avg</p>
                        <p class="text-2xl font-bold text-white group-hover:text-emerald-400 transition-colors">
                            {{ $employeeStats['avg_daily_hours'] }}<span class="text-sm text-slate-500 font-medium ml-1">hrs</span>
                        </p>
                    </div>
                    
                    <div class="bg-slate-800/40 backdrop-blur-sm rounded-2xl p-5 border border-white/5 hover:border-white/10 transition-colors group">
                        <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-2">Lateness</p>
                        <p class="text-2xl font-bold {{ $employeeStats['late_count'] > 0 ? 'text-rose-400' : 'text-emerald-400' }}">
                            {{ $employeeStats['late_count'] }}
                            <span class="text-xs text-slate-500 font-normal ml-1">this month</span>
                        </p>
                    </div>
                    
                    <div class="bg-slate-800/40 backdrop-blur-sm rounded-2xl p-5 border border-white/5 hover:border-white/10 transition-colors group">
                        <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-2">Total Hours</p>
                        <p class="text-2xl font-bold text-white group-hover:text-purple-400 transition-colors">
                            {{ $employeeStats['total_hours'] }}<span class="text-sm text-slate-500 font-medium ml-1">hrs</span>
                        </p>
                    </div>
                </div>

                <!-- Chart Area -->
                <div class="bg-slate-900/50 rounded-2xl p-5 border border-white/5">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-sm font-bold text-slate-300">Activity Trend</h4>
                        <span class="text-[10px] font-bold text-slate-500 uppercase bg-slate-800 px-2 py-1 rounded">Last 7 Days</span>
                    </div>
                    <div class="h-48 w-full">
                        <canvas id="employeeTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- Team Overview Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-10">
            <!-- Weekly Hours Chart -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100 dark:border-gray-700">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h4 class="text-lg font-bold text-slate-800 dark:text-white">Weekly Team Hours</h4>
                        <p class="text-sm text-slate-500 mt-1">Total hours worked per day this week</p>
                    </div>
                    <div class="h-10 w-10 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-600">
                        <i class="fas fa-chart-bar text-lg"></i>
                    </div>
                </div>
                <div class="h-64">
                    <canvas id="weeklyHoursChart"></canvas>
                </div>
            </div>

            <!-- Peak Traffic Chart -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100 dark:border-gray-700">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h4 class="text-lg font-bold text-slate-800 dark:text-white">Peak Clock-in Times</h4>
                        <p class="text-sm text-slate-500 mt-1">Busiest hours at the scanner</p>
                    </div>
                    <div class="h-10 w-10 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-600">
                        <i class="fas fa-clock text-lg"></i>
                    </div>
                </div>
                <div class="h-64">
                    <canvas id="trafficChart"></canvas>
                </div>
            </div>
        </div>
    @endif

    <!-- Detailed Logs Table -->
    <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100 dark:border-gray-700 overflow-hidden">
        <div class="px-8 py-6 border-b border-slate-100 dark:border-gray-700 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-50/30 dark:bg-gray-800/50">
            <div>
                <h3 class="text-lg font-bold text-slate-800 dark:text-white">Attendance Log</h3>
                <p class="text-sm text-slate-500 mt-1">Detailed breakdown of all clocking activity</p>
            </div>
            <button onclick="exportReports()" class="group flex items-center justify-center px-5 py-2.5 bg-white border border-slate-200 text-slate-700 hover:border-emerald-500 hover:text-emerald-600 rounded-xl text-sm font-bold transition-all shadow-sm hover:shadow-md">
                <i class="fas fa-file-csv mr-2 text-slate-400 group-hover:text-emerald-500 transition-colors"></i>
                Export CSV
            </button>
        </div>
        
        <div class="p-0">
            <table id="logsTable" class="w-full text-left admin-data-table display" style="width:100%">
                <thead class="bg-slate-50/50 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-8 py-5 text-xs font-bold text-slate-500 uppercase tracking-wider">Employee</th>
                        <th class="px-8 py-5 text-xs font-bold text-slate-500 uppercase tracking-wider">Type</th>
                        <th class="px-8 py-5 text-xs font-bold text-slate-500 uppercase tracking-wider">Timestamp</th>
                        <th class="px-8 py-5 text-xs font-bold text-slate-500 uppercase tracking-wider">Location & Meta</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-gray-700">
                    @foreach($logs as $log)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-gray-700/50 transition-colors group">
                            <td class="px-8 py-5">
                                <div class="flex items-center cursor-pointer" onclick="window.location='{{ route('admin.staff.show', $log->user_id) }}'">
                                    <div class="h-10 w-10 rounded-xl bg-gradient-to-br from-slate-100 to-slate-200 text-slate-600 font-bold flex items-center justify-center mr-4 shadow-sm border border-white group-hover:from-blue-50 group-hover:to-blue-100 group-hover:text-emerald-600 transition-all">
                                        {{ strtoupper(substr($log->user_name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-800 dark:text-gray-200 group-hover:text-emerald-600 transition-colors">{{ $log->user_name }}</div>
                                        <div class="text-xs text-slate-400 font-medium tracking-wide">ID: {{ $log->user->employee_number ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-5">
                                @if($log->action == 'clock_in')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-600 border border-emerald-100">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-2"></span>
                                        Clocked In
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-600 border border-rose-100">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500 mr-2"></span>
                                        Clocked Out
                                    </span>
                                @endif
                            </td>
                            <td class="px-8 py-5" data-order="{{ $log->timestamp->timestamp }}">
                                <div class="font-bold text-slate-700 dark:text-gray-200">{{ $log->timestamp->format('g:i A') }}</div>
                                <div class="text-xs text-slate-400 font-medium mt-0.5">{{ $log->timestamp->format('M j, Y') }}</div>
                            </td>
                            <td class="px-8 py-5">
                                <div class="flex flex-col gap-1">
                                    <div class="flex items-center text-xs font-medium text-slate-500">
                                        <i class="fas fa-map-marker-alt w-4 text-slate-400"></i>
                                        {{ $log->location_name ?? 'Main Campus' }}
                                    </div>
                                    @if($log->network_info)
                                    <div class="flex items-center text-[10px] font-bold text-slate-400 uppercase tracking-wide">
                                        <i class="fas fa-wifi w-4 text-slate-300"></i>
                                        {{ Str::limit($log->network_info, 20) }}
                                    </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    $(document).ready(function() {
        $('#logsTable').DataTable({
            "pageLength": 5,
            "lengthMenu": [[5, 15, 30, 50, -1], [5, 15, 30, 50, "All"]],
            "order": [[ 2, "desc" ]], // Order by Timestamp column
            "language": {
                "search": "",
                "searchPlaceholder": "Search logs...",
                "paginate": {
                    "previous": "<i class='fas fa-chevron-left'></i>",
                    "next": "<i class='fas fa-chevron-right'></i>"
                },
                "info": "Showing _START_ to _END_ of _TOTAL_ records"
            },
            "dom": '<"admin-dt-toolbar"lf>rt<"admin-dt-footer"ip>'
        });
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Chart.defaults.font.family = "'Plus Jakarta Sans', 'Inter', sans-serif";
        Chart.defaults.color = '#64748b';
        
        const chartDefaults = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { 
                y: { 
                    beginAtZero: true,
                    grid: { color: 'rgba(100, 116, 139, 0.1)', drawBorder: false },
                    ticks: { padding: 10 }
                },
                x: {
                    grid: { display: false },
                    ticks: { padding: 10 }
                }
            },
            layout: { padding: 0 }
        };

        @if(isset($selectedEmployee) && $employeeStats)
            // Premium Employee Trend Chart
            const empData = @json($employeeStats['daily_trend']);
            
            // Custom Gradient for bars
            const ctx = document.getElementById('employeeTrendChart').getContext('2d');
            const gradient = ctx.createLinearGradient(0, 0, 0, 400);
            gradient.addColorStop(0, '#6366f1'); // Indigo 500
            gradient.addColorStop(1, '#3b82f6'); // Blue 500

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: Object.keys(empData),
                    datasets: [{
                        data: Object.values(empData),
                        backgroundColor: gradient,
                        borderRadius: 8,
                        barThickness: 24,
                        hoverBackgroundColor: '#818cf8'
                    }]
                },
                options: {
                    ...chartDefaults,
                    scales: {
                        y: { 
                            ...chartDefaults.scales.y,
                            grid: { color: 'rgba(255, 255, 255, 0.05)' },
                            ticks: { color: '#94a3b8' }
                        },
                        x: { 
                            ...chartDefaults.scales.x,
                            ticks: { color: '#94a3b8' }
                        }
                    }
                }
            });
        @else
            // Team Weekly Hours
            const weeklyData = @json($teamStats['weekly_hours_trend']);
            new Chart(document.getElementById('weeklyHoursChart').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: Object.keys(weeklyData),
                    datasets: [{
                        data: Object.values(weeklyData),
                        backgroundColor: '#3b82f6',
                        borderRadius: 6,
                        barThickness: 20
                    }]
                },
                options: chartDefaults
            });

            // Peak Traffic - Smooth Area Chart
            const trafficData = @json($teamStats['peak_traffic']);
            const trafficCtx = document.getElementById('trafficChart').getContext('2d');
            const trafficGradient = trafficCtx.createLinearGradient(0, 0, 0, 300);
            trafficGradient.addColorStop(0, 'rgba(16, 185, 129, 0.2)');
            trafficGradient.addColorStop(1, 'rgba(16, 185, 129, 0)');

            new Chart(trafficCtx, {
                type: 'line',
                data: {
                    labels: Object.keys(trafficData).map(h => h + ':00'),
                    datasets: [{
                        data: Object.values(trafficData),
                        borderColor: '#10b981',
                        borderWidth: 3,
                        backgroundColor: trafficGradient,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 0,
                        pointHoverRadius: 6
                    }]
                },
                options: chartDefaults
            });
        @endif
    });

    function exportReports() {
        let csv = 'Employee,ID,Action,Date,Time,Location,Network\n';
        const rows = document.querySelectorAll('#logsTable tbody tr'); // Select rows from specific table ID
        
        rows.forEach(row => {
            // Skip empty/loading rows
            if(row.cells.length < 4) return;
            
            // Safer robust selectors that won't break if classes change slightly
            const employeeCell = row.cells[0]; 
            const typeCell = row.cells[1];
            const timeCell = row.cells[2];
            const locCell = row.cells[3];

            // Extract with fallbacks
            const employee = employeeCell.querySelector('.font-bold')?.textContent.trim() || 'Unknown';
            const id = employeeCell.innerText.match(/ID:\s*([^\s]+)/)?.[1] || '';
            const action = typeCell.textContent.trim().replace(/\s+/g, ' ');
            
            // Time logic might need adjusting depending on exact HTML structure
            const timeRaw = timeCell.innerText.split('\n');
            const time = timeRaw[0] || '';
            const date = timeRaw[1] || '';
            
            const location = locCell.innerText.split('\n')[0] || '';
            const network = locCell.innerText.split('\n')[1] || '';
            
            csv += `"${employee}","${id}","${action}","${date}","${time}","${location.trim()}","${network.trim()}"\n`;
        });
        
        const blob = new Blob([csv], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'analytics_export_' + new Date().toISOString().split('T')[0] + '.csv';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    }
</script>
@endpush


