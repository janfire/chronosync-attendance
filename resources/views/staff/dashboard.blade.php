<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5, user-scalable=yes">
    <title>Staff Dashboard - ChronoSync</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800|outfit:500,600,700" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Scripts & Styles -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        display: ['Outfit', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            900: '#1e3a8a',
                        },
                        dark: {
                            bg: '#0f172a',
                            card: '#1e293b',
                            border: '#334155'
                        }
                    },
                    animation: {
                        'float': 'float 3s ease-in-out infinite',
                        'fade-in-up': 'fadeInUp 0.5s ease-out forwards',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-5px)' },
                        },
                        fadeInUp: {
                            '0%': { opacity: '0', transform: 'translateY(10px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background-color: #f8fafc;
            color: #334155;
            -webkit-font-smoothing: antialiased;
        }
        
        .dark body {
            background-color: #0f172a;
            color: #cbd5e1;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        }

        .dark .glass-card {
            background: rgba(30, 41, 59, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2);
        }

        .metric-card {
            transition: all 0.3s ease;
        }
        
        .metric-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        
        .dark .metric-card:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3);
        }

        .gradient-text {
            background: linear-gradient(135deg, #3b82f6 0%, #10b981 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .rank-badge {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            text-shadow: 0 1px 2px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body class="min-h-screen relative overflow-x-hidden selection:bg-primary-500 selection:text-white">

    <!-- Ambient Background Blobs -->
    <div class="fixed top-0 left-0 w-full h-full overflow-hidden -z-10 pointer-events-none">
        <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] rounded-full bg-blue-400/20 dark:bg-blue-600/10 blur-[100px]"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] rounded-full bg-emerald-400/20 dark:bg-emerald-600/10 blur-[100px]"></div>
    </div>

    <!-- Navigation -->
    <nav class="glass-card sticky top-0 z-50 border-b border-gray-200 dark:border-dark-border">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center space-x-3.5">
                    <div class="relative shrink-0 group">
                        <!-- Background glow -->
                        <div class="absolute inset-0 bg-emerald-500 rounded-xl blur-md opacity-30 group-hover:opacity-60 transition-opacity duration-500"></div>
                        
                        <!-- Logo Container -->
                        <div class="relative h-10 w-10 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-700 flex items-center justify-center shadow-lg border border-white/10 overflow-hidden">
                            <!-- Abstract Monogram C & S -->
                            <svg class="w-6 h-6 text-white relative z-10 drop-shadow-sm transform group-hover:scale-105 transition-transform duration-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <!-- Abstract C -->
                                <path d="M16 19 A 8 8 0 1 1 16 5" stroke-opacity="0.5" />
                                <!-- Abstract S -->
                                <path d="M16 8 A 3.5 3.5 0 0 0 9 8 C 9 12 16 11 16 15 A 3.5 3.5 0 0 1 9 15" />
                            </svg>
                        </div>
                    </div>
                    <div>
                        <h1 class="text-xl leading-none tracking-tight flex items-center text-gray-900 dark:text-white">
                            <span class="font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-teal-600 dark:from-emerald-400 dark:to-teal-400">Chrono</span>
                            <span class="font-light">Sync</span>
                        </h1>
                        <p class="text-[0.55rem] uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400 font-bold mt-1">Attendance</p>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <button id="theme-toggle" class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors text-gray-500 dark:text-gray-400">
                        <i class="fas fa-moon dark:hidden"></i>
                        <i class="fas fa-sun hidden dark:inline"></i>
                    </button>
                    <form method="POST" action="{{ route('logout') }}" class="m-0">
                        @csrf
                        <button type="submit" class="text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors">
                            Sign Out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-12">
        
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <!-- Left Column: Profile & Actions -->
            <div class="lg:col-span-4 space-y-6">
                
                <!-- Profile Card -->
                <div class="glass-card rounded-3xl p-8 text-center relative overflow-hidden animate-fade-in-up" style="animation-delay: 0.1s;">
                    <!-- Decorative Background element -->
                    <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-blue-100 to-transparent dark:from-blue-900/40 rounded-bl-full -z-10 opacity-50"></div>
                    
                    <div class="relative w-24 h-24 mx-auto mb-4">
                        <div class="absolute inset-0 bg-gradient-to-tr from-blue-500 to-emerald-400 rounded-full animate-pulse opacity-20"></div>
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=3b82f6&color=fff&size=150" alt="{{ $user->name }}" class="w-full h-full object-cover rounded-full border-4 border-white dark:border-dark-card shadow-lg relative z-10">
                    </div>
                    
                    <h2 class="text-2xl font-display font-bold text-gray-900 dark:text-white mb-1">{{ $user->name }}</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">{{ $user->email }}</p>
                    
                    <div class="inline-block bg-gray-100 dark:bg-gray-800 rounded-full px-4 py-1.5 text-sm font-medium text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-700">
                        ID: <span class="font-bold font-mono">{{ $user->employee_number ?? 'N/A' }}</span>
                    </div>
                </div>

                <!-- QR Code Access -->
                <div class="glass-card rounded-3xl p-6 animate-fade-in-up" style="animation-delay: 0.2s;">
                    <div class="flex flex-col items-center justify-center text-center">
                        <div class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400 flex items-center justify-center mb-4">
                            <i class="fas fa-qrcode text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Ready to Clock In?</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Use your unique QR code at the kiosk.</p>
                        
                        <a href="{{ route('attendance.qr') }}" class="w-full inline-flex justify-center items-center gap-2 px-6 py-3 bg-gray-900 hover:bg-gray-800 dark:bg-white dark:hover:bg-gray-100 dark:text-gray-900 text-white font-medium rounded-xl transition-all active:scale-95 shadow-md">
                            <span>View My QR Code</span>
                            <i class="fas fa-arrow-right text-sm"></i>
                        </a>
                    </div>
                </div>

                <!-- Privacy Settings -->
                @if($user->biometric_consent_granted)
                <div class="glass-card rounded-3xl p-6 animate-fade-in-up" style="animation-delay: 0.3s;">
                    <div class="flex flex-col items-center justify-center text-center">
                        <div class="w-12 h-12 rounded-xl bg-red-100 dark:bg-red-900/50 text-red-600 dark:text-red-400 flex items-center justify-center mb-4">
                            <i class="fas fa-user-shield text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Privacy Settings</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">You have granted biometric consent. You can revoke it at any time to delete your data.</p>
                        
                        <form method="POST" action="{{ route('profile.biometric.revoke') }}" class="w-full" onsubmit="return confirm('Are you sure you want to revoke consent and permanently delete your biometric data?');">
                            @csrf
                            <button type="submit" class="w-full inline-flex justify-center items-center gap-2 px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-medium rounded-xl transition-all active:scale-95 shadow-md">
                                <span>Revoke Consent & Delete Data</span>
                                <i class="fas fa-trash-alt text-sm"></i>
                            </button>
                        </form>
                    </div>
                </div>
                @endif

            </div>

            <!-- Right Column: Analytics & Gamification -->
            <div class="lg:col-span-8 space-y-6">
                
                <!-- Welcome Banner -->
                <div class="glass-card rounded-3xl p-8 relative overflow-hidden animate-fade-in-up" style="animation-delay: 0.15s;">
                    <div class="absolute right-0 bottom-0 text-gray-100 dark:text-gray-800/30 -mr-4 -mb-8 pointer-events-none">
                        <i class="fas fa-chart-line text-[12rem]"></i>
                    </div>
                    
                    <div class="relative z-10">
                        <span class="inline-block px-3 py-1 bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300 rounded-full text-xs font-bold tracking-wider uppercase mb-3">
                            {{ now()->format('F Y') }} Overview
                        </span>
                        @php
                            $hour = now()->format('H');
                            if ($hour < 12) {
                                $greeting = 'Good morning';
                            } elseif ($hour < 17) {
                                $greeting = 'Good afternoon';
                            } else {
                                $greeting = 'Good evening';
                            }
                        @endphp
                        <h1 class="text-3xl sm:text-4xl font-display font-bold text-gray-900 dark:text-white mb-2">
                            {{ $greeting }}, {{ explode(' ', $user->name)[0] }}
                        </h1>
                        <p class="text-gray-600 dark:text-gray-300 max-w-lg">
                            Here is a snapshot of your attendance performance for this month. Keep up the great work!
                        </p>
                    </div>
                </div>

                <!-- Gamification Ranking Card -->
                <div class="bg-gradient-to-br from-gray-900 to-gray-800 dark:from-dark-card dark:to-gray-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden animate-fade-in-up border border-gray-700" style="animation-delay: 0.25s;">
                    <!-- Shine effect -->
                    <div class="absolute top-0 left-[-100%] w-1/2 h-full bg-gradient-to-r from-transparent via-white/10 to-transparent skew-x-[-20deg] animate-[shimmer_3s_infinite]"></div>
                    
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-6 relative z-10">
                        <div>
                            <h3 class="text-lg text-gray-300 font-medium mb-1">Your Punctuality Ranking</h3>
                            <div class="text-3xl sm:text-4xl font-display font-bold mb-2">
                                You are ranked <span class="text-yellow-400 animate-float inline-block">{{ $rank }}{{ date('S', mktime(0, 0, 0, 0, $rank, 0)) }}</span> out of {{ $totalEmployees }}
                            </div>
                            <p class="text-sm text-gray-400">Based on total attendance points this month.</p>
                        </div>
                        <div class="w-20 h-20 shrink-0 rounded-full rank-badge flex items-center justify-center border-4 border-gray-800 shadow-[0_0_20px_rgba(245,158,11,0.4)]">
                            <i class="fas fa-trophy text-3xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 animate-fade-in-up" style="animation-delay: 0.35s;">
                    
                    <div class="glass-card rounded-2xl p-6 metric-card">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Days Present</h4>
                        </div>
                        <div class="text-3xl font-display font-bold text-gray-900 dark:text-white">
                            {{ $daysPresent }}
                        </div>
                    </div>

                    <div class="glass-card rounded-2xl p-6 metric-card">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                                <i class="fas fa-clock"></i>
                            </div>
                            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">On Time</h4>
                        </div>
                        <div class="text-3xl font-display font-bold text-gray-900 dark:text-white">
                            {{ $onTimeCount }}
                        </div>
                    </div>

                    <div class="glass-card rounded-2xl p-6 metric-card">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-full bg-orange-100 dark:bg-orange-900/40 text-orange-600 dark:text-orange-400 flex items-center justify-center">
                                <i class="fas fa-stopwatch"></i>
                            </div>
                            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Late Arrivals</h4>
                        </div>
                        <div class="text-3xl font-display font-bold text-gray-900 dark:text-white">
                            {{ $lateCount }}
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </main>

    <script>
        // Dark Mode Toggle Logic
        const themeToggleBtn = document.getElementById('theme-toggle');
        
        themeToggleBtn.addEventListener('click', function() {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.theme = 'light';
            } else {
                document.documentElement.classList.add('dark');
                localStorage.theme = 'dark';
            }
        });
    </script>
</body>
</html>
