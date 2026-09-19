@extends('admin.layout')

@section('title', 'Tenant Details — ' . $tenant->company_name)
@section('page-title', 'Tenant Details')

@section('content')

{{-- ── Page Header & Navigation ── --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <div class="flex items-center gap-2 mb-1">
            <a href="{{ route('superadmin.dashboard') }}"
               class="text-sm text-gray-400 hover:text-emerald-600 transition-colors flex items-center gap-1">
                <i class="fas fa-th-large text-xs"></i> Dashboard
            </a>
            <i class="fas fa-chevron-right text-gray-300 text-xs"></i>
            <a href="{{ route('superadmin.tenants') }}"
               class="text-sm text-gray-400 hover:text-emerald-600 transition-colors">
                Tenants
            </a>
            <i class="fas fa-chevron-right text-gray-300 text-xs"></i>
            <span class="text-sm text-gray-600 font-medium dark:text-gray-300">{{ $tenant->company_name }}</span>
        </div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Tenant Overview</h1>
    </div>
</div>

{{-- ── Main Header Card ── --}}
<div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden mb-6 dark:bg-gray-800 dark:border-gray-700">
    <div class="p-6 sm:p-8 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
        
        {{-- Identity --}}
        <div class="flex items-center gap-5">
            @php
                $avatarBg = ['bg-emerald-500', 'bg-teal-500', 'bg-cyan-500', 'bg-blue-500', 'bg-violet-500', 'bg-purple-500', 'bg-pink-500', 'bg-rose-500'][abs(crc32($tenant->company_name)) % 8];
                $initials = strtoupper(substr($tenant->company_name, 0, 2));
            @endphp
            <div class="h-16 w-16 rounded-2xl {{ $avatarBg }} flex items-center justify-center shrink-0 shadow-sm">
                <span class="text-white font-bold text-2xl">{{ $initials }}</span>
            </div>
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    {{ $tenant->company_name }}
                    @if($tenant->status === 'active')
                        <span class="bg-green-100 text-green-800 text-[10px] uppercase font-bold px-2 py-0.5 rounded-full tracking-wide">Active</span>
                    @elseif($tenant->status === 'suspended')
                        <span class="bg-red-100 text-red-800 text-[10px] uppercase font-bold px-2 py-0.5 rounded-full tracking-wide">Suspended</span>
                    @elseif($tenant->status === 'trial')
                        <span class="bg-blue-100 text-blue-800 text-[10px] uppercase font-bold px-2 py-0.5 rounded-full tracking-wide">Trial</span>
                    @else
                        <span class="bg-gray-100 text-gray-800 text-[10px] uppercase font-bold px-2 py-0.5 rounded-full tracking-wide">{{ $tenant->status }}</span>
                    @endif
                </h2>
                <div class="mt-1 flex items-center gap-3 text-sm text-gray-500 dark:text-gray-400">
                    <span class="flex items-center gap-1.5"><i class="fas fa-globe text-gray-400"></i> {{ $tenant->subdomain }}</span>
                    <span class="text-gray-300">•</span>
                    <span class="flex items-center gap-1.5"><i class="fas fa-envelope text-gray-400"></i> {{ $tenant->email }}</span>
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="flex items-center gap-3 w-full md:w-auto">
            @if($tenant->status !== 'active')
            <button type="button" onclick="actionTenant('activate')" class="flex-1 md:flex-none px-4 py-2 bg-green-500 hover:bg-green-600 text-white text-sm font-semibold rounded-xl shadow-sm transition-colors flex items-center justify-center gap-2">
                <i class="fas fa-check-circle"></i> Activate
            </button>
            @endif

            @if($tenant->status !== 'suspended')
            <button type="button" onclick="actionTenant('suspend')" class="flex-1 md:flex-none px-4 py-2 bg-red-50 hover:bg-red-100 text-red-600 text-sm font-semibold rounded-xl transition-colors flex items-center justify-center gap-2 dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-900/50">
                <i class="fas fa-ban"></i> Suspend
            </button>
            @endif
        </div>
    </div>
</div>

{{-- ── Tabs Navigation ── --}}
<div class="border-b border-gray-200 mb-6 dark:border-gray-700">
    <nav class="flex gap-6" aria-label="Tabs">
        <button onclick="switchTab('analytics')" id="tab-btn-analytics" class="tab-btn whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm border-emerald-500 text-emerald-600 dark:text-emerald-400">
            <i class="fas fa-chart-pie mr-2"></i> Analytics
        </button>
        <button onclick="switchTab('users')" id="tab-btn-users" class="tab-btn whitespace-nowrap py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300">
            <i class="fas fa-users mr-2"></i> User Roster
        </button>
        <button onclick="switchTab('billing')" id="tab-btn-billing" class="tab-btn whitespace-nowrap py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300">
            <i class="fas fa-file-invoice-dollar mr-2"></i> Billing
        </button>
    </nav>
</div>

{{-- ── Tab Contents ── --}}
<div class="tab-contents">
    
    {{-- 1. Analytics Tab --}}
    <div id="tab-analytics" class="tab-panel">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            {{-- Capacity Progress Bar Card --}}
            <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-200 p-6 shadow-sm dark:bg-gray-800 dark:border-gray-700">
                <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-6 flex items-center gap-2 dark:text-gray-200">
                    <i class="fas fa-users text-gray-400"></i> Employee Capacity
                </h3>
                
                @php
                    $used = $tenant->users_count;
                    $max = $tenant->max_employees ?: 9999; // 9999 for unlimited/uncapped
                    $percentage = $max > 0 ? min(100, round(($used / $max) * 100)) : 0;
                    
                    $barColor = 'bg-emerald-500';
                    if ($percentage > 80) $barColor = 'bg-yellow-400';
                    if ($percentage >= 95) $barColor = 'bg-red-500';
                @endphp

                <div class="flex items-end justify-between mb-2">
                    <div>
                        <span class="text-3xl font-extrabold text-gray-900 dark:text-white">{{ $used }}</span>
                        <span class="text-gray-500 font-medium dark:text-gray-400"> / {{ $max === 9999 ? 'Unlimited' : $max }} seats filled</span>
                    </div>
                    <span class="text-sm font-bold {{ str_replace('bg-', 'text-', $barColor) }}">{{ $percentage }}%</span>
                </div>
                
                <div class="w-full bg-gray-100 rounded-full h-4 mb-4 overflow-hidden dark:bg-gray-700">
                    <div class="{{ $barColor }} h-4 rounded-full transition-all duration-1000" style="width: {{ $percentage }}%"></div>
                </div>
                
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Plan: <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $tenant->getPlanLabel() }}</span>
                </p>
            </div>

            {{-- Summary Card --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm dark:bg-gray-800 dark:border-gray-700">
                <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-6 flex items-center gap-2 dark:text-gray-200">
                    <i class="fas fa-info-circle text-gray-400"></i> Account Status
                </h3>
                
                <div class="space-y-4">
                    <div class="flex justify-between items-center pb-3 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Created On</span>
                        <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $tenant->created_at->format('d M Y') }}</span>
                    </div>
                    <div class="flex justify-between items-center pb-3 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Trial Ends</span>
                        <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $tenant->trial_ends_at ? $tenant->trial_ends_at->format('d M Y') : 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Next Billing</span>
                        <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $tenant->subscription_expires_at ? $tenant->subscription_expires_at->format('d M Y') : 'N/A' }}</span>
                    </div>
                </div>
            </div>
            
        </div>
    </div>

    {{-- 2. Users Tab --}}
    <div id="tab-users" class="tab-panel hidden">
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden dark:bg-gray-800 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center dark:bg-gray-900 dark:border-gray-700">
                <h3 class="font-bold text-gray-800 dark:text-white">Registered Users</h3>
                <span class="text-xs font-semibold text-gray-500 bg-gray-200 px-2.5 py-1 rounded-lg dark:bg-gray-700 dark:text-gray-300">Read Only</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 border-b border-gray-200 text-xs text-gray-500 uppercase font-bold dark:bg-gray-900 dark:border-gray-700 dark:text-gray-400">
                        <tr>
                            <th class="px-6 py-3">Name</th>
                            <th class="px-6 py-3">Role</th>
                            <th class="px-6 py-3">Employee ID</th>
                            <th class="px-6 py-3">Joined</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($users as $user)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-6 py-4">
                                <div class="font-semibold text-gray-900 dark:text-white">{{ $user->name }}</div>
                                <div class="text-xs text-gray-500">{{ $user->email }}</div>
                            </td>
                            <td class="px-6 py-4">
                                @if($user->isAdmin())
                                    <span class="bg-purple-100 text-purple-700 text-xs font-bold px-2.5 py-0.5 rounded-full">Admin</span>
                                @else
                                    <span class="bg-gray-100 text-gray-700 text-xs font-semibold px-2.5 py-0.5 rounded-full">Staff</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-500 font-mono text-xs">{{ $user->employee_number ?? '-' }}</td>
                            <td class="px-6 py-4 text-gray-500">{{ $user->created_at->format('M d, Y') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-500">No users found for this tenant.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($users->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                    {{ $users->appends(['tab' => 'users'])->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- 3. Billing Tab --}}
    <div id="tab-billing" class="tab-panel hidden">
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden dark:bg-gray-800 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center dark:bg-gray-900 dark:border-gray-700">
                <h3 class="font-bold text-gray-800 dark:text-white">Invoice History</h3>
                <span class="text-xs font-semibold text-gray-500 bg-gray-200 px-2.5 py-1 rounded-lg dark:bg-gray-700 dark:text-gray-300">Read Only</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 border-b border-gray-200 text-xs text-gray-500 uppercase font-bold dark:bg-gray-900 dark:border-gray-700 dark:text-gray-400">
                        <tr>
                            <th class="px-6 py-3">Invoice #</th>
                            <th class="px-6 py-3">Amount</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($invoices as $invoice)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-6 py-4 font-mono text-gray-900 dark:text-gray-300">{{ $invoice->invoice_number }}</td>
                            <td class="px-6 py-4 font-semibold text-gray-900 dark:text-white">${{ number_format($invoice->amount_usd, 2) }}</td>
                            <td class="px-6 py-4">
                                @if($invoice->status === 'paid')
                                    <span class="bg-green-100 text-green-700 text-xs font-bold px-2.5 py-0.5 rounded-full">Paid</span>
                                @elseif($invoice->status === 'pending')
                                    <span class="bg-orange-100 text-orange-700 text-xs font-bold px-2.5 py-0.5 rounded-full">Pending</span>
                                @else
                                    <span class="bg-red-100 text-red-700 text-xs font-bold px-2.5 py-0.5 rounded-full">{{ ucfirst($invoice->status) }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-500">{{ $invoice->created_at->format('M d, Y') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-500">No invoices found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($invoices->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                    {{ $invoices->appends(['tab' => 'billing'])->links() }}
                </div>
            @endif
        </div>
    </div>

</div>

{{-- ── Action Form (Hidden) ── --}}
<form id="actionForm" method="POST" class="hidden">
    @csrf
</form>

@endsection

@push('scripts')
<script>
    // Initialization
    document.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const activeTab = urlParams.get('tab') || 'analytics';
        switchTab(activeTab);
    });

    // Tab Switching Logic
    function switchTab(tabId) {
        // Hide all panels
        document.querySelectorAll('.tab-panel').forEach(el => el.classList.add('hidden'));
        // Show target panel
        document.getElementById('tab-' + tabId).classList.remove('hidden');
        
        // Reset all buttons styling
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('border-emerald-500', 'text-emerald-600', 'dark:text-emerald-400');
            btn.classList.add('border-transparent', 'text-gray-500');
        });
        
        // Highlight active button
        const activeBtn = document.getElementById('tab-btn-' + tabId);
        activeBtn.classList.remove('border-transparent', 'text-gray-500');
        activeBtn.classList.add('border-emerald-500', 'text-emerald-600', 'dark:text-emerald-400');
    }

    // Quick Actions Logic
    function actionTenant(action) {
        if (!confirm('Are you sure you want to ' + action + ' this tenant?')) return;
        
        const form = document.getElementById('actionForm');
        form.action = `/superadmin/tenants/{{ $tenant->id }}/${action}`;
        form.submit();
    }
</script>
@endpush
