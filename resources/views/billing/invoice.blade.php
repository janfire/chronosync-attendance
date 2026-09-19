@extends('admin.layout')

@section('title', 'Invoice ' . $invoice->invoice_number)

@section('content')
<div class="p-6">
    <div class="mb-8 flex items-center justify-between">
        <div>
            <a href="{{ route('billing.index') }}" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 flex items-center mb-2 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i> Back to Billing
            </a>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Invoice {{ $invoice->invoice_number }}</h1>
        </div>
        <div class="flex items-center space-x-3">
            <span class="px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider border {{ $invoice->getStatusBadgeClass() }}">
                {{ $invoice->status }}
            </span>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-100 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 rounded-xl flex items-center animate-pulse">
            <i class="fas fa-check-circle mr-3"></i>
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Invoice Details -->
        <div class="lg:col-span-2 space-y-8">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-8">
                <div class="flex justify-between mb-12">
                    <div>
                        <h2 class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-4">From</h2>
                        <p class="font-bold text-gray-900 dark:text-white">ChronoSync Attendance (TaxEase ZW)</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Finance Department</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Harare, Zimbabwe</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">billing@attenda.co.zw</p>
                    </div>
                    <div class="text-right">
                        <h2 class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-4">To</h2>
                        <p class="font-bold text-gray-900 dark:text-white">{{ $invoice->tenant->company_name }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $invoice->tenant->email }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $invoice->tenant->phone }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-8 mb-12 py-6 border-y border-gray-50 dark:border-gray-700">
                    <div>
                        <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">Issue Date</p>
                        <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $invoice->created_at->format('M d, Y') }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">Due Date</p>
                        <p class="text-sm font-bold text-gray-900 dark:text-white {{ $invoice->isActuallyOverdue() ? 'text-rose-600 dark:text-rose-400' : '' }}">
                            {{ $invoice->due_date->format('M d, Y') }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">Billing Period</p>
                        <p class="text-sm font-bold text-gray-900 dark:text-white">
                            {{ $invoice->period_start->format('M d') }} - {{ $invoice->period_end->format('M d, Y') }}
                        </p>
                    </div>
                </div>

                <table class="w-full mb-12">
                    <thead>
                        <tr class="text-left border-b border-gray-100 dark:border-gray-700 text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">
                            <th class="pb-4">Description</th>
                            <th class="pb-4 text-right">Price</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-700">
                        <tr>
                            <td class="py-6">
                                <p class="font-bold text-gray-900 dark:text-white">{{ $invoice->plan->name }} Plan Subscription</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Up to {{ $invoice->plan->max_employees }} employees, full feature access.</p>
                            </td>
                            <td class="py-6 text-right font-bold text-gray-900 dark:text-white">
                                ${{ number_format($invoice->amount_usd, 2) }}
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="flex justify-end">
                    <div class="w-full max-w-xs space-y-3">
                        <div class="flex justify-between text-gray-500 dark:text-gray-400">
                            <span>Subtotal</span>
                            <span>${{ number_format($invoice->amount_usd, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-gray-500 dark:text-gray-400 pb-3 border-b border-gray-100 dark:border-gray-700">
                            <span>Tax (0%)</span>
                            <span>$0.00</span>
                        </div>
                        <div class="flex justify-between text-xl font-bold text-gray-900 dark:text-white pt-2">
                            <span>Total Due</span>
                            <span class="text-emerald-600 dark:text-emerald-400">${{ number_format($invoice->amount_usd, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Sidebar -->
        <div class="lg:col-span-1 space-y-6">
            @if($invoice->isPending())
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg dark:shadow-none border border-gray-100 dark:border-gray-700 p-6">
                    <h3 class="font-bold text-gray-900 dark:text-white mb-6 flex items-center">
                        <i class="fas fa-money-bill-wave text-emerald-500 dark:text-emerald-400 mr-2"></i>
                        Payment Instructions
                    </h3>
                    
                    <div class="space-y-6">
                        <!-- EcoCash -->
                        <div class="p-4 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl border border-emerald-100 dark:border-emerald-800/50">
                            <div class="flex items-center mb-3">
                                <span class="px-2 py-0.5 bg-emerald-600 dark:bg-emerald-500 text-white text-[10px] font-bold rounded mr-2">ECOCASH</span>
                                <span class="text-xs font-bold text-emerald-800 dark:text-emerald-400">Merchant/Personal</span>
                            </div>
                            <p class="text-sm font-bold text-emerald-950 dark:text-emerald-300 mb-1">{{ $invoice->tenant->ecocash_number ?: '0771234567' }}</p>
                            <p class="text-[10px] text-emerald-600 dark:text-emerald-500 italic">Name: T. Masiya (TaxEase ZW)</p>
                        </div>

                        <!-- ZIPIT -->
                        <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-100 dark:border-blue-800/50">
                            <div class="flex items-center mb-3">
                                <span class="px-2 py-0.5 bg-blue-600 dark:bg-blue-500 text-white text-[10px] font-bold rounded mr-2">ZIPIT</span>
                                <span class="text-xs font-bold text-blue-800 dark:text-blue-400">Bank Transfer</span>
                            </div>
                            <p class="text-sm font-bold text-blue-950 dark:text-blue-300 mb-1">{{ $invoice->tenant->zipit_account ?: 'Stanbic Bank - 914000123456' }}</p>
                            <p class="text-[10px] text-blue-600 dark:text-blue-500 italic">Branch Code: 24001 | Currency: USD</p>
                        </div>
                    </div>

                    <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-700">
                        <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-4">Submit Proof of Payment</h4>
                        <form action="{{ route('billing.upload-proof', $invoice->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-xs font-semibold text-gray-400 dark:text-gray-500 mb-1">Method Used</label>
                                <select name="payment_method" class="w-full p-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
                                    <option value="ecocash">EcoCash</option>
                                    <option value="zipit">ZIPIT</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-400 dark:text-gray-500 mb-1">Transaction/Ref ID</label>
                                <input type="text" name="payment_reference" required placeholder="e.g. MP230101.1200.H12345" class="w-full p-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-400 dark:text-gray-500 mb-1">Upload Screenshot</label>
                                <input type="file" name="payment_proof" required class="w-full text-xs text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 dark:file:bg-emerald-900/30 file:text-emerald-700 dark:file:text-emerald-400 hover:file:bg-emerald-100 dark:hover:file:bg-emerald-900/50 cursor-pointer">
                            </div>
                            <button type="submit" class="w-full py-3 bg-emerald-600 text-white font-bold rounded-xl shadow-lg shadow-emerald-200 dark:shadow-none hover:bg-emerald-700 transition-all">
                                Confirm Payment
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                    <h3 class="font-bold text-gray-900 dark:text-white mb-4">Payment Summary</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-400 dark:text-gray-500">Paid on</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ $invoice->paid_at ? $invoice->paid_at->format('M d, Y') : 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-400 dark:text-gray-500">Method</span>
                            <span class="font-medium text-gray-900 dark:text-white uppercase">{{ $invoice->payment_method }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-400 dark:text-gray-500">Reference</span>
                            <span class="font-mono text-xs text-gray-900 dark:text-white">{{ $invoice->payment_reference }}</span>
                        </div>
                    </div>
                    
                    @if($invoice->payment_proof_path)
                        <div class="mt-6 pt-6 border-t border-gray-100 dark:border-gray-700">
                            <p class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase mb-3 tracking-wider">Proof Image</p>
                            <a href="{{ asset('storage/' . $invoice->payment_proof_path) }}" target="_blank" class="block rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 hover:opacity-80 transition-opacity">
                                <img src="{{ asset('storage/' . $invoice->payment_proof_path) }}" alt="Payment Proof" class="w-full h-auto">
                            </a>
                        </div>
                    @endif
                </div>
            @endif

            <div class="p-6 bg-gray-50 dark:bg-gray-800/50 rounded-2xl border border-gray-100 dark:border-gray-700">
                <h4 class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase mb-3 tracking-wider">Need Help?</h4>
                <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                    If you have any issues with your payment, please contact our support team at <a href="mailto:support@attenda.co.zw" class="text-emerald-600 dark:text-emerald-400 font-bold underline">support@attenda.co.zw</a> or WhatsApp us on <span class="font-bold text-gray-700 dark:text-gray-300">+263 771 234 567</span>.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
