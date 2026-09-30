@extends('admin.layout')

@section('title', 'Preferences & Limits Settings')
@section('page-title', 'Preferences & Limits')

@section('content')
<div class="space-y-6">

    <!-- Header & Reset Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm">
        <div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Preferences & Limits</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage system timeouts, expirations, and UI table row limits.</p>
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
