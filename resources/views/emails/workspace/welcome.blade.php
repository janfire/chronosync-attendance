<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to ChronoSync</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
            background-color: #f9fafb;
            margin: 0;
            padding: 0;
        }
        .wrapper {
            width: 100%;
            background-color: #f9fafb;
            padding: 40px 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-top: 6px solid #10b981; /* Emerald Green */
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            overflow: hidden;
        }
        .header {
            text-align: center;
            padding: 30px 20px 20px 20px;
            border-bottom: 1px solid #f3f4f6;
        }
        .logo-placeholder {
            display: inline-block;
            background-color: #10b981;
            color: #ffffff;
            width: 48px;
            height: 48px;
            line-height: 48px;
            border-radius: 12px;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 22px;
            color: #111827;
            font-weight: 700;
        }
        .content {
            padding: 30px 40px;
        }
        .greeting {
            font-size: 16px;
            margin-bottom: 20px;
            color: #1f2937;
        }
        .welcome-text {
            font-size: 15px;
            color: #4b5563;
            margin-bottom: 30px;
        }
        .access-box {
            background-color: #f0fdf4; /* Very light emerald green */
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .access-box h3 {
            margin-top: 0;
            margin-bottom: 15px;
            color: #14532d;
            font-size: 15px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .access-row {
            margin-bottom: 10px;
            font-size: 14px;
        }
        .access-row:last-child {
            margin-bottom: 0;
        }
        .access-label {
            font-weight: 600;
            color: #166534;
            display: inline-block;
            width: 120px;
        }
        .access-value {
            color: #1f2937;
            word-break: break-all;
        }
        .access-value a {
            color: #10b981;
            text-decoration: none;
            font-weight: 600;
        }
        .access-value a:hover {
            text-decoration: underline;
        }
        .cta-container {
            text-align: center;
            margin: 30px 0;
        }
        .btn-primary {
            display: inline-block;
            background-color: #10b981;
            color: #ffffff !important;
            padding: 12px 30px;
            border-radius: 9999px;
            font-weight: 600;
            font-size: 15px;
            text-decoration: none;
            box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.4);
            transition: background-color 0.2s ease;
        }
        .btn-primary:hover {
            background-color: #059669;
        }
        .guide-section {
            border-top: 1px solid #f3f4f6;
            padding-top: 30px;
            margin-top: 30px;
        }
        .guide-section h2 {
            font-size: 16px;
            color: #111827;
            margin-top: 0;
            margin-bottom: 20px;
            font-weight: 600;
        }
        .step {
            margin-bottom: 20px;
            position: relative;
            padding-left: 36px;
        }
        .step:last-child {
            margin-bottom: 0;
        }
        .step-number {
            position: absolute;
            left: 0;
            top: 2px;
            background-color: #e6f4ea;
            color: #10b981;
            width: 24px;
            height: 24px;
            line-height: 24px;
            border-radius: 50%;
            text-align: center;
            font-size: 12px;
            font-weight: bold;
        }
        .step-title {
            font-weight: 600;
            color: #1f2937;
            font-size: 14px;
            margin-bottom: 4px;
        }
        .step-desc {
            color: #6b7280;
            font-size: 13px;
            margin: 0;
        }
        .footer {
            text-align: center;
            padding: 20px;
            font-size: 12px;
            color: #9ca3af;
            border-top: 1px solid #f3f4f6;
            background-color: #fafafa;
        }
        .footer a {
            color: #9ca3af;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <div class="logo-placeholder">⏰</div>
                <h1>Welcome to ChronoSync</h1>
            </div>
            
            <div class="content">
                <div class="greeting">Hello {{ $adminName }},</div>
                <div class="welcome-text">
                    Congratulations! Your ChronoSync Attendance workspace has been successfully provisioned. We are excited to help you streamline employee attendance management, tracking, and reports.
                </div>
                
                <div class="access-box">
                    <h3>Access Credentials & Details</h3>
                    <div class="access-row">
                        <span class="access-label">Company Name:</span>
                        <span class="access-value">{{ $companyName }}</span>
                    </div>
                    <div class="access-row">
                        <span class="access-label">Workspace URL:</span>
                        <span class="access-value">
                            <a href="{{ $workspaceUrl }}" target="_blank">{{ $workspaceUrl }}</a>
                        </span>
                    </div>
                    <div class="access-row">
                        <span class="access-label">Admin Email:</span>
                        <span class="access-value">{{ $adminEmail }}</span>
                    </div>
                    <div class="access-row">
                        <span class="access-label">Password:</span>
                        <span class="access-value" style="color: #6b7280; font-style: italic;">Use the password you entered during registration.</span>
                    </div>
                </div>
                
                <div class="cta-container">
                    <a href="{{ $workspaceUrl }}" class="btn-primary" target="_blank">Access Your Dashboard</a>
                </div>
                
                <div class="guide-section">
                    <h2>3 Quick Steps to Get Started</h2>
                    
                    <div class="step">
                        <div class="step-number">1</div>
                        <div class="step-title">Configure Attendance Rules</div>
                        <p class="step-desc">Go to the Dashboard settings to adjust default work hours, late tolerance rules, and overtime policies to match your organization.</p>
                    </div>
                    
                    <div class="step">
                        <div class="step-number">2</div>
                        <div class="step-title">Add Staff Members</div>
                        <p class="step-desc">Register your employees under "Staff Management". You can generate high-quality biometric keys and manage shift groups from there.</p>
                    </div>
                    
                    <div class="step">
                        <div class="step-number">3</div>
                        <div class="step-title">Access the User Guide</div>
                        <p class="step-desc">We have attached a fully detailed interactive User Guide inside your sidebar. You can also view it online anytime to learn how to navigate filters, run automated reports, and manage finances.</p>
                    </div>
                </div>
            </div>
            
            <div class="footer">
                <p>This is an automated notification from ChronoSync Attendance Automations.</p>
                <p>Need support? Contact us at <a href="mailto:support@chronosync.com">support@chronosync.com</a></p>
                <p>&copy; {{ date('Y') }} ChronoSync. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
