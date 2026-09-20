<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5, user-scalable=yes">
    <title>Set Up Password - ChronoSync Attendance System</title>
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
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4 bg-cover bg-center" style="background-image: url('{{ asset('images/background-pattern.png') }}');">
    
    <div class="w-full max-w-sm">
        <div class="glass-panel rounded-2xl shadow-2xl overflow-hidden flex flex-col h-auto">
            <!-- Setup Form -->
            <div class="w-full max-w-sm mx-auto bg-white flex flex-col justify-center p-5 relative">
                
                <div class="mb-4 text-center text-sm uppercase tracking-[0.32em] text-gray-400">ChronoSync</div>
                <div class="mb-6 text-center">
                    <h2 class="text-xl font-bold text-gray-800">Set Up Password</h2>
                    <p class="text-gray-500 text-sm mt-1">Please enter your new password to secure your account.</p>
                </div>

                @if($errors->any())
                    <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 px-4 py-3 rounded-r-lg text-sm shadow-sm flex items-start">
                        <i class="fas fa-exclamation-triangle mr-3 mt-0.5"></i>
                        <div>{{ $errors->first() }}</div>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.update') }}" class="space-y-6">
                    @csrf

                    <input type="hidden" name="token" value="{{ $token }}">

                    <!-- Email -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">Email Address</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <input type="email" name="email" value="{{ $email ?? old('email') }}" required readonly
                                   class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all text-gray-500 bg-gray-100 cursor-not-allowed">
                        </div>
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">New Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <i class="fas fa-lock"></i>
                            </div>
                            <input type="password" name="password" id="password" required autofocus
                                   class="w-full pl-10 pr-10 py-3 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all placeholder-gray-400 bg-gray-50/50 focus:bg-white"
                                   placeholder="Create a strong password">
                            <button type="button" onclick="togglePassword('password')" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 hover:text-emerald-500 transition-colors">
                                <i class="fas fa-eye text-xs"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">Confirm Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <input type="password" name="password_confirmation" id="password_confirmation" required 
                                   class="w-full pl-10 pr-10 py-3 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all placeholder-gray-400 bg-gray-50/50 focus:bg-white"
                                   placeholder="Confirm your new password">
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-3 rounded-lg font-semibold shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-0.5 active:scale-[0.98] flex items-center justify-center">
                            <i class="fas fa-save mr-2 text-sm"></i> Save Password
                        </button>
                    </div>
                </form>

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
