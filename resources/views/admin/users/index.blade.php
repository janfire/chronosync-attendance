@extends('admin.layout')

@section('title', 'Staff Management')
@section('page-title', 'Staff Management')

@section('content')
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 mb-1 font-medium">Total Users</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total_users'] }}</p>
                    <p class="text-xs text-gray-400 mt-1">All accounts</p>
                </div>
                <div class="h-12 w-12 bg-emerald-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-users text-emerald-600 text-lg"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 mb-1 font-medium">Super Admins</p>
                    <p class="text-2xl font-bold text-purple-600">{{ $stats['super_admins'] }}</p>
                    <p class="text-xs text-gray-400 mt-1">Full access</p>
                </div>
                <div class="h-12 w-12 bg-purple-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-crown text-purple-600 text-lg"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 mb-1 font-medium">Admins</p>
                    <p class="text-2xl font-bold text-emerald-600">{{ $stats['admins'] }}</p>
                    <p class="text-xs text-gray-400 mt-1">Limited access</p>
                </div>
                <div class="h-12 w-12 bg-emerald-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-user-shield text-emerald-600 text-lg"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 mb-1 font-medium">General Users</p>
                    <p class="text-2xl font-bold text-green-600">{{ $stats['general_users'] }}</p>
                    <p class="text-xs text-gray-400 mt-1">Standard access</p>
                </div>
                <div class="h-12 w-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-user text-green-600 text-lg"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 mb-1 font-medium">Staff</p>
                    <p class="text-2xl font-bold text-orange-600">{{ $stats['staff'] }}</p>
                    <p class="text-xs text-gray-400 mt-1">Basic access</p>
                </div>
                <div class="h-12 w-12 bg-orange-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-user-tie text-orange-600 text-lg"></i>
                </div>
            </div>
        </div>
    </div>

     <!-- Filters removed as DataTables handles search/filter -->

    <!-- Users Table Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">System Users</h3>
                <p class="text-xs text-gray-500 mt-1">Manage all registered users</p>
            </div>
            <div class="flex items-center space-x-2">
                <button onclick="exportUsers()" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm transition-colors flex items-center space-x-2">
                    <i class="fas fa-download"></i>
                    <span>Export CSV</span>
                </button>
                <a href="{{ route('admin.users.create') }}" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center space-x-2 text-sm font-medium">
                    <i class="fas fa-plus"></i>
                    <span>Create User</span>
                </a>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full" id="staffTable">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors">
                            <div class="flex items-center space-x-1">
                                <span>Name</span>
                                <i class="fas fa-sort text-gray-400 text-xs"></i>
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors">
                            <div class="flex items-center space-x-1">
                                <span>Created</span>
                                <i class="fas fa-sort text-gray-400 text-xs"></i>
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($users as $user)
                        @php
                            $avatarColors = [
                                'bg-gradient-to-br from-blue-400 to-blue-600',
                                'bg-gradient-to-br from-green-400 to-green-600',
                                'bg-gradient-to-br from-purple-400 to-purple-600',
                                'bg-gradient-to-br from-orange-400 to-orange-600',
                                'bg-gradient-to-br from-indigo-400 to-indigo-600',
                                'bg-gradient-to-br from-pink-400 to-pink-600',
                                'bg-gradient-to-br from-teal-400 to-teal-600',
                            ];
                            $colorIndex = abs(crc32($user->name . $user->id)) % count($avatarColors);
                            $avatarColor = $avatarColors[$colorIndex];

                            $roleColors = [
                                'super_admin' => 'bg-purple-100 text-purple-800 border-purple-200',
                                'admin' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                'general_user' => 'bg-green-100 text-green-800 border-green-200',
                                'staff' => 'bg-gray-100 text-gray-800 border-gray-200',
                            ];
                            $roleColor = $roleColors[$user->role->value] ?? 'bg-gray-100 text-gray-800 border-gray-200';
                            $roleLabel = $roles[$user->role->value] ?? ucfirst(str_replace('_', ' ', $user->role->value));

                            $isEnrolled = $user->biometricData && $user->biometricData->facial_status == 'captured';
                            $lastActivity = $user->attendanceLogs()->latest('timestamp')->first();
                        @endphp
                        <tr class="hover:bg-emerald-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 rounded-full {{ $avatarColor }} flex items-center justify-center text-white font-semibold mr-3 shadow-sm">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                        @if($lastActivity)
                                            <div class="text-xs text-gray-500">Last active {{ $lastActivity->timestamp->diffForHumans() }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $user->email }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-mono text-gray-900">{{ $user->employee_number }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full {{ $roleColor }} border flex items-center w-fit">
                                    @if($user->role->value === 'super_admin')
                                        <i class="fas fa-crown mr-1 text-xs"></i>
                                    @elseif($user->role->value === 'admin')
                                        <i class="fas fa-user-shield mr-1 text-xs"></i>
                                    @elseif($user->role->value === 'general_user')
                                        <i class="fas fa-user mr-1 text-xs"></i>
                                    @else
                                        <i class="fas fa-user-tie mr-1 text-xs"></i>
                                    @endif
                                    {{ $roleLabel }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $user->created_at->format('M j, Y') }}</div>
                                <div class="text-xs text-gray-500">{{ $user->created_at->diffForHumans() }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($user->role->value === 'staff')
                                    @if($isEnrolled)
                                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 flex items-center w-fit">
                                            <span class="h-2 w-2 bg-green-500 rounded-full mr-1.5"></span>
                                            Enrolled
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 flex items-center w-fit">
                                            <span class="h-2 w-2 bg-yellow-500 rounded-full mr-1.5"></span>
                                            Not Enrolled
                                        </span>
                                    @endif
                                @else
                                    <span class="text-xs text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-3">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="text-emerald-600 hover:text-gray-900 transition-colors" title="Edit User">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if($user->role->value === 'staff')
                                        <a href="{{ route('admin.staff.show', $user) }}" class="text-green-600 hover:text-green-900 transition-colors" title="View Profile">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    @endif
                                    @if($isEnrolled)
                                        <form action="{{ route('biometric.facial.delete.user', $user) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete facial data for {{ addslashes($user->name) }}? They will need to re-enroll.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-orange-500 hover:text-orange-700 transition-colors" title="Delete Facial Data">
                                                <i class="fas fa-user-times"></i>
                                            </button>
                                        </form>
                                    @endif
                                    @if($user->id !== Auth::id())
                                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete {{ addslashes($user->name) }}? This action cannot be undone.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900 transition-colors" title="Delete User">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-gray-300 cursor-not-allowed" title="Cannot delete yourself">
                                            <i class="fas fa-trash"></i>
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="h-20 w-20 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                                        <i class="fas fa-users text-gray-400 text-3xl"></i>
                                    </div>
                                    <p class="text-sm font-medium text-gray-900 mb-1">No users found</p>
                                    <p class="text-xs text-gray-500 mb-4">
                                        @if($search || $roleFilter)
                                            Try adjusting your filters
                                        @else
                                            Create your first user to get started
                                        @endif
                                    </p>
                                    @if($search || $roleFilter)
                                        <button onclick="clearFilters()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm transition-colors mb-2">
                                            Clear Filters
                                        </button>
                                    @endif
                                    <a href="{{ route('admin.users.create') }}" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm hover:bg-emerald-700 transition-colors">
                                        <i class="fas fa-plus mr-2"></i>Create User
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination Handled by DataTables -->
    </div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#staffTable').DataTable({
            "pageLength": 10,
            "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
            "order": [[ 4, "desc" ]], // Order by Created At
            "language": {
                "search": "<i class='fas fa-search text-gray-400'></i>",
                "searchPlaceholder": "Search users...",
                "paginate": {
                    "previous": "<i class='fas fa-chevron-left'></i>",
                    "next": "<i class='fas fa-chevron-right'></i>"
                }
            },
            "dom": '<"flex items-center justify-between mb-4"lf>rt<"flex items-center justify-between mt-4"ip>',
            "drawCallback": function(settings) {
                 // Re-apply any custom logic on draw if needed
            }
        });
    });

    function exportUsers() {
        // Simple export - warns if data is hidden
        alert("This export only includes the current page of data. For full export, use backend implementation.");
        // Get all visible records
        const rows = document.querySelectorAll('#staffTable tbody tr');
        let csv = 'Name,Email,Employee Number,Role,Created At,Status\n';
        
        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            if (cells.length < 6) return;
            
            const name = cells[0].textContent.trim().split('\n')[0];
            const email = cells[1].textContent.trim();
            const empNumber = cells[2].textContent.trim();
            const role = cells[3].textContent.trim();
            const created = cells[4].textContent.trim().split('\n')[0];
            const status = cells[5].textContent.trim() || '-';
            
            csv += `"${name}","${email}","${empNumber}","${role}","${created}","${status}"\n`;
        });
        
        const blob = new Blob([csv], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'users_export_' + new Date().toISOString().split('T')[0] + '.csv';
        a.click();
    }
</script>
@endpush


