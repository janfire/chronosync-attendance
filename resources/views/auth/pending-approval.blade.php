<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5, user-scalable=yes">
    <title>Waiting for Approval - ChronoSync Attendance System</title>
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
            <!-- Pending Form -->
            <div class="w-full max-w-sm mx-auto bg-white flex flex-col justify-center p-8 relative items-center text-center">
                
                <div class="mb-4 text-center text-sm uppercase tracking-[0.32em] text-gray-400">ChronoSync</div>
                
                <div class="w-16 h-16 border-4 border-emerald-500 border-t-transparent rounded-full animate-spin mb-6"></div>

                <h2 class="text-xl font-bold text-gray-800 mb-2">Check Your Email</h2>
                <p class="text-gray-500 text-sm mb-6">
                    We sent a secure login link to your email address. Please click "Approve Login" in the email to continue.
                </p>

                <p class="text-xs text-gray-400">
                    This page will automatically redirect once approved. Do not close this tab.
                </p>
                
                <div class="mt-8">
                    <a href="{{ route('login') }}" class="text-emerald-600 text-sm hover:underline font-medium">Cancel Login</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function() {
            const checkInterval = 3000; // 3 seconds
            
            function checkApprovalStatus() {
                fetch("{{ route('login.check-approval') }}")
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'approved' && data.redirect_url) {
                            window.location.href = data.redirect_url;
                        } else if (data.status === 'expired') {
                            window.location.href = "{{ route('login') }}";
                        }
                        // if pending, do nothing and wait for next interval
                    })
                    .catch(error => {
                        console.error('Error checking approval status:', error);
                    });
            }

            setInterval(checkApprovalStatus, checkInterval);
        })();
    </script>
</body>
</html>
