@extends('admin.layout')

@section('title', 'Pending Confirmations - Finance')

@section('content')
<div class="p-8">
    <div class="mb-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight dark:text-white">Finance Operations</h1>
            <p class="text-gray-500 mt-1 text-sm font-medium dark:text-gray-400">Verify and confirm manual payments from EcoCash and ZIPIT.</p>
        </div>
        <div class="flex items-center space-x-3 bg-white p-2 rounded-2xl shadow-sm border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
            <div class="px-4 py-2 bg-amber-50 rounded-xl border border-amber-100">
                <span class="text-xs font-bold text-amber-600 uppercase tracking-wider block mb-0.5">Pending Review</span>
                <span class="text-xl font-black text-amber-900">{{ count($invoices) }}</span>
            </div>
            <div class="h-10 w-[1px] bg-gray-100 mx-2 dark:bg-gray-800"></div>
            <div class="pr-4">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block mb-0.5">Total Value</span>
                <span class="text-xl font-black text-gray-900 dark:text-white">${{ number_format($invoices->sum('amount_usd'), 2) }}</span>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-8 p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-2xl flex items-center shadow-sm animate-in fade-in slide-in-from-top-4 duration-500">
            <div class="w-8 h-8 bg-emerald-500 text-white rounded-full flex items-center justify-center mr-3 shadow-lg shadow-emerald-500/20">
                <i class="fas fa-check text-xs"></i>
            </div>
            <span class="font-semibold">{{ session('success') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6">
        @forelse($invoices as $invoice)
            <div class="group bg-white rounded-3xl shadow-sm hover:shadow-xl hover:shadow-emerald-900/5 border border-gray-100 transition-all duration-300 overflow-hidden dark:bg-gray-800 dark:border-gray-700">
                <div class="flex flex-col lg:flex-row">
                    <!-- Left: Tenant & Invoice Info -->
                    <div class="p-8 lg:w-1/3 border-b lg:border-b-0 lg:border-r border-gray-50">
                        <div class="flex items-center mb-6">
                            <div class="w-12 h-12 bg-gray-900 text-white rounded-2xl flex items-center justify-center font-black text-lg mr-4 shadow-lg shadow-gray-900/10 uppercase">
                                {{ substr($invoice->tenant->company_name, 0, 2) }}
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900 text-lg leading-tight dark:text-white">{{ $invoice->tenant->company_name }}</h3>
                                <p class="text-emerald-600 text-xs font-bold uppercase tracking-wider mt-1">{{ $invoice->tenant->subdomain }}.attenda.zw</p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="flex justify-between items-end">
                                <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Invoice</span>
                                <span class="text-sm font-black text-gray-900 dark:text-white">{{ $invoice->invoice_number }}</span>
                            </div>
                            <div class="flex justify-between items-end">
                                <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Amount Due</span>
                                <span class="text-2xl font-black text-emerald-600">${{ number_format($invoice->amount_usd, 2) }}</span>
                            </div>
                            <div class="flex justify-between items-end">
                                <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Period</span>
                                <span class="text-sm font-semibold text-gray-600 dark:text-gray-300">{{ $invoice->period_start->format('M d') }} - {{ $invoice->period_end->format('M d, Y') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Middle: Payment Proof -->
                    <div class="p-8 lg:w-1/3 bg-gray-50/50 flex flex-col justify-center border-b lg:border-b-0 lg:border-r border-gray-50 dark:bg-gray-900">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">Payment Submission</h4>
                        
                        <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm mb-4 dark:bg-gray-800 dark:border-gray-700">
                            <div class="flex items-center mb-3">
                                <span class="px-2 py-0.5 bg-{{ $invoice->payment_method === 'ecocash' ? 'emerald' : 'blue' }}-600 text-white text-[10px] font-bold rounded uppercase mr-2 tracking-widest">
                                    {{ $invoice->payment_method }}
                                </span>
                                <span class="text-xs font-mono font-bold text-gray-900 dark:text-white">{{ $invoice->payment_reference }}</span>
                            </div>
                            <p class="text-[10px] text-gray-400 italic">Submitted {{ $invoice->updated_at->diffForHumans() }}</p>
                        </div>

                        @if($invoice->payment_proof_path)
                            <button onclick="openModal('{{ asset('storage/' . $invoice->payment_proof_path) }}')" class="relative rounded-2xl overflow-hidden group/img cursor-pointer">
                                <img src="{{ asset('storage/' . $invoice->payment_proof_path) }}" alt="Proof" class="w-full h-24 object-cover blur-[1px] group-hover/img:blur-0 transition-all duration-500">
                                <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-100 group-hover/img:opacity-0 transition-opacity">
                                    <span class="text-white text-xs font-bold flex items-center">
                                        <i class="fas fa-search-plus mr-2"></i> View Full Proof
                                    </span>
                                </div>
                            </button>
                        @else
                            <div class="h-24 rounded-2xl border-2 border-dashed border-gray-200 flex items-center justify-center dark:border-gray-700">
                                <span class="text-xs text-gray-400 font-medium italic text-center px-4">No screenshot uploaded. Verification required via bank/gateway portal.</span>
                            </div>
                        @endif
                    </div>

                    <!-- Right: Actions -->
                    <div class="p-8 lg:w-1/3 flex flex-col justify-center">
                        <form action="{{ route('superadmin.finance.confirm', $invoice->id) }}" method="POST" onsubmit="return confirm('Confirm this payment and reactivate tenant subscription?')">
                            @csrf
                            <button type="submit" class="w-full py-4 bg-emerald-600 text-white font-bold rounded-2xl shadow-xl shadow-emerald-500/20 hover:bg-emerald-700 hover:-translate-y-0.5 transition-all duration-300 flex items-center justify-center">
                                <i class="fas fa-check-double mr-3"></i> Confirm & Activate
                            </button>
                        </form>
                        <p class="text-[10px] text-gray-400 text-center mt-4 leading-relaxed">
                            By confirming, you verify that the reference ID has been reconciled in our bank/EcoCash statement. This action extends the tenant's subscription by 30 days.
                        </p>
                    </div>
                </div>
            </div>
        @empty
            <div class="py-24 flex flex-col items-center justify-center bg-white rounded-3xl border border-dashed border-gray-200 dark:bg-gray-800 dark:border-gray-700">
                <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center text-gray-300 mb-6 dark:bg-gray-900">
                    <i class="fas fa-inbox text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">All caught up!</h3>
                <p class="text-gray-500 dark:text-gray-400">No pending payment confirmations at the moment.</p>
            </div>
        @endforelse
    </div>
</div>

<!-- Modal for Image Preview -->
<div id="imageModal" class="fixed inset-0 bg-black/90 z-[999] hidden items-center justify-center p-8 backdrop-blur-sm" onclick="closeModal()">
    <button class="absolute top-8 right-8 text-white text-3xl hover:text-gray-300 transition-colors">
        <i class="fas fa-times"></i>
    </button>
    <img id="modalImg" src="" alt="Proof Preview" class="max-w-full max-h-full rounded-xl shadow-2xl scale-95 transition-transform duration-300" onclick="event.stopPropagation()">
</div>

<script>
    function openModal(src) {
        const modal = document.getElementById('imageModal');
        const img = document.getElementById('modalImg');
        img.src = src;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => img.classList.remove('scale-95'), 10);
    }

    function closeModal() {
        const modal = document.getElementById('imageModal');
        const img = document.getElementById('modalImg');
        img.classList.add('scale-95');
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 300);
    }
</script>
@endsection
