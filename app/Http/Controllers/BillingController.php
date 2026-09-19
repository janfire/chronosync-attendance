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

    public function changePlan(Request $request)
    {
        $request->validate([
            'plan' => 'required|exists:subscription_plans,slug'
        ]);

        $tenant = app('current_tenant');
        if ($tenant->plan === $request->plan) {
            return back()->with('info', 'You are already on this plan.');
        }

        $newPlan = \App\Models\SubscriptionPlan::where('slug', $request->plan)->firstOrFail();
        $currentPlan = \App\Models\SubscriptionPlan::where('slug', $tenant->plan)->first();

        // 1. Check if the current user count exceeds the new plan's max employees
        if ($newPlan->max_employees > 0) {
            $employeeCount = \App\Models\User::where('tenant_id', $tenant->id)
                ->where('role', '!=', \App\Enums\UserRole::PLATFORM_ADMIN)
                ->count();
            if ($employeeCount > $newPlan->max_employees) {
                return back()->with('error', "Cannot switch to {$newPlan->name} plan. You currently have {$employeeCount} employees, but the limit is {$newPlan->max_employees}. Please remove employees first.");
            }
        }

        // Downgrade logic (price is less)
        if ($newPlan->price_usd < ($currentPlan->price_usd ?? 0)) {
            $tenant->update(['upcoming_plan' => $newPlan->slug]);
            return back()->with('success', "Your plan will be downgraded to {$newPlan->name} at the end of your current billing cycle.");
        }

        // Upgrade logic (Proration)
        if ($newPlan->price_usd > ($currentPlan->price_usd ?? 0)) {
            $totalDays = $tenant->subscription_starts_at && $tenant->subscription_expires_at 
                ? $tenant->subscription_starts_at->diffInDays($tenant->subscription_expires_at) 
                : 30;
            if ($totalDays <= 0) $totalDays = 30;

            $daysRemaining = $tenant->subscription_expires_at && $tenant->subscription_expires_at->isFuture()
                ? now()->diffInDays($tenant->subscription_expires_at)
                : 0;

            $currentDailyRate = ($currentPlan->price_usd ?? 0) / $totalDays;
            $newDailyRate = $newPlan->price_usd / $totalDays;

            $proratedAmount = ($newDailyRate - $currentDailyRate) * $daysRemaining;

            // Generate a prorated invoice if amount > 0
            if ($proratedAmount > 0) {
                $invoice = Invoice::create([
                    'tenant_id' => $tenant->id,
                    'subscription_plan_id' => $newPlan->id,
                    'invoice_number' => Invoice::generateInvoiceNumber(),
                    'amount_usd' => round($proratedAmount, 2),
                    'period_start' => now(),
                    'period_end' => $tenant->subscription_expires_at ?? now()->addDays(30),
                    'due_date' => now()->addDays(7),
                    'status' => 'pending',
                ]);

                return redirect()->route('billing.invoice', $invoice->id)
                    ->with('success', "Your prorated upgrade invoice is ready. Your plan will switch to {$newPlan->name} as soon as this is paid.");
            } else {
                // If there are no days remaining or something else, just switch them
                $tenant->update(['plan' => $newPlan->slug]);
                return back()->with('success', "Plan changed to {$newPlan->name} successfully.");
            }
        }

        return back()->with('success', 'Plan changed successfully.');
    }
}
