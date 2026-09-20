@extends('admin.layout')

@section('title', 'Edit Announcement')
@section('page-title', 'Edit Announcement')

@section('content')

<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('superadmin.announcements.index') }}" class="text-sm font-semibold text-gray-500 hover:text-emerald-600 transition-colors">
            <i class="fas fa-arrow-left mr-1"></i> Back to Announcements
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden dark:bg-gray-800 dark:border-gray-700">
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50 flex items-center gap-3 dark:bg-gray-900 dark:border-gray-700">
            <div class="h-10 w-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center dark:bg-emerald-900/30 dark:text-emerald-400">
                <i class="fas fa-edit text-lg"></i>
            </div>
            <div>
                <h3 class="font-bold text-gray-900 dark:text-white">Edit Announcement</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">Modify your broadcast message for all tenants.</p>
            </div>
        </div>

        <form action="{{ route('superadmin.announcements.update', $announcement) }}" method="POST" class="p-6 sm:p-8">
            @csrf
            @method('PUT')

            <div class="mb-6">
                <label for="title" class="block text-sm font-bold text-gray-700 mb-2 dark:text-gray-300">Announcement Title <span class="text-red-500">*</span></label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <i class="fas fa-heading"></i>
                    </div>
                    <input type="text" name="title" id="title" value="{{ old('title', $announcement->title) }}" required class="w-full pl-10 rounded-xl border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm dark:bg-gray-900 dark:border-gray-600 dark:text-white transition-colors" placeholder="e.g., Scheduled System Maintenance">
                </div>
                @error('title')
                    <p class="text-red-500 text-xs mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="message" class="block text-sm font-bold text-gray-700 mb-2 dark:text-gray-300">Message Content <span class="text-red-500">*</span></label>
                <div class="relative">
                    <textarea name="message" id="message" rows="4" required class="w-full p-4 rounded-xl border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm dark:bg-gray-900 dark:border-gray-600 dark:text-white transition-colors" placeholder="What do you want to tell the users? Markdown or HTML is not supported, just plain text.">{{ old('message', $announcement->message) }}</textarea>
                </div>
                @error('message')
                    <p class="text-red-500 text-xs mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8">
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-100 dark:bg-gray-900/50 dark:border-gray-700">
                    <label for="type" class="block text-sm font-bold text-gray-700 mb-3 dark:text-gray-300">Announcement Type <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <i class="fas fa-palette"></i>
                        </div>
                        <select name="type" id="type" required class="w-full pl-11 py-3 rounded-xl border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white transition-colors cursor-pointer">
                            <option value="info" {{ old('type', $announcement->type) == 'info' ? 'selected' : '' }}>Info (Blue)</option>
                            <option value="warning" {{ old('type', $announcement->type) == 'warning' ? 'selected' : '' }}>Warning (Orange)</option>
                            <option value="success" {{ old('type', $announcement->type) == 'success' ? 'selected' : '' }}>Success (Green)</option>
                            <option value="danger" {{ old('type', $announcement->type) == 'danger' ? 'selected' : '' }}>Danger (Red)</option>
                        </select>
                    </div>
                </div>

                <div class="bg-gray-50 rounded-xl p-4 border border-gray-100 dark:bg-gray-900/50 dark:border-gray-700">
                    <label for="expires_at" class="block text-sm font-bold text-gray-700 mb-3 dark:text-gray-300">Expires At <span class="text-gray-400 font-normal text-xs">(Optional)</span></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <i class="fas fa-clock"></i>
                        </div>
                        <input type="datetime-local" name="expires_at" id="expires_at" value="{{ old('expires_at', $announcement->expires_at ? $announcement->expires_at->format('Y-m-d\TH:i') : '') }}" class="w-full pl-11 py-3 rounded-xl border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white transition-colors cursor-pointer">
                    </div>
                    <p class="text-xs text-gray-500 mt-3"><i class="fas fa-info-circle mr-1 text-gray-400"></i> Leave blank to show until manually disabled.</p>
                </div>
            </div>

            <div class="flex items-center justify-between p-4 bg-emerald-50 rounded-xl border border-emerald-100 mb-8 dark:bg-emerald-900/20 dark:border-emerald-900/30">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-full bg-white flex items-center justify-center text-emerald-500 shadow-sm dark:bg-gray-800">
                        <i class="fas fa-paper-plane"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-gray-900 dark:text-white">Publish Immediately</h4>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Will be visible to all tenants instantly.</p>
                    </div>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $announcement->is_active) ? 'checked' : '' }} class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-emerald-300 dark:peer-focus:ring-emerald-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-emerald-500"></div>
                </label>
            </div>

            <div class="flex justify-end gap-3 pt-2 border-t border-gray-100 dark:border-gray-700">
                <a href="{{ route('superadmin.announcements.index') }}" class="px-6 py-3 rounded-xl text-sm font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 transition-colors">
                    Cancel
                </a>
                <button type="submit" class="bg-emerald-600 text-white px-8 py-3 rounded-xl text-sm font-bold shadow-sm shadow-emerald-500/30 hover:bg-emerald-700 hover:shadow-emerald-500/50 transform hover:-translate-y-0.5 transition-all">
                    <i class="fas fa-save mr-2"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
