@extends('admin.layout')

@section('title', 'All Tenants — Platform Admin')
@section('page-title', 'Tenant Directory')

@section('content')

{{-- ── Page Header ── --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <div class="flex items-center gap-2 mb-1">
            <a href="{{ route('superadmin.dashboard') }}"
               class="text-sm text-gray-400 hover:text-emerald-600 transition-colors flex items-center gap-1">
                <i class="fas fa-th-large text-xs"></i> Dashboard
            </a>
            <i class="fas fa-chevron-right text-gray-300 text-xs"></i>
            <span class="text-sm text-gray-600 font-medium">Tenants</span>
        </div>
        <h1 class="text-2xl font-bold text-gray-900">Tenant Directory</h1>
        <p class="text-sm text-gray-500 mt-0.5" id="tableInfo">Loading tenants…</p>
    </div>
</div>

{{-- ── Summary Stats Bar ── --}}
<div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-6">
    @php
        $statItems = [
            ['label' => 'Total',     'value' => $summary['total'],     'color' => 'text-gray-800',   'bg' => 'bg-gray-100',  'dot' => 'bg-gray-500',   'status' => ''],
            ['label' => 'Active',    'value' => $summary['active'],    'color' => 'text-green-800',  'bg' => 'bg-green-50',  'dot' => 'bg-green-500',  'status' => 'active'],
            ['label' => 'Trial',     'value' => $summary['trial'],     'color' => 'text-blue-800',   'bg' => 'bg-blue-50',   'dot' => 'bg-blue-500',   'status' => 'trial'],
            ['label' => 'Pending',   'value' => $summary['pending'],   'color' => 'text-orange-800', 'bg' => 'bg-orange-50', 'dot' => 'bg-orange-400', 'status' => 'pending'],
            ['label' => 'Suspended', 'value' => $summary['suspended'], 'color' => 'text-red-800',    'bg' => 'bg-red-50',    'dot' => 'bg-red-500',    'status' => 'suspended'],
        ];
    @endphp
    @foreach($statItems as $stat)
    <button type="button"
            data-status-filter="{{ $stat['status'] }}"
            class="stat-filter-btn flex items-center gap-3 {{ $stat['bg'] }} rounded-xl px-4 py-3
                   border border-transparent hover:border-gray-300 transition-all duration-150 text-left w-full">
        <span class="h-2.5 w-2.5 rounded-full {{ $stat['dot'] }} shrink-0"></span>
        <div>
            <p class="text-xs text-gray-500 font-medium">{{ $stat['label'] }}</p>
            <p id="stat-count-{{ $stat['status'] ?: 'total' }}" class="text-xl font-bold {{ $stat['color'] }} leading-none mt-0.5">{{ $stat['value'] }}</p>
        </div>
    </button>
    @endforeach
</div>

{{-- ── Table Card ── --}}
<div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">

    {{-- Toolbar — populated by DataTables initComplete --}}
    <div id="dt-toolbar" class="px-5 py-4 border-b border-gray-100 flex flex-col sm:flex-row gap-3 items-center"></div>

    <div class="overflow-x-auto">
        <table id="tenantsTable" class="w-full text-sm" style="width:100%">
            <thead>
                <tr class="bg-gray-50 border-b-2 border-gray-200">
                    <th class="px-6 py-3.5 text-left   text-xs font-bold text-gray-500 uppercase tracking-widest whitespace-nowrap">Client</th>
                    <th class="px-5 py-3.5 text-left   text-xs font-bold text-gray-500 uppercase tracking-widest whitespace-nowrap">Subdomain</th>
                    <th class="px-5 py-3.5 text-left   text-xs font-bold text-gray-500 uppercase tracking-widest whitespace-nowrap">Plan</th>
                    <th class="px-5 py-3.5 text-left   text-xs font-bold text-gray-500 uppercase tracking-widest whitespace-nowrap">Status</th>
                    <th class="px-5 py-3.5 text-center text-xs font-bold text-gray-500 uppercase tracking-widest whitespace-nowrap">Staff</th>
                    <th class="px-5 py-3.5 text-left   text-xs font-bold text-gray-500 uppercase tracking-widest whitespace-nowrap">Date Joined</th>
                    <th class="px-5 py-3.5 text-left   text-xs font-bold text-gray-500 uppercase tracking-widest whitespace-nowrap">Subscription</th>
                    <th class="px-5 py-3.5 text-left   text-xs font-bold text-gray-500 uppercase tracking-widest whitespace-nowrap">Latest Invoice</th>
                    <th class="px-5 py-3.5 text-center text-xs font-bold text-gray-500 uppercase tracking-widest whitespace-nowrap">Actions</th>
                </tr>
            </thead>
            <tbody>
                {{-- Rows injected by DataTables AJAX --}}
            </tbody>
        </table>
    </div>

    {{-- Pagination footer — populated by DataTables drawCallback --}}
    <div id="dt-footer" class="px-6 py-4 border-t border-gray-100 bg-gray-50/60 flex flex-col sm:flex-row items-center justify-between gap-3 hidden">
        <p id="dt-info" class="text-sm text-gray-500"></p>
        <div id="dt-pagination" class="flex items-center gap-1.5 flex-wrap justify-center"></div>
    </div>
</div>


{{-- ══════════════════════════════════════════════════════════════════
     DROPDOWN MENU (single instance, repositioned per click)
     ══════════════════════════════════════════════════════════════════ --}}
<div id="tenantDropdown"
     class="hidden fixed z-50 w-52 bg-white rounded-xl shadow-xl border border-gray-100 py-1 text-sm"
     role="menu">

    {{-- View Details --}}
    <button type="button" data-action="view"
            class="dropdown-item w-full flex items-center gap-3 px-4 py-2.5 text-gray-700 hover:bg-gray-50 transition-colors">
        <i class="fas fa-eye w-4 text-gray-400"></i> View Details
    </button>

    <div class="border-t border-gray-100 my-1"></div>

    {{-- Activate (hidden when already active) --}}
    <button type="button" data-action="activate"
            class="dropdown-item w-full flex items-center gap-3 px-4 py-2.5 text-green-700 hover:bg-green-50 transition-colors">
        <i class="fas fa-check-circle w-4 text-green-500"></i> Activate
    </button>

    {{-- Suspend (hidden when already suspended) --}}
    <button type="button" data-action="suspend"
            class="dropdown-item w-full flex items-center gap-3 px-4 py-2.5 text-orange-700 hover:bg-orange-50 transition-colors">
        <i class="fas fa-ban w-4 text-orange-500"></i> Suspend
    </button>

    {{-- Reset Subscription --}}
    <button type="button" data-action="reset"
            class="dropdown-item w-full flex items-center gap-3 px-4 py-2.5 text-blue-700 hover:bg-blue-50 transition-colors">
        <i class="fas fa-sync-alt w-4 text-blue-500"></i> Reset Subscription
    </button>

    <div class="border-t border-gray-100 my-1"></div>

    {{-- Delete --}}
    <button type="button" data-action="delete"
            class="dropdown-item w-full flex items-center gap-3 px-4 py-2.5 text-red-600 hover:bg-red-50 transition-colors font-medium">
        <i class="fas fa-trash-alt w-4 text-red-500"></i> Delete Tenant
    </button>
</div>


{{-- ══════════════════════════════════════════════════════════════════
     DELETE CONFIRMATION MODAL
     ══════════════════════════════════════════════════════════════════ --}}
<div id="deleteModal" class="hidden fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true">
    {{-- Backdrop --}}
    <div id="deleteBackdrop" class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm"></div>

    {{-- Panel --}}
    <div class="relative z-10 bg-white rounded-2xl shadow-2xl w-full max-w-md border border-gray-100 p-6">

        {{-- Icon + title --}}
        <div class="flex items-start gap-4 mb-5">
            <div class="h-12 w-12 bg-red-100 rounded-xl flex items-center justify-center shrink-0">
                <i class="fas fa-trash-alt text-red-600 text-lg"></i>
            </div>
            <div>
                <h2 class="text-lg font-bold text-gray-900">Delete Tenant</h2>
                <p class="text-sm text-gray-500 mt-0.5">
                    This will permanently delete <strong id="deleteModalName" class="text-gray-800"></strong>
                    and all associated data. This action cannot be undone.
                </p>
            </div>
        </div>

        {{-- Confirmation input --}}
        <div class="mb-5">
            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                Type the company name to confirm
            </label>
            <input type="text"
                   id="deleteConfirmInput"
                   placeholder="Company name…"
                   autocomplete="off"
                   class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-xl
                          focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent transition">
            <p id="deleteConfirmHint" class="text-xs text-red-500 mt-1 hidden">Name does not match — please try again.</p>
        </div>

        {{-- Buttons --}}
        <div class="flex gap-3 justify-end">
            <button type="button" id="deleteCancelBtn"
                    class="px-5 py-2.5 text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">
                Cancel
            </button>
            <button type="button" id="deleteConfirmBtn"
                    disabled
                    class="px-5 py-2.5 text-sm font-semibold text-white bg-red-500 hover:bg-red-600 disabled:opacity-40 disabled:cursor-not-allowed rounded-xl transition-colors flex items-center gap-2">
                <i class="fas fa-trash-alt"></i> Delete Tenant
            </button>
        </div>
    </div>
</div>


{{-- ══════════════════════════════════════════════════════════════════
     TOAST NOTIFICATION
     ══════════════════════════════════════════════════════════════════ --}}
<div id="toast"
     class="hidden fixed bottom-6 right-6 z-[70] flex items-center gap-3 px-5 py-4
            bg-white rounded-2xl shadow-xl border border-gray-100 min-w-[280px] max-w-sm">
    <div id="toastIcon" class="h-9 w-9 rounded-xl flex items-center justify-center shrink-0"></div>
    <div class="flex-1 min-w-0">
        <p id="toastMessage" class="text-sm font-semibold text-gray-800"></p>
    </div>
    <button type="button" onclick="Toast.hide()" class="text-gray-400 hover:text-gray-600 transition-colors shrink-0">
        <i class="fas fa-times text-xs"></i>
    </button>
</div>

@endsection


@push('scripts')
<!-- DataTables Buttons Extension -->
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

<style>
    /* Shared DataTables cell padding */
    #tenantsTable tbody td { padding: 1rem 1.25rem; vertical-align: middle; border-bottom: 1px solid #f3f4f6; }
    #tenantsTable tbody td:first-child { padding-left: 1.5rem; }
    #tenantsTable tbody tr:last-child td { border-bottom: none; }
    #tenantsTable tbody tr:hover td { background-color: rgba(236, 253, 245, 0.4); }

    /* Status pill helper classes (set in PHP constant) */
    .dt-status-pill { display: inline-flex; align-items: center; gap: 6px; padding: 2px 10px; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; }
    .dt-status-dot  { display: inline-block; width: 6px; height: 6px; border-radius: 9999px; }

    /* Processing overlay */
    #tenantsTable_processing { background: rgba(255,255,255,0.8); border: none; box-shadow: none; }
</style>

<script>
$(function () {

    // ── CSRF token (used in all AJAX mutations) ──────────────────────────────
    const CSRF = '{{ csrf_token() }}';

    // ── Active status filter ──────────────────────────────────────────────────
    let activeStatus = '';

    // ═════════════════════════════════════════════════════════════════════════
    // DATATABLES INIT
    // ═════════════════════════════════════════════════════════════════════════
    const table = $('#tenantsTable').DataTable({
        buttons    : ['csv', 'excel', 'pdf', 'print'], // Hidden native buttons
        processing : true,
        serverSide : true,
        ajax: {
            url : '{{ route('superadmin.tenants.data') }}',
            data: function (d) { d.status = activeStatus; },
        },
        columns: [
            { data: 0, orderable: true  },                              // Client
            { data: 1, orderable: true  },                              // Subdomain
            { data: 2, orderable: true  },                              // Plan
            { data: 3, orderable: true  },                              // Status
            { data: 4, orderable: true,  className: 'text-center' },   // Staff
            { data: 5, orderable: true  },                              // Date Joined
            { data: 6, orderable: true  },                              // Subscription
            { data: 7, orderable: false },                              // Latest Invoice
            { data: 8, orderable: false, className: 'text-center' },   // Actions
        ],
        order      : [[5, 'desc']],
        pageLength : 10,
        lengthMenu : [[5, 10, 25, 50, 100], [5, 10, 25, 50, 100]],
        dom        : 'rt',      // custom toolbar + footer — no built-in controls
        language: {
            processing  : '<div class="py-10 text-center text-gray-400"><i class="fas fa-circle-notch fa-spin text-emerald-500 mr-2"></i>Loading…</div>',
            zeroRecords : '<div class="py-16 text-center"><i class="fas fa-building text-4xl text-gray-200 mb-3 block"></i><p class="font-semibold text-gray-500">No tenants found</p><p class="text-sm text-gray-400 mt-1">Try a different search or filter.</p></div>',
            emptyTable  : '<div class="py-16 text-center"><i class="fas fa-building text-4xl text-gray-200 mb-3 block"></i><p class="font-semibold text-gray-500">No tenants registered yet.</p></div>',
        },
        initComplete: function () {
            Toolbar.build(this.api());
            Footer.build(this.api());
        },
        drawCallback: function () {
            const api = this.api();
            Footer.update(api);
            TableInfo.update(api);
        },
    });

    // ═════════════════════════════════════════════════════════════════════════
    // STATS BAR FILTER & REFRESH
    // ═════════════════════════════════════════════════════════════════════════
    const StatsBar = {
        refresh() {
            $.get('{{ route('superadmin.tenants.summary') }}', function(data) {
                $('#stat-count-total').text(data.total);
                $('#stat-count-active').text(data.active);
                $('#stat-count-trial').text(data.trial);
                $('#stat-count-pending').text(data.pending);
                $('#stat-count-suspended').text(data.suspended);
            });
        }
    };

    $('.stat-filter-btn').on('click', function () {
        activeStatus = $(this).data('status-filter');
        $('.stat-filter-btn').removeClass('ring-2 ring-emerald-400 ring-offset-1');
        $(this).addClass('ring-2 ring-emerald-400 ring-offset-1');
        table.draw();
    });

    // ═════════════════════════════════════════════════════════════════════════
    // TOOLBAR
    // ═════════════════════════════════════════════════════════════════════════
    const Toolbar = {
        build(api) {
            const $tb = $('#dt-toolbar').empty();

            // Search
            const $sw = $('<div class="flex-1 relative w-full"></div>');
            $('<span class="absolute inset-y-0 left-3.5 flex items-center pointer-events-none text-gray-400"><i class="fas fa-search text-sm"></i></span>').appendTo($sw);
            $('<input type="text" placeholder="Search by name, subdomain or email…" class="w-full pl-10 pr-4 py-2.5 text-sm border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-400 transition">')
                .appendTo($sw)
                .on('keyup', function () { api.search(this.value).draw(); });
            $tb.append($sw);

            // Export Dropdown
            const $ex = $('<div class="relative shrink-0"></div>');
            const $exBtn = $('<button type="button" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-emerald-400 transition flex items-center gap-2"><i class="fas fa-inbox text-gray-400"></i> Export <i class="fas fa-chevron-down text-xs ml-1 text-gray-400"></i></button>').appendTo($ex);
            const $exMenu = $('<div class="hidden absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-xl border border-gray-100 py-1 z-50"></div>').appendTo($ex);
            
            const btnTpl = (icon, color, label, action) => `<button type="button" class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors" onclick="$('#tenantsTable').DataTable().button('.buttons-${action}').trigger()"><i class="${icon} w-4 ${color}"></i> ${label}</button>`;
            
            $exMenu.append(btnTpl('fas fa-file-pdf', 'text-red-500', 'Download PDF', 'pdf'));
            $exMenu.append(btnTpl('fas fa-file-excel', 'text-green-500', 'Download Excel', 'excel'));
            $exMenu.append(btnTpl('fas fa-file-csv', 'text-blue-500', 'Download CSV', 'csv'));
            $exMenu.append('<div class="border-t border-gray-100 my-1"></div>');
            $exMenu.append(btnTpl('fas fa-print', 'text-gray-500', 'Print Directory', 'print'));

            $exBtn.on('click', (e) => { e.stopPropagation(); $exMenu.toggleClass('hidden'); });
            $(document).on('click', () => $exMenu.addClass('hidden'));
            
            $tb.append($ex);

            // Page-length chooser
            const $lw = $('<div class="flex items-center gap-2 shrink-0"></div>');
            $('<span class="text-sm text-gray-500 font-medium whitespace-nowrap">Show</span>').appendTo($lw);
            const $sel = $('<select class="px-3 py-2.5 text-sm border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-400 transition"></select>');
            [[5,'5 rows'],[10,'10 rows'],[25,'25 rows'],[50,'50 rows'],[100,'100 rows']].forEach(([val, label]) => {
                $('<option>').val(val).text(label).prop('selected', api.page.len() === val).appendTo($sel);
            });
            $sel.on('change', function () { api.page.len(+this.value).draw(); }).appendTo($lw);
            $tb.append($lw);
        },
    };

    // ═════════════════════════════════════════════════════════════════════════
    // FOOTER & PAGINATION
    // ═════════════════════════════════════════════════════════════════════════
    const Footer = {
        build(api) {
            $('#dt-footer').html(
                '<p id="dt-info" class="text-sm text-gray-500"></p>' +
                '<div id="dt-pagination" class="flex items-center gap-1.5 flex-wrap justify-center"></div>'
            );
            this.update(api);
        },
        update(api) {
            const info = api.page.info();
            $('#dt-footer').toggleClass('hidden', info.pages <= 1);
            this.renderPagination(api, info);
        },
        renderPagination(api, info) {
            const $p = $('#dt-pagination').empty();
            const cur = info.page, total = info.pages;
            if (total <= 1) return;

            const btn = (label, handler, disabled = false) =>
                $('<button type="button">')
                    .addClass('px-3 py-1.5 text-sm border rounded-lg transition-colors ' +
                        (disabled ? 'border-gray-200 text-gray-300 cursor-not-allowed'
                                  : 'border-gray-200 text-gray-600 hover:bg-emerald-50 hover:border-emerald-300 hover:text-emerald-700'))
                    .html(label)
                    .prop('disabled', disabled)
                    .on('click', handler);

            $p.append(btn('<i class="fas fa-chevron-left text-xs"></i>', () => api.page('previous').draw('page'), cur === 0));

            const start = Math.max(0, Math.min(cur - 2, total - 5));
            const end   = Math.min(total - 1, start + 4);
            for (let pg = start; pg <= end; pg++) {
                const isActive = pg === cur;
                $('<button type="button">')
                    .addClass('px-3.5 py-1.5 text-sm border rounded-lg transition-colors ' +
                        (isActive ? 'bg-emerald-600 text-white border-emerald-600 font-bold'
                                  : 'border-gray-200 text-gray-600 hover:bg-emerald-50 hover:border-emerald-300 hover:text-emerald-700'))
                    .text(pg + 1)
                    .on('click', () => api.page(pg).draw('page'))
                    .appendTo($p);
            }

            $p.append(btn('<i class="fas fa-chevron-right text-xs"></i>', () => api.page('next').draw('page'), cur >= total - 1));
        },
    };

    const TableInfo = {
        update(api) {
            const i = api.page.info();
            if (!i.recordsTotal) { $('#tableInfo').text('No tenants found.'); return; }
            $('#tableInfo').text(
                `Showing ${i.start + 1}–${i.end} of ${i.recordsFiltered} organisations` +
                (i.recordsFiltered !== i.recordsTotal ? ` (filtered from ${i.recordsTotal} total)` : '')
            );
        },
    };

    // ═════════════════════════════════════════════════════════════════════════
    // TOAST NOTIFICATION
    // ═════════════════════════════════════════════════════════════════════════
    const Toast = window.Toast = {
        _timer: null,
        show(message, type = 'success') {
            const $toast = $('#toast');
            const $icon  = $('#toastIcon');
            const $msg   = $('#toastMessage');

            const config = {
                success: { bg: 'bg-green-100',  icon: 'fas fa-check-circle', color: 'text-green-600' },
                error:   { bg: 'bg-red-100',    icon: 'fas fa-times-circle',  color: 'text-red-600'   },
                info:    { bg: 'bg-blue-100',   icon: 'fas fa-info-circle',   color: 'text-blue-600'  },
            }[type] || { bg: 'bg-gray-100', icon: 'fas fa-bell', color: 'text-gray-600' };

            $icon.attr('class', `h-9 w-9 rounded-xl flex items-center justify-center shrink-0 ${config.bg}`)
                 .html(`<i class="${config.icon} ${config.color}"></i>`);
            $msg.text(message);

            $toast.removeClass('hidden').addClass('flex');
            clearTimeout(this._timer);
            this._timer = setTimeout(() => this.hide(), 4000);
        },
        hide() {
            $('#toast').removeClass('flex').addClass('hidden');
        },
    };

    // ═════════════════════════════════════════════════════════════════════════
    // ACTION DROPDOWN
    // ═════════════════════════════════════════════════════════════════════════
    const Dropdown = {
        _currentTenant: null,
        $el: $('#tenantDropdown'),

        open(btn) {
            const $btn = $(btn);
            this._currentTenant = {
                id:     $btn.data('tenant-id'),
                name:   $btn.data('tenant-name'),
                status: $btn.data('tenant-status'),
            };

            this._adjustMenuItems(this._currentTenant.status);

            // Position below the trigger button
            const rect = btn.getBoundingClientRect();
            const spaceBelow = window.innerHeight - rect.bottom;
            const menuHeight = 220;
            const top = spaceBelow >= menuHeight
                ? rect.bottom + window.scrollY + 4
                : rect.top  + window.scrollY - menuHeight - 4;

            this.$el.css({ top, right: window.innerWidth - rect.right })
                    .removeClass('hidden');
        },

        close() { this.$el.addClass('hidden'); },

        _adjustMenuItems(status) {
            // Show/hide context-sensitive items based on current tenant status
            $('[data-action="activate"]').toggle(status !== 'active');
            $('[data-action="suspend"]').toggle(status !== 'suspended');
        },
    };

    // Open dropdown on ⋮ click (delegated — rows are rendered by DataTables)
    $(document).on('click', '.tenant-action-btn', function (e) {
        e.stopPropagation();
        if (Dropdown._currentTenant && !Dropdown.$el.hasClass('hidden')) {
            Dropdown.close();
            return;
        }
        Dropdown.open(this);
    });

    // Close dropdown when clicking outside
    $(document).on('click', function (e) {
        if (!$(e.target).closest('#tenantDropdown, .tenant-action-btn').length) {
            Dropdown.close();
        }
    });

    // ─── Dropdown item handlers ───────────────────────────────────────────────
    function doAction(action, tenant) {
        Dropdown.close();
        const urls = {
            activate : `/superadmin/tenants/${tenant.id}/activate`,
            suspend  : `/superadmin/tenants/${tenant.id}/suspend`,
            reset    : `/superadmin/tenants/${tenant.id}/reset-subscription`,
        };

        if (action === 'view') {
            // View details — for now open in new tab via subdomain (future: detail page)
            Toast.show(`Viewing ${tenant.name}…`, 'info');
            return;
        }

        if (action === 'delete') {
            DeleteModal.open(tenant);
            return;
        }

        // POST actions (activate / suspend / reset)
        $.ajax({
            url   : urls[action],
            method: 'POST',
            data  : { _token: CSRF },
            success(res) {
                Toast.show(res.message, 'success');
                table.ajax.reload(null, false);   // reload without resetting page
                StatsBar.refresh();
            },
            error(xhr) {
                Toast.show(xhr.responseJSON?.message || 'Something went wrong.', 'error');
            },
        });
    }

    $(document).on('click', '[data-action]', function () {
        const action = $(this).data('action');
        if (Dropdown._currentTenant) doAction(action, Dropdown._currentTenant);
    });

    // ═════════════════════════════════════════════════════════════════════════
    // DELETE CONFIRMATION MODAL
    // ═════════════════════════════════════════════════════════════════════════
    const DeleteModal = {
        _tenant: null,

        open(tenant) {
            this._tenant = tenant;
            $('#deleteModalName').text(tenant.name);
            $('#deleteConfirmInput').val('').trigger('input');
            $('#deleteConfirmHint').addClass('hidden');
            $('#deleteModal').removeClass('hidden');
            $('#deleteConfirmInput').focus();
        },

        close() {
            $('#deleteModal').addClass('hidden');
            this._tenant = null;
        },

        confirm() {
            if (!this._tenant) return;
            const tenant = this._tenant;

            $('#deleteConfirmBtn').prop('disabled', true)
                .html('<i class="fas fa-circle-notch fa-spin mr-1"></i> Deleting…');

            $.ajax({
                url   : `/superadmin/tenants/${tenant.id}`,
                method: 'POST',
                data  : { _token: CSRF, _method: 'DELETE' },
                success(res) {
                    DeleteModal.close();
                    Toast.show(res.message, 'success');
                    table.ajax.reload(null, false);
                    StatsBar.refresh();
                },
                error(xhr) {
                    DeleteModal.close();
                    Toast.show(xhr.responseJSON?.message || 'Delete failed.', 'error');
                },
            });
        },
    };

    // Enable confirm button only when typed name matches exactly
    $('#deleteConfirmInput').on('input', function () {
        const matches = $(this).val().trim() === DeleteModal._tenant?.name?.trim();
        $('#deleteConfirmBtn').prop('disabled', !matches);
        $('#deleteConfirmHint').toggleClass('hidden', matches || !$(this).val());
    });

    $('#deleteConfirmBtn').on('click', () => DeleteModal.confirm());
    $('#deleteCancelBtn, #deleteBackdrop').on('click', () => DeleteModal.close());

    // Keyboard: Escape closes dropdown and modal
    $(document).on('keydown', function (e) {
        if (e.key === 'Escape') { Dropdown.close(); DeleteModal.close(); }
    });

});
</script>
@endpush
