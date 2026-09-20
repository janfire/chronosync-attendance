<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>You've been invited to ChronoSync</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f3f4f6;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
            color: #374151;
        }
        .wrapper {
            width: 100%;
            background-color: #f3f4f6;
            padding: 40px 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .header {
            background-color: #059669;
            padding: 40px 30px;
            text-align: center;
        }
        .header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 28px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        .content {
            padding: 40px 30px;
        }
        .greeting {
            font-size: 20px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 20px;
        }
        .message {
            font-size: 16px;
            line-height: 1.6;
            color: #4b5563;
            margin-bottom: 30px;
        }
        .role-badge {
            display: inline-block;
            background-color: #ecfdf5;
            color: #059669;
            padding: 6px 12px;
            border-radius: 9999px;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 20px;
        }
        .button-container {
            text-align: center;
            margin: 40px 0;
        }
        .button {
            display: inline-block;
            background-color: #059669;
            color: #ffffff !important;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            box-shadow: 0 4px 6px -1px rgba(5, 150, 105, 0.2);
        }
        .footer {
            background-color: #f9fafb;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #f3f4f6;
        }
        .footer p {
            margin: 0;
            font-size: 14px;
            color: #6b7280;
            line-height: 1.5;
        }
        .footer a {
            color: #059669;
            text-decoration: none;
        }
        .security-notice {
            font-size: 12px;
            color: #9ca3af;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <h1>ChronoSync Attendance</h1>
            </div>
            
            <div class="content">
                <div class="greeting">Hi {{ explode(' ', $user->name)[0] }},</div>
                
                <div class="message">
                    You have been invited to join the administrative team for <strong>ChronoSync</strong>. You have been assigned the following access level:
                </div>

                <div style="text-align: center;">
                    <div class="role-badge">
                        {{ $user->getRoleLabel() }}
                    </div>
                </div>

                <div class="message">
                    To accept this invitation and access your dashboard, please click the button below to securely set up your password.
                </div>
                
                <div class="button-container">
                    <a href="{{ $resetUrl }}" class="button">Set Up Password</a>
                </div>

                <div class="message" style="font-size: 14px; margin-bottom: 0;">
                    If you're having trouble clicking the button, copy and paste the URL below into your web browser:<br>
                    <a href="{{ $resetUrl }}" style="color: #059669; word-break: break-all;">{{ $resetUrl }}</a>
                </div>
            </div>
            
            <div class="footer">
                <p>This invitation link will expire in 48 hours for security reasons.</p>
                <p>If you did not expect this invitation, you can safely ignore this email.</p>
                <div class="security-notice">
                    &copy; {{ date('Y') }} ChronoSync Platform. All rights reserved.
                </div>
            </div>
        </div>
    </div>
</body>
</html>
