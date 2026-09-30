@extends('admin.layout')

@section('title', 'System Settings')
@section('page-title', 'System Settings')

@push('styles')
<style>
    .settings-tab-content { display: none; }
    .settings-tab-content.active { display: block; animation: fadeIn 0.3s ease; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
</style>
@endpush

@section('content')
<div class="space-y-6">

    <!-- Header & Reset Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm">
        <div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Workspace Configuration</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage attendance rules, security timeouts, and application limits for your workspace.</p>
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
        
        <!-- Tabs -->
        <div class="flex overflow-x-auto border-b border-gray-200 dark:border-gray-700">
            <button type="button" class="settings-tab-btn active px-6 py-4 text-sm font-medium text-emerald-600 dark:text-emerald-400 border-b-2 border-emerald-500 whitespace-nowrap" data-target="#tab-attendance">
                <i class="fas fa-clock mr-2"></i> Attendance & Gamification
            </button>
            <button type="button" class="settings-tab-btn px-6 py-4 text-sm font-medium text-gray-500 dark:text-gray-400 border-b-2 border-transparent hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600 whitespace-nowrap" data-target="#tab-locations">
                <i class="fas fa-map-marker-alt mr-2"></i> Locations & Geofences
            </button>
            <button type="button" class="settings-tab-btn px-6 py-4 text-sm font-medium text-gray-500 dark:text-gray-400 border-b-2 border-transparent hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600 whitespace-nowrap" data-target="#tab-security">
                <i class="fas fa-shield-alt mr-2"></i> Security & Biometrics
            </button>
            <button type="button" class="settings-tab-btn px-6 py-4 text-sm font-medium text-gray-500 dark:text-gray-400 border-b-2 border-transparent hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600 whitespace-nowrap" data-target="#tab-ui">
                <i class="fas fa-desktop mr-2"></i> Preferences & Limits
            </button>
        </div>

        <form action="{{ route('admin.settings.update') }}" method="POST" id="settingsForm">
            @csrf
            @method('PUT')

            <div class="p-6 md:p-8">
                
                <!-- Tab: Attendance & Gamification -->
                <div id="tab-attendance" class="settings-tab-content active space-y-8">
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

                <!-- Tab: Locations -->
                <div id="tab-locations" class="settings-tab-content space-y-6">
                    <div>
                        <h4 class="text-base font-bold text-gray-900 dark:text-white mb-1">Authorized Campuses & Geofences</h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Define the geographic coordinates where employees are allowed to clock in. GPS scans will be measured against the radius of these locations.</p>
                        
                        <div class="hidden">
                            <input type="hidden" name="geofences_submitted" value="1">
                        </div>

                        <div id="geofences-container" class="space-y-4">
                            @foreach($settings['geofences'] as $index => $geofence)
                                <div class="geofence-row flex flex-col md:flex-row gap-3 bg-gray-50 dark:bg-gray-900/50 p-4 rounded-xl border border-gray-200 dark:border-gray-700">
                                    <div class="flex-1">
                                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wide">Location Name</label>
                                        <input type="text" name="geofences[{{$index}}][name]" value="{{ $geofence['name'] }}" required class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                                    </div>
                                    <div class="w-full md:w-32">
                                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wide">Latitude</label>
                                        <input type="number" step="any" name="geofences[{{$index}}][lat]" value="{{ $geofence['lat'] }}" required class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                                    </div>
                                    <div class="w-full md:w-32">
                                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wide">Longitude</label>
                                        <input type="number" step="any" name="geofences[{{$index}}][lon]" value="{{ $geofence['lon'] }}" required class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                                    </div>
                                    <div class="w-full md:w-32">
                                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wide">Radius (m)</label>
                                        <input type="number" name="geofences[{{$index}}][radius]" value="{{ $geofence['radius'] }}" required class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                                    </div>
                                    <div class="flex items-end">
                                        <button type="button" class="remove-geofence-btn h-[38px] px-3 py-2 rounded-lg bg-white dark:bg-gray-800 text-red-500 hover:bg-red-50 hover:text-red-600 border border-gray-200 dark:border-gray-700 transition-colors">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <button type="button" id="add-geofence-btn" class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-lg text-sm font-medium transition-colors">
                            <i class="fas fa-plus"></i> Add Another Location
                        </button>
                    </div>
                </div>

                <!-- Tab: Security -->
                <div id="tab-security" class="settings-tab-content space-y-8">
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

                    <div>
                        <h4 class="text-base font-bold text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-700 pb-2 mb-4">Timeouts & Expirations</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Summary Link Expiry (Minutes)</label>
                                <input type="number" name="expirations[summary_link_minutes]" value="{{ $settings['expirations']['summary_link_minutes'] }}" class="w-32 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">How long the private post-scan attendance profile is valid before expiring.</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Registration OTP Expiry (Minutes)</label>
                                <input type="number" name="expirations[otp_minutes]" value="{{ $settings['expirations']['otp_minutes'] }}" class="w-32 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">How long emailed One-Time-Passwords are valid.</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pending Login Session (Minutes)</label>
                                <input type="number" name="expirations[pending_login_minutes]" value="{{ $settings['expirations']['pending_login_minutes'] }}" class="w-32 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">How long the system waits for an Admin to approve an unverified login.</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Default Shift Length (Hours)</label>
                                <input type="number" name="expirations[default_shift_hours]" value="{{ $settings['expirations']['default_shift_hours'] }}" class="w-32 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Assumed shift length used to auto-calculate end times when resolving attendance anomalies.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab: UI -->
                <div id="tab-ui" class="settings-tab-content space-y-6">
                    <div>
                        <h4 class="text-base font-bold text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-700 pb-2 mb-4">Table View Limits</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Default Table Rows</label>
                                <select name="pagination_limits[default_rows]" class="w-32 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                                    <option value="10" {{ $settings['pagination_limits']['default_rows'] == 10 ? 'selected' : '' }}>10</option>
                                    <option value="20" {{ $settings['pagination_limits']['default_rows'] == 20 ? 'selected' : '' }}>20</option>
                                    <option value="50" {{ $settings['pagination_limits']['default_rows'] == 50 ? 'selected' : '' }}>50</option>
                                    <option value="100" {{ $settings['pagination_limits']['default_rows'] == 100 ? 'selected' : '' }}>100</option>
                                </select>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Default records per page on Employees and Attendance Logs screens.</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Approvals Table Rows</label>
                                <select name="pagination_limits[exceptions_rows]" class="w-32 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                                    <option value="10" {{ $settings['pagination_limits']['exceptions_rows'] == 10 ? 'selected' : '' }}>10</option>
                                    <option value="25" {{ $settings['pagination_limits']['exceptions_rows'] == 25 ? 'selected' : '' }}>25</option>
                                    <option value="50" {{ $settings['pagination_limits']['exceptions_rows'] == 50 ? 'selected' : '' }}>50</option>
                                    <option value="100" {{ $settings['pagination_limits']['exceptions_rows'] == 100 ? 'selected' : '' }}>100</option>
                                </select>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Default records per page on the Pending Approvals screen.</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Export Generation Limit</label>
                                <input type="number" name="pagination_limits[export_limit]" value="{{ $settings['pagination_limits']['export_limit'] }}" class="w-32 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Maximum number of rows processed in a single query when viewing reports, to prevent memory crashes.</p>
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

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Tab Switching Logic
        const tabBtns = document.querySelectorAll('.settings-tab-btn');
        const tabContents = document.querySelectorAll('.settings-tab-content');

        tabBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                // Remove active from all
                tabBtns.forEach(b => {
                    b.classList.remove('active', 'text-emerald-600', 'dark:text-emerald-400', 'border-emerald-500');
                    b.classList.add('text-gray-500', 'dark:text-gray-400', 'border-transparent');
                });
                tabContents.forEach(c => c.classList.remove('active'));

                // Add active to clicked
                btn.classList.add('active', 'text-emerald-600', 'dark:text-emerald-400', 'border-emerald-500');
                btn.classList.remove('text-gray-500', 'dark:text-gray-400', 'border-transparent');
                const target = document.querySelector(btn.dataset.target);
                if(target) target.classList.add('active');
            });
        });

        // Dynamic Geofence Rows
        const container = document.getElementById('geofences-container');
        const addBtn = document.getElementById('add-geofence-btn');
        let nextIndex = {{ count($settings['geofences']) }};

        const attachRemoveEvent = (btn) => {
            btn.addEventListener('click', (e) => {
                e.target.closest('.geofence-row').remove();
            });
        };

        document.querySelectorAll('.remove-geofence-btn').forEach(attachRemoveEvent);

        addBtn.addEventListener('click', () => {
            const html = `
                <div class="geofence-row flex flex-col md:flex-row gap-3 bg-gray-50 dark:bg-gray-900/50 p-4 rounded-xl border border-gray-200 dark:border-gray-700">
                    <div class="flex-1">
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wide">Location Name</label>
                        <input type="text" name="geofences[${nextIndex}][name]" placeholder="e.g. HQ Building" required class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div class="w-full md:w-32">
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wide">Latitude</label>
                        <input type="number" step="any" name="geofences[${nextIndex}][lat]" required class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div class="w-full md:w-32">
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wide">Longitude</label>
                        <input type="number" step="any" name="geofences[${nextIndex}][lon]" required class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div class="w-full md:w-32">
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wide">Radius (m)</label>
                        <input type="number" name="geofences[${nextIndex}][radius]" value="500" required class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div class="flex items-end">
                        <button type="button" class="remove-geofence-btn h-[38px] px-3 py-2 rounded-lg bg-white dark:bg-gray-800 text-red-500 hover:bg-red-50 hover:text-red-600 border border-gray-200 dark:border-gray-700 transition-colors">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
            attachRemoveEvent(container.lastElementChild.querySelector('.remove-geofence-btn'));
            nextIndex++;
        });
    });
</script>
@endsection
