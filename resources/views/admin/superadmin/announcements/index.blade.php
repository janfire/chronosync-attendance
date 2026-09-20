@extends('admin.layout')

@section('title', 'Global Announcements')
@section('page-title', 'Global Announcements')

@section('content')

<div class="flex justify-between items-center mb-6">
    <h2 class="text-xl font-bold text-gray-800 dark:text-white">Broadcast Announcements</h2>
    <a href="{{ route('superadmin.announcements.create') }}" class="bg-emerald-600 text-white px-4 py-2 rounded-xl text-sm font-bold shadow-sm hover:bg-emerald-700 transition-colors">
        <i class="fas fa-plus mr-1"></i> New Announcement
    </a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden dark:bg-gray-800 dark:border-gray-700">
    <table class="w-full text-left text-sm text-gray-600 dark:text-gray-400">
        <thead class="bg-gray-50 border-b border-gray-200 text-gray-500 font-semibold uppercase text-xs tracking-wider dark:bg-gray-900/50 dark:border-gray-700">
            <tr>
                <th class="px-6 py-4">Title</th>
                <th class="px-6 py-4">Message</th>
                <th class="px-6 py-4">Type</th>
                <th class="px-6 py-4">Status</th>
                <th class="px-6 py-4">Expires At</th>
                <th class="px-6 py-4 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($announcements as $announcement)
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors">
                <td class="px-6 py-4 font-semibold text-gray-900 dark:text-white">
                    {{ $announcement->title }}
                </td>
                <td class="px-6 py-4">
                    {{ Str::limit($announcement->message, 50) }}
                </td>
                <td class="px-6 py-4">
                    @php
                        $badges = [
                            'info' => 'bg-blue-100 text-blue-700',
                            'warning' => 'bg-orange-100 text-orange-700',
                            'success' => 'bg-green-100 text-green-700',
                            'danger' => 'bg-red-100 text-red-700',
                        ];
                        $badgeClass = $badges[$announcement->type] ?? 'bg-gray-100 text-gray-700';
                    @endphp
                    <span class="px-2 py-1 text-xs font-bold rounded-lg {{ $badgeClass }}">
                        {{ ucfirst($announcement->type) }}
                    </span>
                </td>
                <td class="px-6 py-4">
                    @if($announcement->is_active && ($announcement->expires_at === null || $announcement->expires_at->isFuture()))
                        <span class="text-green-600 font-semibold text-xs"><i class="fas fa-circle mr-1" style="font-size:8px"></i> Active</span>
                    @else
                        <span class="text-gray-400 font-semibold text-xs"><i class="fas fa-circle mr-1" style="font-size:8px"></i> Inactive</span>
                    @endif
                </td>
                <td class="px-6 py-4 text-xs">
                    {{ $announcement->expires_at ? $announcement->expires_at->format('d M Y H:i') : 'Never' }}
                </td>
                <td class="px-6 py-4 text-right">
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('superadmin.announcements.edit', $announcement) }}" class="p-2 text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('superadmin.announcements.destroy', $announcement) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this announcement?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-6 py-8 text-center text-gray-400">
                    No announcements found. Click "New Announcement" to create one.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
