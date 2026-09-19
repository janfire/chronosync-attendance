<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvoiceGenerated;

class BillingService
{
    /**
     * Generate the monthly invoice for a tenant.
     * Idempotent: will return an existing invoice if one already exists
     * for this period (regardless of status), preventing duplicates.
     */
    public function generateMonthlyInvoice(Tenant $tenant): ?Invoice
    {
        $periodStart = now()->startOfMonth();
        $periodEnd   = now()->endOfMonth();

        // Return any existing non-cancelled invoice for this period
        // (covers pending, paid, overdue) to prevent duplicates
        $existing = Invoice::withoutTenantScope()
            ->where('tenant_id', $tenant->id)
            ->where('period_start', $periodStart->toDateString())
            ->whereNotIn('status', ['cancelled'])
            ->first();

        if ($existing) {
            return $existing;
        }

        // Process end-of-cycle downgrades
        if ($tenant->upcoming_plan) {
            $tenant->update(['plan' => $tenant->upcoming_plan, 'upcoming_plan' => null]);
        }

        $plan = SubscriptionPlan::where('slug', $tenant->plan)->first();
        if (!$plan) {
            return null;
        }

        // Apply account balance credits
        $invoiceAmount = $plan->price_usd;
        if ($tenant->account_balance_usd > 0) {
            if ($tenant->account_balance_usd >= $invoiceAmount) {
                $tenant->update(['account_balance_usd' => $tenant->account_balance_usd - $invoiceAmount]);
                $invoiceAmount = 0;
            } else {
                $invoiceAmount -= $tenant->account_balance_usd;
                $tenant->update(['account_balance_usd' => 0]);
            }
        }

        $invoice = Invoice::create([
            'tenant_id'            => $tenant->id,
            'subscription_plan_id' => $plan->id,
            'invoice_number'       => Invoice::generateInvoiceNumber(),
            'amount_usd'           => $invoiceAmount,
            'period_start'         => $periodStart,
            'period_end'           => $periodEnd,
            'due_date'             => now()->addDays(14),
            'status'               => 'pending',
        ]);

        // Notify tenant that their invoice is ready
        try {
            Mail::to($tenant->billing_email ?: $tenant->email)
                ->send(new InvoiceGenerated($tenant, $invoice));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("InvoiceGenerated mail failed for tenant #{$tenant->id}: {$e->getMessage()}");
        }

        return $invoice;
    }

    /**
     * Mark an invoice as paid and extend subscription.
     */
    public function markAsPaid(Invoice $invoice, User $confirmedBy, $method = 'ecocash', $reference = null)
    {
        return DB::transaction(function () use ($invoice, $confirmedBy, $method, $reference) {
            $invoice->update([
                'status' => 'paid',
                'paid_at' => now(),
                'payment_method' => $method,
                'payment_reference' => $reference,
                'confirmed_by' => $confirmedBy->id,
            ]);

            $tenant = $invoice->tenant;
            
            // Set new expiration date
            // If already active, add 30 days to existing expiry. Otherwise, add to today.
            $baseDate = ($tenant->subscription_expires_at && $tenant->subscription_expires_at->isFuture()) 
                ? $tenant->subscription_expires_at 
                : now();

            $tenant->update([
                'status' => 'active',
                'plan' => $invoice->subscriptionPlan->slug ?? $tenant->plan,
                'subscription_starts_at' => now(),
                'subscription_expires_at' => $baseDate->addDays(30),
            ]);

            return $invoice;
        });
    }
}
