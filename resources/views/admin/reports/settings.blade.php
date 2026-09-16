@extends('admin.layout')

@section('title', 'Report Settings')
@section('page-title', 'Shift & Rule Configuration')

@section('content')
<div class="max-w-5xl mx-auto space-y-8">
    <div class="mb-6">
        <a href="{{ route('admin.reports.index') }}" class="text-sm text-emerald-600 hover:text-emerald-800 font-medium flex items-center transition-colors">
            <i class="fas fa-arrow-left mr-2"></i> Back to Advanced Reports
        </a>
    </div>

    <form method="POST" action="{{ route('admin.reports.settings.update') }}" class="space-y-8">
        @csrf
        
        <!-- Shift Timings -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden dark:bg-gray-800 dark:border-gray-700">
            <div class="px-8 py-6 border-b border-gray-100 bg-gray-50/30 dark:bg-gray-900 dark:border-gray-700">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-emerald-100/50 rounded-lg text-emerald-600">
                        <i class="fas fa-clock text-lg"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 text-lg dark:text-white">Shift & Punctuality Rules</h3>
                        <p class="text-xs text-gray-500 font-medium dark:text-gray-400">Define working hours and late thresholds</p>
                    </div>
                </div>
            </div>
            <div class="p-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-8">
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider dark:text-gray-200">Shift Start</label>
                    <div class="relative">
                        <input type="time" name="shift_start" value="{{ $shiftRules['shift_start'] }}" class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition-all text-gray-700 font-medium dark:text-gray-200 dark:border-gray-700">
                        <i class="fas fa-sun absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    </div>
                    <p class="text-[11px] text-gray-400 leading-tight">Hours before this time will be clipped to match shift start.</p>
                </div>
                
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider dark:text-gray-200">Shift End</label>
                    <div class="relative">
                        <input type="time" name="shift_end" value="{{ $shiftRules['shift_end'] }}" class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition-all text-gray-700 font-medium dark:text-gray-200 dark:border-gray-700">
                        <i class="fas fa-moon absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    </div>
                    <p class="text-[11px] text-gray-400 leading-tight">Hours after this time will be clipped to match shift end.</p>
                </div>

                <div class="space-y-2">
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider dark:text-gray-200">Late Threshold</label>
                    <div class="relative">
                        <input type="time" name="late_after" value="{{ $shiftRules['late_after'] }}" class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 outline-none transition-all text-gray-700 font-medium dark:text-gray-200 dark:border-gray-700">
                        <i class="fas fa-user-clock absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    </div>
                    <p class="text-[11px] text-gray-400 leading-tight">Sign-ins after this time flag the user as <span class="text-amber-600 font-bold">'Late'</span>.</p>
                </div>

                <div class="space-y-2">
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider dark:text-gray-200">Early Departure</label>
                    <div class="relative">
                        <input type="time" name="early_out_before" value="{{ $shiftRules['early_out_before'] }}" class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none transition-all text-gray-700 font-medium dark:text-gray-200 dark:border-gray-700">
                        <i class="fas fa-sign-out-alt absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    </div>
                    <p class="text-[11px] text-gray-400 leading-tight">Sign-outs before this time flag as <span class="text-red-600 font-bold">'Early Leave'</span>.</p>
                </div>
            </div>
        </div>

        <!-- Holidays -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden dark:bg-gray-800 dark:border-gray-700" x-data="holidayManager({{ json_encode($holidays) }})">
            <div class="px-8 py-6 border-b border-gray-100 bg-gray-50/30 flex flex-col md:flex-row md:items-center justify-between gap-4 dark:bg-gray-900 dark:border-gray-700">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-indigo-100/50 rounded-lg text-indigo-600">
                        <i class="fas fa-calendar-alt text-lg"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 text-lg dark:text-white">Public Holidays</h3>
                        <p class="text-xs text-gray-500 font-medium dark:text-gray-400">Manage holiday calendar for automated reports</p>
                    </div>
                </div>
                
                <div class="flex items-center space-x-3">
                    <button type="button" @click="addHoliday()" class="px-4 py-2 bg-gray-900 hover:bg-gray-800 text-white text-xs font-bold uppercase tracking-wider rounded-lg transition-all shadow-md hover:shadow-lg flex items-center">
                        <i class="fas fa-plus mr-2"></i> Add Custom
                    </button>
                    <button type="button" @click="fetchHolidays()" :disabled="isLoading" class="px-4 py-2 bg-emerald-50 text-emerald-600 hover:bg-emerald-100 text-xs font-bold uppercase tracking-wider rounded-lg transition-colors flex items-center">
                        <i class="fas fa-sync-alt mr-2" :class="{ 'fa-spin': isLoading }"></i>
                        <span x-text="isLoading ? 'Syncing...' : 'Sync from Web'"></span>
                    </button>
                </div>
            </div>

            <div class="p-0">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50 border-b border-gray-100 text-xs uppercase text-gray-500 tracking-wider dark:bg-gray-900 dark:text-gray-400 dark:border-gray-700">
                                <th class="px-8 py-4 font-semibold w-48">Date</th>
                                <th class="px-8 py-4 font-semibold">Holiday Name</th>
                                <th class="px-8 py-4 font-semibold w-24 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <template x-if="holidays.length === 0">
                                <tr>
                                    <td colspan="3" class="px-8 py-12 text-center text-gray-400">
                                        <div class="flex flex-col items-center justify-center space-y-2">
                                            <i class="fas fa-calendar-times text-3xl opacity-20"></i>
                                            <p class="text-sm">No holidays configured yet.</p>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                            
                            <template x-for="(holiday, index) in holidays" :key="index">
                                <tr class="hover:bg-gray-50/50 transition-colors group dark:bg-gray-900">
                                    <td class="px-8 py-3">
                                        <input type="date" :name="`holidays[${index}][date]`" x-model="holiday.date" class="w-full bg-transparent border-none focus:ring-0 p-0 text-sm font-medium text-gray-700 font-mono dark:text-gray-200" required>
                                    </td>
                                    <td class="px-8 py-3">
                                        <input type="text" :name="`holidays[${index}][name]`" x-model="holiday.name" placeholder="e.g. New Year's Day" class="w-full bg-transparent border-none focus:ring-0 p-0 text-sm font-medium text-gray-900 placeholder-gray-300 dark:text-white" required>
                                    </td>
                                    <td class="px-8 py-3 text-center">
                                        <button type="button" @click="removeHoliday(index)" class="text-gray-300 hover:text-red-500 transition-colors p-2 rounded-full hover:bg-red-50">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <div class="px-8 py-4 bg-gray-50 border-t border-gray-200/60 flex items-start space-x-3 text-gray-500 dark:bg-gray-900 dark:text-gray-400 dark:border-gray-700">
                    <i class="fas fa-info-circle mt-0.5 text-emerald-500"></i>
                    <p class="text-xs leading-relaxed">Weekends (Saturday/Sunday) are <strong class="text-gray-700 font-bold dark:text-gray-200">automatically excluded</strong> from working hour calculations. You only need to list public holidays or specific non-working days here.</p>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end space-x-4 pt-4">
            <button type="reset" class="px-6 py-3 text-gray-500 font-medium hover:text-gray-700 hover:bg-gray-100 rounded-xl transition-all text-sm dark:bg-gray-800 dark:text-gray-200">Cancel</button>
            <button type="submit" class="px-8 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl text-sm font-bold shadow-lg shadow-emerald-500/30 hover:shadow-emerald-500/40 hover:-translate-y-0.5 transition-all">
                Save Changes
            </button>
        </div>
    </form>
</div>

<!-- Alpine.js for interactivity -->
<script src="//unpkg.com/alpinejs" defer></script>
<script>
    function holidayManager(initialHolidays) {
        return {
            holidays: initialHolidays || [],
            isLoading: false,

            addHoliday() {
                this.holidays.push({ date: '', name: '' });
                // Focus logic could go here if needed
            },

            removeHoliday(index) {
                this.holidays.splice(index, 1);
            },

            fetchHolidays() {
                this.isLoading = true;
                fetch('{{ route("admin.reports.fetch.holidays") }}')
                    .then(response => response.json())
                    .then(data => {
                        if(data.success) {
                            // Merge strategy: Add non-duplicate holidays
                            const existingDates = new Set(this.holidays.map(h => h.date));
                            let addedCount = 0;
                            
                            data.holidays.forEach(h => {
                                if (!existingDates.has(h.date)) {
                                    this.holidays.push({ date: h.date, name: h.name });
                                    addedCount++;
                                }
                            });
                            
                            // Sort by date
                            this.holidays.sort((a, b) => new Date(a.date) - new Date(b.date));
                            
                            if(addedCount > 0) {
                                // show success notification if you have a toast library, else alert
                                alert(`Successfully added ${addedCount} new holidays.`);
                            } else {
                                alert('All holidays from API are already in your list.');
                            }
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(error => {
                        alert('Connection error: ' + error);
                    })
                    .finally(() => {
                        this.isLoading = false;
                    });
            }
        }
    }
</script>
@endsection


