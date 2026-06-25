@extends('admin.layout')

@section('title', 'Billing & Subscription')

@section('content')
<div class="p-6">

    {{-- Expiry / suspension alert banner --}}
    @if(!$tenant->canAccess())
        <div class="mb-8 flex items-start gap-4 bg-white border-l-4 border-rose-500 rounded-2xl px-5 py-4 shadow-sm">
            <div class="w-9 h-9 bg-rose-100 text-rose-600 rounded-full flex items-center justify-center shrink-0 mt-0.5">
                <i class="fas fa-lock text-sm"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-bold text-gray-900">Subscription Expired — Access Suspended</p>
                <p class="text-sm text-gray-500 mt-0.5">Your account has been suspended due to a lapsed subscription. Generate an invoice, pay via EcoCash or ZIPIT, then upload your proof below.</p>
            </div>
        </div>
    @elseif($tenant->subscription_expires_at && $tenant->subscription_expires_at->diffInDays(now(), false) >= -7)
        <div class="mb-8 flex items-start gap-4 bg-white border-l-4 border-amber-400 rounded-2xl px-5 py-4 shadow-sm">
            <div class="w-9 h-9 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center shrink-0 mt-0.5">
                <i class="fas fa-clock text-sm"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-bold text-gray-900">Subscription expires {{ $tenant->subscription_expires_at->diffForHumans() }}</p>
                <p class="text-sm text-gray-500 mt-0.5">Renew now to avoid any interruption to your service. Use the button below to generate your invoice.</p>
            </div>
        </div>
    @endif

    @if(session('success'))
        <div class="mb-6 flex items-center gap-3 bg-white border-l-4 border-emerald-500 rounded-2xl px-5 py-4 shadow-sm">
            <i class="fas fa-check-circle text-emerald-500 shrink-0"></i>
            <span class="text-sm font-medium text-gray-700">{{ session('success') }}</span>
        </div>
    @endif

    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Billing &amp; Subscription</h1>
        <p class="text-gray-500">Manage your company's plan and payment history.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Current Plan Card -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-50 bg-gray-50/50">
                    <h2 class="font-semibold text-gray-900">Current Plan</h2>
                </div>
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <span class="px-3 py-1 bg-emerald-100 text-emerald-700 text-xs font-bold rounded-full uppercase tracking-wider">
                            {{ $tenant->getPlanLabel() }}
                        </span>
                        <span class="text-sm text-gray-500">
                            Status: <span class="font-medium text-{{ $tenant->canAccess() ? 'emerald' : 'rose' }}-600">{{ ucfirst($tenant->status) }}</span>
                        </span>
                    </div>

                    <div class="mb-6">
                        <span class="text-4xl font-bold text-gray-900">$35</span>
                        <span class="text-gray-400">/ month</span>
                    </div>

                    <ul class="space-y-3 mb-8">
                        <li class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-check-circle text-emerald-500 mr-2"></i>
                            Up to {{ $tenant->max_employees }} Employees
                        </li>
                        <li class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-check-circle text-emerald-500 mr-2"></i>
                            Facial Recognition
                        </li>
                        <li class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-check-circle text-emerald-500 mr-2"></i>
                            Attendance Analytics
                        </li>
                    </ul>

                    @if($tenant->subscription_expires_at)
                        <div class="p-4 bg-blue-50 rounded-xl border border-blue-100 mb-6">
                            <p class="text-xs text-blue-600 font-semibold uppercase tracking-wider mb-1">Expires On</p>
                            <p class="text-sm text-blue-900 font-bold">{{ $tenant->subscription_expires_at->format('M d, Y') }}</p>
                            <p class="text-xs text-blue-500 mt-1">
                                {{ $tenant->subscription_expires_at->diffForHumans() }}
                            </p>
                        </div>
                    @endif

                    <button disabled class="w-full py-3 bg-gray-100 text-gray-400 font-medium rounded-xl cursor-not-allowed">
                        Change Plan
                    </button>
                    <p class="text-[10px] text-gray-400 text-center mt-2 italic">Self-service plan switching coming soon.</p>

                    {{-- Renew / Generate Invoice CTA --}}
                    @php
                        $pendingInvoice = $invoices->firstWhere('status', 'pending');
                        $showRenew = !$tenant->isActive()
                            || ($tenant->subscription_expires_at && $tenant->subscription_expires_at->diffInDays(now(), false) >= -7);
                    @endphp

                    @if($showRenew)
                        <div class="mt-4 pt-4 border-t border-gray-100">
                            @if($pendingInvoice)
                                <a href="{{ route('billing.invoice', $pendingInvoice->id) }}"
                                   class="block w-full py-3 text-center bg-emerald-600 text-white font-bold rounded-xl shadow-lg shadow-emerald-100 hover:bg-emerald-700 transition-all">
                                    <i class="fas fa-file-invoice-dollar mr-2"></i> View Pending Invoice
                                </a>
                                <p class="text-[10px] text-gray-400 text-center mt-2">Invoice {{ $pendingInvoice->invoice_number }} awaiting payment.</p>
                            @else
                                <form action="{{ route('billing.renew') }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                            class="w-full py-3 bg-emerald-600 text-white font-bold rounded-xl shadow-lg shadow-emerald-100 hover:bg-emerald-700 transition-all">
                                        <i class="fas fa-sync-alt mr-2"></i> Generate Renewal Invoice
                                    </button>
                                </form>
                                <p class="text-[10px] text-gray-400 text-center mt-2">A new invoice will be created instantly.</p>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Invoices List -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-50 flex items-center justify-between">
                    <h2 class="font-semibold text-gray-900">Invoice History</h2>
                    <span class="text-xs text-gray-400">Showing all records</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-gray-50/50 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                                <th class="px-6 py-4">Invoice #</th>
                                <th class="px-6 py-4">Date</th>
                                <th class="px-6 py-4">Amount</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($invoices as $invoice)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ $invoice->invoice_number }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $invoice->created_at->format('M d, Y') }}</td>
                                    <td class="px-6 py-4 text-sm font-bold text-gray-900">${{ number_format($invoice->amount_usd, 2) }}</td>
                                    <td class="px-6 py-4">
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide border {{ $invoice->getStatusBadgeClass() }}">
                                            {{ $invoice->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <a href="{{ route('billing.invoice', $invoice->id) }}" class="text-emerald-600 hover:text-emerald-700 font-semibold text-sm">
                                            View Details
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-400 italic">
                                        No invoices found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
