<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    body {font-family: Arial, Helvetica, sans-serif; background: #f9fafb; margin:0; padding:24px; color:#111827;}
    .wrapper {max-width:600px; margin:0 auto;}
    .card {background:#ffffff; border:1px solid #e5e7eb; border-radius:12px; padding:32px;}
    .badge {display:inline-block;background:#fef3c7;color:#92400e;font-size:12px;font-weight:bold;padding:4px 10px;border-radius:99px;margin-bottom:16px;text-transform:uppercase;letter-spacing:0.05em;}
    h1 {font-size:22px;margin:0 0 8px;color:#111827;}
    p {font-size:15px;color:#374151;line-height:1.6;margin:0 0 16px;}
    .btn {display:inline-block;padding:12px 24px;background:#065f46;color:#fff;border-radius:8px;text-decoration:none;font-weight:bold;font-size:15px;margin-top:8px;}
    .footer {margin-top:24px;padding-top:16px;border-top:1px solid #f3f4f6;}
  </style>
</head>
<body>
  <div class="wrapper">
    <div class="card">
      <div class="badge">🧾 Payment Proof Received</div>
      <h1>Tenant {{ $tenant->company_name }} uploaded a payment proof</h1>
      <p>Invoice #: <strong>{{ $invoice->invoice_number }}</strong></p>
      <p>Amount: <strong>${{ number_format($invoice->amount_usd, 2) }} USD</strong></p>
      <p>Payment Method: <strong>{{ ucfirst($invoice->payment_method) }}</strong></p>
      <p>Reference: <strong>{{ $invoice->payment_reference }}</strong></p>
      <p>You can review the uploaded proof and verify the payment in the admin Finance panel.</p>
      <a class="btn" href="{{ url('/superadmin/finance/pending') }}">View Pending Payments →</a>
      <div class="footer">
        <p class="muted">This is an automated notification. If you have any questions, contact support at <a href="mailto:support@attenda.co.zw">support@attenda.co.zw</a>.</p>
      </div>
    </div>
  </div>
</body>
</html>
