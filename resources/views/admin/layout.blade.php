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
        .sidebar-item { 
            font-size: 0.82rem;
        }
        .sidebar-item:hover { 
            background-color: rgba(59, 130, 246, 0.08); 
        }        #sidebar.sidebar-closed {
            display: none !important;
        }
        #sidebar.sidebar-open {
            display: flex !important;
        }

        /* Compact sidebar at medium screens */
        @media (min-width: 768px) {
            #sidebar.compact {
                width: 4.5rem;
            }
            #sidebar.compact .sidebar-item {
                justify-content: center;
                padding-left: 0.75rem;
                padding-right: 0.75rem;
            }
            #sidebar.compact .sidebar-item .font-medium { display: none; }
            #sidebar.compact .sidebar-action { display: none; }
            #sidebar.compact .text-xs { display: none; }
            #sidebar.compact .h-12.w-12 { width: 2.25rem; height: 2.25rem; }
        }

        /* Admin tables & DataTables */
        .admin-table-scroll {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .users-table-card .dataTables_wrapper {
            width: 100%;
        }

        .users-table-card .admin-dt-toolbar,
        .users-table-card .admin-dt-footer {
            margin: 0;
        }

        .admin-table-checkbox {
            width: 1rem;
            height: 1rem;
            border-radius: 0.25rem;
            border: 1px solid #d1d5db;
            accent-color: #059669;
            cursor: pointer;
        }

        table.admin-data-table th.col-select,
        table.admin-data-table td.col-select {
            width: 2.75rem;
            padding-left: 1rem;
            padding-right: 0.5rem;
            text-align: center;
        }

        table.admin-data-table th.col-actions,
        table.admin-data-table td.col-actions {
            min-width: 9rem;
            padding-right: 1.5rem;
            white-space: nowrap;
        }

        table.admin-data-table {
            border-collapse: separate;
            border-spacing: 0;
            width: 100% !important;
        }

        table.admin-data-table thead th {
            position: sticky;
            top: 0;
            z-index: 10;
            background: #f8fafc;
            border-bottom: 1px solid #e5e7eb;
            padding: 0.85rem 1.25rem;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #64748b;
            white-space: nowrap;
        }

        table.admin-data-table tbody td {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
            color: #1f2937;
        }

        table.admin-data-table tbody tr {
            transition: background-color 0.15s ease;
        }

        table.admin-data-table tbody tr:hover {
            background-color: #ecfdf5 !important;
        }

        table.dataTable.admin-data-table thead .sorting:before,
        table.dataTable.admin-data-table thead .sorting:after,
        table.dataTable.admin-data-table thead .sorting_asc:before,
        table.dataTable.admin-data-table thead .sorting_asc:after,
        table.dataTable.admin-data-table thead .sorting_desc:before,
        table.dataTable.admin-data-table thead .sorting_desc:after {
            display: none !important;
        }

        table.dataTable.admin-data-table.no-footer {
            border-bottom: none;
        }

        table.dataTable.admin-data-table tbody tr {
            background-color: #ffffff !important;
        }

        table.dataTable.admin-data-table.stripe tbody tr.odd {
            background-color: #f8fafc !important;
        }

        table.dataTable.admin-data-table.stripe tbody tr.odd:hover,
        table.dataTable.admin-data-table.stripe tbody tr.even:hover {
            background-color: #ecfdf5 !important;
        }

        .dataTables_wrapper {
            padding: 0;
        }

        .dataTables_wrapper .admin-dt-toolbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #f1f5f9;
            background: #fafafa;
        }

        .dataTables_wrapper .admin-dt-footer {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            padding: 1rem 1.5rem;
            border-top: 1px solid #f1f5f9;
            background: #fafafa;
        }

        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            margin: 0;
            padding: 0;
            color: #64748b;
            font-size: 0.875rem;
        }

        .dataTables_wrapper .dataTables_length label,
        .dataTables_wrapper .dataTables_filter label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
            color: #475569;
        }

        .dataTables_wrapper .dataTables_length select {
            border-radius: 0.65rem;
            border: 1px solid #d1d5db;
            background: #ffffff;
            padding: 0.4rem 2rem 0.4rem 0.75rem;
            font-size: 0.875rem;
            color: #334155;
            box-shadow: inset 0 1px 1px rgba(15, 23, 42, 0.04);
        }

        .dataTables_wrapper .dataTables_length select:focus,
        .dataTables_wrapper .dataTables_filter input:focus {
            outline: none;
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
        }

        .dataTables_wrapper .dataTables_filter input {
            border-radius: 0.65rem;
            border: 1px solid #d1d5db;
            background: #ffffff;
            padding: 0.45rem 0.75rem;
            margin-left: 0;
            min-width: 220px;
            font-size: 0.875rem;
            color: #334155;
            box-shadow: inset 0 1px 1px rgba(15, 23, 42, 0.04);
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            border-radius: 0.5rem !important;
            border: 1px solid #e5e7eb !important;
            background: #ffffff !important;
            color: #475569 !important;
            margin: 0 0.15rem !important;
            padding: 0.35rem 0.7rem !important;
            font-size: 0.8125rem !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            background: #059669 !important;
            border-color: #059669 !important;
            color: #ffffff !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #ecfdf5 !important;
            border-color: #a7f3d0 !important;
            color: #065f46 !important;
        }

        .dataTables_wrapper .dataTables_processing {
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            color: #059669;
            font-weight: 600;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
        }

        .table-action-group {
            display: inline-flex;
            align-items: center;
            gap: 0.15rem;
            padding: 0.2rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.65rem;
            background: #ffffff;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.05);
        }

        .table-action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2rem;
            height: 2rem;
            border-radius: 0.45rem;
            color: #64748b;
            transition: background-color 0.15s ease, color 0.15s ease;
        }

        .table-action-btn:hover {
            background: #f1f5f9;
        }

        .table-action-btn--edit { color: #059669; }
        .table-action-btn--edit:hover { background: #ecfdf5; color: #047857; }
        .table-action-btn--view { color: #2563eb; }
        .table-action-btn--view:hover { background: #eff6ff; color: #1d4ed8; }
        .table-action-btn--warn { color: #ea580c; }
        .table-action-btn--warn:hover { background: #fff7ed; color: #c2410c; }
        .table-action-btn--danger { color: #dc2626; }
        .table-action-btn--danger:hover { background: #fef2f2; color: #b91c1c; }
        .table-action-btn--disabled {
            color: #cbd5e1;
            cursor: not-allowed;
        }
        .sidebar-item.active { 
            background-color: rgba(59, 130, 246, 0.12); 
            border-left: 3px solid #2563eb; 
            font-weight: 600;
        }
        .sidebar-item.active i {
            color: #2563eb;
        }
        .sidebar-action {
            font-size: 0.86rem;
            border-radius: 0.85rem;
            padding: 0.5rem 0.85rem;
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
        <aside id="sidebar" class="w-56 hidden lg:flex flex-col bg-white border-r border-gray-200 transition-all duration-200">
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
                @unless(Auth::user()->isPlatformAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="sidebar-item flex items-center space-x-3 px-3 py-2 rounded-lg text-gray-700 transition-colors {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-chart-line w-4 text-gray-500"></i>
                        <span class="font-medium">Dashboard</span>
                    </a>
                    <a href="{{ route('admin.users.index') }}" class="sidebar-item flex items-center space-x-3 px-3 py-2 rounded-lg text-gray-700 transition-colors {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <i class="fas fa-users-cog w-4 text-gray-500"></i>
                        <span class="font-medium">Staff Management</span>
                    </a>
                    @if(Auth::user()->canManageUsers())
                        <a href="{{ route('admin.users.create') }}" class="sidebar-item sidebar-action inline-flex items-center space-x-2 px-3 py-2 text-gray-700 bg-gray-50 hover:bg-gray-100 transition-colors {{ request()->routeIs('admin.users.create') ? 'active' : '' }}">
                            <i class="fas fa-user-plus w-4 text-gray-500"></i>
                            <span class="font-medium">Add Employee</span>
                        </a>
                    @endif
                    <a href="{{ route('admin.insights') }}" class="sidebar-item flex items-center space-x-3 px-3 py-2 rounded-lg text-gray-700 transition-colors {{ request()->routeIs('admin.insights') ? 'active' : '' }}">
                        <i class="fas fa-chart-pie w-4 text-gray-500"></i>
                        <span class="font-medium">Analytics</span>
                    </a>
                    <a href="{{ route('admin.reports.index') }}" class="sidebar-item flex items-center space-x-3 px-3 py-2 rounded-lg text-gray-700 transition-colors {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                        <i class="fas fa-file-contract w-4 text-gray-500"></i>
                        <span class="font-medium">Advanced Reports</span>
                    </a>
                    <a href="{{ route('admin.exceptions.index') }}" class="sidebar-item flex items-center space-x-3 px-3 py-2 rounded-lg text-gray-700 transition-colors {{ request()->routeIs('admin.exceptions.*') ? 'active' : '' }}">
                        <i class="fas fa-inbox w-4 text-gray-500"></i>
                        <span class="font-medium">Approvals</span>
                        @php $pending = \App\Models\AttendanceException::where('status', 'pending')->count(); @endphp
                        @if($pending > 0)
                            <span class="ml-auto inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-amber-100 text-amber-800 font-semibold">{{ $pending }}</span>
                        @endif
                    </a>

                    <a href="{{ route('attendance.qr') }}" class="sidebar-item flex items-center space-x-3 px-3 py-2 rounded-lg text-gray-700 transition-colors {{ request()->routeIs('attendance.qr') ? 'active' : '' }}">
                        <i class="fas fa-qrcode w-4 text-gray-500"></i>
                        <span class="font-medium">QR Code</span>
                    </a>
                    <a href="{{ route('admin.guide') }}" class="sidebar-item flex items-center space-x-3 px-3 py-2 rounded-lg text-gray-700 transition-colors {{ request()->routeIs('admin.guide') ? 'active' : '' }}">
                        <i class="fas fa-book w-4 text-gray-500"></i>
                        <span class="font-medium">User Guide</span>
                    </a>

                    <div class="pt-4 pb-2 px-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest">SaaS & Billing</div>
                    <a href="{{ route('billing.index') }}" class="sidebar-item flex items-center space-x-3 px-3 py-2 rounded-lg text-gray-700 transition-colors {{ request()->routeIs('billing.*') ? 'active' : '' }}">
                        <i class="fas fa-credit-card w-4 text-gray-500"></i>
                        <span class="font-medium">My Subscription</span>
                    </a>
                @endunless

                @if(Auth::user()->isPlatformAdmin())
                    <a href="{{ route('superadmin.finance.pending') }}" class="sidebar-item flex items-center space-x-3 px-3 py-2 rounded-lg text-gray-700 transition-colors {{ request()->routeIs('superadmin.finance.*') ? 'active' : '' }}">
                        <i class="fas fa-vault w-4 text-gray-500"></i>
                        <span class="font-medium">Finance Ops</span>
                    </a>
                @endif

            </nav>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden min-h-0">
            <div class="max-w-screen-xl mx-auto w-full px-4 sm:px-6 lg:px-8 flex-1 flex flex-col min-h-0">
                <!-- Top Bar -->
                <header class="bg-white border border-gray-200 rounded-2xl px-6 py-4 mt-4 shadow-sm shrink-0">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <button id="sidebarToggle" type="button" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                                <i class="fas fa-bars"></i>
                                <span class="hidden sm:inline">Hide Sidebar</span>
                            </button>
                            <button id="sidebarCompactToggle" type="button" title="Toggle compact sidebar" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-2 py-2 text-sm text-gray-600 hover:bg-gray-50 transition-colors">
                                <i class="fas fa-compress"></i>
                            </button>
                            <div>
                                <h2 class="text-2xl font-semibold text-gray-900">@yield('page-title', 'Dashboard')</h2>
                                <p class="text-sm text-gray-500 mt-1">{{ now()->format('l, F j, Y') }}</p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-3">
                            <div class="text-right mr-2">
                                <p id="currentTime" class="text-sm font-medium text-gray-900">{{ now()->format('g:i A') }}</p>
                                <p class="text-xs text-gray-500">Current Time</p>
                            </div>

                            <div class="relative">
                                <button id="userMenuButton" class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 border border-gray-200" aria-haspopup="true" aria-expanded="false">
                                    <span class="h-8 w-8 rounded-full bg-emerald-600 flex items-center justify-center text-white font-semibold">{{ substr(Auth::user()->name ?? 'A', 0, 1) }}</span>
                                    <span class="hidden sm:inline">{{ Auth::user()->name ?? 'Admin' }}</span>
                                </button>

                                <div id="userMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-100 py-2 z-50" role="menu" aria-label="User menu">
                                    <a href="{{ route('profile.show') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">My Profile</a>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Logout</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>

            <!-- Page Content -->
            <main class="flex-1 min-h-0 overflow-y-auto py-6">
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

                <script>
                    document.addEventListener('DOMContentLoaded', () => {
                        const sidebar = document.getElementById('sidebar');
                        const toggle = document.getElementById('sidebarToggle');
                        const compactToggle = document.getElementById('sidebarCompactToggle');
                        const userMenuButton = document.getElementById('userMenuButton');
                        const userMenu = document.getElementById('userMenu');

                        const isSidebarVisible = () => {
                            if (!sidebar) return false;
                            return window.getComputedStyle(sidebar).display !== 'none' && !sidebar.classList.contains('sidebar-closed');
                        };

                        const updateToggleLabel = () => {
                            if (!toggle) return;
                            const hidden = !isSidebarVisible();
                            toggle.innerHTML = hidden
                                ? '<i class="fas fa-bars"></i><span class="hidden sm:inline">Show Sidebar</span>'
                                : '<i class="fas fa-bars"></i><span class="hidden sm:inline">Hide Sidebar</span>';
                        };

                        // Initialize hidden/open state
                        if (sidebar) {
                            const sidebarHidden = localStorage.getItem('sidebarHidden') === 'true';
                            if (sidebarHidden) {
                                sidebar.classList.add('sidebar-closed');
                                sidebar.classList.remove('sidebar-open');
                            }
                        }
                        updateToggleLabel();

                        if (toggle && sidebar) {
                            toggle.addEventListener('click', () => {
                                if (isSidebarVisible()) {
                                    sidebar.classList.add('sidebar-closed');
                                    sidebar.classList.remove('sidebar-open');
                                    localStorage.setItem('sidebarHidden', 'true');
                                } else {
                                    sidebar.classList.remove('sidebar-closed');
                                    sidebar.classList.add('sidebar-open');
                                    localStorage.setItem('sidebarHidden', 'false');
                                }
                                updateToggleLabel();
                            });
                        }

                        // Compact sidebar handling
                        const setCompact = (compact) => {
                            if (!sidebar) return;
                            if (compact) {
                                sidebar.classList.add('compact');
                                localStorage.setItem('sidebarCompact', 'true');
                            } else {
                                sidebar.classList.remove('compact');
                                localStorage.setItem('sidebarCompact', 'false');
                            }
                        };

                        const compactPref = localStorage.getItem('sidebarCompact');
                        if (compactPref === 'true') {
                            setCompact(true);
                        } else if (compactPref === 'false') {
                            setCompact(false);
                        } else {
                            // Default: enable compact for md widths (>=768 && <1024)
                            if (window.innerWidth >= 768 && window.innerWidth < 1024) {
                                setCompact(true);
                            } else {
                                setCompact(false);
                            }
                        }

                        if (compactToggle) {
                            compactToggle.addEventListener('click', () => {
                                if (sidebar) {
                                    setCompact(!sidebar.classList.contains('compact'));
                                }
                            });
                        }

                        window.addEventListener('resize', () => {
                            const pref = localStorage.getItem('sidebarCompact');
                            if (pref === null) {
                                if (window.innerWidth >= 768 && window.innerWidth < 1024) {
                                    setCompact(true);
                                } else {
                                    setCompact(false);
                                }
                            }
                        });

                        // User menu dropdown
                        if (userMenuButton && userMenu) {
                            userMenuButton.addEventListener('click', (e) => {
                                e.preventDefault();
                                userMenu.classList.toggle('hidden');
                            });

                            document.addEventListener('click', (e) => {
                                if (!userMenuButton.contains(e.target) && !userMenu.contains(e.target)) {
                                    userMenu.classList.add('hidden');
                                }
                            });
                        }

                        // Current time updater
                        const updateCurrentTime = () => {
                            const timeElement = document.getElementById('currentTime');
                            if (!timeElement) return;
                            const now = new Date();
                            const hours = now.getHours() % 12 || 12;
                            const minutes = String(now.getMinutes()).padStart(2, '0');
                            const ampm = now.getHours() >= 12 ? 'PM' : 'AM';
                            timeElement.textContent = `${hours}:${minutes} ${ampm}`;
                        };

                        updateCurrentTime();
                        setInterval(updateCurrentTime, 1000);
                    });
                </script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    @stack('scripts')
    
    <!-- Session Keep-Alive -->
    <script>
        (function() {
            // Ping every 15 minutes (in milliseconds)
            const interval = 15 * 60 * 1000;
            setInterval(() => {
                fetch("{{ route('session.keep-alive') }}")
                    .then(response => {
                        if (!response.ok) {
                            console.warn('Session keep-alive ping failed with status:', response.status);
                        }
                    })
                    .catch(error => {
                        console.error('Session keep-alive ping failed:', error);
                    });
            }, interval);
        })();
    </script>
</body>
</html>



