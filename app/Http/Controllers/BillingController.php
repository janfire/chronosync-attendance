<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\BillingService;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    public function __construct(protected BillingService $billingService) {}

    public function index()
    {
        $tenant   = app('current_tenant');
        $invoices = Invoice::withoutTenantScope()
            ->where('tenant_id', $tenant->id)
            ->latest()
            ->get();

        // Safety net: if tenant cannot access the system and has no pending invoice
        // this month, auto-generate one so they can immediately pay.
        if (!$tenant->canAccess()) {
            $pendingThisMonth = $invoices->where('status', 'pending')
                ->where('period_start', now()->startOfMonth()->toDateString())
                ->first();

            if (!$pendingThisMonth) {
                $newInvoice = $this->billingService->generateMonthlyInvoice($tenant);

                if ($newInvoice) {
                    // Reload invoices to include the newly generated one
                    $invoices = Invoice::withoutTenantScope()
                        ->where('tenant_id', $tenant->id)
                        ->latest()
                        ->get();
                }
            }
        }

        return view('billing.index', compact('tenant', 'invoices'));
    }

    public function show(Invoice $invoice)
    {
        // Ensure the invoice belongs to this tenant
        $invoice = Invoice::withoutTenantScope()
            ->where('tenant_id', app('current_tenant')->id)
            ->findOrFail($invoice->id);

        return view('billing.invoice', compact('invoice'));
    }

    /**
     * Generate a renewal invoice on demand (e.g. from the "Renew" button).
     * Idempotent — if one already exists for this period it redirects to it.
     */
    public function renew()
    {
        $tenant  = app('current_tenant');
        $invoice = $this->billingService->generateMonthlyInvoice($tenant);

        if (!$invoice) {
            return back()->with('error', 'Could not generate an invoice. Please contact support.');
        }

        return redirect()
            ->route('billing.invoice', $invoice->id)
            ->with('success', "Invoice {$invoice->invoice_number} is ready. Please review and submit your payment.");
    }

    public function uploadProof(Request $request, Invoice $invoice)
    {
        $request->validate([
            'payment_method'    => 'required|in:ecocash,zipit,bank_transfer',
            'payment_reference' => 'required|string',
            'payment_proof'     => 'required|image|max:2048',
        ]);

        // Ensure invoice belongs to this tenant
        $invoice = Invoice::withoutTenantScope()
            ->where('tenant_id', app('current_tenant')->id)
            ->findOrFail($invoice->id);

        $path = $request->file('payment_proof')->store('payment_proofs', 'public');

        $invoice->update([
            'payment_method'    => $request->payment_method,
            'payment_reference' => $request->payment_reference,
            'payment_proof_path' => $path,
            'notes'             => 'Proof of payment uploaded by tenant.',
        ]);

        // Notify platform admins that a proof has been uploaded
        try {
            $adminEmails = \App\Models\User::where('role', \App\Enums\UserRole::PLATFORM_ADMIN)
                ->pluck('email')
                ->toArray();

            \Illuminate\Support\Facades\Mail::to($adminEmails)
                ->send(new \App\Mail\AdminInvoiceProofUploaded($tenant, $invoice));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to send admin proof upload mail: ' . $e->getMessage());
        }

        return back()->with('success', 'Proof of payment uploaded. Our team will verify and activate your account shortly.');
    }
}
