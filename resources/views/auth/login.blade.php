<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5, user-scalable=yes">
    <title>Admin Login - ChronoSync Attendance System</title>
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
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4 bg-cover bg-center" style="background-image: url('{{ asset('images/background-pattern.png') }}');">
    
    <div class="w-full max-w-4xl">
        <div class="glass-panel rounded-2xl shadow-2xl overflow-hidden flex flex-col lg:flex-row h-auto min-h-[600px]">
            
            <!-- Left Side: Branding -->
            <div class="lg:w-5/12 bg-[#0f2a1d] text-white p-8 relative overflow-hidden flex flex-col justify-between">
                <div class="absolute inset-0 opacity-10 bg-pattern"></div>
                
                <!-- Logo Section -->
                <div class="relative z-10">
                    <div class="flex items-center mb-8">
                        <div class="h-12 w-12 bg-emerald-500 rounded-xl flex items-center justify-center text-white shadow-lg shrink-0"><i class="fas fa-clock text-2xl"></i></div>
                        <div>
                            <h1 class="font-bold text-xl leading-tight">ChronoSync Attendance</h1>
                            <p class="text-blue-200 text-xs tracking-wider uppercase">Admin Portal</p>
                        </div>
                    </div>

                    <div class="space-y-6 mt-12">
                        <h2 class="text-3xl font-bold tracking-tight">Access <br/> Control Center.</h2>
                        <p class="text-emerald-100/80 leading-relaxed text-sm">
                            Manage users, view insights, and configure the attendance system from one secure dashboard.
                        </p>
                    </div>
                </div>

                <!-- Footer -->
                <div class="relative z-10 mt-8">
                    <p class="text-xs text-blue-300/60">&copy; {{ date('Y') }} Zimbabwe Open University</p>
                </div>
            </div>

            <!-- Right Side: Login Form -->
            <div class="lg:w-7/12 bg-white flex flex-col justify-center p-8 lg:p-12 relative">
                
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-800">Welcome Back</h2>
                    <p class="text-gray-500 text-sm mt-1">Please enter your credentials to continue.</p>
                </div>

                @if($errors->any())
                    <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 px-4 py-3 rounded-r-lg text-sm shadow-sm flex items-start">
                        <i class="fas fa-exclamation-triangle mr-3 mt-0.5"></i>
                        <div>{{ $errors->first() }}</div>
                    </div>
                @endif

                @if(session('success'))
                    <div class="mb-6 bg-green-50 border-l-4 border-green-500 text-green-700 px-4 py-3 rounded-r-lg text-sm shadow-sm flex items-center">
                        <i class="fas fa-check-circle mr-3"></i>
                        <div>{{ session('success') }}</div>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf

                    <!-- Email -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">Email Address</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <input type="email" name="email" value="{{ old('email') }}" required autofocus
                                   class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all placeholder-gray-400 bg-gray-50/50 focus:bg-white"
                                   placeholder="admin@example.com">
                        </div>
                    </div>

                    <!-- Password -->
                    <div>
                        <div class="flex justify-between items-center mb-1.5">
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wide">Password</label>
                            @if (Route::has('password.request'))
                                <a class="text-xs text-emerald-600 hover:text-emerald-800" href="{{ route('password.request') }}">
                                    Forgot Password?
                                </a>
                            @endif
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <i class="fas fa-lock"></i>
                            </div>
                            <input type="password" name="password" id="password" required 
                                   class="w-full pl-10 pr-10 py-3 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all placeholder-gray-400 bg-gray-50/50 focus:bg-white"
                                   placeholder="Enter your password">
                            <button type="button" onclick="togglePassword('password')" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 hover:text-emerald-500 transition-colors">
                                <i class="fas fa-eye text-xs"></i>
                            </button>
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-3 rounded-lg font-semibold shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-0.5 active:scale-[0.98] flex items-center justify-center">
                            <i class="fas fa-sign-in-alt mr-2 text-sm"></i> Sign In
                        </button>
                    </div>
                </form>
                
                <div class="mt-8 text-center border-t border-gray-100 pt-6">
                    <p class="text-sm text-gray-500">Don't have an admin account?</p>
                    <a href="{{ route('register') }}" class="inline-flex items-center mt-2 text-emerald-600 font-semibold text-sm hover:text-emerald-800 transition-colors">
                        Register as Staff <i class="fas fa-arrow-right text-xs ml-1"></i>
                    </a>
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


