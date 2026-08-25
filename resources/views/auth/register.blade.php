<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, user-scalable=yes">
    <title>Register - ChronoSync Attendance System</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Scripts & Styles -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    <style>
        body {
            font-family: 'Instrument Sans', sans-serif;
        }
        .glass-panel {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .bg-pattern {
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        .custom-scroll::-webkit-scrollbar {
            width: 6px;
        }
        .custom-scroll::-webkit-scrollbar-track {
            background: rgba(0,0,0,0.05);
        }
        .custom-scroll::-webkit-scrollbar-thumb {
            background: rgba(0,0,0,0.1);
            border-radius: 10px;
        }
        .custom-scroll::-webkit-scrollbar-thumb:hover {
            background: rgba(0,0,0,0.2);
        }
        
        /* Tabs animations */
        .tab-content {
            display: none;
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
        }
        .tab-content.active {
            display: block;
            opacity: 1;
            animation: fadeIn 0.3s ease-in-out forwards;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(5px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .tab-btn {
            position: relative;
        }
        .tab-btn.active {
            color: #059669;
            background-color: #ffffff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4 bg-cover bg-center" style="background-image: url('{{ asset('images/background-pattern.png') }}');">
    
    <div class="w-full max-w-md">
        <div class="glass-panel rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">

            <!-- Registration Form -->
            <div class="w-full bg-white flex flex-col relative flex-1 min-h-0">
                <div class="p-4 sm:p-5 md:p-6 overflow-y-auto custom-scroll h-full">
                    
                    <div class="mb-4 text-center">
                        <div class="flex justify-center items-center space-x-3 mb-3">
                            <!-- Logo Icon -->
                            <div class="w-12 h-12 bg-emerald-500 rounded-xl shadow-lg flex items-center justify-center relative overflow-hidden shrink-0">
                                <span class="text-white/40 font-bold text-3xl absolute -left-0.5">C</span>
                                <span class="text-white font-bold text-2xl z-10 relative left-1">S</span>
                            </div>
                            <!-- Logo Text -->
                            <div class="flex flex-col text-left">
                                <div class="text-2xl font-bold tracking-tight leading-none">
                                    <span class="text-emerald-600">Chrono</span><span class="text-gray-800">Sync</span>
                                </div>
                                <div class="text-gray-500 text-[0.6rem] font-bold tracking-[0.25em] mt-1 uppercase">
                                    Attendance
                                </div>
                            </div>
                        </div>
                        <h2 class="text-2xl font-bold text-emerald-600">Welcome to ChronoSync</h2>
                        <p class="text-gray-500 text-sm mt-1">Please select your registration type below.</p>
                    </div>

                    @if($errors->any())
                        <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 px-4 py-3 rounded-r-lg text-sm shadow-sm">
                            <div class="flex items-center mb-1">
                                <i class="fas fa-exclamation-circle mr-2"></i>
                                <span class="font-bold">Please correct the following errors:</span>
                            </div>
                            <ul class="list-disc list-inside ml-5 space-y-1 text-xs">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 px-4 py-3 rounded-r-lg text-sm shadow-sm flex items-center">
                            <i class="fas fa-exclamation-triangle mr-3 text-lg"></i>
                            <div>{{ session('error') }}</div>
                        </div>
                    @endif

                    @if(session('success'))
                        <div class="mb-6 bg-green-50 border-l-4 border-green-500 text-green-700 px-4 py-3 rounded-r-lg text-sm shadow-sm flex items-center">
                            <i class="fas fa-check-circle mr-3 text-lg"></i>
                            <div>{{ session('success') }}</div>
                        </div>
                    @endif

                    <!-- Tabs Header -->
                    <div class="flex justify-center mb-5">
                        <div class="inline-flex w-full bg-gray-100 p-1.5 rounded-xl">
                            <button type="button" onclick="switchTab('zou-client')" id="tab-btn-zou" class="tab-btn active flex-1 py-2 text-sm font-semibold rounded-lg transition-all duration-200 flex items-center justify-center text-gray-500">
                                <i class="fas fa-user-tie mr-2"></i> Staff
                            </button>
                            <button type="button" onclick="switchTab('guest')" id="tab-btn-guest" class="tab-btn flex-1 py-2 text-sm font-semibold rounded-lg transition-all duration-200 flex items-center justify-center text-gray-500 hover:text-gray-700">
                                <i class="fas fa-user-tag mr-2"></i> Guest Account
                            </button>
                        </div>
                    </div>

                    <!-- TAB 1: Zou Client (Google OAuth) -->
                    <div id="tab-zou-client" class="tab-content active">
                        <div class="bg-gray-50 rounded-xl p-5 border border-gray-100 text-center">
                            <div class="h-10 w-10 bg-white rounded-full flex items-center justify-center mx-auto shadow-sm mb-2">
                                <i class="fab fa-google text-2xl text-[#DB4437]"></i>
                            </div>
                            <h3 class="text-base font-bold text-gray-900 mb-1">Sign up with Google</h3>
                            <p class="text-xs text-gray-600 mb-4 px-2">
                                Use your official organizational or student email.
                            </p>
                            
                            <a href="{{ route('auth.google.redirect') }}" class="inline-flex items-center justify-center w-full bg-white border border-gray-200 hover:border-[#DB4437] hover:bg-gray-50 text-gray-700 py-2.5 px-4 rounded-lg text-sm font-semibold shadow-sm hover:shadow transition-all duration-200 transform hover:-translate-y-0.5 active:scale-[0.98]">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/c/c1/Google_%22G%22_logo.svg" alt="Google" class="h-4 mr-2">
                                Sign up with Google
                            </a>
                            
                            <p class="text-[0.65rem] text-gray-400 mt-3">
                                <i class="fas fa-lock mr-1"></i> Secure Single Sign-On provided by your organization.
                            </p>
                        </div>
                    </div>

                    <!-- TAB 2: Guest Registration -->
                    <div id="tab-guest" class="tab-content">
                        <form method="POST" action="{{ route('register') }}" class="space-y-3 sm:space-y-4">
                            @csrf
                            <input type="hidden" name="is_guest" value="1">

                            <div class="bg-amber-50 border border-amber-200 rounded-lg p-2 sm:p-2.5 text-xs text-amber-800 mb-2 flex gap-2 items-start">
                                <i class="fas fa-info-circle mt-0.5 text-amber-600"></i>
                                <span class="leading-snug">Guest accounts are for visitors who do not possess organizational Google credentials. Approvals may be required.</span>
                            </div>

                            <!-- Row 1: Name & ID -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">Full Name</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                            <i class="fas fa-user-circle"></i>
                                        </div>
                                        <input type="text" name="name" value="{{ old('name') }}" required 
                                               class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all placeholder-gray-400 bg-gray-50/50 focus:bg-white"
                                               placeholder="e.g. Tendai Moyo">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1 uppercase tracking-wide">ID Number</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                            <i class="fas fa-id-card"></i>
                                        </div>
                                        <input type="text" name="employee_number" value="{{ old('employee_number') }}" required 
                                               class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all placeholder-gray-400 bg-gray-50/50 focus:bg-white"
                                               placeholder="e.g. 12-345678 A 12">
                                    </div>
                                </div>
                            </div>

                            <!-- Email -->
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1 uppercase tracking-wide">Email Address</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                        <i class="fas fa-envelope"></i>
                                    </div>
                                    <input type="email" name="email" value="{{ old('email') }}" required 
                                           class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all placeholder-gray-400 bg-gray-50/50 focus:bg-white"
                                           placeholder="guest@example.com">
                                </div>
                            </div>

                            <!-- Duration of Stay (Guest Only) -->
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1 uppercase tracking-wide">Estimated Period of Stay</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                    <select name="stay_duration" required 
                                           class="w-full pl-10 pr-8 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all bg-gray-50/50 focus:bg-white text-gray-700 appearance-none">
                                        <option value="" disabled selected>Select duration...</option>
                                        <option value="1_day" {{ old('stay_duration') == '1_day' ? 'selected' : '' }}>1 Day</option>
                                        <option value="3_days" {{ old('stay_duration') == '3_days' ? 'selected' : '' }}>3 Days</option>
                                        <option value="1_week" {{ old('stay_duration') == '1_week' ? 'selected' : '' }}>1 Week</option>
                                        <option value="1_month" {{ old('stay_duration') == '1_month' ? 'selected' : '' }}>1 Month</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-400">
                                        <i class="fas fa-chevron-down text-xs"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- Row 2: Passwords -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1 uppercase tracking-wide">Password</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                            <i class="fas fa-lock"></i>
                                        </div>
                                        <input type="password" name="password" id="password" required 
                                               class="w-full pl-10 pr-10 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all placeholder-gray-400 bg-gray-50/50 focus:bg-white"
                                               placeholder="Min. 8 characters">
                                        <button type="button" onclick="togglePassword('password')" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 hover:text-emerald-500 transition-colors">
                                            <i class="fas fa-eye text-xs"></i>
                                        </button>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1 uppercase tracking-wide">Confirm Password</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                            <i class="fas fa-lock"></i>
                                        </div>
                                        <input type="password" name="password_confirmation" id="password_confirmation" required 
                                               class="w-full pl-10 pr-10 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all placeholder-gray-400 bg-gray-50/50 focus:bg-white"
                                               placeholder="Re-enter password">
                                        <button type="button" onclick="togglePassword('password_confirmation')" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 hover:text-emerald-500 transition-colors">
                                            <i class="fas fa-eye text-xs"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="pt-2">
                                <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-2.5 rounded-lg font-semibold shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-0.5 active:scale-[0.98] flex items-center justify-center">
                                    <i class="fas fa-arrow-right mr-2 text-sm"></i> Register as Guest
                                </button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const button = input.nextElementSibling;
            const icon = button.querySelector('i');
            
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
                button.classList.add("text-emerald-600");
            } else {
                input.type = "password";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
                button.classList.remove("text-emerald-600");
            }
        }

        function switchTab(tab) {
            // Hide all contents
            document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
            // Remove active state from all buttons
            document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
            
            // Show selected content and set button active
            if(tab === 'zou-client') {
                document.getElementById('tab-zou-client').classList.add('active');
                document.getElementById('tab-btn-zou').classList.add('active');
            } else {
                document.getElementById('tab-guest').classList.add('active');
                document.getElementById('tab-btn-guest').classList.add('active');
            }
            
            // Save choice to memory so it survives page reloads
            localStorage.setItem('activeRegisterTab', tab);
        }

        // Run when the page loads
        document.addEventListener('DOMContentLoaded', function() {
            // If Laravel caught an error from the guest form, force it open
            const hasGuestError = {{ old('is_guest') ? 'true' : 'false' }};
            
            if (hasGuestError) {
                switchTab('guest');
            } else {
                // Otherwise, remember whatever tab they were looking at last
                const savedTab = localStorage.getItem('activeRegisterTab');
                if (savedTab) {
                    switchTab(savedTab);
                }
            }
        });
    </script>
</body>
</html>
