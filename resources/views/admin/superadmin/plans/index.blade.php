@extends('admin.layout')

@section('title', 'Subscription Plans')
@section('page-title', 'Subscription Plans')

@section('content')

<div class="flex justify-between items-center mb-6">
    <h2 class="text-xl font-bold text-gray-800 dark:text-white">Pricing Tiers</h2>
    <a href="{{ route('superadmin.plans.create') }}" class="bg-emerald-600 text-white px-4 py-2 rounded-xl text-sm font-bold shadow-sm hover:bg-emerald-700 transition-colors">
        <i class="fas fa-plus mr-1"></i> New Plan
    </a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden dark:bg-gray-800 dark:border-gray-700">
    <table class="w-full text-left text-sm text-gray-600 dark:text-gray-400">
        <thead class="bg-gray-50 border-b border-gray-200 text-gray-500 font-semibold uppercase text-xs tracking-wider dark:bg-gray-900/50 dark:border-gray-700">
            <tr>
                <th class="px-6 py-4">Plan Name</th>
                <th class="px-6 py-4">Price (USD)</th>
                <th class="px-6 py-4">Capacity</th>
                <th class="px-6 py-4">Trial Days</th>
                <th class="px-6 py-4">Status</th>
                <th class="px-6 py-4 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($plans as $plan)
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors">
                <td class="px-6 py-4 font-semibold text-gray-900 dark:text-white">
                    {{ $plan->name }}
                </td>
                <td class="px-6 py-4 font-bold text-emerald-600 dark:text-emerald-400">
                    {{ $plan->getFormattedPrice() }}<span class="text-xs text-gray-400 font-normal">/mo</span>
                </td>
                <td class="px-6 py-4">
                    {{ $plan->isUnlimited() ? 'Unlimited' : $plan->max_employees . ' Employees' }}
                </td>
                <td class="px-6 py-4">
                    {{ $plan->trial_days }} Days
                </td>
                <td class="px-6 py-4">
                    @if($plan->is_active)
                        <span class="text-green-600 font-semibold text-xs"><i class="fas fa-circle mr-1" style="font-size:8px"></i> Active</span>
                    @else
                        <span class="text-gray-400 font-semibold text-xs"><i class="fas fa-circle mr-1" style="font-size:8px"></i> Disabled</span>
                    @endif
                </td>
                <td class="px-6 py-4 text-right">
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('superadmin.plans.edit', $plan) }}" class="p-2 text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('superadmin.plans.destroy', $plan) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this plan? This action cannot be undone if no tenants are using it.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-6 py-8 text-center text-gray-400">
                    No plans found. Click "New Plan" to create one.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
