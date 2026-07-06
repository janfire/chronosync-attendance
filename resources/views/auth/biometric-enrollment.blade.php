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
    
    <!-- MediaPipe Face Mesh for Liveness Detection -->
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/camera_utils/camera_utils.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/face_mesh/face_mesh.js" crossorigin="anonymous"></script>
    <script src="{{ asset('js/liveness-detector.js') }}"></script>
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
    
    <!-- Container properly sized and scaled down to 75% for sleek look -->
    <div class="w-full max-w-md" style="zoom: 0.75; -moz-transform: scale(0.75); -moz-transform-origin: center center;">
        <div class="glass-panel rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[95vh]">
            
            <div class="w-full bg-white flex flex-col relative flex-1 min-h-0">
                <div class="p-4 sm:p-6 overflow-y-auto custom-scroll h-full">
                    
                    <!-- Header -->
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
                        <h2 class="text-xl font-bold text-emerald-600">Biometric Setup</h2>
                    </div>

                    <!-- Display Messages -->
                    @include('auth.partials.messages')

                    <!-- Method Selector -->
                    <div class="mb-6 bg-gray-50 border border-gray-100 p-1.5 rounded-xl flex text-sm font-medium shadow-sm">
                        <button type="button" id="btn-method-facial" class="flex-1 py-2 px-3 rounded-lg bg-white text-emerald-600 shadow-sm border border-gray-200 transition-all font-semibold flex items-center justify-center">
                            <i class="fas fa-camera mr-2"></i> Facial
                        </button>
                        <button type="button" id="btn-method-fingerprint" class="flex-1 py-2 px-3 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-white/50 transition-all flex items-center justify-center">
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
                    <div id="enrollment-choice" class="hidden text-center py-8">
                        <div class="mb-6">
                            <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-green-50 mb-4 border-4 border-green-100 animate-bounce">
                                <i class="fas fa-check text-3xl text-green-500"></i>
                            </div>
                            <h2 class="text-xl font-bold text-gray-800 mb-2">Facial Data Captured!</h2>
                            <p class="text-gray-500 text-sm max-w-xs mx-auto">The face has been successfully enrolled. You can now add a fingerprint for extra security.</p>
                        </div>
                        
                        <div class="flex flex-col gap-3">
                            <button id="btn-choice-fingerprint" class="w-full py-2.5 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 font-semibold shadow-lg shadow-emerald-200 flex items-center justify-center transition-transform hover:-translate-y-0.5">
                                <i class="fas fa-fingerprint mr-2"></i> Enroll Fingerprint
                            </button>
                            
                            <button id="btn-choice-skip" class="w-full py-2.5 bg-white text-gray-600 rounded-lg hover:bg-gray-50 font-medium border border-gray-200 shadow-sm flex items-center justify-center transition-colors">
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

