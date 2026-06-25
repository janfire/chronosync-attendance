<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    body { font-family: Arial, Helvetica, sans-serif; color: #111827; background: #f9fafb; margin: 0; padding: 24px; }
    .wrapper { max-width: 560px; margin: 0 auto; }
    .card { background: #ffffff; padding: 32px; border-radius: 12px; border: 1px solid #e5e7eb; }
    .badge { display: inline-block; background: #fef3c7; color: #92400e; font-size: 12px; font-weight: bold; padding: 4px 10px; border-radius: 99px; margin-bottom: 16px; text-transform: uppercase; letter-spacing: 0.05em; }
    h1 { font-size: 22px; margin: 0 0 8px; color: #111827; }
    p { font-size: 15px; color: #374151; line-height: 1.6; margin: 0 0 16px; }
    .highlight-box { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 16px; margin: 20px 0; }
    .highlight-box p { margin: 0; color: #166534; font-size: 14px; }
    .btn { display: inline-block; padding: 12px 24px; background: #065f46; color: #fff; border-radius: 8px; text-decoration: none; font-weight: bold; font-size: 15px; margin-top: 8px; }
    .muted { color: #6b7280; font-size: 13px; }
    .footer { margin-top: 24px; padding-top: 16px; border-top: 1px solid #f3f4f6; }
  </style>
</head>
<body>
  <div class="wrapper">
    <div class="card">
      <div class="badge">⚠️ Trial Ending Soon</div>
      <h1>Your free trial ends in 3 days</h1>
      <p>Hi {{ $tenant->billing_name ?: $tenant->company_name }},</p>
      <p>
        Your <strong>ChronoSync Attendance</strong> free trial for <strong>{{ $tenant->company_name }}</strong>
        will expire on <strong>{{ $tenant->trial_ends_at->format('l, F j, Y') }}</strong>.
      </p>

      <div class="highlight-box">
        <p>
          🕐 <strong>Trial ends:</strong> {{ $tenant->trial_ends_at->format('M j, Y') }}<br>
          📦 <strong>Your plan:</strong> {{ $tenant->getPlanLabel() }} — $35 / month<br>
          👥 <strong>Employees:</strong> Up to {{ $tenant->max_employees }}
        </p>
      </div>

      <p>
        To keep your team's attendance running without interruption, please log in to your billing
        portal and submit your first payment before your trial ends.
      </p>

      <a class="btn" href="{{ url('/billing') }}">Go to Billing Portal →</a>

      <div class="footer">
        <p class="muted">
          Payments accepted via <strong>EcoCash</strong> or <strong>ZIPIT</strong>.
          Once your proof of payment is verified by our team, your subscription will be activated within 2 hours.
        </p>
        <p class="muted">If you have any questions, contact us at <a href="mailto:support@attenda.co.zw">support@attenda.co.zw</a> or WhatsApp +263 771 234 567.</p>
      </div>
    </div>
  </div>
</body>
</html>
