<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5, user-scalable=yes">
    <title>Login Approval - ChronoSync Attendance System</title>
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
            <div class="w-full max-w-sm mx-auto bg-white flex flex-col justify-center p-8 relative items-center text-center">
                
                <div class="mb-4 text-center text-sm uppercase tracking-[0.32em] text-gray-400">ChronoSync</div>
                
                @if($success)
                    <div class="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mb-6">
                        <i class="fas fa-check text-2xl text-emerald-600"></i>
                    </div>
                    <h2 class="text-xl font-bold text-gray-800 mb-2">Login Approved</h2>
                    <p class="text-gray-500 text-sm mb-6">{{ $message }}</p>
                @else
                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mb-6">
                        <i class="fas fa-times text-2xl text-red-600"></i>
                    </div>
                    <h2 class="text-xl font-bold text-gray-800 mb-2">Approval Failed</h2>
                    <p class="text-gray-500 text-sm mb-6">{{ $message }}</p>
                @endif
                
            </div>
        </div>
    </div>
</body>
</html>
