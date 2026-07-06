<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, user-scalable=yes">
    <title>Verify Email - ChronoSync Attendance System</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Scripts & Styles -->
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body {
            font-family: 'Instrument Sans', sans-serif;
        }
        .glass-panel {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        /* OTP Input Styles */
        .otp-input {
            width: 3rem;
            height: 3.5rem;
            text-align: center;
            font-size: 1.5rem;
            font-weight: 700;
            border-radius: 0.5rem;
            border: 2px solid #e5e7eb;
            background-color: #f9fafb;
            color: #059669;
            transition: all 0.2s ease;
        }
        .otp-input:focus {
            border-color: #10b981;
            background-color: #ffffff;
            outline: none;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
        }
        /* Hide arrows from number input */
        .otp-input::-webkit-outer-spin-button,
        .otp-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4 bg-cover bg-center" style="background-image: url('{{ asset('images/background-pattern.png') }}');">
    
    <div class="w-full max-w-md">
        <div class="glass-panel rounded-2xl shadow-2xl overflow-hidden flex flex-col">

            <!-- Content -->
            <div class="w-full bg-white flex flex-col relative flex-1 p-8">
                
                <div class="mb-6 text-center">
                    <div class="flex justify-center items-center space-x-3 mb-6">
                        <!-- Logo Icon -->
                        <div class="w-12 h-12 bg-emerald-500 rounded-xl shadow-lg flex items-center justify-center relative overflow-hidden shrink-0">
                            <span class="text-white/40 font-bold text-3xl absolute -left-0.5">C</span>
                            <span class="text-white font-bold text-2xl z-10 relative left-1">S</span>
                        </div>
                    </div>
                    <h2 class="text-2xl font-bold text-emerald-600">Verify your Email</h2>
                    <p class="text-gray-500 text-sm mt-2">
                        We've sent a 6-digit verification code to <br>
                        <span class="font-bold text-gray-800">{{ session('unverified_registration')['email'] ?? 'your email address' }}</span>
                    </p>
                </div>

                @if($errors->any())
                    <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 px-4 py-3 rounded-r-lg text-sm shadow-sm flex items-center">
                        <i class="fas fa-exclamation-circle mr-3 text-lg"></i>
                        <div>{{ $errors->first() }}</div>
                    </div>
                @endif

                @if(session('success'))
                    <div class="mb-6 bg-green-50 border-l-4 border-green-500 text-green-700 px-4 py-3 rounded-r-lg text-sm shadow-sm flex items-center">
                        <i class="fas fa-check-circle mr-3 text-lg"></i>
                        <div>{{ session('success') }}</div>
                    </div>
                @endif

                <form method="POST" action="{{ route('register.verify-otp') }}" id="otp-form" class="space-y-6">
                    @csrf
                    <input type="hidden" name="otp" id="hidden-otp">
                    
                    <div class="flex justify-between gap-2 max-w-[320px] mx-auto" id="otp-container">
                        <input type="number" class="otp-input" maxlength="1" autocomplete="off" autofocus>
                        <input type="number" class="otp-input" maxlength="1" autocomplete="off">
                        <input type="number" class="otp-input" maxlength="1" autocomplete="off">
                        <input type="number" class="otp-input" maxlength="1" autocomplete="off">
                        <input type="number" class="otp-input" maxlength="1" autocomplete="off">
                        <input type="number" class="otp-input" maxlength="1" autocomplete="off">
                    </div>

                    <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-3 rounded-lg font-semibold shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-0.5 active:scale-[0.98] flex items-center justify-center mt-6">
                        <i class="fas fa-check-circle mr-2 text-sm"></i> Verify & Continue
                    </button>
                </form>

                <form method="POST" action="{{ route('register.resend-otp') }}" class="mt-6 text-center">
                    @csrf
                    <p class="text-sm text-gray-500">
                        Didn't receive the code? 
                        <button type="submit" id="resend-btn" class="text-emerald-600 font-semibold hover:text-emerald-700 transition-colors ml-1">
                            Resend Code
                        </button>
                    </p>
                </form>

            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const inputs = document.querySelectorAll(".otp-input");
            const form = document.getElementById("otp-form");
            const hiddenOtp = document.getElementById("hidden-otp");

            inputs.forEach((input, index) => {
                // Prevent scrolling to change number
                input.addEventListener('wheel', function(e) {
                    e.preventDefault();
                });

                input.addEventListener("input", function(e) {
                    const val = e.target.value;
                    
                    if (val.length > 1) {
                        e.target.value = val.slice(0, 1); // keep only first char
                    }
                    
                    if (val !== "") {
                        if (index < inputs.length - 1) {
                            inputs[index + 1].focus();
                        } else {
                            // Automatically submit when the last digit is entered
                            updateHiddenOtp();
                            form.submit();
                        }
                    }
                    updateHiddenOtp();
                });

                input.addEventListener("keydown", function(e) {
                    if (e.key === "Backspace" && e.target.value === "") {
                        if (index > 0) {
                            inputs[index - 1].focus();
                            inputs[index - 1].value = "";
                        }
                    }
                });

                // Handle pasting
                input.addEventListener("paste", function(e) {
                    e.preventDefault();
                    const pastedData = e.clipboardData.getData("text").trim();
                    if (/^\d{6}$/.test(pastedData)) {
                        for (let i = 0; i < 6; i++) {
                            inputs[i].value = pastedData[i];
                        }
                        updateHiddenOtp();
                        form.submit();
                    }
                });
            });

            function updateHiddenOtp() {
                let otp = "";
                inputs.forEach(input => {
                    otp += input.value;
                });
                hiddenOtp.value = otp;
            }
        });
    </script>
</body>
</html>
