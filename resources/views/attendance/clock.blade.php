<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5, user-scalable=yes">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Clock In/Out - ChronoSync Attendance System</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
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
    <!-- MediaPipe Face Mesh for Liveness Detection -->
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/camera_utils/camera_utils.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/face_mesh/face_mesh.js" crossorigin="anonymous"></script>
    <script src="{{ asset('js/liveness-detector.js') }}"></script>
    <!-- Load attendance script via Vite (module build) -->
    @vite(['resources/js/attendance-clock.js'])
    <style>
        body {
            font-family: 'Sora', sans-serif;
            min-height: 100vh;
            background-color: #f3f4f6;
            background-image: url('{{ asset('images/background-pattern.png') }}');
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            position: relative;
        }
        .bg-pattern {
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            position: absolute;
            inset: 0;
            opacity: 0.1;
            z-index: 0;
        }
        .glass-panel {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .custom-scroll::-webkit-scrollbar { width: 6px; }
        .custom-scroll::-webkit-scrollbar-track { background: rgba(0,0,0,0.05); }
        .custom-scroll::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.1); border-radius: 10px; }
        .custom-scroll::-webkit-scrollbar-thumb:hover { background: rgba(0,0,0,0.2); }

        /* Camera */
        #camera-container { display: none; }
        #camera-container.visible { display: flex; }
        .camera-wrap {
            position: relative;
            width: 100%;
            max-width: 320px;
            border-radius: 16px;
            overflow: hidden;
            background: #0f2a1d;
            aspect-ratio: 4/3;
            box-shadow: 0 12px 32px rgba(0,0,0,0.14);
            margin: 0 auto;
        }
        #attendance-video { width: 100%; height: 100%; object-fit: cover; transform: scaleX(-1); }
        #attendance-canvas { display: none; }
        .scan-overlay { position: absolute; inset: 0; pointer-events: none; }
        .corner { position: absolute; width: 28px; height: 28px; }
        .corner.tl { top: 14px; left: 14px; border-top: 3px solid #10b981; border-left: 3px solid #10b981; border-radius: 4px 0 0 0; }
        .corner.tr { top: 14px; right: 14px; border-top: 3px solid #10b981; border-right: 3px solid #10b981; border-radius: 0 4px 0 0; }
        .corner.bl { bottom: 14px; left: 14px; border-bottom: 3px solid #10b981; border-left: 3px solid #10b981; border-radius: 0 0 0 4px; }
        .corner.br { bottom: 14px; right: 14px; border-bottom: 3px solid #10b981; border-right: 3px solid #10b981; border-radius: 0 0 4px 0; }
        .scan-line {
            position: absolute; top: 0; left: 0; width: 100%; height: 2px;
            background: linear-gradient(90deg, transparent, #10b981, transparent);
            animation: scanMove 2.5s linear infinite;
        }
        @keyframes scanMove {
            0% { top: 0%; opacity: 0; }
            5% { opacity: 1; }
            95% { opacity: 1; }
            100% { top: 100%; opacity: 0; }
        }
        .face-guide { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; }
        .face-guide-oval { width: 44%; height: 60%; border: 1.5px dashed rgba(16, 185, 129, 0.4); border-radius: 50%; }
        
        .cam-status-badge {
            position: absolute; bottom: 12px; left: 50%; transform: translateX(-50%);
            background: rgba(0,0,0,0.65); backdrop-filter: blur(12px);
            border-radius: 100px; padding: 0.32rem 0.9rem; display: flex; align-items: center; gap: 0.4rem;
        }
        .cam-status-badge .dot {
            width: 8px; height: 8px; border-radius: 50%; background: #10b981;
            animation: pulse-dot 1.5s ease-in-out infinite;
        }
        @keyframes pulse-dot { 0%, 100% { opacity: 1; transform: scale(1); } 50% { opacity: 0.5; transform: scale(0.8); } }
        .cam-status-badge span { font-size: 0.7rem; color: #fff; font-family: 'JetBrains Mono', monospace; }

        /* Fingerprint */
        #fingerprint-container { display: none; }
        #fingerprint-container.visible { display: flex; }
        .fp-ring-wrap { position: relative; width: 120px; height: 120px; margin: 0 auto; }
        .fp-ring { position: absolute; inset: 0; border-radius: 50%; border: 2px solid transparent; border-top-color: #10b981; animation: spin 1.8s linear infinite; }
        .fp-ring-2 { position: absolute; inset: 12px; border-radius: 50%; border: 1.5px solid transparent; border-top-color: rgba(16,185,129,0.4); animation: spin 3s linear infinite reverse; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .fp-icon { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; font-size: 3.4rem; color: rgba(16,185,129,0.25); animation: breathe 3s ease-in-out infinite; }
        @keyframes breathe { 0%, 100% { transform: scale(1); color: rgba(16,185,129,0.25); } 50% { transform: scale(1.05); color: rgba(16,185,129,0.4); } }
        
        #fingerprint-indicator { display: none; }
        #fingerprint-indicator.visible { display: flex; }

        /* Result Overlay */
        #result-overlay {
            position: absolute; inset: 0; background: rgba(255,255,255,0.97); backdrop-filter: blur(20px);
            z-index: 20; display: flex; flex-direction: column; align-items: center; justify-content: center;
            opacity: 0; pointer-events: none; transition: opacity 0.3s;
        }
        #result-overlay.visible { opacity: 1; pointer-events: all; }
        .result-progress-bar { height: 100%; background: #10b981; border-radius: 100px; animation: progressShrink 3s linear forwards; }
        @keyframes progressShrink { from { width: 100%; } to { width: 0%; } }

        /* Method selection */
        .method-btn {
            transition: all 0.2s ease-in-out;
        }
        .method-btn.active-method {
            background-color: #ffffff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            color: #059669;
        }
        
    </style>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen relative p-4">
    <div class="bg-pattern"></div>
    
    <div class="w-full max-w-lg z-10 relative">
        <div class="glass-panel rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
            
            <div class="p-6 md:p-8 overflow-y-auto custom-scroll w-full bg-white flex flex-col relative flex-1 min-h-0 text-center">
                
                <!-- Header / Logo -->
                <div class="flex justify-center items-center space-x-3 mb-4">
                    <div class="w-12 h-12 bg-emerald-500 rounded-xl shadow-lg flex items-center justify-center relative overflow-hidden shrink-0">
                        <span class="text-white/40 font-bold text-3xl absolute -left-0.5">C</span>
                        <span class="text-white font-bold text-2xl z-10 relative left-1">S</span>
                    </div>
                    <div class="flex flex-col text-left">
                        <div class="text-2xl font-bold tracking-tight leading-none">
                            <span class="text-emerald-600">Chrono</span><span class="text-gray-800">Sync</span>
                        </div>
                        <div class="text-gray-500 text-[0.6rem] font-bold tracking-[0.25em] mt-1 uppercase">
                            Attendance
                        </div>
                    </div>
                </div>

                <!-- Live Clock -->
                <div class="mb-6">
                    <div class="font-mono text-4xl sm:text-5xl font-bold text-emerald-700 tracking-tight" id="live-clock">--<span class="opacity-50">:</span>--<span class="opacity-50">:</span>--</div>
                    <div class="text-sm text-gray-500 font-medium mt-1 uppercase tracking-widest" id="live-date">{{ now()->format('l, d F Y') }}</div>
                </div>

                <!-- Selection Screen -->
                <div id="selection-screen" class="flex-1 flex flex-col">
                    <h2 class="text-lg font-bold text-gray-900 mb-1">Choose Clock-In Method</h2>
                    <p class="text-sm text-gray-500 mb-6">Select your preferred method to verify your identity.</p>

                    <!-- Segmented Control for Method Selection -->
                    <div class="flex justify-center mb-6">
                        <div class="inline-flex w-full bg-gray-100 p-1.5 rounded-xl">
                            <button id="btn-select-face" class="method-btn active-method flex-1 py-3 text-sm font-semibold rounded-lg transition-all duration-200 flex flex-col items-center justify-center text-emerald-700 bg-white shadow-sm gap-1">
                                <i class="fas fa-camera text-xl mb-1"></i>
                                Facial Scan
                            </button>
                            <button id="btn-select-finger" class="method-btn flex-1 py-3 text-sm font-semibold rounded-lg transition-all duration-200 flex flex-col items-center justify-center text-gray-500 hover:text-gray-700 gap-1">
                                <i class="fas fa-fingerprint text-xl mb-1"></i>
                                Fingerprint
                            </button>
                        </div>
                    </div>
                    
                    <a href="{{ route('attendance.qr') }}" class="inline-flex items-center justify-center px-4 py-2 mt-2 mx-auto text-sm font-medium text-gray-500 hover:text-emerald-600 bg-gray-50 hover:bg-emerald-50 rounded-lg transition-colors w-fit">
                        <i class="fas fa-qrcode mr-2"></i> Use QR Code instead
                    </a>
                </div>

                <!-- Camera Container -->
                <div id="camera-container" class="flex-col items-center flex-1 w-full mt-2">
                    <!-- Active Header (replacing old active-header div) -->
                    <div class="flex items-center justify-between w-full mb-4 px-2">
                        <button class="w-8 h-8 rounded-lg bg-gray-50 text-gray-500 hover:bg-gray-100 hover:text-gray-800 transition-colors flex items-center justify-center" onclick="document.getElementById('camera-container').classList.remove('visible'); document.getElementById('selection-screen').style.display='flex';">
                            <i class="fas fa-arrow-left"></i>
                        </button>
                        <div class="text-center">
                            <h2 class="text-sm font-bold text-gray-900">Facial Scan</h2>
                            <p class="text-xs text-gray-500">Ready to scan</p>
                        </div>
                        <div class="w-8"></div><!-- Spacer for centering -->
                    </div>

                    <div class="camera-wrap shadow-xl">
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
                    <div class="mt-4 px-4 py-3 bg-emerald-50 text-emerald-800 text-xs rounded-lg flex items-start text-left border border-emerald-100">
                        <i class="fas fa-info-circle mt-0.5 mr-2 text-emerald-600"></i>
                        <p>Look directly at the camera. Ensure your face is well-lit. Verification happens automatically.</p>
                    </div>
                </div>

                <!-- Fingerprint Container -->
                <div id="fingerprint-container" class="flex-col items-center justify-center flex-1 w-full mt-2">
                    <div class="flex items-center justify-between w-full mb-6 px-2">
                        <button class="w-8 h-8 rounded-lg bg-gray-50 text-gray-500 hover:bg-gray-100 hover:text-gray-800 transition-colors flex items-center justify-center" onclick="document.getElementById('fingerprint-container').classList.remove('visible'); document.getElementById('selection-screen').style.display='flex';">
                            <i class="fas fa-arrow-left"></i>
                        </button>
                        <div class="text-center">
                            <h2 class="text-sm font-bold text-gray-900">Fingerprint Scan</h2>
                        </div>
                        <div class="w-8"></div>
                    </div>

                    <div class="fp-ring-wrap mb-4">
                        <div class="fp-ring"></div>
                        <div class="fp-ring-2"></div>
                        <div class="fp-icon"><i class="fas fa-fingerprint"></i></div>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900" id="fp-main-title">Place Finger on Scanner</h3>
                    <p class="text-sm text-gray-500 mt-1" id="fp-sub-title">Waiting for fingerprint capture...</p>
                    
                    <div id="fingerprint-indicator" class="mt-6 w-full flex items-center justify-between px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl">
                        <div class="flex items-center gap-3">
                            <div id="zk-status-dot" class="w-2.5 h-2.5 rounded-full bg-gray-400"></div>
                            <span id="zk-status-text" class="text-sm font-medium text-gray-700">Connecting...</span>
                        </div>
                        <i class="fas fa-fingerprint text-gray-400 text-lg" id="zk-icon"></i>
                    </div>

                    <div id="webauthn-fallback-container" class="hidden mt-6 w-full flex-col items-center">
                        <button id="btn-trigger-webauthn" class="w-full px-6 py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl shadow-lg shadow-emerald-200 transition-all font-semibold flex justify-center items-center gap-2 transform active:scale-95">
                            <i class="fas fa-mobile-alt text-lg"></i>
                            Scan Device Fingerprint
                        </button>
                        <p class="text-xs text-gray-500 mt-3 text-center">Using your device's built-in security</p>
                    </div>
                </div>

                <!-- Result Overlay -->
                <div id="result-overlay">
                    <div id="result-icon-container" class="w-20 h-20 rounded-full flex items-center justify-center text-4xl mb-4"></div>
                    <h3 id="result-title" class="text-2xl font-bold text-gray-900 tracking-tight"></h3>
                    <p id="result-message" class="text-sm text-gray-500 mt-2 max-w-xs mx-auto"></p>
                    <div class="w-32 h-1.5 bg-gray-100 rounded-full overflow-hidden mt-8">
                        <div class="result-progress-bar" id="result-progress-bar"></div>
                    </div>
                </div>

                <!-- Processing Overlay -->
                <div id="processing-overlay" style="display: none; position: absolute; inset: 0; background: rgba(255,255,255,0.85); backdrop-filter: blur(4px); z-index: 15; align-items: center; justify-content: center; flex-direction: column; gap: 1rem;">
                    <div class="w-16 h-16 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl animate-pulse shadow-sm border border-emerald-100">
                        <i class="fas fa-sync-alt fa-spin"></i>
                    </div>
                    <p class="font-bold text-gray-800 tracking-wide text-sm">Processing...</p>
                </div>

                <!-- Footer -->
                <div class="mt-8 pt-4 border-t border-gray-100 flex items-center justify-between">
                    <a href="{{ route('register') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 transition-colors flex items-center group">
                        Not yet registered? Click here <i class="fas fa-arrow-right ml-1 group-hover:translate-x-1 transition-transform"></i>
                    </a>
                    <span class="text-[0.65rem] font-mono text-gray-400 bg-gray-50 px-2 py-1 rounded-md border border-gray-100">v2.0</span>
                </div>

            </div>
        </div>
    </div>
    
    <script>
        // UI Interaction for segmented control simulation
        document.getElementById('btn-select-face').addEventListener('click', function() {
            document.getElementById('selection-screen').style.display = 'none';
            document.getElementById('camera-container').classList.add('visible');
            document.getElementById('fingerprint-container').classList.remove('visible');
        });
        
        document.getElementById('btn-select-finger').addEventListener('click', function() {
            document.getElementById('selection-screen').style.display = 'none';
            document.getElementById('fingerprint-container').classList.add('visible');
            document.getElementById('camera-container').classList.remove('visible');
        });

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


