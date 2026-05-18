<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5, user-scalable=yes">
    <title>Biometric Enrollment - ChronoSync Attendance System</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Scripts & Styles -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    
    <!-- Local assets via Vite -->
    @vite(['resources/css/app.css', 'resources/js/biometric-enrollment.js'])

    <script>
        window.biometricStatusUrl = '{{ route("biometric.status") }}';
        window.routes = {
            facialEnroll: '{{ route("biometric.facial.enroll") }}',
            fingerprintEnroll: '{{ route("biometric.fingerprint.enroll") }}', 
            registrationOptions: '{{ route("biometric.registration.options") }}', 
            verifyRegistration: '{{ route("biometric.verify.registration") }}', 
            complete: '{{ route("biometric.complete") }}'
        };
        window.baseUrl = '{{ url("/") }}';
    </script>
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

        .compact-form {
            max-height: 90vh;
        }
        @media (max-width: 640px) {
            video {
                max-height: 50vh;
                object-fit: cover;
            }
            .compact-form {
                max-height: 100vh;
            }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4 bg-cover bg-center" style="background-image: url('{{ asset('images/background-pattern.png') }}');">
    
    <div class="w-full max-w-5xl">
        <div class="glass-panel rounded-2xl shadow-2xl overflow-hidden flex flex-col lg:flex-row compact-form mx-auto">

            <!-- Left Side - Progress & Instructions -->
            @include('auth.partials.biometric-progress')

            <!-- Right Side - Biometric Capture -->
            <div class="lg:w-7/12 bg-white flex flex-col relative h-full">
                <div class="p-6 sm:p-8 overflow-y-auto custom-scroll h-full">
                    
                    <!-- Display Messages -->
                    @include('auth.partials.messages')

                    <!-- Method Selector -->
                    <div class="mb-8 bg-gray-50 border border-gray-100 p-1.5 rounded-xl flex text-sm font-medium shadow-sm">
                        <button type="button" id="btn-method-facial" class="flex-1 py-2.5 px-4 rounded-lg bg-white text-emerald-600 shadow-sm border border-gray-200 transition-all font-semibold flex items-center justify-center">
                            <i class="fas fa-camera mr-2"></i> Facial Recognition
                        </button>
                        <button type="button" id="btn-method-fingerprint" class="flex-1 py-2.5 px-4 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-white/50 transition-all flex items-center justify-center">
                            <i class="fas fa-fingerprint mr-2"></i> Fingerprint
                        </button>
                    </div>

                    <!-- Facial Recognition Section -->
                    @include('auth.partials.facial-section')

                    <!-- Fingerprint Section -->
                    @include('auth.partials.fingerprint-section')

                    <!-- Completion Section -->
                    @include('auth.partials.enrollment-complete')

                    <!-- Choice Modal (Hidden by default) -->
                    <div id="enrollment-choice" class="hidden text-center py-12">
                        <div class="mb-8">
                            <div class="mx-auto flex items-center justify-center h-24 w-24 rounded-full bg-green-50 mb-6 border-4 border-green-100 animate-bounce">
                                <i class="fas fa-check text-4xl text-green-500"></i>
                            </div>
                            <h2 class="text-2xl font-bold text-gray-800 mb-2">Facial Data Captured!</h2>
                            <p class="text-gray-500 max-w-xs mx-auto">The face has been successfully enrolled. You can now add a fingerprint for extra security.</p>
                        </div>
                        
                        <div class="flex flex-col sm:flex-row justify-center gap-4">
                            <button id="btn-choice-fingerprint" class="px-6 py-3 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 font-semibold shadow-lg shadow-emerald-200 flex items-center justify-center transition-transform hover:-translate-y-0.5">
                                <i class="fas fa-fingerprint mr-2"></i> Enroll Fingerprint
                            </button>
                            
                            <button id="btn-choice-skip" class="px-6 py-3 bg-white text-gray-600 rounded-lg hover:bg-gray-50 font-medium border border-gray-200 shadow-sm flex items-center justify-center transition-colors">
                                Skip & Finish <i class="fas fa-arrow-right ml-2 text-xs"></i>
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</body>
</html>

