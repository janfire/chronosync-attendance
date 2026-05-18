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
        /* Custom scrollbar for form area */
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
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4 bg-cover bg-center" style="background-image: url('{{ asset('images/background-pattern.png') }}');">
    
    <div class="w-full max-w-4xl">
        <div class="glass-panel rounded-2xl shadow-2xl overflow-hidden flex flex-col lg:flex-row max-h-[90vh]">
            
            <!-- Left Side: Branding -->
            <div class="lg:w-5/12 bg-[#0f2a1d] text-white p-8 relative overflow-hidden flex flex-col justify-between">
                <div class="absolute inset-0 opacity-10 bg-pattern"></div>
                
                <!-- Logo Section -->
                <div class="relative z-10">
                    <div class="flex items-center mb-8">
                        <div class="h-12 w-12 bg-emerald-500 rounded-xl flex items-center justify-center text-white shadow-lg shrink-0"><i class="fas fa-clock text-2xl"></i></div>
                        <div>
                            <h1 class="font-bold text-xl leading-tight">ChronoSync Attendance</h1>
                            <p class="text-blue-200 text-xs tracking-wider uppercase">Staff Portal</p>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <h2 class="text-3xl font-bold tracking-tight">Welcome <br/> to the Future.</h2>
                        <p class="text-emerald-100/80 leading-relaxed text-sm">
                            Access our secure biometric attendance system. Creating an account is the first step to seamless unified identity management.
                        </p>
                    </div>
                </div>

                <!-- Features List -->
                <div class="relative z-10 mt-8 space-y-4">
                    <div class="flex items-center text-emerald-100/90 text-sm">
                        <div class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center mr-3 backdrop-blur-sm">
                            <i class="fas fa-shield-alt text-xs"></i>
                        </div>
                        <span>Secure Enterprise Login</span>
                    </div>

                </div>

                <!-- Footer -->
                <div class="relative z-10 mt-8">
                    <p class="text-xs text-blue-300/60">&copy; {{ date('Y') }} Zimbabwe Open University</p>
                </div>
            </div>

            <!-- Right Side: Registration Form -->
            <div class="lg:w-7/12 bg-white flex flex-col relative h-full">
                <div class="p-8 overflow-y-auto custom-scroll h-full">
                    
                    <div class="mb-6">
                        <h2 class="text-2xl font-bold text-gray-800">Create Account</h2>
                        <p class="text-gray-500 text-sm mt-1">Register using your official ChronoSync credentials.</p>
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

                    @if(session('success'))
                        <div class="mb-6 bg-green-50 border-l-4 border-green-500 text-green-700 px-4 py-3 rounded-r-lg text-sm shadow-sm flex items-center">
                            <i class="fas fa-check-circle mr-3 text-lg"></i>
                            <div>{{ session('success') }}</div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('register') }}" class="space-y-5">
                        @csrf

                        <!-- Row 1: Name & ID -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">Full Name</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                        <i class="fas fa-user-circle"></i>
                                    </div>
                                    <input type="text" name="name" value="{{ old('name') }}" required 
                                           class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all placeholder-gray-400 bg-gray-50/50 focus:bg-white"
                                           placeholder="e.g. Tendai Moyo">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">Employee ID</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                        <i class="fas fa-id-card"></i>
                                    </div>
                                    <input type="text" name="employee_number" value="{{ old('employee_number') }}" required 
                                           class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all placeholder-gray-400 bg-gray-50/50 focus:bg-white"
                                           placeholder="e.g. ChronoSync00123">
                                </div>
                            </div>
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">ChronoSync Email Address</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <input type="email" name="email" value="{{ old('email') }}" required 
                                       class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all placeholder-gray-400 bg-gray-50/50 focus:bg-white"
                                       placeholder="username@example.com">
                            </div>
                            <p class="text-[10px] text-gray-400 mt-1 ml-1">Must be a valid organization email address ending in @example.com</p>
                        </div>

                        <!-- Row 2: Passwords -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">Password</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                        <i class="fas fa-lock"></i>
                                    </div>
                                    <input type="password" name="password" id="password" required 
                                           class="w-full pl-10 pr-10 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all placeholder-gray-400 bg-gray-50/50 focus:bg-white"
                                           placeholder="Min. 8 characters">
                                    <button type="button" onclick="togglePassword('password')" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 hover:text-emerald-500 transition-colors">
                                        <i class="fas fa-eye text-xs"></i>
                                    </button>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">Confirm Password</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                        <i class="fas fa-lock"></i>
                                    </div>
                                    <input type="password" name="password_confirmation" id="password_confirmation" required 
                                           class="w-full pl-10 pr-10 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all placeholder-gray-400 bg-gray-50/50 focus:bg-white"
                                           placeholder="Re-enter password">
                                    <button type="button" onclick="togglePassword('password_confirmation')" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 hover:text-emerald-500 transition-colors">
                                        <i class="fas fa-eye text-xs"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="pt-4">
                            <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-3 rounded-lg font-semibold shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-0.5 active:scale-[0.98] flex items-center justify-center">
                                <i class="fas fa-arrow-right mr-2 text-sm"></i> Create Account
                            </button>
                        </div>
                    </form>
                    


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
    </script>
</body>
</html>


