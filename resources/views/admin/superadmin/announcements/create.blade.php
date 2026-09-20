@extends('admin.layout')

@section('title', 'New Announcement')
@section('page-title', 'New Announcement')

@section('content')

<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('superadmin.announcements.index') }}" class="text-sm font-semibold text-gray-500 hover:text-emerald-600 transition-colors">
            <i class="fas fa-arrow-left mr-1"></i> Back to Announcements
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 sm:p-8 dark:bg-gray-800 dark:border-gray-700">
        <form action="{{ route('superadmin.announcements.store') }}" method="POST">
            @csrf

            <div class="mb-6">
                <label for="title" class="block text-sm font-bold text-gray-700 mb-2 dark:text-gray-300">Announcement Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" id="title" value="{{ old('title') }}" required class="w-full rounded-xl border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm dark:bg-gray-900 dark:border-gray-600 dark:text-white" placeholder="e.g., Scheduled Maintenance">
                @error('title')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="message" class="block text-sm font-bold text-gray-700 mb-2 dark:text-gray-300">Message Content <span class="text-red-500">*</span></label>
                <textarea name="message" id="message" rows="4" required class="w-full rounded-xl border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm dark:bg-gray-900 dark:border-gray-600 dark:text-white" placeholder="What do you want to tell the users?"></textarea>
                @error('message')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="type" class="block text-sm font-bold text-gray-700 mb-2 dark:text-gray-300">Announcement Type <span class="text-red-500">*</span></label>
                    <select name="type" id="type" required class="w-full rounded-xl border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                        <option value="info" {{ old('type') == 'info' ? 'selected' : '' }}>Info (Blue)</option>
                        <option value="warning" {{ old('type') == 'warning' ? 'selected' : '' }}>Warning (Orange)</option>
                        <option value="success" {{ old('type') == 'success' ? 'selected' : '' }}>Success (Green)</option>
                        <option value="danger" {{ old('type') == 'danger' ? 'selected' : '' }}>Danger (Red)</option>
                    </select>
                </div>
                <div>
                    <label for="expires_at" class="block text-sm font-bold text-gray-700 mb-2 dark:text-gray-300">Expires At (Optional)</label>
                    <input type="datetime-local" name="expires_at" id="expires_at" value="{{ old('expires_at') }}" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                    <p class="text-xs text-gray-400 mt-1">Leave blank to show until manually disabled.</p>
                </div>
            </div>

            <div class="mb-8">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="rounded border-gray-300 text-emerald-600 shadow-sm focus:ring-emerald-500">
                    <span class="text-sm font-bold text-gray-700 dark:text-gray-300">Publish Immediately</span>
                </label>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-emerald-600 text-white px-6 py-2.5 rounded-xl text-sm font-bold shadow-sm hover:bg-emerald-700 transition-colors">
                    Create Announcement
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
