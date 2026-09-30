@extends('admin.layout')

@section('title', 'Security & Biometrics Settings')
@section('page-title', 'Security & Biometrics')

@section('content')
<div class="space-y-6">

    <!-- Header & Reset Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm">
        <div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Security & Biometrics</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage AI facial recognition sensitivities.</p>
        </div>
        <div>
            <form action="{{ route('admin.settings.reset') }}" method="POST" onsubmit="return confirm('Are you sure you want to reset all settings to their system defaults? This action cannot be undone.');">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 dark:bg-rose-900/20 dark:hover:bg-rose-900/40 dark:text-rose-400 rounded-lg text-sm font-medium transition-colors border border-rose-200 dark:border-rose-800">
                    <i class="fas fa-undo"></i> Reset to Defaults
                </button>
            </form>
        </div>
    </div>

    <!-- Main Settings Card -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        
        <form action="{{ route('admin.settings.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="p-6 md:p-8 space-y-8">
                
                <div>
                    <h4 class="text-base font-bold text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-700 pb-2 mb-4">Biometric Sensitivities</h4>
                    
                    <div class="grid grid-cols-1 gap-6">
                        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4 flex gap-3">
                            <i class="fas fa-info-circle text-blue-500 mt-0.5"></i>
                            <p class="text-sm text-blue-800 dark:text-blue-300">Tolerance dictates how strictly the AI matches a face. <b>Lower is stricter</b> (less false positives, but harder to scan). <b>Higher is looser</b> (easier to scan, but risks false matches).</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Standard Clock-in Tolerance</label>
                                <input type="number" step="0.01" min="0" max="1" name="biometric_tolerances[default]" value="{{ $settings['biometric_tolerances']['default'] }}" class="w-32 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Used during daily attendance scans. Default is 0.38.</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Enrollment Duplicate Check</label>
                                <input type="number" step="0.01" min="0" max="1" name="biometric_tolerances[enrollment]" value="{{ $settings['biometric_tolerances']['enrollment'] }}" class="w-32 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Used when ensuring a newly registered face isn't already in the system. Slightly looser (e.g. 0.42).</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Footer Save Actions -->
            <div class="bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700 px-6 py-4 flex justify-end">
                <button type="submit" class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2.5 rounded-lg text-sm font-bold shadow-sm transition-colors focus:ring-4 focus:ring-emerald-500/20">
                    <i class="fas fa-save"></i> Save Configuration
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
