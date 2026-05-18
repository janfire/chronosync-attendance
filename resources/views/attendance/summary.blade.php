<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5, user-scalable=yes">
    <title>Attendance Summary - ChronoSync Attendance System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        :root {
            --navy: #0f2a1d;
            --font-main: 'Sora', sans-serif;
            --font-mono: 'JetBrains Mono', monospace;
        }

        body {
            font-family: var(--font-main);
            min-height: 100vh;
            background-color: #f3f4f6;
            background-image: url('{{ asset("images/background-pattern.png") }}');
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
        
        /* Time progress bar animation */
        .timeout-progress {
            width: 100%;
            height: 4px;
            background: #e2e8f0;
            overflow: hidden;
            border-radius: 4px;
            margin-top: 1rem;
        }
        
        .timeout-bar {
            height: 100%;
            background: var(--navy);
            width: 100%;
            border-radius: 4px;
            transform-origin: left;
        }
        
        @keyframes shrinkBar {
            from { transform: scaleX(1); }
            to { transform: scaleX(0); }
        }
        
        .animate-shrink {
            animation: shrinkBar 7s linear forwards;
        }
    </style>
</head>
<body>
    <div class="relative z-10 w-full max-w-lg">
        <div class="bg-white/95 backdrop-blur-xl border border-white/20 rounded-2xl shadow-2xl overflow-hidden">
            <!-- Header section (Navy blue) -->
            <div class="bg-[#0f2a1d] border-b border-white/10 p-6 relative overflow-hidden">
                <div class="bg-pattern"></div>
                
                <div class="relative z-10 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="h-12 w-12 bg-emerald-500 rounded-xl flex items-center justify-center text-white shadow-lg shrink-0"><i class="fas fa-clock text-2xl"></i></div>
                        <h1 class="text-white font-bold tracking-tight">ChronoSync Attendance</h1>
                    </div>
                    
                    @if($todayLog->action === 'clock_in')
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-green-500/20 text-green-300 border border-green-500/30">
                            <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
                            CLOCKED IN
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-gray-500/20 text-gray-300 border border-gray-500/30">
                            <span class="w-2 h-2 rounded-full bg-gray-400"></span>
                            CLOCKED OUT
                        </span>
                    @endif
                </div>
            </div>

            <!-- Content Body -->
            <div class="p-8">
                <!-- User Profile Intro -->
                <div class="flex items-start gap-4 mb-8">
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-50 to-blue-100 flex items-center justify-center text-emerald-600 text-2xl font-bold border border-emerald-200 shadow-sm flex-shrink-0">
                        {{ substr($user->name, 0, 1) }}
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 leading-tight">{{ $user->name }}</h2>
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-widest mt-1">ID: {{ $user->employee_number }}</p>
                        
                        <p class="mt-2 text-sm text-gray-600 bg-gray-50 inline-block px-3 py-1 rounded-lg border border-gray-100">
                            @if($todayLog->action === 'clock_in')
                                Have a productive day ahead! <i class="fas fa-coffee text-amber-600 ml-1"></i>
                            @else
                                Thank you for your hard work! <i class="fas fa-hand-sparkles text-amber-500 ml-1"></i>
                            @endif
                        </p>
                    </div>
                </div>

                <!-- Status Cards -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 flex items-center gap-2">
                            <i class="far fa-clock text-emerald-500"></i> Local Time
                        </div>
                        <div class="text-xl font-bold text-gray-900 font-mono tracking-tight">
                            {{ $todayLog->timestamp->format('h:i A') }}
                        </div>
                        <div class="text-xs text-gray-400 mt-1">{{ $todayLog->timestamp->format('M j, Y') }}</div>
                    </div>

                    <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                        @if($todayLog->action === 'clock_out' && $hoursWorked)
                            <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 flex items-center gap-2">
                                <i class="fas fa-business-time text-purple-500"></i> Hours Today
                            </div>
                            <div class="text-base font-bold text-gray-900 tracking-tight mt-1">
                                {{ $hoursWorked }}
                            </div>
                        @else
                            <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 flex items-center gap-2">
                                <i class="fas fa-sign-in-alt text-green-500"></i> Action Taken
                            </div>
                            <div class="text-base font-bold text-gray-900 tracking-tight mt-1">
                                First Clock In
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Footer Area & Redirect logic -->
                <div class="mt-8 pt-6 border-t border-gray-100">
                    <div class="flex flex-col items-center justify-center">
                        <a href="{{ route('attendance.clock') }}" class="w-full text-center bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-3 px-6 rounded-xl transition-colors shadow-sm shadow-emerald-600/20 border border-emerald-500">
                            <i class="fas fa-camera mr-2"></i> Done, return to camera
                        </a>
                        
                        @if(!$isMobile)
                            <p class="text-xs text-center text-gray-400 mt-4 h-4" id="redirectText">
                                Returning to camera in <strong class="text-gray-600" id="countdown">7</strong> seconds...
                            </p>
                            <div class="timeout-progress">
                                <div class="timeout-bar animate-shrink"></div>
                            </div>
                        @else
                            <p class="text-xs text-center text-emerald-500/70 border border-emerald-100 bg-emerald-50 px-3 py-1.5 rounded-lg mt-4 font-medium flex items-center justify-center gap-2 w-full">
                                <i class="fas fa-mobile-alt"></i> Mobile view activated. Auto-return disabled.
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(!$isMobile)
    <script>
        // Kiosk Auto-Return Logic
        let secondsLeft = 7;
        const countdownEl = document.getElementById('countdown');
        let countdownInterval;
        
        function startCountdown() {
            countdownInterval = setInterval(() => {
                secondsLeft--;
                if(countdownEl) countdownEl.innerText = secondsLeft;
                
                if(secondsLeft <= 0) {
                    clearInterval(countdownInterval);
                    window.location.href = "{{ route('attendance.clock') }}";
                }
            }, 1000); // exactly 1 second intervals for accurate text update -> CSS handles the smooth bar
        }

        // Only start countdown if no rapid clock-out prompt is shown
        const urlParams = new URLSearchParams(window.location.search);
        if (!urlParams.has('prompt_clockout')) {
            startCountdown();
        }
    </script>
    @endif

    <!-- SweetAlert2 for modern popups -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            
            // Check if prompt_clockout param is in URL and user is currently clocked in
            @if($todayLog->action === 'clock_in')
            if (urlParams.has('prompt_clockout') && urlParams.get('prompt_clockout') == '1') {
                
                Swal.fire({
                    title: 'Already Clocked In',
                    text: 'You are currently clocked in. Would you like to clock out?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444', // Red for clock out
                    cancelButtonColor: '#3b82f6',  // Blue for cancel/stay
                    confirmButtonText: '<i class="fas fa-sign-out-alt mr-1"></i> Yes, Clock Out',
                    cancelButtonText: 'No, Stay Clocked In',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        // User chose to clock out manually
                        Swal.fire({
                            title: 'Clocking out...',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        fetch("{{ route('attendance.manual-clock-out') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                user_id: {{ $user->id }}
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    text: data.message,
                                    timer: 2000,
                                    showConfirmButton: false
                                }).then(() => {
                                    // Remove the rapid_clocking param and reload the page to show clock out status
                                    window.location.href = window.location.pathname;
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: data.error || 'Failed to clock out'
                                }).then(() => {
                                    @if(!$isMobile) startCountdown(); @endif
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire('Error', 'Network error occurred.', 'error')
                            .then(() => {
                                @if(!$isMobile) startCountdown(); @endif
                            });
                        });
                    } else {
                        // User chose not to clock out
                        // Start the regular countdown if on kiosk
                        @if(!$isMobile)
                            startCountdown();
                        @endif
                        
                        // Clean up URL to prevent loop on manual refresh
                        window.history.replaceState({}, document.title, window.location.pathname);
                    }
                });
            }
            @endif
        });
    </script>
</body>
</html>


