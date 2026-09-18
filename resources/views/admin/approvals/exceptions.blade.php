@extends('admin.layout')

@section('content')
    <div class="p-6">
        <h2 class="text-2xl font-semibold mb-4 text-gray-900 dark:text-white">Pending Attendance Requests</h2>

        @include('auth.partials.messages')

        <div class="bg-white rounded-lg shadow p-4 dark:bg-gray-800">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-sm text-gray-600 border-b dark:text-gray-300">
                        <th class="p-2">Requested</th>
                        <th class="p-2">Employee</th>
                        <th class="p-2">Type</th>
                        <th class="p-2">Note</th>
                        <th class="p-2">Requested By</th>
                        <th class="p-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($exceptions as $ex)
                        <tr class="border-b hover:bg-gray-50 dark:bg-gray-900">
                            <td class="p-2 text-sm text-gray-600 dark:text-gray-300">{{ $ex->requested_at?->format('Y-m-d H:i') }}</td>
                            <td class="p-2 font-medium text-gray-900 dark:text-gray-200">{{ $ex->user->name }}</td>
                            <td class="p-2 text-sm text-gray-900 dark:text-gray-200">{{ ucfirst(str_replace('_', ' ', $ex->type)) }}</td>
                            <td class="p-2 text-sm text-gray-700 dark:text-gray-200">{{ Str::limit($ex->note, 120) }}</td>
                            <td class="p-2 text-sm text-gray-900 dark:text-gray-200">{{ $ex->requester?->name ?? 'Self' }}</td>
                            <td class="p-2 text-sm">
                                <!-- Approve with optional correction timestamp -->
                                <div class="approve-wrapper">
                                    <button type="button" class="approve-toggle px-3 py-1 bg-emerald-600 text-white rounded">Approve</button>

                                    <form action="{{ route('admin.exceptions.approve', $ex) }}" method="POST" class="inline-block ml-2 approve-form hidden">
                                        @csrf
                                        <input type="datetime-local" name="correction_timestamp" value="{{ optional($ex->requested_at)->format('Y-m-d\TH:i') ?? now()->format('Y-m-d\TH:i') }}" class="border p-1 rounded text-sm" />
                                        <button class="px-3 py-1 bg-emerald-700 text-white rounded ml-2">Apply</button>
                                    </form>

                                    <form action="{{ route('admin.exceptions.reject', $ex) }}" method="POST" class="inline ml-2">
                                        @csrf
                                        <button class="px-3 py-1 bg-red-600 text-white rounded">Reject</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-4">
                {{ $exceptions->links() }}
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.approve-toggle').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const wrapper = btn.closest('.approve-wrapper');
                    const form = wrapper.querySelector('.approve-form');
                    if (form) form.classList.toggle('hidden');
                });
            });
        });
    </script>
@endsection
