<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\BillingService;
use Illuminate\Http\Request;

class FinanceController extends Controller
{
    protected $billingService;

    public function __construct(BillingService $billingService)
    {
        $this->billingService = $billingService;
    }

    public function pendingInvoices()
    {
        // Use withoutTenantScope because super-admin needs to see all tenants
        $invoices = Invoice::withoutTenantScope()
            ->where('status', 'pending')
            ->whereNotNull('payment_reference')
            ->with('tenant')
            ->latest()
            ->get();

        return view('admin.finance.index', compact('invoices'));
    }

    public function confirmPayment(Request $request, Invoice $invoice)
    {
        // Use withoutTenantScope to find the invoice
        $invoice = Invoice::withoutTenantScope()->findOrFail($invoice->id);
        
        $this->billingService->markAsPaid(
            $invoice, 
            auth()->user(), 
            $invoice->payment_method ?: 'ecocash', 
            $invoice->payment_reference
        );

        return back()->with('success', "Payment for Invoice {$invoice->invoice_number} confirmed. Tenant '{$invoice->tenant->company_name}' is now active.");
    }
}
