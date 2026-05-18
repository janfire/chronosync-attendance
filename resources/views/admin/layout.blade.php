<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard') - ChronoSync Attendance System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; 
        }
        .sidebar-item:hover { 
            background-color: rgba(59, 130, 246, 0.08); 
        }
        /* DataTables Global Styling */
        .dataTables_wrapper .dataTables_length select {
            border-radius: 0.5rem;
            border-color: #e5e7eb;
            padding-left: 0.75rem;
            padding-right: 2rem;
            padding-top: 0.25rem;
            padding-bottom: 0.25rem;
        }
        .dataTables_wrapper .dataTables_filter input {
            border-radius: 0.5rem;
            border-color: #e5e7eb;
            padding: 0.25rem 0.5rem;
            margin-left: 0.5rem;
        }
        table.dataTable.no-footer {
            border-bottom: 1px solid #e5e7eb;
        }
        .sidebar-item.active { 
            background-color: rgba(59, 130, 246, 0.12); 
            border-left: 3px solid #2563eb; 
            font-weight: 600;
        }
        .sidebar-item.active i {
            color: #2563eb;
        }
        .stat-card {
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
    </style>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    @stack('styles')
</head>
<body class="bg-gray-50">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-64 bg-white border-r border-gray-200 flex flex-col">
            <!-- Logo Section -->
            <div class="p-5 border-b border-gray-200 bg-gradient-to-br from-blue-50 to-indigo-50">
                <div class="flex items-center space-x-3">
                    <div class="relative">
                        <div class="h-12 w-12 bg-emerald-500 rounded-xl flex items-center justify-center text-white shadow-lg shrink-0"><i class="fas fa-clock text-2xl"></i></div>
                    </div>
                    <div>
                        <h1 class="text-lg font-bold text-gray-900">ChronoSync</h1>
                        <p class="text-xs text-gray-600 font-medium">Attendance System</p>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
                <a href="{{ route('admin.dashboard') }}" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 transition-colors {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-chart-line w-5 text-gray-500"></i>
                    <span class="font-medium">Dashboard</span>
                </a>
                <a href="{{ route('admin.users.index') }}" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 transition-colors {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <i class="fas fa-users-cog w-5 text-gray-500"></i>
                    <span class="font-medium">Staff Management</span>
                </a>
                <a href="{{ route('admin.insights') }}" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 transition-colors {{ request()->routeIs('admin.insights') ? 'active' : '' }}">
                    <i class="fas fa-chart-pie w-5 text-gray-500"></i>
                    <span class="font-medium">Analytics</span>
                </a>
                <a href="{{ route('admin.reports.index') }}" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 transition-colors {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                    <i class="fas fa-file-contract w-5 text-gray-500"></i>
                    <span class="font-medium">Advanced Reports</span>
                </a>
                <a href="{{ route('admin.scores.index') }}" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 transition-colors {{ request()->routeIs('admin.scores.*') ? 'active' : '' }}">
                    <i class="fas fa-star w-5 text-gray-500"></i>
                    <span class="font-medium">Scores & Grades</span>
                </a>
                <a href="{{ route('attendance.qr') }}" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 transition-colors {{ request()->routeIs('attendance.qr') ? 'active' : '' }}">
                    <i class="fas fa-qrcode w-5 text-gray-500"></i>
                    <span class="font-medium">QR Code</span>
                </a>

                <div class="pt-4 pb-2 px-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest">SaaS & Billing</div>
                <a href="{{ route('billing.index') }}" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 transition-colors {{ request()->routeIs('billing.*') ? 'active' : '' }}">
                    <i class="fas fa-credit-card w-5 text-gray-500"></i>
                    <span class="font-medium">My Subscription</span>
                </a>

                @if(Auth::user()->email === 'admin@zou.ac.zw') {{-- Temporary check for super-admin --}}
                <a href="{{ route('superadmin.finance.pending') }}" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 transition-colors {{ request()->routeIs('superadmin.finance.*') ? 'active' : '' }}">
                    <i class="fas fa-vault w-5 text-gray-500"></i>
                    <span class="font-medium">Finance Ops</span>
                </a>
                @endif
            </nav>

            <!-- User Section -->
            <div class="p-4 border-t border-gray-200">
                <a href="{{ route('profile.show') }}" class="flex items-center space-x-3 px-2 py-2 hover:bg-gray-50 rounded-lg transition-colors mb-2">
                    <div class="h-10 w-10 rounded-full bg-emerald-600 flex items-center justify-center text-white font-semibold">
                        {{ substr(Auth::user()->name ?? 'A', 0, 1) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ Auth::user()->name ?? 'Admin' }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email ?? '' }}</p>
                    </div>
                </a>
                <a href="{{ route('profile.show') }}" class="w-full flex items-center space-x-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg transition-colors mb-2">
                    <i class="fas fa-user-circle"></i>
                    <span>My Profile</span>
                </a>
                <form method="POST" action="{{ route('logout') }}" class="mt-1">
                    @csrf
                    <button type="submit" class="w-full flex items-center space-x-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Bar -->
            <header class="bg-white border-b border-gray-200 px-6 py-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-semibold text-gray-900">@yield('page-title', 'Dashboard')</h2>
                        <p class="text-sm text-gray-500 mt-1">{{ now()->format('l, F j, Y') }}</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="text-right">
                            <p class="text-sm font-medium text-gray-900">{{ now()->format('g:i A') }}</p>
                            <p class="text-xs text-gray-500">Current Time</p>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-6">
                @if(session('success'))
                    <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    @stack('scripts')
</body>
</html>



