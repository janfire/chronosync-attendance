<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard') - ChronoSync Attendance System</title>
    <!-- Dark Mode Configuration & FOUC Prevention -->
    <script>
        // Check local storage or system preference immediately
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark')
        } else {
            document.documentElement.classList.remove('dark')
        }
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: { extend: {} }
        }
    </script>
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

        /* Dark Mode Overrides */
        html.dark .sidebar-item { color: #cbd5e1; }
        html.dark .sidebar-item.active { color: #60a5fa; background-color: rgba(59, 130, 246, 0.2); }
        html.dark .sidebar-item i { color: #94a3b8; }
        html.dark .sidebar-action { background: #1e293b; color: #cbd5e1; }
        html.dark .sidebar-action:hover { background: #334155; }
        html.dark .bg-slate-50 { background-color: #0f172a; }
        html.dark .text-slate-600 { color: #94a3b8; }
        
        /* Dark Mode Overrides for Custom DataTables */
        html.dark .admin-data-table thead th {
            background: #1e293b;
            border-bottom-color: #334155;
            color: #94a3b8;
        }
        html.dark .admin-data-table tbody td {
            border-bottom-color: #1e293b;
            color: #e2e8f0;
        }
        html.dark .admin-data-table tbody tr {
            background-color: #0f172a !important;
        }
        html.dark .admin-data-table tbody tr:hover {
            background-color: #1e293b !important;
        }
        html.dark .admin-data-table.stripe tbody tr.odd {
            background-color: #1e293b !important;
        }
        html.dark .dataTables_wrapper .admin-dt-toolbar,
        html.dark .dataTables_wrapper .admin-dt-footer {
            background: #0f172a;
            border-color: #1e293b;
        }
        html.dark .dataTables_wrapper .dataTables_length select,
        html.dark .dataTables_wrapper .dataTables_filter input {
            background: #1e293b;
            border-color: #334155;
            color: #f8fafc;
        }
        html.dark .dataTables_wrapper .dataTables_paginate .paginate_button {
            background: #1e293b !important;
            border-color: #334155 !important;
            color: #cbd5e1 !important;
        }
        html.dark .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #059669 !important;
            color: #ffffff !important;
        }
        html.dark .dataTables_wrapper .dataTables_paginate .paginate_button:hover:not(.current) {
            background: #334155 !important;
            color: #f8fafc !important;
        }
    </style>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">
    <link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/airbnb.css">
    @stack('styles')
</head>
<body class="bg-gray-50 dark:bg-gray-900 transition-colors duration-200">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside id="sidebar" class="w-56 hidden lg:flex flex-col bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 transition-all duration-200">
            <!-- Logo Section -->
            <div class="p-6 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-br from-gray-50 to-white dark:from-gray-800 dark:to-gray-900 transition-colors">
                <div class="flex items-center space-x-3.5">
                    <div class="relative shrink-0 group">
                        <!-- Background glow -->
                        <div class="absolute inset-0 bg-emerald-500 rounded-xl blur-md opacity-30 group-hover:opacity-60 transition-opacity duration-500"></div>
                        
                        <!-- Logo Container -->
                        <div class="relative h-12 w-12 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-700 flex items-center justify-center shadow-lg border border-white/10 overflow-hidden">
                            <!-- Abstract Monogram C & S -->
                            <svg class="w-7 h-7 text-white relative z-10 drop-shadow-sm transform group-hover:scale-105 transition-transform duration-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <!-- Abstract C -->
                                <path d="M16 19 A 8 8 0 1 1 16 5" stroke-opacity="0.5" />
                                <!-- Abstract S -->
                                <path d="M16 8 A 3.5 3.5 0 0 0 9 8 C 9 12 16 11 16 15 A 3.5 3.5 0 0 1 9 15" />
                            </svg>
                        </div>
                    </div>
                    <div>
                        <h1 class="text-[1.35rem] leading-none tracking-tight flex items-center text-gray-900 dark:text-white">
                            <span class="font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-teal-600 dark:from-emerald-400 dark:to-teal-400">Chrono</span>
                            <span class="font-light">Sync</span>
                        </h1>
                        <p class="text-[0.65rem] uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400 font-bold mt-1">Attendance</p>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
                @unless(Auth::user()->isPlatformAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="sidebar-item flex items-center space-x-3 px-3 py-2 rounded-lg text-gray-700 transition-colors {{ request()->routeIs('admin.dashboard') ? 'active' : '' }} dark:text-gray-200">
                        <i class="fas fa-chart-line w-4 text-gray-500 dark:text-gray-400"></i>
                        <span class="font-medium">Dashboard</span>
                    </a>
                    <a href="{{ route('admin.users.index') }}" class="sidebar-item flex items-center space-x-3 px-3 py-2 rounded-lg text-gray-700 transition-colors {{ request()->routeIs('admin.users.*') ? 'active' : '' }} dark:text-gray-200">
                        <i class="fas fa-users-cog w-4 text-gray-500 dark:text-gray-400"></i>
                        <span class="font-medium">Staff Management</span>
                    </a>
                    @if(Auth::user()->canManageUsers())
                        <a href="{{ route('admin.users.create') }}" class="sidebar-item sidebar-action inline-flex items-center space-x-2 px-3 py-2 text-gray-700 bg-gray-50 hover:bg-gray-100 transition-colors {{ request()->routeIs('admin.users.create') ? 'active' : '' }} dark:bg-gray-900 dark:text-gray-200">
                            <i class="fas fa-user-plus w-4 text-gray-500 dark:text-gray-400"></i>
                            <span class="font-medium">Add Employee</span>
                        </a>
                    @endif
                    @if(app('current_tenant') && app('current_tenant')->hasFeature('Advanced Analytics'))
                        <a href="{{ route('admin.insights') }}" class="sidebar-item flex items-center space-x-3 px-3 py-2 rounded-lg text-gray-700 transition-colors {{ request()->routeIs('admin.insights') ? 'active' : '' }} dark:text-gray-200">
                            <i class="fas fa-chart-pie w-4 text-gray-500 dark:text-gray-400"></i>
                            <span class="font-medium">Analytics</span>
                        </a>
                    @endif
                    <a href="{{ route('admin.reports.index') }}" class="sidebar-item flex items-center space-x-3 px-3 py-2 rounded-lg text-gray-700 transition-colors {{ request()->routeIs('admin.reports.*') ? 'active' : '' }} dark:text-gray-200">
                        <i class="fas fa-file-contract w-4 text-gray-500 dark:text-gray-400"></i>
                        <span class="font-medium">Advanced Reports</span>
                    </a>
                    <a href="{{ route('admin.exceptions.index') }}" class="sidebar-item flex items-center space-x-3 px-3 py-2 rounded-lg text-gray-700 transition-colors {{ request()->routeIs('admin.exceptions.*') ? 'active' : '' }} dark:text-gray-200">
                        <i class="fas fa-inbox w-4 text-gray-500 dark:text-gray-400"></i>
                        <span class="font-medium">Approvals</span>
                        @php $pending = \App\Models\AttendanceException::where('status', 'pending')->count(); @endphp
                        @if($pending > 0)
                            <span class="ml-auto inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-amber-100 text-amber-800 font-semibold">{{ $pending }}</span>
                        @endif
                    </a>

                    @if(app('current_tenant') && app('current_tenant')->hasFeature('QR Codes'))
                        <a href="{{ route('attendance.qr') }}" class="sidebar-item flex items-center space-x-3 px-3 py-2 rounded-lg text-gray-700 transition-colors {{ request()->routeIs('attendance.qr') ? 'active' : '' }} dark:text-gray-200">
                            <i class="fas fa-qrcode w-4 text-gray-500 dark:text-gray-400"></i>
                            <span class="font-medium">QR Code</span>
                        </a>
                    @endif
                    <a href="{{ route('admin.guide') }}" class="sidebar-item flex items-center space-x-3 px-3 py-2 rounded-lg text-gray-700 transition-colors {{ request()->routeIs('admin.guide') ? 'active' : '' }} dark:text-gray-200">
                        <i class="fas fa-book w-4 text-gray-500 dark:text-gray-400"></i>
                        <span class="font-medium">User Guide</span>
                    </a>

                    <div class="pt-4 pb-2 px-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest">SaaS & Billing</div>
                    <a href="{{ route('billing.index') }}" class="sidebar-item flex items-center space-x-3 px-3 py-2 rounded-lg text-gray-700 transition-colors {{ request()->routeIs('billing.*') ? 'active' : '' }} dark:text-gray-200">
                        <i class="fas fa-credit-card w-4 text-gray-500 dark:text-gray-400"></i>
                        <span class="font-medium">My Subscription</span>
                    </a>
                @endunless

                @if(Auth::user()->isPlatformAdmin())
                    <a href="{{ route('superadmin.dashboard') }}" class="sidebar-item flex items-center space-x-3 px-3 py-2 rounded-lg text-gray-700 transition-colors {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }} dark:text-gray-200">
                        <i class="fas fa-th-large w-4 text-gray-500 dark:text-gray-400"></i>
                        <span class="font-medium">Dashboard</span>
                    </a>
                    <a href="{{ route('superadmin.tenants') }}" class="sidebar-item flex items-center space-x-3 px-3 py-2 rounded-lg text-gray-700 transition-colors {{ request()->routeIs('superadmin.tenants') ? 'active' : '' }} dark:text-gray-200">
                        <i class="fas fa-building w-4 text-gray-500 dark:text-gray-400"></i>
                        <span class="font-medium">Tenant Directory</span>
                    </a>
                    <a href="{{ route('superadmin.finance.pending') }}" class="sidebar-item flex items-center space-x-3 px-3 py-2 rounded-lg text-gray-700 transition-colors {{ request()->routeIs('superadmin.finance.*') ? 'active' : '' }} dark:text-gray-200">
                        <i class="fas fa-vault w-4 text-gray-500 dark:text-gray-400"></i>
                        <span class="font-medium">Finance Ops</span>
                    </a>
                    <a href="{{ route('superadmin.audit.index') }}" class="sidebar-item flex items-center space-x-3 px-3 py-2 rounded-lg text-gray-700 transition-colors {{ request()->routeIs('superadmin.audit.*') ? 'active' : '' }} dark:text-gray-200">
                        <i class="fas fa-shield-alt w-4 text-gray-500 dark:text-gray-400"></i>
                        <span class="font-medium">System Audit</span>
                        @php $newErrors = \App\Models\SystemErrorLog::where('status', 'new')->count(); @endphp
                        @if($newErrors > 0)
                            <span class="ml-auto flex h-2.5 w-2.5 relative" title="{{ $newErrors }} new system crashes">
                              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                              <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-red-500"></span>
                            </span>
                        @endif
                    </a>
                @endif

            </nav>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden min-h-0">
            <div class="max-w-screen-xl mx-auto w-full px-4 sm:px-6 lg:px-8 flex-1 flex flex-col min-h-0">
                <!-- Top Bar -->
                <header class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl px-6 py-4 mt-4 shadow-sm shrink-0 transition-colors duration-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <button id="sidebarToggle" type="button" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <i class="fas fa-bars"></i>
                                <span class="hidden sm:inline">Hide Sidebar</span>
                            </button>
                            <button id="sidebarCompactToggle" type="button" title="Toggle compact sidebar" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-2 py-2 text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <i class="fas fa-compress"></i>
                            </button>
                            <div>
                                <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">@yield('page-title', 'Dashboard')</h2>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ now()->format('l, F j, Y') }}</p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-3">
                            <button id="themeToggle" type="button" class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <i id="themeToggleIcon" class="fas fa-moon text-lg"></i>
                            </button>
                        
                            <div class="text-right ml-2 mr-2 hidden sm:block">
                                <p id="currentTime" class="text-sm font-medium text-gray-900 dark:text-white">{{ now()->format('g:i A') }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Current Time</p>
                            </div>

                            <div class="relative">
                                <button id="userMenuButton" class="inline-flex items-center gap-2 rounded-full bg-white dark:bg-gray-800 px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700 transition-colors" aria-haspopup="true" aria-expanded="false">
                                    <span class="h-8 w-8 rounded-full bg-emerald-600 flex items-center justify-center text-white font-semibold">{{ substr(Auth::user()->name ?? 'A', 0, 1) }}</span>
                                    <span class="hidden sm:inline">{{ Auth::user()->name ?? 'Admin' }}</span>
                                </button>

                                <div id="userMenu" class="hidden absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-100 dark:border-gray-700 py-2 z-50" role="menu" aria-label="User menu">
                                    <a href="{{ route('profile.show') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors dark:bg-gray-900">My Profile</a>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors dark:bg-gray-900">Logout</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>

            <!-- Page Content -->
            <main class="flex-1 min-h-0 overflow-y-auto py-6">
                @if(session('success'))
                    <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg dark:bg-green-900/20 dark:border-green-800 dark:text-green-300">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg dark:bg-red-900/20 dark:border-red-800 dark:text-red-300">
                        {{ session('error') }}
                    </div>
                @endif

                {{-- Subscription expiry warning banner (non-platform-admin only, hidden on billing pages) --}}
                @if(app()->bound('current_tenant') && !Auth::user()->isPlatformAdmin() && !request()->is('billing*'))
                    @php $tenant = app('current_tenant'); @endphp
                    @if(!$tenant->canAccess())
                        <div class="mb-6 flex items-start gap-3 bg-rose-50 border border-rose-200 rounded-xl px-4 py-3 dark:bg-rose-900/20 dark:border-rose-800">
                            <i class="fas fa-exclamation-circle text-rose-500 mt-0.5 shrink-0 dark:text-rose-400"></i>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-rose-800 dark:text-rose-300">Subscription Expired — Access Suspended</p>
                                <p class="text-sm text-rose-600 mt-0.5 dark:text-rose-400">
                                    Go to <a href="{{ route('billing.index') }}" class="underline font-semibold">Billing &amp; Subscription</a> to generate your renewal invoice and submit payment.
                                </p>
                            </div>
                        </div>
                    @elseif($tenant->trial_ends_at && $tenant->isOnTrial() && $tenant->trial_ends_at->diffInDays(now(), false) >= -7)
                        <div id="billing-banner" class="mb-6 flex items-start gap-3 bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 dark:bg-amber-900/20 dark:border-amber-800">
                            <i class="fas fa-clock text-amber-500 mt-0.5 shrink-0 dark:text-amber-400"></i>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-amber-800 dark:text-amber-300">Trial expires {{ $tenant->trial_ends_at->diffForHumans() }}</p>
                                <p class="text-sm text-amber-600 mt-0.5 dark:text-amber-400">
                                    <a href="{{ route('billing.index') }}" class="underline font-semibold">Visit Billing</a> to set up your subscription before access is interrupted.
                                </p>
                            </div>
                            <button onclick="document.getElementById('billing-banner').remove()" class="text-amber-400 hover:text-amber-600 shrink-0 text-lg leading-none dark:text-amber-500 dark:hover:text-amber-400">&times;</button>
                        </div>
                    @elseif($tenant->subscription_expires_at && $tenant->subscription_expires_at->diffInDays(now(), false) >= -7)
                        <div id="billing-banner" class="mb-6 flex items-start gap-3 bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 dark:bg-amber-900/20 dark:border-amber-800">
                            <i class="fas fa-clock text-amber-500 mt-0.5 shrink-0 dark:text-amber-400"></i>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-amber-800 dark:text-amber-300">Subscription expires {{ $tenant->subscription_expires_at->diffForHumans() }}</p>
                                <p class="text-sm text-amber-600 mt-0.5 dark:text-amber-400">
                                    <a href="{{ route('billing.index') }}" class="underline font-semibold">Renew now</a> to avoid any interruption to your service.
                                </p>
                            </div>
                            <button onclick="document.getElementById('billing-banner').remove()" class="text-amber-400 hover:text-amber-600 shrink-0 text-lg leading-none dark:text-amber-500 dark:hover:text-amber-400">&times;</button>
                        </div>
                    @endif
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

                        // Theme Toggle Logic
                        const themeToggleBtn = document.getElementById('themeToggle');
                        const themeToggleIcon = document.getElementById('themeToggleIcon');

                        const updateThemeIcon = (isDark) => {
                            if (isDark) {
                                themeToggleIcon.classList.remove('fa-moon');
                                themeToggleIcon.classList.add('fa-sun');
                            } else {
                                themeToggleIcon.classList.remove('fa-sun');
                                themeToggleIcon.classList.add('fa-moon');
                            }
                        };

                        // Initial icon state
                        updateThemeIcon(document.documentElement.classList.contains('dark'));

                        if (themeToggleBtn) {
                            themeToggleBtn.addEventListener('click', () => {
                                document.documentElement.classList.toggle('dark');
                                const isDark = document.documentElement.classList.contains('dark');
                                
                                if (isDark) {
                                    localStorage.theme = 'dark';
                                } else {
                                    localStorage.theme = 'light';
                                }
                                
                                updateThemeIcon(isDark);
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
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Flatpickr for Date inputs
            flatpickr('input[type="date"]', {
                dateFormat: "Y-m-d",
                allowInput: true,
                altInput: true,
                altFormat: "F j, Y"
            });
            
            // Initialize Flatpickr for Month inputs
            flatpickr('input[type="month"]', {
                plugins: [
                    new monthSelectPlugin({
                        shorthand: true,
                        dateFormat: "Y-m",
                        altFormat: "F Y"
                    })
                ],
                allowInput: true,
                altInput: true,
                onChange: function(selectedDates, dateStr, instance) {
                    // Manually trigger change event for things like onchange="this.form.submit()"
                    const event = new Event('change', { bubbles: true });
                    instance.element.dispatchEvent(event);
                }
            });

            // Initialize Flatpickr for Time inputs
            flatpickr('input[type="time"]', {
                enableTime: true,
                noCalendar: true,
                dateFormat: "H:i",
                time_24hr: true,
                allowInput: true
            });
        });
    </script>
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



