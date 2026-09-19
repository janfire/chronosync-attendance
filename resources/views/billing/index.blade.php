@extends('admin.layout')

@section('title', 'Billing & Subscription')

@section('content')
<div class="p-6">

    {{-- Expiry / suspension alert banner --}}
    @if(!$tenant->canAccess())
        <div class="mb-8 flex items-start gap-4 bg-white border-l-4 border-rose-500 rounded-2xl px-5 py-4 shadow-sm dark:bg-rose-900/20 dark:border-rose-800">
            <div class="w-9 h-9 bg-rose-100 text-rose-600 rounded-full flex items-center justify-center shrink-0 mt-0.5 dark:bg-rose-900/50 dark:text-rose-400">
                <i class="fas fa-lock text-sm"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-bold text-gray-900 dark:text-rose-300">Subscription Expired — Access Suspended</p>
                <p class="text-sm text-gray-500 mt-0.5 dark:text-rose-400">Your account has been suspended due to a lapsed subscription. Generate an invoice, pay via EcoCash or ZIPIT, then upload your proof below.</p>
            </div>
        </div>
    @elseif($tenant->subscription_expires_at && $tenant->subscription_expires_at->diffInDays(now(), false) >= -7)
        <div class="mb-8 flex items-start gap-4 bg-white border-l-4 border-amber-400 rounded-2xl px-5 py-4 shadow-sm dark:bg-amber-900/20 dark:border-amber-800">
            <div class="w-9 h-9 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center shrink-0 mt-0.5 dark:bg-amber-900/50 dark:text-amber-400">
                <i class="fas fa-clock text-sm"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-bold text-gray-900 dark:text-amber-300">Subscription expires {{ $tenant->subscription_expires_at->diffForHumans() }}</p>
                <p class="text-sm text-gray-500 mt-0.5 dark:text-amber-400">Renew now to avoid any interruption to your service. Use the button below to generate your invoice.</p>
            </div>
        </div>
    @endif

    @if(session('success'))
        <div class="mb-6 flex items-center gap-3 bg-white border-l-4 border-emerald-500 rounded-2xl px-5 py-4 shadow-sm dark:bg-emerald-900/20 dark:border-emerald-800">
            <i class="fas fa-check-circle text-emerald-500 shrink-0 dark:text-emerald-400"></i>
            <span class="text-sm font-medium text-gray-700 dark:text-emerald-300">{{ session('success') }}</span>
        </div>
    @endif

    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Billing &amp; Subscription</h1>
        <p class="text-gray-500 dark:text-gray-400">Manage your company's plan and payment history.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Current Plan Card -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden dark:bg-gray-800 dark:border-gray-700">
                <div class="p-6 border-b border-gray-50 bg-gray-50/50 dark:border-gray-700 dark:bg-gray-700/50">
                    <h2 class="font-semibold text-gray-900 dark:text-white">Current Plan</h2>
                </div>
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <span class="px-3 py-1 bg-emerald-100 text-emerald-700 text-xs font-bold rounded-full uppercase tracking-wider dark:bg-emerald-900/50 dark:text-emerald-400">
                            {{ $tenant->getPlanLabel() }}
                        </span>
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            Status: <span class="font-medium text-{{ $tenant->canAccess() ? 'emerald' : 'rose' }}-600 dark:text-{{ $tenant->canAccess() ? 'emerald' : 'rose' }}-400">{{ ucfirst($tenant->status) }}</span>
                        </span>
                    </div>

                    <div class="mb-6">
                        <span class="text-4xl font-bold text-gray-900 dark:text-white">$35</span>
                        <span class="text-gray-400 dark:text-gray-500">/ month</span>
                    </div>

                    <ul class="space-y-3 mb-8">
                        <li class="flex items-center text-sm text-gray-600 dark:text-gray-300">
                            <i class="fas fa-check-circle text-emerald-500 mr-2 dark:text-emerald-400"></i>
                            Up to {{ $tenant->max_employees }} Employees
                        </li>
                        <li class="flex items-center text-sm text-gray-600 dark:text-gray-300">
                            <i class="fas fa-check-circle text-emerald-500 mr-2 dark:text-emerald-400"></i>
                            Facial Recognition
                        </li>
                        <li class="flex items-center text-sm text-gray-600 dark:text-gray-300">
                            <i class="fas fa-check-circle text-emerald-500 mr-2 dark:text-emerald-400"></i>
                            Attendance Analytics
                        </li>
                    </ul>

                    @if($tenant->subscription_expires_at)
                        <div class="p-4 bg-blue-50 rounded-xl border border-blue-100 mb-6 dark:bg-blue-900/20 dark:border-blue-800">
                            <p class="text-xs text-blue-600 font-semibold uppercase tracking-wider mb-1 dark:text-blue-400">Expires On</p>
                            <p class="text-sm text-blue-900 font-bold dark:text-blue-300">{{ $tenant->subscription_expires_at->format('M d, Y') }}</p>
                            <p class="text-xs text-blue-500 mt-1 dark:text-blue-400">
                                {{ $tenant->subscription_expires_at->diffForHumans() }}
                            </p>
                        </div>
                    @endif

                    <button type="button" onclick="document.getElementById('changePlanModal').classList.remove('hidden')" class="w-full py-3 bg-blue-50 text-blue-600 font-bold rounded-xl shadow-sm hover:bg-blue-100 transition-all dark:bg-blue-900/30 dark:text-blue-400 dark:hover:bg-blue-900/50">
                        Change Plan
                    </button>

                    {{-- Renew / Generate Invoice CTA --}}
                    @php
                        $pendingInvoice = $invoices->firstWhere('status', 'pending');
                        $showRenew = !$tenant->isActive()
                            || ($tenant->subscription_expires_at && $tenant->subscription_expires_at->diffInDays(now(), false) >= -7);
                    @endphp

                    @if($showRenew)
                        <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                            @if($pendingInvoice)
                                <a href="{{ route('billing.invoice', $pendingInvoice->id) }}"
                                   class="block w-full py-3 text-center bg-emerald-600 text-white font-bold rounded-xl shadow-lg shadow-emerald-100 hover:bg-emerald-700 transition-all dark:shadow-none">
                                    <i class="fas fa-file-invoice-dollar mr-2"></i> View Pending Invoice
                                </a>
                                <p class="text-[10px] text-gray-400 text-center mt-2 dark:text-gray-500">Invoice {{ $pendingInvoice->invoice_number }} awaiting payment.</p>
                            @else
                                <form action="{{ route('billing.renew') }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                            class="w-full py-3 bg-emerald-600 text-white font-bold rounded-xl shadow-lg shadow-emerald-100 hover:bg-emerald-700 transition-all dark:shadow-none">
                                        <i class="fas fa-sync-alt mr-2"></i> Generate Renewal Invoice
                                    </button>
                                </form>
                                <p class="text-[10px] text-gray-400 text-center mt-2 dark:text-gray-500">A new invoice will be created instantly.</p>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Invoices List -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden dark:bg-gray-800 dark:border-gray-700">
                <div class="p-6 border-b border-gray-50 flex items-center justify-between dark:border-gray-700">
                    <h2 class="font-semibold text-gray-900 dark:text-white">Invoice History</h2>
                    <span class="text-xs text-gray-400 dark:text-gray-500">Showing all records</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-gray-50/50 text-xs font-semibold text-gray-400 uppercase tracking-wider dark:bg-gray-700/50 dark:text-gray-500">
                                <th class="px-6 py-4">Invoice #</th>
                                <th class="px-6 py-4">Date</th>
                                <th class="px-6 py-4">Amount</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-gray-700">
                            @forelse($invoices as $invoice)
                                <tr class="hover:bg-gray-50 transition-colors dark:hover:bg-gray-700/50">
                                    <td class="px-6 py-4 font-medium text-gray-900 dark:text-gray-200">{{ $invoice->invoice_number }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $invoice->created_at->format('M d, Y') }}</td>
                                    <td class="px-6 py-4 text-sm font-bold text-gray-900 dark:text-white">${{ number_format($invoice->amount_usd, 2) }}</td>
                                    <td class="px-6 py-4">
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide border {{ $invoice->getStatusBadgeClass() }}">
                                            {{ $invoice->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <a href="{{ route('billing.invoice', $invoice->id) }}" class="text-emerald-600 hover:text-emerald-700 font-semibold text-sm dark:text-emerald-400 dark:hover:text-emerald-300">
                                            View Details
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-400 italic dark:text-gray-500">
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

<!-- Change Plan Modal -->
<div id="changePlanModal" class="fixed inset-0 z-50 hidden bg-gray-900/50 backdrop-blur-sm overflow-y-auto w-full h-full flex items-center justify-center">
    <div class="relative w-full max-w-4xl mx-4 my-8 bg-white dark:bg-gray-800 rounded-3xl shadow-2xl overflow-hidden border border-gray-100 dark:border-gray-700">
        <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white">Choose Your Plan</h3>
            <button onclick="document.getElementById('changePlanModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 bg-gray-50 dark:bg-gray-700 w-8 h-8 rounded-full flex items-center justify-center transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form action="{{ route('billing.change-plan') }}" method="POST" class="p-6">
            @csrf
            
            @if($tenant->upcoming_plan)
                <div class="mb-6 bg-blue-50 dark:bg-blue-900/20 text-blue-800 dark:text-blue-300 p-4 rounded-xl text-sm border border-blue-100 dark:border-blue-800">
                    <i class="fas fa-info-circle mr-2"></i> You have a scheduled plan change to <strong>{{ \App\Models\SubscriptionPlan::where('slug', $tenant->upcoming_plan)->value('name') }}</strong> at the end of your billing cycle. Selecting a new plan here will overwrite it.
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                @foreach(\App\Models\SubscriptionPlan::orderBy('price_usd')->get() as $plan)
                    <label class="relative block cursor-pointer group">
                        <input type="radio" name="plan" value="{{ $plan->slug }}" class="peer sr-only" required {{ $tenant->plan === $plan->slug && !$tenant->upcoming_plan ? 'disabled' : '' }}>
                        <div class="h-full rounded-2xl border-2 p-6 transition-all {{ $tenant->plan === $plan->slug && !$tenant->upcoming_plan ? 'border-emerald-500 bg-emerald-50/30 dark:bg-emerald-900/10' : 'border-gray-200 dark:border-gray-700 peer-checked:border-blue-500 peer-checked:bg-blue-50/50 dark:peer-checked:bg-blue-900/20 group-hover:border-blue-300 dark:group-hover:border-blue-600' }}">
                            @if($tenant->plan === $plan->slug && !$tenant->upcoming_plan)
                                <span class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-1 bg-emerald-500 text-white text-[10px] font-bold uppercase tracking-widest rounded-full shadow-sm">Current Plan</span>
                            @endif
                            <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-2">{{ $plan->name }}</h4>
                            <div class="flex items-baseline mb-4">
                                <span class="text-3xl font-extrabold text-gray-900 dark:text-white">${{ $plan->price_usd }}</span>
                                <span class="text-sm text-gray-500 dark:text-gray-400 ml-1">/mo</span>
                            </div>
                            <ul class="space-y-3 text-sm text-gray-600 dark:text-gray-300">
                                <li class="flex items-start">
                                    <i class="fas fa-check text-emerald-500 mt-1 mr-2 text-xs"></i>
                                    <span>{{ $plan->max_employees == 0 ? 'Unlimited' : 'Up to ' . $plan->max_employees }} Employees</span>
                                </li>
                                @if(is_array($plan->features))
                                    @foreach($plan->features as $feature)
                                        <li class="flex items-start">
                                            <i class="fas fa-check text-emerald-500 mt-1 mr-2 text-xs"></i>
                                            <span>{{ $feature }}</span>
                                        </li>
                                    @endforeach
                                @endif
                            </ul>
                        </div>
                    </label>
                @endforeach
            </div>

            <div class="bg-gray-50 dark:bg-gray-700/30 rounded-xl p-5 mb-6 text-sm text-gray-600 dark:text-gray-300">
                <p class="font-bold text-gray-900 dark:text-white mb-2"><i class="fas fa-balance-scale mr-2 text-gray-400"></i> How changes work:</p>
                <ul class="list-disc pl-5 space-y-1">
                    <li>
                        <strong>Upgrades:</strong> You will be immediately invoiced for the prorated difference for the rest of your billing cycle.
                        <span id="dynamicProrationText" class="hidden text-emerald-600 dark:text-emerald-400 font-bold ml-1"></span>
                    </li>
                    <li><strong>Downgrades:</strong> Take effect at the end of your current billing cycle. Make sure you don't exceed the employee limit of the new plan!</li>
                </ul>
            </div>

            <div class="flex justify-end gap-3 border-t border-gray-100 dark:border-gray-700 pt-6">
                <button type="button" onclick="document.getElementById('changePlanModal').classList.add('hidden')" class="px-5 py-2.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-700 dark:text-gray-300 font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">Cancel</button>
                <button type="submit" class="px-5 py-2.5 bg-blue-600 text-white font-bold rounded-xl shadow-lg shadow-blue-200 dark:shadow-none hover:bg-blue-700 transition-colors">Confirm Selection</button>
            </div>
        </form>
    </div>
</div>

@php
    // Calculate proration variables for JS
    $currentPlanPrice = \App\Models\SubscriptionPlan::where('slug', $tenant->plan)->value('price_usd') ?? 0;
    
    $totalDays = $tenant->subscription_starts_at && $tenant->subscription_expires_at 
        ? $tenant->subscription_starts_at->diffInDays($tenant->subscription_expires_at) 
        : 30;
    if ($totalDays <= 0) $totalDays = 30;

    $daysRemaining = $tenant->subscription_expires_at && $tenant->subscription_expires_at->isFuture()
        ? now()->diffInDays($tenant->subscription_expires_at)
        : 0;
@endphp

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const currentPrice = {{ $currentPlanPrice }};
        const totalDays = {{ $totalDays }};
        const daysRemaining = {{ $daysRemaining }};
        
        const planRadios = document.querySelectorAll('input[name="plan"]');
        const prorationText = document.getElementById('dynamicProrationText');

        planRadios.forEach(radio => {
            radio.addEventListener('change', (e) => {
                // Find the price of the selected plan. We attached it to a data attribute.
                // Let's get the price text from the DOM.
                const parentDiv = e.target.nextElementSibling;
                const priceText = parentDiv.querySelector('.text-3xl').innerText.replace('$', '');
                const newPrice = parseFloat(priceText);

                if (newPrice > currentPrice) {
                    const currentDailyRate = currentPrice / totalDays;
                    const newDailyRate = newPrice / totalDays;
                    const proratedAmount = (newDailyRate - currentDailyRate) * daysRemaining;
                    
                    if (proratedAmount > 0) {
                        prorationText.innerHTML = `(Upgrading today will cost $${proratedAmount.toFixed(2)})`;
                        prorationText.classList.remove('hidden');
                    } else {
                        prorationText.classList.add('hidden');
                    }
                } else {
                    prorationText.classList.add('hidden');
                }
            });
        });
    });
</script>

@endsection
