<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #f3f4f6;
            color: #1f2937;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            overflow: hidden;
        }
        .header {
            background-color: #059669; /* Emerald 600 */
            padding: 30px;
            text-align: center;
        }
        .logo-container {
            display: inline-block;
            background-color: #10b981; /* Emerald 500 */
            width: 48px;
            height: 48px;
            border-radius: 12px;
            text-align: center;
            line-height: 48px;
            font-size: 24px;
            font-weight: bold;
            color: #ffffff;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            position: relative;
        }
        .logo-text {
            color: #ffffff;
            font-size: 20px;
            font-weight: bold;
            margin-top: 10px;
            letter-spacing: -0.5px;
        }
        .content {
            padding: 40px 30px;
            text-align: center;
        }
        .title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #111827;
        }
        .message {
            font-size: 16px;
            color: #4b5563;
            margin-bottom: 30px;
        }
        .otp-box {
            background-color: #f9fafb;
            border: 2px dashed #d1d5db;
            border-radius: 12px;
            padding: 20px;
            margin: 0 auto 30px auto;
            display: inline-block;
        }
        .otp-code {
            font-size: 36px;
            font-weight: 800;
            color: #059669;
            letter-spacing: 8px;
            margin: 0;
            font-family: monospace;
        }
        .warning {
            font-size: 13px;
            color: #6b7280;
            max-width: 400px;
            margin: 0 auto;
        }
        .footer {
            background-color: #f9fafb;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #9ca3af;
            border-top: 1px solid #f3f4f6;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo-container">
                <span style="opacity: 0.4; position: absolute; left: 8px;">C</span>
                <span style="position: relative; left: 4px;">S</span>
            </div>
            <div class="logo-text">ChronoSync</div>
        </div>
        
        <div class="content">
            <h1 class="title">Verify your email address</h1>
            <p class="message">
                Thanks for starting the registration process! Please use the following 6-digit code to verify your email address and continue with your biometric enrollment.
            </p>
            
            <div class="otp-box">
                <p class="otp-code">{{ $otp }}</p>
            </div>
            
            <p class="warning">
                This code will expire in 10 minutes. If you did not request this code, you can safely ignore this email.
            </p>
        </div>
        
        <div class="footer">
            &copy; {{ date('Y') }} ChronoSync Attendance System. All rights reserved.
        </div>
    </div>
</body>
</html>
