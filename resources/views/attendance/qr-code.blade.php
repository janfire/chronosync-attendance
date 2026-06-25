<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5, user-scalable=yes">
    <title>Attendance QR Code - ChronoSync Attendance System</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        /* Touch-friendly */
        a, button {
            touch-action: manipulation;
        }
        /* Smooth transitions */
        * {
            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen flex items-center justify-center p-2 sm:p-4 overflow-y-auto">

    <div class="max-w-xs sm:max-w-sm w-full card-container py-2 sm:py-4">
        <!-- Card Wrapper -->
        <div class="bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden transform transition duration-300 hover:scale-[1.01]">

            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-700 to-blue-500 p-2 text-center text-white">
                <div class="flex justify-center mb-0.5">
                    <div class="relative h-12 w-12 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-700 flex items-center justify-center shadow-lg border border-white/10 overflow-hidden shrink-0">
                        <svg class="w-7 h-7 text-white relative z-10 drop-shadow-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 19 A 8 8 0 1 1 16 5" stroke-opacity="0.5" />
                            <path d="M16 8 A 3.5 3.5 0 0 0 9 8 C 9 12 16 11 16 15 A 3.5 3.5 0 0 1 9 15" />
                        </svg>
                    </div>
                </div>
                <p class="text-xs opacity-90">Staff Attendance System</p>
            </div>

            <!-- Content -->
            <div class="p-2">

                <h2 class="text-center text-gray-800 font-semibold text-sm mb-2 flex items-center justify-center gap-2">
                    <i class="fas fa-qrcode text-emerald-600"></i>
                    Scan to Clock In/Out
                </h2>

                <!-- QR Code -->
                <div class="flex justify-center mb-3">
                    <div class="p-2 bg-white rounded-xl border border-gray-300 shadow-sm">
                        {!! $qrCode !!}
                    </div>
                </div>

                <!-- Steps -->
                <div class="space-y-1.5 mb-3">
                    <div class="flex items-start gap-2">
                        <span class="bg-emerald-600 text-white text-xs w-5 h-5 flex items-center justify-center rounded-full font-bold">1</span>
                        <p class="text-gray-700 text-sm">Scan QR code</p>
                    </div>
                </div>

                <!-- Alternative Link -->
                <div class="pt-2 border-t border-gray-300 text-center">
                    <p class="text-gray-600 text-xs sm:text-sm mb-1.5 flex items-center justify-center gap-1">
                        <i class="fas fa-info-circle text-emerald-600"></i>
                        Can't scan?
                    </p>
                    <a href="{{ route('attendance.clock') }}" class="inline-flex items-center gap-2 px-4 py-2 sm:px-3 sm:py-1 bg-emerald-600 text-white text-xs sm:text-sm rounded-lg shadow-lg hover:bg-emerald-700 active:bg-gray-800 active:scale-[0.98] transition duration-200 touch-manipulation">
                        <i class="fas fa-external-link-alt"></i>
                        Open Clock
                    </a>
                    
                    <div class="mt-3 pt-2 border-t border-gray-100">
                        <a href="{{ route('login') }}" class="text-xs text-emerald-600 hover:text-emerald-800 hover:underline">
                            Staff Login (Enroll Face)
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <p class="text-center text-gray-600 text-xs mt-3">Enable camera for facial recognition</p>
    </div>


<style>
/* Responsive fine-tuning */
@media (max-width: 640px) {
    .qr-wrapper { padding: 1rem !important; }
    .header-title { font-size: 1rem !important; }
    .header-sub { font-size: 0.75rem !important; }
}

@media (min-width: 1024px) {
    .card-container { max-width: 28rem !important; }
}
</style>
</body>
</html>


