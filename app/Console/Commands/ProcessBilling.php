<?php

namespace App\Console\Commands;

use App\Mail\SubscriptionEndingMail;
use App\Mail\TrialEndingMail;
use App\Models\Invoice;
use App\Models\Tenant;
use App\Services\BillingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ProcessBilling extends Command
{
    protected $signature = 'billing:process {--dry-run : Log actions without persisting any changes}';

    protected $description = 'Daily billing lifecycle: send expiry warnings, generate invoices, suspend overdue tenants, mark invoices overdue.';

    public function __construct(protected BillingService $billingService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('DRY RUN — no changes will be persisted.');
        }

        $this->line('');
        $this->info('=== ChronoSync Billing Processor ===');
        $this->line('');

        $this->sendTrialEndingWarnings($dryRun);
        $this->generateInvoicesForExpiredTrials($dryRun);
        $this->sendSubscriptionEndingWarnings($dryRun);
        $this->suspendTenantsAfterGracePeriod($dryRun);
        $this->markOverdueInvoices($dryRun);

        $this->line('');
        $this->info('✅ Billing processing complete.');

        return self::SUCCESS;
    }

    // ────────────────────────────────────────────
    // STEP 1 — Warn trials ending in exactly 3 days
    // ────────────────────────────────────────────
    private function sendTrialEndingWarnings(bool $dryRun): void
    {
        $this->comment('[1/5] Checking for trials ending in 3 days...');

        $tenants = Tenant::query()
            ->where('status', 'trial')
            ->whereBetween('trial_ends_at', [now()->addDays(3)->startOfDay(), now()->addDays(3)->endOfDay()])
            ->whereNull('trial_reminder_sent_at')
            ->get();

        if ($tenants->isEmpty()) {
            $this->line('    → None found.');
            return;
        }

        foreach ($tenants as $tenant) {
            $this->line("    → Sending trial-ending warning to: {$tenant->company_name} ({$tenant->email})");

            if (!$dryRun) {
                try {
                    Mail::to($tenant->billing_email ?: $tenant->email)
                        ->send(new TrialEndingMail($tenant));

                    $tenant->update(['trial_reminder_sent_at' => now()]);

                    Log::info("billing:process — trial warning sent to tenant #{$tenant->id} ({$tenant->company_name})");
                } catch (\Exception $e) {
                    $this->error("    ✗ Mail failed for {$tenant->company_name}: {$e->getMessage()}");
                    Log::error("billing:process — trial warning mail failed tenant #{$tenant->id}: {$e->getMessage()}");
                }
            }
        }

        $this->info("    ✓ Processed {$tenants->count()} trial warning(s).");
    }

    // ────────────────────────────────────────────
    // STEP 2 — Generate invoices for expired trials (no pending invoice yet)
    // ────────────────────────────────────────────
    private function generateInvoicesForExpiredTrials(bool $dryRun): void
    {
        $this->comment('[2/5] Generating invoices for expired trials...');

        $tenants = Tenant::query()
            ->where('status', 'trial')
            ->where('trial_ends_at', '<', now())
            ->get();

        if ($tenants->isEmpty()) {
            $this->line('    → None found.');
            return;
        }

        $count = 0;
        foreach ($tenants as $tenant) {
            // Check if they already have a non-cancelled invoice this period
            $existingInvoice = Invoice::withoutTenantScope()
                ->where('tenant_id', $tenant->id)
                ->where('period_start', now()->startOfMonth()->toDateString())
                ->whereNotIn('status', ['cancelled'])
                ->first();

            if ($existingInvoice) {
                $this->line("    → {$tenant->company_name}: invoice {$existingInvoice->invoice_number} already exists, skipping.");
                continue;
            }

            $this->line("    → Generating invoice for: {$tenant->company_name}");

            if (!$dryRun) {
                $invoice = $this->billingService->generateMonthlyInvoice($tenant);
                if ($invoice) {
                    $count++;
                    Log::info("billing:process — invoice {$invoice->invoice_number} generated for expired trial tenant #{$tenant->id}");
                }
            } else {
                $count++;
            }
        }

        $this->info("    ✓ Generated {$count} invoice(s) for expired trials.");
    }

    // ────────────────────────────────────────────
    // STEP 3 — Warn active subscriptions ending in 3 days + generate renewal invoice
    // ────────────────────────────────────────────
    private function sendSubscriptionEndingWarnings(bool $dryRun): void
    {
        $this->comment('[3/5] Checking for active subscriptions ending in 3 days...');

        $tenants = Tenant::query()
            ->where('status', 'active')
            ->whereBetween('subscription_expires_at', [now()->addDays(3)->startOfDay(), now()->addDays(3)->endOfDay()])
            ->whereNull('subscription_reminder_sent_at')
            ->get();

        if ($tenants->isEmpty()) {
            $this->line('    → None found.');
            return;
        }

        foreach ($tenants as $tenant) {
            $this->line("    → Sending renewal reminder to: {$tenant->company_name} ({$tenant->email})");

            if (!$dryRun) {
                try {
                    // Generate the next month's invoice so the client can pay proactively
                    $invoice = $this->billingService->generateMonthlyInvoice($tenant);

                    if ($invoice) {
                        Mail::to($tenant->billing_email ?: $tenant->email)
                            ->send(new SubscriptionEndingMail($tenant, $invoice));

                        $tenant->update(['subscription_reminder_sent_at' => now()]);

                        Log::info("billing:process — subscription warning sent to tenant #{$tenant->id} ({$tenant->company_name}), invoice {$invoice->invoice_number}");
                    }
                } catch (\Exception $e) {
                    $this->error("    ✗ Failed for {$tenant->company_name}: {$e->getMessage()}");
                    Log::error("billing:process — subscription warning failed tenant #{$tenant->id}: {$e->getMessage()}");
                }
            }
        }

        $this->info("    ✓ Processed {$tenants->count()} subscription warning(s).");
    }

    // ────────────────────────────────────────────
    // STEP 4 — Suspend tenants whose grace period has ended (expired > 7 days ago)
    // ────────────────────────────────────────────
    private function suspendTenantsAfterGracePeriod(bool $dryRun): void
    {
        $this->comment('[4/5] Suspending tenants past grace period...');

        // Active tenants whose subscription expired more than 7 days ago
        $tenants = Tenant::query()
            ->where('status', 'active')
            ->where('subscription_expires_at', '<', now()->subDays(7))
            ->get();

        // Also suspend tenants still on 'trial' status whose trial ended more than 7 days ago
        // (they get a grace window matching active subscriptions)
        $expiredTrials = Tenant::query()
            ->where('status', 'trial')
            ->where('trial_ends_at', '<', now()->subDays(7))
            ->get();

        $all = $tenants->merge($expiredTrials);

        if ($all->isEmpty()) {
            $this->line('    → None found.');
            return;
        }

        foreach ($all as $tenant) {
            $this->line("    → Suspending: {$tenant->company_name} (was: {$tenant->status})");

            if (!$dryRun) {
                $tenant->update(['status' => 'suspended']);
                Log::warning("billing:process — tenant #{$tenant->id} ({$tenant->company_name}) suspended after grace period.");
            }
        }

        $this->info("    ✓ Suspended {$all->count()} tenant(s).");
    }

    // ────────────────────────────────────────────
    // STEP 5 — Mark overdue invoices
    // ────────────────────────────────────────────
    private function markOverdueInvoices(bool $dryRun): void
    {
        $this->comment('[5/5] Marking overdue invoices...');

        $overdueInvoices = Invoice::withoutTenantScope()
            ->where('status', 'pending')
            ->where('due_date', '<', now()->toDateString())
            ->get();

        if ($overdueInvoices->isEmpty()) {
            $this->line('    → None found.');
            return;
        }

        foreach ($overdueInvoices as $invoice) {
            $this->line("    → Marking overdue: {$invoice->invoice_number} (tenant #{$invoice->tenant_id}, due {$invoice->due_date->format('M j')})");

            if (!$dryRun) {
                $invoice->update(['status' => 'overdue']);
                Log::info("billing:process — invoice {$invoice->invoice_number} marked overdue.");
            }
        }

        $this->info("    ✓ Marked {$overdueInvoices->count()} invoice(s) as overdue.");
    }
}
