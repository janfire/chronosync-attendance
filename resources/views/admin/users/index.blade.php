@extends('admin.layout')

@section('title', 'Staff Management')
@section('page-title', 'Staff Management')

@section('content')
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1 font-medium">Total Users</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total_users'] }}</p>
                    <p class="text-xs text-gray-400 mt-1">All accounts</p>
                </div>
                <div class="h-12 w-12 bg-emerald-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-users text-emerald-600 text-lg"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1 font-medium">Super Admins</p>
                    <p class="text-2xl font-bold text-purple-600">{{ $stats['super_admins'] }}</p>
                    <p class="text-xs text-gray-400 mt-1">Full access</p>
                </div>
                <div class="h-12 w-12 bg-purple-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-crown text-purple-600 text-lg"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1 font-medium">Admins</p>
                    <p class="text-2xl font-bold text-emerald-600">{{ $stats['admins'] }}</p>
                    <p class="text-xs text-gray-400 mt-1">Limited access</p>
                </div>
                <div class="h-12 w-12 bg-emerald-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-user-shield text-emerald-600 text-lg"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1 font-medium">General Users</p>
                    <p class="text-2xl font-bold text-green-600">{{ $stats['general_users'] }}</p>
                    <p class="text-xs text-gray-400 mt-1">Standard access</p>
                </div>
                <div class="h-12 w-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-user text-green-600 text-lg"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1 font-medium">Staff</p>
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
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 users-table-card overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">System Users</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Manage all registered users</p>
            </div>
            <div class="flex items-center gap-2 shrink-0 flex-wrap">
                <a href="{{ route('admin.users.create') }}" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg transition-colors inline-flex items-center gap-2 text-sm font-medium shadow-sm">
                    <i class="fas fa-plus"></i>
                    <span>Create User</span>
                </a>
                <div class="relative" id="export-menu-wrapper">
                    <button type="button" id="export-menu-button" onclick="toggleExportMenu(event)" class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-sm transition-colors flex items-center space-x-2 font-medium">
                        <i class="fas fa-file-export text-emerald-600"></i>
                        <span>Export</span>
                        <i class="fas fa-chevron-down text-xs text-gray-500"></i>
                    </button>
                    <div id="export-menu" class="hidden absolute right-0 top-full mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-lg z-30 py-1">
                        <button type="button" onclick="copyUsersToClipboard(); closeExportMenu();" class="w-full text-left px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-2">
                            <i class="fas fa-copy w-4 text-slate-600"></i>
                            <span>Copy</span>
                        </button>
                        <button type="button" onclick="printUsers(); closeExportMenu();" class="w-full text-left px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-2">
                            <i class="fas fa-print w-4 text-indigo-600"></i>
                            <span>Print</span>
                        </button>
                        <button type="button" onclick="exportUsersPdf(); closeExportMenu();" class="w-full text-left px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-2">
                            <i class="fas fa-file-pdf w-4 text-red-600"></i>
                            <span>PDF</span>
                        </button>
                        <button type="button" onclick="exportUsersExcel(); closeExportMenu();" class="w-full text-left px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-2">
                            <i class="fas fa-file-excel w-4 text-emerald-700"></i>
                            <span>Excel (.xlsx)</span>
                        </button>
                        <button type="button" onclick="exportUsers(); closeExportMenu();" class="w-full text-left px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-2">
                            <i class="fas fa-download w-4 text-green-600"></i>
                            <span>CSV</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div id="bulk-actions-bar" class="hidden px-6 py-2.5 bg-red-50 dark:bg-red-900/20 border-b border-red-100 dark:border-red-900/50 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-red-800 dark:text-red-400">
                <span id="bulk-selected-count">0</span> user(s) selected
            </p>
            <button
                type="button"
                id="bulk-delete-btn"
                class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium transition-colors"
            >
                <i class="fas fa-trash"></i>
                <span>Delete selected</span>
            </button>
        </div>
        
        <form id="bulk-delete-form" method="POST" action="{{ route('admin.users.bulk-destroy') }}" class="hidden">
            @csrf
            <div id="bulk-delete-inputs"></div>
        </form>

        <div class="admin-table-scroll px-4 pb-4">
            <table class="w-full admin-data-table display" id="staffTable">
                <thead>
                    <tr>
                        <th class="col-select">
                            <input type="checkbox" id="select-all-users" class="admin-table-checkbox" title="Select all on this page" aria-label="Select all users on this page">
                        </th>
                        <th>
                            <div class="flex items-center gap-1.5">
                                <span>Name</span>
                                <i class="fas fa-sort text-gray-400 text-[10px]"></i>
                            </div>
                        </th>
                        <th class="hidden md:table-cell">Email</th>
                        <th class="hidden lg:table-cell">Employee #</th>
                        <th>Role</th>
                        <th class="hidden lg:table-cell">
                            <div class="flex items-center gap-1.5">
                                <span>Created</span>
                                <i class="fas fa-sort text-gray-400 text-[10px]"></i>
                            </div>
                        </th>
                        <th>Status</th>
                        <th class="col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
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
                            $canDelete = $user->id !== Auth::id() && Auth::user()->role->canManage($user->role);
                        @endphp
                        <tr>
                            <td class="col-select">
                                @if($canDelete)
                                    <input
                                        type="checkbox"
                                        class="admin-table-checkbox user-row-checkbox"
                                        value="{{ $user->id }}"
                                        data-user-name="{{ $user->name }}"
                                        aria-label="Select {{ $user->name }}"
                                    >
                                @endif
                            </td>
                            <td class="whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 rounded-full {{ $avatarColor }} flex items-center justify-center text-white font-semibold mr-3 shadow-sm">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $user->name }}</div>
                                        @if($lastActivity)
                                            <div class="text-xs text-gray-500">Last active {{ $lastActivity->timestamp->diffForHumans() }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="hidden md:table-cell whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-gray-300">{{ $user->email }}</div>
                            </td>
                            <td class="hidden lg:table-cell whitespace-nowrap">
                                <div class="text-sm font-mono text-gray-700">{{ $user->employee_number }}</div>
                            </td>
                            <td class="whitespace-nowrap">
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
                            <td class="hidden lg:table-cell whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $user->created_at->format('M j, Y') }}</div>
                                <div class="text-xs text-gray-500">{{ $user->created_at->diffForHumans() }}</div>
                            </td>
                            <td class="whitespace-nowrap">
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
                            <td class="whitespace-nowrap col-actions">
                                <div class="table-action-group">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="table-action-btn table-action-btn--edit" title="Edit User">
                                        <i class="fas fa-edit text-sm"></i>
                                    </a>
                                    @if($user->role->value === 'staff')
                                        <a href="{{ route('admin.staff.show', $user) }}" class="table-action-btn table-action-btn--view" title="View Profile">
                                            <i class="fas fa-eye text-sm"></i>
                                        </a>
                                    @endif
                                    @if($isEnrolled)
                                        <form action="{{ route('biometric.facial.delete.user', $user) }}" method="POST" class="inline js-confirm-submit" data-confirm-title="Delete Facial Data?" data-confirm-text="Are you sure you want to delete facial data for {{ addslashes($user->name) }}? They will need to re-enroll." data-confirm-button="Yes, Delete">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="table-action-btn table-action-btn--warn" title="Delete Facial Data">
                                                <i class="fas fa-user-times text-sm"></i>
                                            </button>
                                        </form>
                                    @endif
                                    @if($user->id !== Auth::id())
                                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline js-confirm-submit" data-confirm-title="Delete User?" data-confirm-text="Are you sure you want to delete {{ addslashes($user->name) }}? This action cannot be undone." data-confirm-button="Yes, Delete">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="table-action-btn table-action-btn--danger" title="Delete User">
                                                <i class="fas fa-trash text-sm"></i>
                                            </button>
                                        </form>
                                    @else
                                        <span class="table-action-btn table-action-btn--disabled" title="Cannot delete yourself">
                                            <i class="fas fa-trash text-sm"></i>
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="h-20 w-20 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center mb-4">
                                        <i class="fas fa-users text-gray-400 dark:text-gray-500 text-3xl"></i>
                                    </div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white mb-1">No users found</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                                        @if($search || $roleFilter)
                                            Try adjusting your filters
                                        @else
                                            Create your first user to get started
                                        @endif
                                    </p>
                                    @if($search || $roleFilter)
                                        <button onclick="clearFilters()" class="px-4 py-2 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-sm transition-colors mb-2">
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
<script>
    let usersTable;

    function showNotice(title, text, icon = 'info') {
        if (typeof Swal !== 'undefined' && Swal.fire) {
            Swal.fire({
                title,
                text,
                icon,
                confirmButtonColor: '#059669'
            });
            return;
        }

        alert(text);
    }

    function closeExportMenu() {
        const exportMenu = document.getElementById('export-menu');
        if (exportMenu) exportMenu.classList.add('hidden');
    }

    function toggleExportMenu(event) {
        if (event) event.stopPropagation();
        const exportMenu = document.getElementById('export-menu');
        if (exportMenu) exportMenu.classList.toggle('hidden');
    }

    document.addEventListener('click', (event) => {
        const exportMenu = document.getElementById('export-menu');
        const exportMenuWrapper = document.getElementById('export-menu-wrapper');
        if (!exportMenu || !exportMenuWrapper) return;
        if (exportMenuWrapper.contains(event.target)) return;
        closeExportMenu();
    });

    // Register delete confirmation first so later script errors cannot disable it.
    document.addEventListener('submit', (event) => {
        const formElement = event.target.closest('form.js-confirm-submit');
        if (!formElement) return;

        if (formElement.dataset.confirmed === 'true') {
            delete formElement.dataset.confirmed;
            return;
        }

        event.preventDefault();
        event.stopPropagation();

        const title = formElement.dataset.confirmTitle || 'Are you sure?';
        const text = formElement.dataset.confirmText || 'This action cannot be undone.';
        const confirmButtonText = formElement.dataset.confirmButton || 'Yes, Continue';

        if (typeof Swal !== 'undefined' && Swal.fire) {
            Swal.fire({
                title,
                text,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText,
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (!result.isConfirmed) return;
                formElement.dataset.confirmed = 'true';
                formElement.requestSubmit();
            });
            return;
        }

        if (window.confirm(text)) {
            formElement.dataset.confirmed = 'true';
            formElement.requestSubmit();
        }
    }, true);

    const bulkDeleteBtn = document.getElementById('bulk-delete-btn');
    const bulkDeleteForm = document.getElementById('bulk-delete-form');
    const bulkDeleteInputs = document.getElementById('bulk-delete-inputs');
    const bulkSelectedCount = document.getElementById('bulk-selected-count');
    const bulkActionsBar = document.getElementById('bulk-actions-bar');
    const selectAllUsers = document.getElementById('select-all-users');

    const getVisibleRowCheckboxes = () => {
        if (usersTable) {
            return usersTable.rows({ page: 'current' }).nodes().toArray()
                .flatMap((row) => [...row.querySelectorAll('.user-row-checkbox')]);
        }

        const table = document.getElementById('staffTable');
        return table ? [...table.querySelectorAll('tbody .user-row-checkbox')] : [];
    };

    const updateBulkDeleteState = () => {
        const checked = document.querySelectorAll('.user-row-checkbox:checked');
        const count = checked.length;

        if (bulkSelectedCount) bulkSelectedCount.textContent = String(count);
        if (bulkActionsBar) {
            bulkActionsBar.classList.toggle('hidden', count === 0);
        }

        const visible = getVisibleRowCheckboxes();
        if (!selectAllUsers) return;

        if (visible.length === 0) {
            selectAllUsers.checked = false;
            selectAllUsers.indeterminate = false;
            return;
        }

        selectAllUsers.checked = visible.every((checkbox) => checkbox.checked);
        selectAllUsers.indeterminate = count > 0 && !selectAllUsers.checked;
    };

    document.addEventListener('change', (event) => {
        if (event.target.classList.contains('user-row-checkbox')) {
            updateBulkDeleteState();
        }

        if (event.target.id === 'select-all-users') {
            const isChecked = event.target.checked;
            getVisibleRowCheckboxes().forEach((checkbox) => {
                checkbox.checked = isChecked;
            });
            updateBulkDeleteState();
        }
    });

    bulkDeleteBtn?.addEventListener('click', () => {
        const checked = [...document.querySelectorAll('.user-row-checkbox:checked')];
        if (!checked.length || !bulkDeleteForm || !bulkDeleteInputs) {
            return;
        }

        const names = checked.map((checkbox) => checkbox.dataset.userName).filter(Boolean);
        const preview = names.length <= 3
            ? names.join(', ')
            : `${names.slice(0, 3).join(', ')} and ${names.length - 3} more`;
        const confirmText = `You are about to delete ${checked.length} user(s): ${preview}. This action cannot be undone.`;

        const submitBulkDelete = () => {
            bulkDeleteInputs.innerHTML = '';
            checked.forEach((checkbox) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'user_ids[]';
                input.value = checkbox.value;
                bulkDeleteInputs.appendChild(input);
            });
            bulkDeleteForm.submit();
        };

        if (typeof Swal !== 'undefined' && Swal.fire) {
            Swal.fire({
                title: 'Delete Selected Users?',
                text: confirmText,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, Delete',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) submitBulkDelete();
            });
            return;
        }

        if (window.confirm(confirmText)) submitBulkDelete();
    });

    if (typeof window.jQuery !== 'undefined' && window.jQuery.fn.DataTable) {
        window.jQuery(function() {
            usersTable = window.jQuery('#staffTable').DataTable({
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'All']],
                order: [[5, 'desc']],
                columnDefs: [
                    { orderable: false, searchable: false, targets: [0, 7] }
                ],
                stripeClasses: ['even', 'odd'],
                language: {
                    search: '',
                    searchPlaceholder: 'Search users...',
                    lengthMenu: 'Show _MENU_ users',
                    info: 'Showing _START_ to _END_ of _TOTAL_ users',
                    infoEmpty: 'No users to show',
                    infoFiltered: '(filtered from _MAX_ total users)',
                    paginate: {
                        previous: '<i class="fas fa-chevron-left"></i>',
                        next: '<i class="fas fa-chevron-right"></i>'
                    },
                    processing: '<i class="fas fa-spinner fa-spin mr-2"></i>Loading users...'
                },
                dom: '<"admin-dt-toolbar"lf>rt<"admin-dt-footer"ip>',
                drawCallback: function() {
                    if (selectAllUsers) {
                        selectAllUsers.checked = false;
                        selectAllUsers.indeterminate = false;
                    }
                    updateBulkDeleteState();
                }
            });
        });
    }

    updateBulkDeleteState();

    function getExportRows() {
        const rows = [];
        const seen = new Set();

        const parseRowNode = (rowNode) => {
            if (!rowNode) return null;
            const cells = rowNode.querySelectorAll('td');
            if (cells.length < 7) return null;

            const emptyStateCell = cells[0];
            if (emptyStateCell && emptyStateCell.getAttribute('colspan') === '8') {
                return null;
            }

            const name = cells[1].querySelector('.text-sm.font-medium.text-gray-900')?.textContent?.trim() || cells[1].textContent.trim().split('\n')[0];
            const email = cells[2].textContent.trim();
            const empNumber = cells[3].textContent.trim();
            const role = cells[4].textContent.trim().replace(/\s+/g, ' ');
            const created = cells[5].querySelector('.text-sm.font-medium.text-gray-900')?.textContent?.trim() || cells[5].textContent.trim().split('\n')[0];
            const status = cells[6].textContent.trim().replace(/\s+/g, ' ') || '-';

            if (!name || !email) return null;

            return { name, email, empNumber, role, created, status };
        };

        if (usersTable) {
            usersTable.rows({ search: 'applied' }).every(function () {
                const rowNode = this.node();
                const row = parseRowNode(rowNode);
                if (!row) return;
                const key = `${row.email}::${row.empNumber}`;
                if (seen.has(key)) return;
                seen.add(key);
                rows.push(row);
            });
        }

        // Fallback for pages where DataTables is unavailable or failed to initialize.
        if (!rows.length) {
            document.querySelectorAll('#staffTable tbody tr').forEach((rowNode) => {
                const row = parseRowNode(rowNode);
                if (!row) return;
                const key = `${row.email}::${row.empNumber}`;
                if (seen.has(key)) return;
                seen.add(key);
                rows.push(row);
            });
        }

        return rows;
    }

    function exportUsers() {
        const rows = getExportRows();
        if (!rows.length) {
            showNotice('No Data', 'No rows available to export.', 'warning');
            return;
        }

        const escapeCsv = (value) => String(value ?? '').replace(/"/g, '""');
        let csv = 'Name,Email,Employee Number,Role,Created At,Status\n';
        
        rows.forEach(row => {
            csv += `"${escapeCsv(row.name)}","${escapeCsv(row.email)}","${escapeCsv(row.empNumber)}","${escapeCsv(row.role)}","${escapeCsv(row.created)}","${escapeCsv(row.status)}"\n`;
        });
        
        const blob = new Blob([csv], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'users_export_' + new Date().toISOString().split('T')[0] + '.csv';
        a.click();
    }

    function exportUsersExcel() {
        const rows = getExportRows();
        if (!rows.length) {
            showNotice('No Data', 'No rows available to export.', 'warning');
            return;
        }

        if (typeof XLSX === 'undefined') {
            showNotice('Export Error', 'Excel export library failed to load. Please refresh and try again.', 'error');
            return;
        }

        const workbookData = rows.map(row => ({
            Name: row.name,
            Email: row.email,
            'Employee Number': row.empNumber,
            Role: row.role,
            'Created At': row.created,
            Status: row.status
        }));

        const worksheet = XLSX.utils.json_to_sheet(workbookData);
        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, worksheet, 'Users');
        XLSX.writeFile(workbook, 'users_export_' + new Date().toISOString().split('T')[0] + '.xlsx');
    }

    function exportUsersPdf() {
        const rows = getExportRows();
        if (!rows.length) {
            showNotice('No Data', 'No rows available to export.', 'warning');
            return;
        }

        if (typeof window.jspdf === 'undefined' || typeof window.jspdf.jsPDF === 'undefined') {
            showNotice('Export Error', 'PDF export library failed to load. Please refresh and try again.', 'error');
            return;
        }

        const { jsPDF } = window.jspdf;
        const doc = new jsPDF({ orientation: 'landscape' });
        const columns = ['Name', 'Email', 'Employee Number', 'Role', 'Created At', 'Status'];
        const body = rows.map(row => [row.name, row.email, row.empNumber, row.role, row.created, row.status]);
        const dateLabel = new Date().toLocaleString();

        doc.setFontSize(14);
        doc.text('System Users Export', 14, 15);
        doc.setFontSize(10);
        doc.text('Generated: ' + dateLabel, 14, 22);

        doc.autoTable({
            head: [columns],
            body,
            startY: 28,
            theme: 'grid',
            styles: { fontSize: 8, cellPadding: 2 },
            headStyles: { fillColor: [16, 185, 129] }
        });

        doc.save('users_export_' + new Date().toISOString().split('T')[0] + '.pdf');
    }

    function printUsers() {
        const rows = getExportRows();
        if (!rows.length) {
            showNotice('No Data', 'No rows available to print.', 'warning');
            return;
        }

        const dateLabel = new Date().toLocaleString();
        let tableRowsHtml = '';
        rows.forEach((row) => {
            tableRowsHtml += `<tr>
                <td>${row.name}</td>
                <td>${row.email}</td>
                <td>${row.empNumber}</td>
                <td>${row.role}</td>
                <td>${row.created}</td>
                <td>${row.status}</td>
            </tr>`;
        });

        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
            <head>
                <title>System Users Print View</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 24px; color: #111827; }
                    h1 { margin: 0 0 8px; font-size: 20px; }
                    p { margin: 0 0 16px; color: #4b5563; font-size: 12px; }
                    table { width: 100%; border-collapse: collapse; }
                    th, td { border: 1px solid #d1d5db; padding: 8px; font-size: 12px; text-align: left; }
                    th { background: #ecfdf5; color: #065f46; font-weight: 700; }
                    tr:nth-child(even) { background: #f9fafb; }
                </style>
            </head>
            <body>
                <h1>System Users</h1>
                <p>Generated: ${dateLabel}</p>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Employee Number</th>
                            <th>Role</th>
                            <th>Created At</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>${tableRowsHtml}</tbody>
                </table>
            </body>
            </html>
        `);
        printWindow.document.close();
        printWindow.focus();
        printWindow.print();
    }

    function copyUsersToClipboard() {
        const rows = getExportRows();
        if (!rows.length) {
            showNotice('No Data', 'No rows available to copy.', 'warning');
            return;
        }

        const header = ['Name', 'Email', 'Employee Number', 'Role', 'Created At', 'Status'].join('\t');
        const lines = rows.map(row => [row.name, row.email, row.empNumber, row.role, row.created, row.status].join('\t'));
        const payload = [header, ...lines].join('\n');

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(payload)
                .then(() => showNotice('Copied', 'Users copied to clipboard.', 'success'))
                .catch(() => fallbackCopyToClipboard(payload));
            return;
        }

        fallbackCopyToClipboard(payload);
    }

    function fallbackCopyToClipboard(text) {
        const textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        textArea.style.left = '-9999px';
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        try {
            document.execCommand('copy');
            showNotice('Copied', 'Users copied to clipboard.', 'success');
        } catch (error) {
            showNotice('Copy Failed', 'Unable to copy automatically. Please try again.', 'error');
        } finally {
            document.body.removeChild(textArea);
        }
    }
</script>
@endpush


