<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    public function index()
    {
        $tenant = app('current_tenant');
        $invoices = Invoice::where('tenant_id', $tenant->id)->latest()->get();
        
        return view('billing.index', compact('tenant', 'invoices'));
    }

    public function show(Invoice $invoice)
    {
        // Trait Global Scope handles the tenant_id check automatically
        return view('billing.invoice', compact('invoice'));
    }

    public function uploadProof(Request $request, Invoice $invoice)
    {
        $request->validate([
            'payment_method' => 'required|in:ecocash,zipit,bank_transfer',
            'payment_reference' => 'required|string',
            'payment_proof' => 'required|image|max:2048',
        ]);

        $path = $request->file('payment_proof')->store('payment_proofs', 'public');

        $invoice->update([
            'payment_method' => $request->payment_method,
            'payment_reference' => $request->payment_reference,
            'payment_proof_path' => $path,
            'notes' => 'Proof of payment uploaded by tenant.',
        ]);

        return back()->with('success', 'Proof of payment uploaded. Our team will verify and activate your account shortly.');
    }
}
