@extends('admin.layout')

@section('title', 'Platform Admin Dashboard')
@section('page-title', 'Platform Dashboard')

@section('content')

{{-- Welcome Banner --}}
<div class="bg-gradient-to-r from-emerald-600 to-teal-700 rounded-2xl p-8 mb-8 text-white shadow-lg">
    <div class="flex items-center gap-5">
        <div class="h-16 w-16 bg-white/20 rounded-2xl flex items-center justify-center shrink-0">
            <i class="fas fa-shield-alt text-white text-3xl"></i>
        </div>
        <div>
            <h1 class="text-3xl font-bold">Platform Admin Workspace</h1>
            <p class="text-emerald-100 mt-1 text-sm">
                Global overview — all tenants, subscriptions and billing activity across the platform.
            </p>
        </div>
        <div class="ml-auto hidden sm:block text-right">
            <p class="text-emerald-200 text-xs uppercase tracking-widest">Access Level</p>
            <p class="text-white font-bold text-lg">Platform Admin</p>
        </div>
    </div>
</div>

{{-- ── Metric Cards ── --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5 mb-8">

    {{-- Total Tenants --}}
    <a href="{{ route('superadmin.tenants') }}"
       class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm hover:shadow-md hover:border-emerald-300 transition-all duration-200 cursor-pointer group">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 font-semibold uppercase tracking-wide mb-1">Total Tenants</p>
                <p class="text-5xl font-bold text-gray-900 group-hover:text-emerald-600 transition-colors">
                    {{ $metrics['total_tenants'] ?? 0 }}
                </p>
                <p class="text-xs text-gray-400 mt-2">All registered organisations</p>
            </div>
            <div class="h-16 w-16 bg-emerald-50 rounded-2xl flex items-center justify-center group-hover:bg-emerald-100 transition-colors shrink-0">
                <i class="fas fa-building text-emerald-600 text-2xl"></i>
            </div>
        </div>
        <div class="mt-4 pt-4 border-t border-gray-100">
            <span class="text-xs text-emerald-600 font-semibold group-hover:underline">
                View all tenants <i class="fas fa-arrow-right ml-1"></i>
            </span>
        </div>
    </a>

    {{-- Active Subscriptions --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm hover:shadow-md transition-all duration-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 font-semibold uppercase tracking-wide mb-1">Active Subscriptions</p>
                <p class="text-5xl font-bold text-gray-900">
                    {{ $metrics['active_subscriptions'] ?? 0 }}
                </p>
                <p class="text-xs text-gray-400 mt-2">Paid &amp; up-to-date</p>
            </div>
            <div class="h-16 w-16 bg-green-50 rounded-2xl flex items-center justify-center shrink-0">
                <i class="fas fa-check-circle text-green-600 text-2xl"></i>
            </div>
        </div>
        <div class="mt-4 pt-4 border-t border-gray-100">
            <span class="text-xs text-green-600 font-semibold">
                <i class="fas fa-circle text-green-400 mr-1" style="font-size:8px"></i> Subscriptions running normally
            </span>
        </div>
    </div>

    {{-- Pending Subscriptions --}}
    <div class="bg-white rounded-2xl border border-orange-200 p-6 shadow-sm hover:shadow-md transition-all duration-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 font-semibold uppercase tracking-wide mb-1">Pending Subscriptions</p>
                <p class="text-5xl font-bold text-orange-600">
                    {{ $metrics['pending_subscriptions'] ?? 0 }}
                </p>
                <p class="text-xs text-gray-400 mt-2">Awaiting payment / activation</p>
            </div>
            <div class="h-16 w-16 bg-orange-50 rounded-2xl flex items-center justify-center shrink-0">
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
    <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm hover:shadow-md transition-all duration-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 font-semibold uppercase tracking-wide mb-1">Monthly Recurring Revenue</p>
                <p class="text-5xl font-bold text-gray-900">
                    ${{ number_format($metrics['mrr'] ?? 0, 2) }}
                </p>
                <p class="text-xs text-gray-400 mt-2">USD – current billing month</p>
            </div>
            <div class="h-16 w-16 bg-blue-50 rounded-2xl flex items-center justify-center shrink-0">
                <i class="fas fa-dollar-sign text-blue-600 text-2xl"></i>
            </div>
        </div>
        <div class="mt-4 pt-4 border-t border-gray-100">
            <span class="text-xs text-blue-600 font-semibold">
                <i class="fas fa-chart-line mr-1"></i> Revenue this month
            </span>
        </div>
    </div>

    {{-- New Tenants (7 days) --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm hover:shadow-md transition-all duration-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 font-semibold uppercase tracking-wide mb-1">New Tenants (7 days)</p>
                <p class="text-5xl font-bold text-gray-900">
                    {{ $metrics['new_tenants'] ?? 0 }}
                </p>
                <p class="text-xs text-gray-400 mt-2">Recent sign-ups</p>
            </div>
            <div class="h-16 w-16 bg-purple-50 rounded-2xl flex items-center justify-center shrink-0">
                <i class="fas fa-user-plus text-purple-600 text-2xl"></i>
            </div>
        </div>
        <div class="mt-4 pt-4 border-t border-gray-100">
            <span class="text-xs text-purple-600 font-semibold">
                <i class="fas fa-calendar-week mr-1"></i> Last 7 days
            </span>
        </div>
    </div>

    {{-- System Health --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm hover:shadow-md transition-all duration-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 font-semibold uppercase tracking-wide mb-1">System Health</p>
                <p class="text-2xl font-bold text-green-600 mt-1">All Services Running</p>
                <p class="text-xs text-gray-400 mt-2">Queue jobs pending: {{ $metrics['queue_jobs'] ?? 0 }}</p>
            </div>
            <div class="h-16 w-16 bg-green-50 rounded-2xl flex items-center justify-center relative shrink-0">
                <i class="fas fa-heartbeat text-green-600 text-2xl"></i>
                <span class="absolute -top-1 -right-1 h-4 w-4 bg-green-500 rounded-full border-2 border-white animate-pulse"></span>
            </div>
        </div>
        <div class="mt-4 pt-4 border-t border-gray-100">
            <span class="text-xs text-green-600 font-semibold">
                <i class="fas fa-server mr-1"></i> All systems operational
            </span>
        </div>
    </div>

</div>{{-- /metric cards --}}

{{-- ── Quick Actions ── --}}
<h3 class="text-sm font-semibold text-gray-500 uppercase tracking-widest mb-4">Quick Actions</h3>
<div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

    <a href="{{ route('superadmin.finance.pending') }}"
       class="flex items-center gap-5 bg-white rounded-2xl border border-emerald-200 p-6 shadow-sm hover:shadow-md hover:border-emerald-400 transition-all duration-200 group">
        <div class="h-14 w-14 bg-emerald-100 rounded-2xl flex items-center justify-center group-hover:bg-emerald-200 transition-colors shrink-0">
            <i class="fas fa-file-invoice-dollar text-emerald-600 text-xl"></i>
        </div>
        <div>
            <p class="font-bold text-gray-900 group-hover:text-emerald-700 transition-colors">Finance Operations</p>
            <p class="text-sm text-gray-500 mt-0.5">Review tenant billing, confirm payments &amp; manage invoices.</p>
        </div>
        <i class="fas fa-chevron-right text-gray-300 group-hover:text-emerald-500 ml-auto transition-colors"></i>
    </a>

    <div class="flex items-center gap-5 bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
        <div class="h-14 w-14 bg-slate-100 rounded-2xl flex items-center justify-center shrink-0">
            <i class="fas fa-sitemap text-slate-400 text-xl"></i>
        </div>
        <div>
            <p class="font-bold text-gray-500">Tenant Management</p>
            <p class="text-sm text-gray-400 mt-0.5">Full tenant management panel coming soon.</p>
        </div>
        <span class="ml-auto text-xs bg-slate-100 text-slate-500 font-semibold px-3 py-1 rounded-full">Soon</span>
    </div>

</div>

@endsection
