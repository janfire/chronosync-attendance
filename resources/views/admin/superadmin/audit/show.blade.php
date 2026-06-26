@extends('admin.layout')

@section('title', 'Error Details - ' . $log->reference_code)
@section('page-title', 'Error Details')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div>
        <a href="{{ route('superadmin.audit.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900 mb-2 inline-block">&larr; Back to Audit Logs</a>
        <h3 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
            {{ $log->reference_code }}
            @if($log->status === 'new')
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">New</span>
            @else
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">Resolved</span>
            @endif
        </h3>
    </div>
    @if($log->status === 'new')
        <form action="{{ route('superadmin.audit.resolve', $log) }}" method="POST">
            @csrf
            <button type="submit" class="px-4 py-2 bg-green-600 border border-transparent rounded-lg text-sm font-medium text-white hover:bg-green-700 transition-colors shadow-sm flex items-center gap-2">
                <i class="fas fa-check"></i> Mark as Resolved
            </button>
        </form>
    @endif
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-5 shadow-sm lg:col-span-2">
        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Error Message</h4>
        <p class="text-lg font-semibold text-red-600 dark:text-red-400">{{ $log->message }}</p>
    </div>

    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-5 shadow-sm">
        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Context</h4>
        <div class="space-y-3">
            <div>
                <span class="text-xs text-gray-500 block mb-0.5">Date Occurred</span>
                <span class="text-sm font-medium text-gray-900 dark:text-gray-200">{{ $log->created_at->format('F j, Y g:i:s A') }}</span>
            </div>
            <div>
                <span class="text-xs text-gray-500 block mb-0.5">Tenant</span>
                <span class="text-sm font-medium text-gray-900 dark:text-gray-200">{{ $log->tenant->name ?? 'None' }}</span>
            </div>
            <div>
                <span class="text-xs text-gray-500 block mb-0.5">User</span>
                <span class="text-sm font-medium text-gray-900 dark:text-gray-200">{{ $log->user->name ?? 'Guest' }} (ID: {{ $log->user_id ?? 'N/A' }})</span>
            </div>
            <div>
                <span class="text-xs text-gray-500 block mb-0.5">Endpoint</span>
                <span class="text-sm font-mono text-gray-900 dark:text-gray-200"><span class="text-indigo-600 font-bold mr-1">{{ $log->method }}</span> {{ $log->url }}</span>
            </div>
        </div>
    </div>
</div>

<div class="bg-gray-900 rounded-xl shadow-sm overflow-hidden border border-gray-800">
    <div class="px-5 py-3 border-b border-gray-800 flex justify-between items-center bg-gray-900">
        <h4 class="text-sm font-bold text-gray-300 uppercase tracking-wider">Stack Trace</h4>
        <button onclick="navigator.clipboard.writeText(document.getElementById('stack-trace').innerText)" class="text-xs text-gray-400 hover:text-white transition-colors flex items-center gap-1">
            <i class="far fa-copy"></i> Copy
        </button>
    </div>
    <div class="p-5 overflow-x-auto">
        <pre id="stack-trace" class="text-xs text-green-400 font-mono whitespace-pre-wrap break-all">{{ $log->stack_trace }}</pre>
    </div>
</div>
@endsection
