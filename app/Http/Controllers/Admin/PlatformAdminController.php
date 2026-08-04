<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\AdminDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlatformAdminController extends Controller
{
    // Avatar colour palette — deterministic per company name
    private const AVATAR_COLOURS = [
        'bg-emerald-500', 'bg-teal-500', 'bg-cyan-500', 'bg-blue-500',
        'bg-violet-500',  'bg-purple-500','bg-pink-500', 'bg-rose-500',
    ];

    // Status badge HTML map
    private const STATUS_PILLS = [
        'active'    => '<span class="dt-status-pill bg-green-100 text-green-800"><span class="dt-status-dot bg-green-500"></span>Active</span>',
        'trial'     => '<span class="dt-status-pill bg-blue-100 text-blue-800"><span class="dt-status-dot bg-blue-500"></span>Trial</span>',
        'pending'   => '<span class="dt-status-pill bg-orange-100 text-orange-800"><span class="dt-status-dot bg-orange-400"></span>Pending</span>',
        'suspended' => '<span class="dt-status-pill bg-red-100 text-red-800"><span class="dt-status-dot bg-red-500"></span>Suspended</span>',
        'expired'   => '<span class="dt-status-pill bg-gray-100 text-gray-600"><span class="dt-status-dot bg-gray-400"></span>Expired</span>',
    ];

    // Invoice badge colour map
    private const INVOICE_PILLS = [
        'paid'    => 'bg-green-100 text-green-700',
        'pending' => 'bg-orange-100 text-orange-700',
        'overdue' => 'bg-red-100 text-red-700',
    ];

    protected AdminDashboardService $adminDashboardService;

    public function __construct(AdminDashboardService $adminDashboardService)
    {
        $this->adminDashboardService = $adminDashboardService;
    }

    // =========================================================================
    // PAGE ACTIONS
    // =========================================================================

    public function dashboard(): \Illuminate\View\View
    {
        $metrics = $this->adminDashboardService->getMetrics();
        return view('admin.platform_dashboard', compact('metrics'));
    }

    public function tenants(): \Illuminate\View\View
    {
        $summary = $this->tenantSummary();
        return view('admin.superadmin.tenants', compact('summary'));
    }

    /**
     * Lightweight JSON endpoint for the stats bar counts.
     * Called by the frontend after every tenant mutation (activate / suspend /
     * reset / delete) so the card numbers update instantly without a page reload.
     */
    public function tenantsSummary(): JsonResponse
    {
        return response()->json($this->tenantSummary());
    }

    // =========================================================================
    // DATATABLES AJAX ENDPOINT
    // =========================================================================

    public function tenantsData(Request $request): JsonResponse
    {
        // Bypass BelongsToTenant global scope on related models.
        // These subqueries must see ALL records across every tenant,
        // not just the one bound to the current request context.
        $query = Tenant::withCount([
                'users' => fn ($q) => $q->withoutGlobalScope('tenant'),
            ])
            ->with([
                // withoutGlobalScope ensures the Invoice scope doesn't filter
                // to only the current tenant's invoices.
                // We cannot use limit(1) in an eager load constraint — it applies
                // globally (1 row total), not 1-per-parent. Use latestOfMany instead.
                'invoices' => fn ($q) => $q->withoutGlobalScope('tenant')->latest()->limit(1),
            ]);

        // Global search across key text columns
        if ($search = $request->input('search.value')) {
            $query->where(function ($q) use ($search) {
                $q->where('company_name', 'ilike', "%{$search}%")
                  ->orWhere('subdomain',   'ilike', "%{$search}%")
                  ->orWhere('email',       'ilike', "%{$search}%");
            });
        }

        // Status filter — cast to string and trim to prevent empty-string truthy issues
        $status = trim((string) $request->input('status', ''));
        if ($status !== '') {
            $query->where('status', $status);
        }

        $totalFiltered = (clone $query)->count();

        // Server-side ordering
        $orderableColumns = [
            0 => 'company_name',
            1 => 'subdomain',
            2 => 'plan',
            3 => 'status',
            4 => 'users_count',
            5 => 'created_at',
            6 => 'subscription_expires_at',
        ];
        $orderColIndex = (int) $request->input('order.0.column', 5);
        $orderDir      = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $orderCol      = $orderableColumns[$orderColIndex] ?? 'created_at';
        $query->orderBy($orderCol, $orderDir);

        // Pagination — clamp to safe bounds
        $start   = max(0, (int) $request->input('start', 0));
        $length  = min(100, max(5, (int) $request->input('length', 10)));
        $tenants = $query->skip($start)->take($length)->get();

        return response()->json([
            'draw'            => (int) $request->input('draw'),
            'recordsTotal'    => Tenant::count(),
            'recordsFiltered' => $totalFiltered,
            'data'            => $tenants->map(fn ($t) => $this->formatTenantRow($t))->values(),
        ]);
    }

    // =========================================================================
    // TENANT ACTIONS  (all return JSON for AJAX consumption)
    // =========================================================================

    public function activateTenant(Tenant $tenant): JsonResponse
    {
        $tenant->update(['status' => 'active']);

        return response()->json([
            'message' => "{$tenant->company_name} has been activated successfully.",
        ]);
    }

    public function suspendTenant(Tenant $tenant): JsonResponse
    {
        $tenant->update(['status' => 'suspended']);

        return response()->json([
            'message' => "{$tenant->company_name} has been suspended.",
        ]);
    }

    public function resetTenantSubscription(Tenant $tenant): JsonResponse
    {
        $tenant->update([
            'status'                  => 'active',
            'subscription_starts_at'  => now(),
            'subscription_expires_at' => now()->addMonth(),
        ]);

        return response()->json([
            'message' => "Subscription for {$tenant->company_name} has been reset. New expiry: " . now()->addMonth()->format('d M Y') . '.',
        ]);
    }

    public function destroyTenant(Tenant $tenant): JsonResponse
    {
        $name = $tenant->company_name;
        $tenant->delete();

        return response()->json([
            'message' => "{$name} has been permanently deleted.",
        ]);
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    /**
     * Returns platform-wide tenant status counts for the stats bar.
     */
    private function tenantSummary(): array
    {
        return [
            'total'     => Tenant::count(),
            'active'    => Tenant::where('status', 'active')->count(),
            'trial'     => Tenant::where('status', 'trial')->count(),
            'pending'   => Tenant::where('status', 'pending')->count(),
            'suspended' => Tenant::where('status', 'suspended')->count(),
        ];
    }

    /**
     * Formats a single Tenant model into the DataTables row array.
     * Each element corresponds to a column in the view.
     */
    private function formatTenantRow(Tenant $tenant): array
    {
        $avatarBg = self::AVATAR_COLOURS[abs(crc32($tenant->company_name)) % count(self::AVATAR_COLOURS)];
        $initials  = strtoupper(substr($tenant->company_name, 0, 2));
        $invoice   = $tenant->invoices->first();

        return [
            $this->clientCell($tenant, $avatarBg, $initials),
            $this->subdomainCell($tenant),
            $this->planCell($tenant),
            self::STATUS_PILLS[$tenant->status] ?? '<span class="text-xs text-gray-500">' . ucfirst($tenant->status) . '</span>',
            $this->staffCell($tenant),
            $this->joinedCell($tenant),
            $this->subscriptionCell($tenant),
            $this->invoiceCell($invoice),
            $this->actionsCell($tenant),  // 9th column — the action menu trigger
        ];
    }

    private function clientCell(Tenant $tenant, string $avatarBg, string $initials): string
    {
        return '<div class="flex items-center gap-3">
            <div class="h-9 w-9 rounded-xl ' . $avatarBg . ' flex items-center justify-center shrink-0 shadow-sm">
                <span class="text-white font-bold text-xs">' . e($initials) . '</span>
            </div>
            <div class="min-w-0">
                <p class="font-semibold text-gray-900 text-sm">' . e($tenant->company_name) . '</p>
                <p class="text-xs text-gray-400">' . e($tenant->email) . '</p>
            </div>
        </div>';
    }

    private function subdomainCell(Tenant $tenant): string
    {
        return '<span class="font-mono text-xs bg-gray-100 text-gray-600 px-2.5 py-1 rounded-lg whitespace-nowrap">'
            . e($tenant->subdomain)
            . '</span>';
    }

    private function planCell(Tenant $tenant): string
    {
        return '<p class="font-semibold text-gray-800 text-sm">' . e($tenant->getPlanLabel()) . '</p>'
            . ($tenant->max_employees
                ? '<p class="text-xs text-gray-400">Up to ' . $tenant->max_employees . ' staff</p>'
                : '');
    }

    private function staffCell(Tenant $tenant): string
    {
        return '<div class="flex items-center justify-center gap-1.5">
            <i class="fas fa-users text-gray-400 text-xs"></i>
            <span class="font-bold text-gray-700">' . $tenant->users_count . '</span>
        </div>';
    }

    private function joinedCell(Tenant $tenant): string
    {
        return '<p class="font-medium text-gray-800 text-sm">' . $tenant->created_at->format('d M Y') . '</p>'
            . '<p class="text-xs text-gray-400">' . $tenant->created_at->diffForHumans() . '</p>';
    }

    private function subscriptionCell(Tenant $tenant): string
    {
        $expiryDate  = $tenant->subscription_expires_at ?? $tenant->trial_ends_at;
        $expiryLabel = $tenant->subscription_expires_at ? 'Expires' : 'Trial ends';
        $pastClass   = $expiryDate?->isPast() ? 'text-red-600' : 'text-gray-800';
        $pastSubClass = $expiryDate?->isPast() ? 'text-red-400' : 'text-gray-400';

        if (! $expiryDate) {
            return '<span class="text-xs text-gray-400 italic">No subscription</span>';
        }

        return '<p class="font-medium text-sm ' . $pastClass . '">' . $expiryLabel . ' ' . $expiryDate->format('d M Y') . '</p>'
            . '<p class="text-xs ' . $pastSubClass . '">' . $expiryDate->diffForHumans() . '</p>';
    }

    private function invoiceCell(?object $invoice): string
    {
        if (! $invoice) {
            return '<span class="text-xs text-gray-400 italic">None</span>';
        }

        $pillClass = self::INVOICE_PILLS[$invoice->status] ?? 'bg-gray-100 text-gray-600';

        return '<p class="font-bold text-gray-800 text-sm">$' . number_format($invoice->amount_usd, 2) . '</p>'
            . '<span class="text-xs px-2 py-0.5 rounded-full font-semibold ' . $pillClass . '">' . ucfirst($invoice->status) . '</span>';
    }

    /**
     * Renders the ⋮ trigger button for the per-row action dropdown.
     * All action metadata is stored in data-* attributes — the JS
     * reads these to build the context-aware menu dynamically.
     */
    private function actionsCell(Tenant $tenant): string
    {
        $html = '<div class="table-action-group tenant-inline-actions justify-center" 
                      data-tenant-id="' . $tenant->id . '"
                      data-tenant-name="' . e($tenant->company_name) . '"
                      data-tenant-status="' . $tenant->status . '">';

        $html .= '<button type="button" data-action="view" class="table-action-btn table-action-btn--view" title="View Details">
                    <i class="fas fa-eye text-sm"></i>
                  </button>';

        if ($tenant->status !== 'active') {
            $html .= '<button type="button" data-action="activate" class="table-action-btn text-green-600 hover:bg-green-50" title="Activate">
                        <i class="fas fa-check-circle text-sm"></i>
                      </button>';
        }

        if ($tenant->status !== 'suspended') {
            $html .= '<button type="button" data-action="suspend" class="table-action-btn table-action-btn--warn" title="Suspend">
                        <i class="fas fa-ban text-sm"></i>
                      </button>';
        }

        $html .= '<button type="button" data-action="reset" class="table-action-btn text-blue-600 hover:bg-blue-50" title="Reset Subscription">
                    <i class="fas fa-sync-alt text-sm"></i>
                  </button>';

        $html .= '<button type="button" data-action="delete" class="table-action-btn table-action-btn--danger" title="Delete Tenant">
                    <i class="fas fa-trash-alt text-sm"></i>
                  </button>';

        $html .= '</div>';
        
        return $html;
    }
}
