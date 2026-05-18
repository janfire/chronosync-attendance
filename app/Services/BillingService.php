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
     */
    public function generateMonthlyInvoice(Tenant $tenant)
    {
        // Don't generate if there's already a pending invoice for the same period
        $periodStart = now()->startOfMonth();
        $periodEnd = now()->endOfMonth();

        $existing = Invoice::where('tenant_id', $tenant->id)
            ->where('period_start', $periodStart->toDateString())
            ->first();

        if ($existing) return $existing;

        $plan = SubscriptionPlan::where('slug', $tenant->plan)->first();
        if (!$plan) return null;

        $invoice = Invoice::create([
            'tenant_id' => $tenant->id,
            'subscription_plan_id' => $plan->id,
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'amount_usd' => $plan->price_usd,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'due_date' => now()->addDays(14),
            'status' => 'pending',
        ]);

        // Send Email (optional: depends on if we have mail configured)
        // Mail::to($tenant->email)->send(new InvoiceGenerated($invoice));

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
                'subscription_starts_at' => now(),
                'subscription_expires_at' => $baseDate->addDays(30),
            ]);

            return $invoice;
        });
    }
}
