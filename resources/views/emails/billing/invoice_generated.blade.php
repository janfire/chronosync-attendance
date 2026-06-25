<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    body { font-family: Arial, Helvetica, sans-serif; color: #111827; background: #f9fafb; margin: 0; padding: 24px; }
    .wrapper { max-width: 560px; margin: 0 auto; }
    .card { background: #ffffff; padding: 32px; border-radius: 12px; border: 1px solid #e5e7eb; }
    .badge { display: inline-block; background: #d1fae5; color: #065f46; font-size: 12px; font-weight: bold; padding: 4px 10px; border-radius: 99px; margin-bottom: 16px; text-transform: uppercase; letter-spacing: 0.05em; }
    h1 { font-size: 22px; margin: 0 0 8px; color: #111827; }
    p { font-size: 15px; color: #374151; line-height: 1.6; margin: 0 0 16px; }
    .invoice-box { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; margin: 20px 0; }
    .invoice-row { display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid #f3f4f6; font-size: 14px; }
    .invoice-row:last-child { border-bottom: none; font-weight: bold; font-size: 16px; color: #065f46; }
    .btn { display: inline-block; padding: 12px 24px; background: #065f46; color: #fff; border-radius: 8px; text-decoration: none; font-weight: bold; font-size: 15px; margin-top: 8px; }
    .steps { padding-left: 0; list-style: none; margin: 16px 0; }
    .steps li { padding: 8px 0 8px 28px; position: relative; font-size: 14px; color: #374151; }
    .steps li::before { content: attr(data-step); position: absolute; left: 0; top: 8px; background: #d1fae5; color: #065f46; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 11px; }
    .muted { color: #6b7280; font-size: 13px; }
    .footer { margin-top: 24px; padding-top: 16px; border-top: 1px solid #f3f4f6; }
  </style>
</head>
<body>
  <div class="wrapper">
    <div class="card">
      <div class="badge">📄 Invoice Ready</div>
      <h1>Your invoice is ready for payment</h1>
      <p>Hi {{ $tenant->billing_name ?: $tenant->company_name }},</p>
      <p>
        A new invoice has been generated for your <strong>ChronoSync Attendance</strong> subscription.
        Please review the details below and submit your payment to continue using the system.
      </p>

      <div class="invoice-box">
        <div class="invoice-row"><span>Invoice #</span><span>{{ $invoice->invoice_number }}</span></div>
        <div class="invoice-row"><span>Plan</span><span>{{ $tenant->getPlanLabel() }}</span></div>
        <div class="invoice-row"><span>Billing Period</span><span>{{ $invoice->period_start->format('M j') }} – {{ $invoice->period_end->format('M j, Y') }}</span></div>
        <div class="invoice-row"><span>Due Date</span><span>{{ $invoice->due_date->format('M j, Y') }}</span></div>
        <div class="invoice-row"><span>Total Due</span><span>${{ number_format($invoice->amount_usd, 2) }} USD</span></div>
      </div>

      <p><strong>How to pay:</strong></p>
      <ul class="steps">
        <li data-step="1">Log in to your billing portal and open this invoice.</li>
        <li data-step="2">Send payment via <strong>EcoCash</strong> or <strong>ZIPIT</strong> using the details on the invoice.</li>
        <li data-step="3">Upload your transaction reference / screenshot on the invoice page.</li>
        <li data-step="4">Our finance team will verify and activate your account within 2 hours.</li>
      </ul>

      <a class="btn" href="{{ url('/billing/invoice/' . $invoice->id) }}">View & Pay Invoice →</a>

      <div class="footer">
        <p class="muted">If you have any questions, contact us at <a href="mailto:support@attenda.co.zw">support@attenda.co.zw</a> or WhatsApp +263 771 234 567.</p>
      </div>
    </div>
  </div>
</body>
</html>
