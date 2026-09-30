@extends('admin.layout')

@section('title', 'Locations & Geofences Settings')
@section('page-title', 'Locations & Geofences')

@section('content')
<div class="space-y-6">

    <!-- Header & Reset Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm">
        <div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Authorized Campuses & Geofences</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Define the geographic coordinates where employees are allowed to clock in.</p>
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

            <div class="p-6 md:p-8 space-y-6">
                
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
