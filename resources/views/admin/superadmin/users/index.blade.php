@extends('admin.layout')

@section('title', 'Manage Platform Users')
@section('page-title', 'Platform Users')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">System Users</h1>
        <p class="text-sm text-gray-500 mt-1 dark:text-gray-400">Manage internal platform administrators and their roles.</p>
    </div>
    <div class="flex items-center gap-3">
        <button onclick="openModal('addUserModal')" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-xl transition-colors shadow-sm focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
            <i class="fas fa-plus"></i>
            Add System User
        </button>
    </div>
</div>

<div class="users-table-card bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden dark:bg-gray-800 dark:border-gray-700">
    <div class="admin-table-scroll p-2">
        <table id="systemUsersTable" class="admin-data-table stripe hover w-full">
            <thead>
                <tr>
                    <th class="text-left">Name</th>
                    <th class="text-left">Role</th>
                    <th class="text-left">Sys ID</th>
                    <th class="text-left">Added</th>
                    <th class="text-right col-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td>
                        <div class="flex items-center gap-3">
                            <div class="h-9 w-9 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-sm dark:bg-emerald-900/40 dark:text-emerald-400">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                            <div>
                                <div class="font-semibold text-gray-900 dark:text-white">{{ $user->name }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $user->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($user->role->value === 'platform_admin')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400">Admin</span>
                        @elseif($user->role->value === 'platform_finance')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">Finance</span>
                        @elseif($user->role->value === 'platform_developer')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">Developer</span>
                        @elseif($user->role->value === 'platform_support')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">Support</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">{{ $user->getRoleLabel() }}</span>
                        @endif
                    </td>
                    <td class="font-mono text-sm text-gray-500 dark:text-gray-400">{{ $user->employee_number }}</td>
                    <td class="text-gray-500 text-sm dark:text-gray-400">{{ $user->created_at->format('M d, Y') }}</td>
                    <td class="text-right">
                        <div class="table-action-group">
                            <button onclick="editUser({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ addslashes($user->email) }}', '{{ $user->role->value }}')" class="table-action-btn table-action-btn--edit" title="Edit User">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="resendInvitation({{ $user->id }}, '{{ addslashes($user->name) }}')" class="table-action-btn table-action-btn--view" title="Resend Invitation Email">
                                <i class="fas fa-envelope"></i>
                            </button>
                            @if(auth()->id() !== $user->id)
                                <button onclick="deleteUser({{ $user->id }}, '{{ addslashes($user->name) }}')" class="table-action-btn table-action-btn--danger" title="Delete User">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            @else
                                <button class="table-action-btn table-action-btn--disabled" title="Cannot delete yourself" disabled>
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Add User Modal -->
<div id="addUserModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/80 transition-opacity" aria-hidden="true" onclick="closeModal('addUserModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full border border-gray-200 dark:border-gray-700">
            <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-gray-100 dark:border-gray-700">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-emerald-100 dark:bg-emerald-900/30 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="fas fa-user-plus text-emerald-600 dark:text-emerald-400"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-bold text-gray-900 dark:text-white" id="modal-title">
                            Add System User
                        </h3>
                    </div>
                </div>
            </div>
            
            <form action="{{ route('superadmin.users.store') }}" method="POST">
                @csrf
                <div class="px-4 py-5 sm:p-6 space-y-4">
                    <div>
                        <label for="name" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">Full Name</label>
                        <input type="text" name="name" id="name" required class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm px-3 py-2 border">
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">Email Address</label>
                        <input type="email" name="email" id="email" required class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm px-3 py-2 border">
                    </div>
                    <div>
                        <label for="role" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">Platform Role</label>
                        <select name="role" id="role" required class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm px-3 py-2 border">
                            <option value="platform_admin">Platform Admin (Full Access)</option>
                            <option value="platform_finance">Platform Finance (Billing Only)</option>
                            <option value="platform_developer">Platform Developer (System Audit Only)</option>
                            <option value="platform_support">Platform Support (Tenant Directory Only)</option>
                        </select>
                    </div>
                </div>
                
                <div class="bg-gray-50 dark:bg-gray-700/50 px-4 py-3 sm:px-6 flex flex-row-reverse gap-2">
                    <button type="submit" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-emerald-600 text-base font-medium text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                        Add User
                    </button>
                    <button type="button" onclick="closeModal('addUserModal')" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div id="editUserModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/80 transition-opacity" aria-hidden="true" onclick="closeModal('editUserModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full border border-gray-200 dark:border-gray-700">
            <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-gray-100 dark:border-gray-700">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 dark:bg-blue-900/30 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="fas fa-edit text-blue-600 dark:text-blue-400"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-bold text-gray-900 dark:text-white" id="modal-title">
                            Edit System User
                        </h3>
                    </div>
                </div>
            </div>
            
            <form id="editForm" action="" method="POST">
                @csrf
                @method('PUT')
                <div class="px-4 py-5 sm:p-6 space-y-4">
                    <div>
                        <label for="edit_name" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">Full Name</label>
                        <input type="text" name="name" id="edit_name" required class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm px-3 py-2 border">
                    </div>
                    <div>
                        <label for="edit_email" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">Email Address</label>
                        <input type="email" name="email" id="edit_email" required class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm px-3 py-2 border">
                    </div>
                    <div>
                        <label for="edit_role" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">Platform Role</label>
                        <select name="role" id="edit_role" required class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm px-3 py-2 border">
                            <option value="platform_admin">Platform Admin (Full Access)</option>
                            <option value="platform_finance">Platform Finance (Billing Only)</option>
                            <option value="platform_developer">Platform Developer (System Audit Only)</option>
                            <option value="platform_support">Platform Support (Tenant Directory Only)</option>
                        </select>
                    </div>
                </div>
                
                <div class="bg-gray-50 dark:bg-gray-700/50 px-4 py-3 sm:px-6 flex flex-row-reverse gap-2">
                    <button type="submit" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                        Save Changes
                    </button>
                    <button type="button" onclick="closeModal('editUserModal')" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete form -->
<form id="deleteForm" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>

<!-- Resend Invitation form -->
<form id="resendForm" method="POST" class="hidden">
    @csrf
</form>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Initialize DataTable
        $('#systemUsersTable').DataTable({
            pageLength: 10,
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
            language: {
                search: "",
                searchPlaceholder: "Search users...",
            },
            dom: '<"flex flex-col sm:flex-row justify-between items-center gap-4 px-4 py-3"lf>rt<"flex flex-col sm:flex-row justify-between items-center gap-4 px-4 py-3 border-t border-gray-100 dark:border-gray-700"ip>',
            drawCallback: function() {
                $('.dataTables_length select').addClass('px-3 py-1.5 text-sm border border-gray-200 rounded-lg bg-gray-50 dark:bg-gray-800 dark:border-gray-700 focus:ring-2 focus:ring-emerald-400 focus:outline-none transition-all');
                $('.dataTables_filter input').addClass('w-full sm:w-64 px-4 py-1.5 text-sm border border-gray-200 rounded-lg bg-gray-50 dark:bg-gray-800 dark:border-gray-700 focus:ring-2 focus:ring-emerald-400 focus:outline-none transition-all').removeClass('form-control input-sm');
                $('.dataTables_paginate .paginate_button').addClass('px-3 py-1.5 mx-1 text-sm rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors cursor-pointer');
                $('.dataTables_paginate .current').addClass('bg-emerald-50 text-emerald-600 font-bold dark:bg-emerald-900/30 dark:text-emerald-400');
            }
        });
    });

    function openModal(id) {
        document.getElementById(id).classList.remove('hidden');
    }

    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
    }

    function editUser(id, name, email, role) {
        const form = document.getElementById('editForm');
        form.action = `/superadmin/users/${id}`;
        
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_email').value = email;
        document.getElementById('edit_role').value = role;
        
        openModal('editUserModal');
    }

    function deleteUser(id, name) {
        if (confirm(`Are you sure you want to permanently delete platform user: ${name}?`)) {
            const form = document.getElementById('deleteForm');
            form.action = `/superadmin/users/${id}`;
            form.submit();
        }
    }

    function resendInvitation(id, name) {
        if (confirm(`Send a new password setup invitation email to ${name}?`)) {
            const form = document.getElementById('resendForm');
            form.action = `/superadmin/users/${id}/resend-invitation`;
            form.submit();
        }
    }
</script>
@endpush
