<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    body { font-family: Arial, Helvetica, sans-serif; color: #111827; }
    .card { background: #ffffff; padding: 20px; border-radius: 8px; border: 1px solid #e5e7eb; }
    .muted { color: #6b7280; font-size: 14px; }
    .btn { display:inline-block; padding:10px 14px; background:#10b981; color:#fff; border-radius:6px; text-decoration:none; }
  </style>
</head>
<body>
  <div class="card">
    <h2>New Attendance Request</h2>
    <p class="muted">A staff member has submitted an attendance exception that requires review.</p>

    <p><strong>Employee:</strong> {{ $exception->user->name ?? 'Unknown' }}<br>
    <strong>Type:</strong> {{ ucfirst(str_replace('_',' ', $exception->type)) }}<br>
    <strong>Submitted:</strong> {{ $exception->requested_at?->toDayDateTimeString() ?? $exception->created_at->toDayDateTimeString() }}</p>

    @if($exception->note)
      <p><strong>Note:</strong><br>{{ $exception->note }}</p>
    @endif

    <p>You can review and action the request in the admin portal:</p>
    <p><a class="btn" href="{{ url('/admin/exceptions') }}">Review Requests</a></p>

    <p class="muted">This email was sent automatically.</p>
  </div>
</body>
</html>
