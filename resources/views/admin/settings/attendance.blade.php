@extends('admin.layout')

@section('title', 'Attendance & Gamification Rules')
@section('page-title', 'Attendance & Gamification')

@section('content')
<div class="space-y-6">

    <!-- Header & Reset Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm">
        <div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Attendance Rules</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage clock-in/out thresholds and gamification points.</p>
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
                
                <!-- Attendance Settings -->
                <div>
                    <h4 class="text-base font-bold text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-700 pb-2 mb-4">Clock-In Thresholds</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <!-- Early -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Early Clock-in (Before)</label>
                            <div class="flex gap-2">
                                <input type="time" name="gamification_rules[early_before]" value="{{ $settings['gamification_rules']['early_before'] }}" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                                <input type="number" name="gamification_rules[early_points]" value="{{ $settings['gamification_rules']['early_points'] }}" class="w-24 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-emerald-500 focus:border-emerald-500" placeholder="Pts">
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Set the cutoff time and points awarded for employees arriving very early.</p>
                        </div>

                        <!-- Punctual -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Punctual Clock-in (Before)</label>
                            <div class="flex gap-2">
                                <input type="time" name="gamification_rules[punctual_before]" value="{{ $settings['gamification_rules']['punctual_before'] }}" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                                <input type="number" name="gamification_rules[punctual_points]" value="{{ $settings['gamification_rules']['punctual_points'] }}" class="w-24 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-emerald-500 focus:border-emerald-500" placeholder="Pts">
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Set the official start time and the points awarded for being exactly on time.</p>
                        </div>

                        <!-- On Time -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Acceptable Delay (Before)</label>
                            <div class="flex gap-2">
                                <input type="time" name="gamification_rules[on_time_before]" value="{{ $settings['gamification_rules']['on_time_before'] }}" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                                <input type="number" name="gamification_rules[on_time_points]" value="{{ $settings['gamification_rules']['on_time_points'] }}" class="w-24 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-emerald-500 focus:border-emerald-500" placeholder="Pts">
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Minor delays that aren't strictly "late". Points can be negative to lightly penalize.</p>
                        </div>

                        <!-- Grace -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Late Grace Period (Before)</label>
                            <div class="flex gap-2">
                                <input type="time" name="gamification_rules[grace_before]" value="{{ $settings['gamification_rules']['grace_before'] }}" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                                <input type="number" name="gamification_rules[grace_points]" value="{{ $settings['gamification_rules']['grace_points'] }}" class="w-24 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-emerald-500 focus:border-emerald-500" placeholder="Pts">
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">The final window before someone is marked fully late.</p>
                        </div>

                        <!-- Late (After Grace) -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fully Late Points (After Grace Period)</label>
                            <input type="number" name="gamification_rules[late_points]" value="{{ $settings['gamification_rules']['late_points'] }}" class="w-32 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-emerald-500 focus:border-emerald-500" placeholder="Pts">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Points assigned when someone clocks in after the grace period.</p>
                        </div>
                    </div>
                </div>

                <div>
                    <h4 class="text-base font-bold text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-700 pb-2 mb-4">Clock-Out Thresholds</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <!-- Early Departure -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Severe Early Departure (Before)</label>
                            <div class="flex gap-2">
                                <input type="time" name="gamification_rules[early_departure_before]" value="{{ $settings['gamification_rules']['early_departure_before'] }}" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                                <input type="number" name="gamification_rules[early_departure_points]" value="{{ $settings['gamification_rules']['early_departure_points'] }}" class="w-24 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-emerald-500 focus:border-emerald-500" placeholder="Pts">
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Threshold for leaving shift significantly early.</p>
                        </div>

                        <!-- Left Early -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Minor Early Departure (Before)</label>
                            <div class="flex gap-2">
                                <input type="time" name="gamification_rules[left_early_before]" value="{{ $settings['gamification_rules']['left_early_before'] }}" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                                <input type="number" name="gamification_rules[left_early_points]" value="{{ $settings['gamification_rules']['left_early_points'] }}" class="w-24 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-emerald-500 focus:border-emerald-500" placeholder="Pts">
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Threshold for leaving just slightly before shift ends.</p>
                        </div>

                        <!-- Normal Departure -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Normal Departure Window (Before)</label>
                            <div class="flex gap-2">
                                <input type="time" name="gamification_rules[normal_departure_before]" value="{{ $settings['gamification_rules']['normal_departure_before'] }}" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                                <input type="number" name="gamification_rules[normal_departure_points]" value="{{ $settings['gamification_rules']['normal_departure_points'] }}" class="w-24 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-emerald-500 focus:border-emerald-500" placeholder="Pts">
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Standard closing time window.</p>
                        </div>

                            <!-- Late Departure (Overtime) -->
                            <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Late Departure / Overtime Points</label>
                            <input type="number" name="gamification_rules[late_departure_points]" value="{{ $settings['gamification_rules']['late_departure_points'] }}" class="w-32 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-emerald-500 focus:border-emerald-500" placeholder="Pts">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Points awarded for staying beyond the normal departure window.</p>
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
