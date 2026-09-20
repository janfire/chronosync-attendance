@extends('admin.layout')

@section('title', 'Platform Settings')
@section('page-title', 'Global Platform Settings')

@section('content')

<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white">System Configuration</h2>
        <p class="text-sm text-gray-500 mt-1 dark:text-gray-400">Manage global settings, maintenance mode, and trial configurations for all tenants.</p>
    </div>

    <form action="{{ route('superadmin.settings.update') }}" method="POST">
        @csrf
        @method('PUT')

        {{-- Maintenance Mode Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden mb-6 dark:bg-gray-800 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center gap-3 dark:bg-gray-900 dark:border-gray-700">
                <i class="fas fa-tools text-gray-400"></i>
                <h3 class="font-bold text-gray-800 dark:text-white">Maintenance Mode</h3>
            </div>
            
            <div class="p-6">
                <div class="mb-6">
                    <label class="flex items-start gap-4 cursor-pointer">
                        <div class="mt-0.5">
                            <input type="checkbox" name="maintenance_mode" value="1" {{ $settings['maintenance_mode'] ? 'checked' : '' }} class="rounded border-gray-300 text-emerald-600 shadow-sm focus:ring-emerald-500 w-5 h-5 transition-colors">
                        </div>
                        <div>
                            <span class="block text-sm font-bold text-gray-900 dark:text-white">Enable Maintenance Mode</span>
                            <span class="block text-xs text-gray-500 mt-1 dark:text-gray-400">When enabled, only Platform Admins can access the system. Tenant users will see the maintenance message below.</span>
                        </div>
                    </label>
                </div>

                <div>
                    <label for="maintenance_message" class="block text-sm font-bold text-gray-700 mb-2 dark:text-gray-300">Maintenance Message</label>
                    <textarea name="maintenance_message" id="maintenance_message" rows="3" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm dark:bg-gray-900 dark:border-gray-600 dark:text-white">{{ old('maintenance_message', $settings['maintenance_message']) }}</textarea>
                </div>
            </div>
        </div>

        {{-- Registration & Trial Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden mb-8 dark:bg-gray-800 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center gap-3 dark:bg-gray-900 dark:border-gray-700">
                <i class="fas fa-user-plus text-gray-400"></i>
                <h3 class="font-bold text-gray-800 dark:text-white">Onboarding & Trials</h3>
            </div>
            
            <div class="p-6">
                <div class="mb-6">
                    <label class="flex items-start gap-4 cursor-pointer">
                        <div class="mt-0.5">
                            <input type="checkbox" name="allow_new_registrations" value="1" {{ $settings['allow_new_registrations'] ? 'checked' : '' }} class="rounded border-gray-300 text-emerald-600 shadow-sm focus:ring-emerald-500 w-5 h-5 transition-colors">
                        </div>
                        <div>
                            <span class="block text-sm font-bold text-gray-900 dark:text-white">Allow New Registrations</span>
                            <span class="block text-xs text-gray-500 mt-1 dark:text-gray-400">Allow new companies to sign up for the platform automatically.</span>
                        </div>
                    </label>
                </div>

                <div>
                    <label for="default_trial_days" class="block text-sm font-bold text-gray-700 mb-2 dark:text-gray-300">Default Trial Duration (Days)</label>
                    <div class="relative w-48">
                        <input type="number" name="default_trial_days" id="default_trial_days" min="0" value="{{ old('default_trial_days', $settings['default_trial_days']) }}" required class="w-full rounded-xl border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm dark:bg-gray-900 dark:border-gray-600 dark:text-white pr-12">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-400 text-sm">Days</div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2 dark:text-gray-400">Set to 0 to require immediate payment upon registration.</p>
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="bg-emerald-600 text-white px-6 py-2.5 rounded-xl text-sm font-bold shadow-sm hover:bg-emerald-700 transition-colors">
                Save All Settings
            </button>
        </div>
    </form>
</div>

@endsection
