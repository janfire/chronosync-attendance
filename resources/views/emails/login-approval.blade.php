<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Approve Your Login</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f9fafb; color: #374151; line-height: 1.6; margin: 0; padding: 0; }
        .wrapper { width: 100%; table-layout: fixed; background-color: #f9fafb; padding: 40px 0; }
        .main { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); }
        .header { background-color: #10b981; padding: 30px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 24px; font-weight: 700; letter-spacing: 0.5px; }
        .content { padding: 40px 30px; }
        .content h2 { color: #111827; font-size: 20px; margin-top: 0; }
        .content p { margin: 0 0 20px; font-size: 16px; color: #4b5563; }
        .details-box { background-color: #f3f4f6; border-radius: 6px; padding: 20px; margin-bottom: 30px; }
        .details-box p { margin: 0 0 10px; font-size: 14px; }
        .details-box p:last-child { margin-bottom: 0; }
        .details-box strong { color: #111827; }
        .btn-container { text-align: center; margin: 40px 0; }
        .btn { display: inline-block; background-color: #10b981; color: #ffffff; text-decoration: none; padding: 14px 28px; font-size: 16px; font-weight: 600; border-radius: 6px; }
        .btn:hover { background-color: #059669; }
        .footer { padding: 30px; text-align: center; font-size: 14px; color: #6b7280; border-top: 1px solid #e5e7eb; }
        .warning { font-size: 13px; color: #9ca3af; margin-top: 20px; }
    </style>
</head>
<body>
    <table class="wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td align="center">
                <table class="main" width="100%" cellpadding="0" cellspacing="0" role="presentation">
                    <tr>
                        <td class="header">
                            <h1>ChronoSync Attendance</h1>
                        </td>
                    </tr>
                    <tr>
                        <td class="content">
                            <h2>Login Approval Required</h2>
                            <p>We detected a new login attempt to your account. To complete the login process, please approve this request.</p>
                            
                            <div class="details-box">
                                <p><strong>Time:</strong> {{ now()->format('F j, Y, g:i A') }}</p>
                                <p><strong>Device:</strong> {{ $deviceInfo }}</p>
                                <p><strong>IP Address:</strong> {{ $ipAddress }}</p>
                            </div>

                            <div class="btn-container">
                                <a href="{{ route('login.approve', ['token' => $token]) }}" class="btn">Approve Login</a>
                            </div>

                            <p class="warning">If you did not attempt to log in, you can safely ignore this email. Your account remains secure as long as you do not click the button above.</p>
                        </td>
                    </tr>
                    <tr>
                        <td class="footer">
                            &copy; {{ date('Y') }} ChronoSync Attendance System. All rights reserved.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
