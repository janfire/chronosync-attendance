<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5, user-scalable=yes">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Clock In/Out - ChronoSync Attendance System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Add SweetAlert2 CSS and JS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        window.routes = {
            loginOptions: '{{ route("biometric.webauthn.login.options") }}',
            verifyLogin: '{{ route("biometric.webauthn.login.verify") }}'
        };
    </script>
    <!-- Load attendance script via Vite (module build) -->
    @vite(['resources/js/attendance-clock.js'])
    <style>
        :root {
            --navy: #0f2a1d; /* TaxEase Dark Green */
            --navy-mid: #16402b; 
            --navy-light: #25704b; 
            --blue: #6abf94; /* TaxEase Accent Green */
            --blue-bright: #42a975;
            --cyan: #2e8b5d; 
            --purple: #1d563a;
            --purple-light: #9dd8ba;
            --gold: #f59e0b;
            --white: #ffffff;
            --off-white: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-300: #cbd5e1;
            --gray-500: #64748b;
            --gray-700: #334155;
            --success: #10b981;
            --error: #ef4444;
            --font-main: 'Sora', sans-serif;
            --font-mono: 'JetBrains Mono', monospace;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: var(--font-main);
            min-height: 100vh;
            background-color: #f3f4f6; /* bg-gray-100 */
            background-image: url('{{ asset('images/background-pattern.png') }}');
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            overflow-x: hidden;
            position: relative;
        }

        /* Background mesh removed to match register page background */

        /* Animated grid removed to match register page background */
        
        /* Pattern from Register page */
        .bg-pattern {
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            position: absolute;
            inset: 0;
            opacity: 0.1;
            z-index: 0;
        }

        .wrapper {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 1080px;
            min-height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Main Card */
        .card {
            display: grid;
            grid-template-columns: 1fr;
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 1rem; /* matches rounded-2xl */
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); /* matches shadow-2xl */
            backdrop-filter: blur(10px);
            min-height: 0;
        }

        @media (min-width: 1024px) {
            .card {
                grid-template-columns: 420px 1fr;
                min-height: 640px;
                max-height: 90vh;
            }
        }

        /* ─── LEFT PANEL ─── */
        .panel-left {
            background: var(--navy);
            border-right: 1px solid rgba(255,255,255,0.06);
            padding: clamp(1.5rem, 4vw, 2.5rem);
            display: flex;
            flex-direction: column;
            gap: 2rem;
            position: relative;
            overflow: hidden;
        }

        /* Decorative elements removed to match register.blade.php solid blue background */

        .brand {
            display: flex;
            align-items: center;
            gap: 0.875rem;
            position: relative;
            z-index: 1;
        }

        .brand-logo {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            border: 1.5px solid rgba(255,255,255,0.15);
            object-fit: cover;
            flex-shrink: 0;
            background: var(--navy-light);
        }

        .brand-text h1 {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--white);
            letter-spacing: -0.02em;
            line-height: 1.2;
        }

        .brand-text p {
            font-size: 0.625rem;
            font-weight: 500;
            color: rgba(148, 183, 255, 0.6);
            letter-spacing: 0.12em;
            text-transform: uppercase;
            margin-top: 2px;
        }

        /* Live clock */
        .live-time {
            position: relative;
            z-index: 1;
            padding: 1.25rem 1.5rem;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.07);
            border-radius: 16px;
        }

        .live-time .time-digits {
            font-family: var(--font-mono);
            font-size: clamp(2.5rem, 6vw, 3.5rem);
            font-weight: 500;
            color: var(--white);
            letter-spacing: -0.02em;
            line-height: 1;
        }

        .live-time .time-date {
            font-size: 0.8rem;
            color: rgba(148, 183, 255, 0.6);
            margin-top: 0.375rem;
            font-weight: 400;
        }

        .time-dot {
            display: inline-block;
            animation: blink 1s step-end infinite;
        }
        @keyframes blink { 50% { opacity: 0; } }

        /* Info cards */
        .info-cards {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            position: relative;
            z-index: 1;
            flex: 1;
        }

        .info-card {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            padding: 1rem 1.25rem;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 14px;
            transition: border-color 0.2s, background 0.2s;
        }

        .info-card:hover {
            background: rgba(255,255,255,0.07);
            border-color: rgba(255,255,255,0.12);
        }

        .info-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.125rem;
            flex-shrink: 0;
        }

        .info-icon.blue { background: rgba(37, 99, 235, 0.2); color: #60a5fa; }
        .info-icon.purple { background: rgba(124, 58, 237, 0.2); color: var(--purple-light); }

        .info-text h3 {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--white);
            margin-bottom: 2px;
        }

        .info-text p {
            font-size: 0.75rem;
            color: rgba(148, 183, 255, 0.55);
            line-height: 1.4;
        }

        .panel-footer {
            font-size: 0.7rem;
            color: rgba(100, 130, 180, 0.5);
            position: relative;
            z-index: 1;
            font-family: var(--font-mono);
        }

        /* ─── RIGHT PANEL ─── */
        .panel-right {
            background: var(--white);
            padding: clamp(1.5rem, 4vw, 2.5rem);
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
        }

        /* ── Selection Screen ── */
        .selection-screen {
            position: relative;
            background: var(--white);
            z-index: 10;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: clamp(1.5rem, 4vw, 2.5rem);
            transition: opacity 0.3s ease, transform 0.3s ease;
            flex: 1;
        }

        .selection-screen.hidden {
            opacity: 0;
            pointer-events: none;
            transform: scale(0.97);
            position: absolute;
            visibility: hidden;
            display: none;
        }

        .selection-screen h2 {
            font-size: clamp(1.25rem, 3vw, 1.75rem);
            font-weight: 700;
            color: var(--navy);
            text-align: center;
            letter-spacing: -0.03em;
        }

        .selection-screen > p {
            font-size: 0.875rem;
            color: var(--gray-500);
            text-align: center;
            margin-top: 0.5rem;
            max-width: 340px;
        }

        .method-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            width: 100%;
            max-width: 420px;
            margin-top: 2rem;
        }

        @media (max-width: 380px) {
            .method-grid { grid-template-columns: 1fr; }
        }

        .method-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 1.5rem 1rem;
            background: var(--white);
            border: 1.5px solid var(--gray-100);
            border-radius: 18px;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.4,0,0.2,1);
            text-align: center;
            gap: 0.875rem;
            box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        }

        .method-btn:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 32px rgba(0,0,0,0.1);
        }

        .method-btn.face:hover {
            border-color: var(--blue);
            background: rgba(37, 99, 235, 0.03);
        }

        .method-btn.finger:hover {
            border-color: var(--purple);
            background: rgba(124, 58, 237, 0.03);
        }

        .method-icon {
            width: 64px;
            height: 64px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            transition: transform 0.25s;
        }

        .method-btn:hover .method-icon { transform: scale(1.1); }

        .method-icon.blue { background: rgba(37,99,235,0.08); color: var(--blue); }
        .method-icon.purple { background: rgba(124,58,237,0.08); color: var(--purple); }

        .method-btn h3 {
            font-size: 0.9375rem;
            font-weight: 600;
            color: var(--navy);
        }

        .method-btn span {
            font-size: 0.7rem;
            color: var(--gray-500);
            font-weight: 400;
        }

        .qr-link {
            margin-top: 1.5rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.8125rem;
            color: var(--gray-500);
            text-decoration: none;
            transition: color 0.2s;
            padding: 0.5rem 1rem;
            border-radius: 8px;
        }

        .qr-link:hover { color: var(--navy); background: var(--gray-100); }

        /* ── Active Screen (Camera / Fingerprint) ── */
        .active-header {
            display: none;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.25rem;
        }

        .active-header.visible { display: flex; }

        .back-btn {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            border: 1.5px solid var(--gray-100);
            background: transparent;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gray-500);
            transition: all 0.2s;
            flex-shrink: 0;
        }

        .back-btn:hover {
            background: var(--gray-100);
            color: var(--navy);
        }

        .active-header h2 {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--navy);
            letter-spacing: -0.02em;
        }

        /* ── Camera Container ── */
        #camera-container {
            display: none;
            flex-direction: column;
            align-items: center;
            flex: 1;
            width: 100%;
        }

        #camera-container.visible { display: flex; }

        .camera-wrap {
            position: relative;
            width: 100%;
            max-width: 440px;
            border-radius: 20px;
            overflow: hidden;
            background: var(--navy);
            aspect-ratio: 4/3;
            box-shadow: 0 20px 60px rgba(0,0,0,0.25), 0 0 0 1px rgba(0,0,0,0.08);
            margin: 0 auto;
        }

        #attendance-video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transform: scaleX(-1);
        }

        #attendance-canvas { display: none; }

        /* Scan overlay */
        .scan-overlay {
            position: absolute;
            inset: 0;
            pointer-events: none;
        }

        .corner {
            position: absolute;
            width: 28px;
            height: 28px;
        }
        .corner.tl { top: 14px; left: 14px; border-top: 3px solid var(--blue-bright); border-left: 3px solid var(--blue-bright); border-radius: 4px 0 0 0; }
        .corner.tr { top: 14px; right: 14px; border-top: 3px solid var(--blue-bright); border-right: 3px solid var(--blue-bright); border-radius: 0 4px 0 0; }
        .corner.bl { bottom: 14px; left: 14px; border-bottom: 3px solid var(--blue-bright); border-left: 3px solid var(--blue-bright); border-radius: 0 0 0 4px; }
        .corner.br { bottom: 14px; right: 14px; border-bottom: 3px solid var(--blue-bright); border-right: 3px solid var(--blue-bright); border-radius: 0 0 4px 0; }

        .scan-line {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--cyan), transparent);
            animation: scanMove 2.5s linear infinite;
        }

        @keyframes scanMove {
            0% { top: 0%; opacity: 0; }
            5% { opacity: 1; }
            95% { opacity: 1; }
            100% { top: 100%; opacity: 0; }
        }

        .face-guide {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .face-guide-oval {
            width: 44%;
            height: 60%;
            border: 1.5px dashed rgba(6, 182, 212, 0.35);
            border-radius: 50%;
        }

        .cam-status-badge {
            position: absolute;
            bottom: 14px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0,0,0,0.65);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 100px;
            padding: 0.4rem 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            white-space: nowrap;
        }

        .cam-status-badge .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--cyan);
            animation: pulse-dot 1.5s ease-in-out infinite;
        }

        @keyframes pulse-dot {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(0.8); }
        }

        .cam-status-badge span {
            font-size: 0.75rem;
            color: rgba(255,255,255,0.85);
            font-weight: 500;
            font-family: var(--font-mono);
        }

        .cam-hint {
            margin-top: 1rem;
            font-size: 0.8rem;
            color: var(--gray-500);
            display: flex;
            align-items: center;
            gap: 0.375rem;
        }

        .cam-hint i { color: var(--gold); }

        /* ── Fingerprint Container ── */
        #fingerprint-container {
            display: none;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            flex: 1;
            gap: 1.5rem;
        }

        #fingerprint-container.visible { display: flex; }

        .fp-ring-wrap {
            position: relative;
            width: 180px;
            height: 180px;
        }

        .fp-ring {
            position: absolute;
            inset: 0;
            border-radius: 50%;
            border: 2px solid transparent;
            border-top-color: var(--purple);
            animation: spin 1.8s linear infinite;
        }

        .fp-ring-2 {
            position: absolute;
            inset: 12px;
            border-radius: 50%;
            border: 1.5px solid transparent;
            border-top-color: rgba(124,58,237,0.4);
            animation: spin 3s linear infinite reverse;
        }

        @keyframes spin { to { transform: rotate(360deg); } }

        .fp-icon {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 5rem;
            color: rgba(124, 58, 237, 0.25);
            animation: breathe 3s ease-in-out infinite;
        }

        @keyframes breathe {
            0%, 100% { transform: scale(1); color: rgba(124, 58, 237, 0.25); }
            50% { transform: scale(1.05); color: rgba(124, 58, 237, 0.4); }
        }

        #fingerprint-container h3 {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--navy);
            letter-spacing: -0.02em;
        }

        #fingerprint-container p {
            font-size: 0.875rem;
            color: var(--gray-500);
        }

        /* Fingerprint indicator pill */
        #fingerprint-indicator {
            display: none;
            align-items: center;
            justify-content: space-between;
            padding: 0.875rem 1.25rem;
            background: var(--gray-100);
            border: 1.5px solid rgba(0,0,0,0.06);
            border-radius: 14px;
            margin-top: auto;
            transition: all 0.3s;
        }

        #fingerprint-indicator.visible { display: flex; }

        #fingerprint-indicator .status-left {
            display: flex;
            align-items: center;
            gap: 0.625rem;
        }

        #zk-status-dot {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            background: var(--gray-300);
            transition: background 0.3s;
        }

        #zk-status-text {
            font-size: 0.8125rem;
            color: var(--gray-700);
            font-weight: 500;
        }

        #zk-icon {
            font-size: 1.25rem;
            color: var(--gray-300);
            transition: color 0.3s;
        }

        /* ── Result Overlay ── */
        #result-overlay {
            position: absolute;
            inset: 0;
            background: rgba(255,255,255,0.97);
            backdrop-filter: blur(20px);
            z-index: 20;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s;
        }

        #result-overlay.visible {
            opacity: 1;
            pointer-events: all;
        }

        .result-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin-bottom: 1.25rem;
        }

        .result-icon.success { background: rgba(16,185,129,0.1); color: var(--success); }
        .result-icon.error { background: rgba(239,68,68,0.1); color: var(--error); }

        #result-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--navy);
            letter-spacing: -0.03em;
        }

        #result-message {
            font-size: 0.875rem;
            color: var(--gray-500);
            text-align: center;
            margin-top: 0.5rem;
            max-width: 280px;
            line-height: 1.5;
        }

        .result-progress {
            margin-top: 2rem;
            width: 120px;
            height: 3px;
            background: var(--gray-100);
            border-radius: 100px;
            overflow: hidden;
        }

        .result-progress-bar {
            height: 100%;
            background: var(--blue);
            border-radius: 100px;
            animation: progressShrink 3s linear forwards;
        }

        @keyframes progressShrink {
            from { width: 100%; }
            to { width: 0%; }
        }

        /* ── Footer ── */
        .panel-right-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1.5rem;
            padding-top: 1rem;
            border-top: 1px solid var(--gray-100);
        }

        .enrollment-link {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            font-size: 0.8rem;
            color: var(--blue);
            text-decoration: none;
            font-weight: 500;
            transition: gap 0.2s;
        }

        .enrollment-link:hover { gap: 0.6rem; }

        .system-badge {
            font-size: 0.7rem;
            font-family: var(--font-mono);
            color: var(--gray-300);
            background: var(--gray-100);
            border-radius: 6px;
            padding: 0.25rem 0.5rem;
        }

        /* ─── Mobile Adjustments ─── */
        @media (max-width: 1023px) {
            .card { border-radius: 20px; overflow-y: auto; max-height: none; }
            .panel-left { 
                padding: 1rem 1.25rem; 
                flex-direction: row; 
                align-items: center; 
                justify-content: space-between; 
                gap: 1rem;
            }
            .brand { gap: 0.5rem; }
            .brand-logo { width: 36px; height: 36px; border-radius: 10px; }
            .brand-text h1 { font-size: 0.9rem; }
            .brand-text p { display: none; }
            .live-time { padding: 0.5rem 1rem; border-radius: 12px; margin: 0; flex-shrink: 0; }
            .live-time .time-digits { font-size: 1.25rem; }
            .live-time .time-date { display: none; }
            .info-cards { display: none !important; }
            .panel-footer { display: none; }
        }

        @media (max-width: 640px) {
            .panel-left { padding: 0.75rem 1rem; }
            .panel-right { padding: 1.25rem; }
            .method-grid { gap: 0.75rem; max-width: 100%; }
            .method-btn { padding: 1.25rem 0.75rem; }
            .method-icon { width: 52px; height: 52px; font-size: 1.4rem; border-radius: 14px; }
            .camera-wrap { border-radius: 14px; }
            .fp-ring-wrap { width: 140px; height: 140px; }
            .fp-icon { font-size: 4rem; }
            .live-time .time-digits { font-size: 1rem; }
        }

        @media (max-width: 380px) {
            .brand-text h1 { display: none; }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">

            <!-- ═══ LEFT PANEL ═══ -->
            <div class="panel-left">
                <div class="bg-pattern"></div>
                <!-- Brand -->
                <div class="brand">
                    <div class="h-12 w-12 bg-emerald-500 rounded-xl flex items-center justify-center text-white shadow-lg shrink-0"><i class="fas fa-clock text-2xl"></i></div>
                    <div class="brand-text">
                        <h1>ChronoSync Attendance</h1>
                        <p>Unified Biometric System</p>
                    </div>
                </div>

                <!-- Live Clock -->
                <div class="live-time">
                    <div class="time-digits" id="live-clock">--<span class="time-dot">:</span>--<span class="time-dot">:</span>--</div>
                    <div class="time-date" id="live-date">{{ now()->format('l, d F Y') }}</div>
                </div>

                <!-- Info Cards -->
                <div class="info-cards">
                    <div class="info-card">
                        <div class="info-icon blue"><i class="fas fa-camera"></i></div>
                        <div class="info-text">
                            <h3>Facial Recognition</h3>
                            <p>Look directly at the camera. Verification happens automatically.</p>
                        </div>
                    </div>
                    <div class="info-card">
                        <div class="info-icon purple"><i class="fas fa-fingerprint"></i></div>
                        <div class="info-text">
                            <h3>Fingerprint Scan</h3>
                            <p>Place your finger on the scanner at any time.</p>
                        </div>
                    </div>
                </div>

                <div class="panel-footer">&copy; {{ date('Y') }} Zimbabwe Open University</div>
            </div>

            <!-- ═══ RIGHT PANEL ═══ -->
            <div class="panel-right">

                <!-- Selection Screen -->
                <div id="selection-screen" class="selection-screen">
                    <h2>Choose Clock-In Method</h2>
                    <p>Select your preferred method to verify your identity and record attendance.</p>

                    <div class="method-grid">
                        <button id="btn-select-face" class="method-btn face" aria-label="Use Facial Scan">
                            <div class="method-icon blue"><i class="fas fa-camera"></i></div>
                            <h3>Facial Scan</h3>
                            <span>Automatic &amp; Contactless</span>
                        </button>

                        <button id="btn-select-finger" class="method-btn finger" aria-label="Use Fingerprint">
                            <div class="method-icon purple"><i class="fas fa-fingerprint"></i></div>
                            <h3>Fingerprint</h3>
                            <span>Scanner Required</span>
                        </button>
                    </div>

                    <a href="{{ route('attendance.qr') }}" class="qr-link">
                        <i class="fas fa-qrcode"></i> Use QR Code instead
                    </a>
                </div>

                <!-- Active Method Header -->
                <div class="active-header" id="active-header">
                    <button class="back-btn" id="back-to-selection" aria-label="Go back">
                        <i class="fas fa-arrow-left"></i>
                    </button>
                    <div style="display: flex; flex-direction: column;">
                        <h2 id="active-method-title">Facial Scan</h2>
                        <span id="clock-status-title" style="font-size: 0.75rem; color: var(--gray-500); font-weight: 400;">Ready to scan</span>
                    </div>
                </div>

                <!-- Camera Container -->
                <div id="camera-container">
                    <div class="camera-wrap">
                        <video id="attendance-video" autoplay playsinline></video>
                        <canvas id="attendance-canvas"></canvas>
                        <div class="scan-overlay">
                            <div class="corner tl"></div>
                            <div class="corner tr"></div>
                            <div class="corner bl"></div>
                            <div class="corner br"></div>
                            <div class="scan-line"></div>
                            <div class="face-guide"><div class="face-guide-oval"></div></div>
                        </div>
                        <div class="cam-status-badge">
                            <div class="dot"></div>
                            <span>Looking for face...</span>
                        </div>
                    </div>
                    <p class="cam-hint">
                        <i class="fas fa-lightbulb"></i>
                        Ensure your face is well-lit and clearly visible
                    </p>
                </div>

                <!-- Fingerprint Container -->
                <div id="fingerprint-container">
                    <div class="fp-ring-wrap">
                        <div class="fp-ring"></div>
                        <div class="fp-ring-2"></div>
                        <div class="fp-icon"><i class="fas fa-fingerprint"></i></div>
                    </div>
                    <h3>Place Finger on Scanner</h3>
                    <p>Waiting for fingerprint capture...</p>
                </div>

                <!-- Fingerprint Scanner Status -->
                <div id="fingerprint-indicator">
                    <div class="status-left">
                        <div id="zk-status-dot"></div>
                        <span id="zk-status-text">Connecting to Scanner...</span>
                    </div>
                    <i class="fas fa-fingerprint" id="zk-icon"></i>
                </div>

                <!-- Result Overlay -->
                <div id="result-overlay">
                    <div id="result-icon-container" class="result-icon"></div>
                    <h3 id="result-title"></h3>
                    <p id="result-message"></p>
                    <div class="result-progress">
                        <div class="result-progress-bar" id="result-progress-bar"></div>
                    </div>
                </div>

                <!-- Processing Overlay -->
                <div id="processing-overlay" style="display: none; position: absolute; inset: 0; background: rgba(255,255,255,0.7); backdrop-filter: blur(4px); z-index: 15; align-items: center; justify-content: center; flex-direction: column; gap: 1rem;">
                    <div class="method-icon blue animate-pulse" style="width: 80px; height: 80px;"><i class="fas fa-sync-alt fa-spin"></i></div>
                    <p style="font-weight: 600; color: var(--navy);">Processing...</p>
                </div>

                <!-- Footer -->
                <div class="panel-right-footer">
                    <a href="{{ route('register') }}" class="enrollment-link" style="color: var(--blue-bright);">
                        Not yet registered? Click here to register <i class="fas fa-arrow-right"></i>
                    </a>
                    <span class="system-badge">v2.0</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Live clock
        function updateClock() {
            const now = new Date();
            const h = String(now.getHours()).padStart(2, '0');
            const m = String(now.getMinutes()).padStart(2, '0');
            const s = String(now.getSeconds()).padStart(2, '0');
            const el = document.getElementById('live-clock');
            if (el) el.innerHTML = `${h}<span class="time-dot">:</span>${m}<span class="time-dot">:</span>${s}`;
        }
        setInterval(updateClock, 1000);
        updateClock();

        // Safety: ensure the selection screen is visible by default.
        // (Prevents a blank right panel if cached JS/CSS toggled classes unexpectedly.)
        (function ensureDefaultClockUi() {
            const selection = document.getElementById('selection-screen');
            const camera = document.getElementById('camera-container');
            const finger = document.getElementById('fingerprint-container');
            const indicator = document.getElementById('fingerprint-indicator');

            if (selection) selection.classList.remove('hidden');
            if (camera) camera.classList.remove('visible');
            if (finger) finger.classList.remove('visible');
            if (indicator) indicator.classList.remove('visible');
        })();
    </script>
</body>
</html>


