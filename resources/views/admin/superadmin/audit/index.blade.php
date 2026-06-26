@extends('admin.layout')

@section('title', 'System Audit Logs')
@section('page-title', 'System Audit Logs')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div>
        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Crash Reports & Errors</h3>
        <p class="text-sm text-gray-500">Monitor system stability and investigate unhandled exceptions.</p>
    </div>
    <form action="{{ route('superadmin.audit.resolve-all') }}" method="POST">
        @csrf
        <button type="submit" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700 transition-colors shadow-sm">
            Mark All as Resolved
        </button>
    </form>
</div>

<div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700 text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    <th class="px-6 py-4 font-semibold">Reference</th>
                    <th class="px-6 py-4 font-semibold">Tenant / User</th>
                    <th class="px-6 py-4 font-semibold">Error Message</th>
                    <th class="px-6 py-4 font-semibold">Date</th>
                    <th class="px-6 py-4 font-semibold">Status</th>
                    <th class="px-6 py-4 font-semibold text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($logs as $log)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors {{ $log->status === 'new' ? 'bg-red-50/30 dark:bg-red-900/10' : '' }}">
                    <td class="px-6 py-4">
                        <span class="font-mono text-xs font-semibold text-gray-900 dark:text-gray-200">{{ $log->reference_code }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm">
                            <div class="font-medium text-gray-900 dark:text-gray-200">{{ $log->tenant->name ?? 'System/Public' }}</div>
                            <div class="text-gray-500 text-xs">{{ $log->user->name ?? 'Guest' }}</div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-red-600 dark:text-red-400 truncate max-w-xs" title="{{ $log->message }}">
                            {{ Str::limit($log->message, 50) }}
                        </div>
                        <div class="text-xs text-gray-500 mt-1 font-mono">
                            {{ $log->method }} {{ $log->url }}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                        {{ $log->created_at->format('M j, Y H:i:s') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($log->status === 'new')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">
                                New
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                Resolved
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="{{ route('superadmin.audit.show', $log) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-3">View</a>
                        @if($log->status === 'new')
                            <form action="{{ route('superadmin.audit.resolve', $log) }}" method="POST" class="inline-block">
                                @csrf
                                <button type="submit" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300">Resolve</button>
                            </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                        No errors logged yet. The system is running smoothly!
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $logs->links() }}
        </div>
    @endif
</div>
@endsection
