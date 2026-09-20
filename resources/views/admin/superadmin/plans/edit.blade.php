@extends('admin.layout')

@section('title', 'Edit Plan')
@section('page-title', 'Edit Subscription Plan')

@section('content')

<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('superadmin.plans.index') }}" class="text-sm font-semibold text-gray-500 hover:text-emerald-600 transition-colors">
            <i class="fas fa-arrow-left mr-1"></i> Back to Plans
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 sm:p-8 dark:bg-gray-800 dark:border-gray-700">
        <form action="{{ route('superadmin.plans.update', $plan) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-6">
                <label for="name" class="block text-sm font-bold text-gray-700 mb-2 dark:text-gray-300">Plan Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name', $plan->name) }}" required class="w-full rounded-xl border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                @error('name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="price_usd" class="block text-sm font-bold text-gray-700 mb-2 dark:text-gray-300">Price (USD / month) <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400 text-sm">$</div>
                        <input type="number" step="0.01" name="price_usd" id="price_usd" value="{{ old('price_usd', $plan->price_usd) }}" required class="w-full pl-8 rounded-xl border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                    </div>
                    @error('price_usd')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="max_employees" class="block text-sm font-bold text-gray-700 mb-2 dark:text-gray-300">Max Employees <span class="text-red-500">*</span></label>
                    <input type="number" name="max_employees" id="max_employees" value="{{ old('max_employees', $plan->max_employees) }}" required class="w-full rounded-xl border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                    <p class="text-xs text-gray-400 mt-1">Set to 0 for unlimited.</p>
                    @error('max_employees')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mb-6">
                <label for="trial_days" class="block text-sm font-bold text-gray-700 mb-2 dark:text-gray-300">Trial Days <span class="text-red-500">*</span></label>
                <input type="number" name="trial_days" id="trial_days" value="{{ old('trial_days', $plan->trial_days) }}" required class="w-full rounded-xl border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                @error('trial_days')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="features" class="block text-sm font-bold text-gray-700 mb-2 dark:text-gray-300">Features List</label>
                <textarea name="features" id="features" rows="3" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm dark:bg-gray-900 dark:border-gray-600 dark:text-white" placeholder="Advanced Reports, QR Code Attendance">{{ old('features', is_array($plan->features) ? implode(', ', $plan->features) : '') }}</textarea>
                <p class="text-xs text-gray-400 mt-1">Comma separated list of features to display on the pricing page.</p>
                @error('features')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-8">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $plan->is_active) ? 'checked' : '' }} class="rounded border-gray-300 text-emerald-600 shadow-sm focus:ring-emerald-500">
                    <span class="text-sm font-bold text-gray-700 dark:text-gray-300">Active (Visible to new users)</span>
                </label>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('superadmin.plans.index') }}" class="px-6 py-2.5 rounded-xl text-sm font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 transition-colors">
                    Cancel
                </a>
                <button type="submit" class="bg-emerald-600 text-white px-6 py-2.5 rounded-xl text-sm font-bold shadow-sm hover:bg-emerald-700 transition-colors">
                    Update Plan
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
