<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, user-scalable=yes">
    <title>Complete Staff Profile - ChronoSync</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    <style>
        body { font-family: 'Instrument Sans', sans-serif; }
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
    
    <div class="w-full max-w-lg">
        <div class="glass-panel rounded-2xl shadow-2xl overflow-hidden relative">
            
            <div class="bg-[#0f2a1d] text-white p-6 relative overflow-hidden text-center">
                <div class="absolute inset-0 opacity-10 bg-pattern"></div>
                <div class="relative z-10">
                    <h1 class="font-bold text-xl">ChronoSync Staff Portal</h1>
                    <p class="text-sm opacity-80 mt-1">Almost there! Complete your profile.</p>
                </div>
            </div>

            <div class="p-8 bg-white">
                <div class="mb-6 text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 bg-emerald-100 text-emerald-600 rounded-full mb-3">
                        <i class="fas fa-id-card text-xl"></i>
                    </div>
                    <h2 class="text-xl font-bold text-gray-800">Verify Employee ID</h2>
                    <p class="text-sm text-gray-500 mt-1">
                        Welcome <strong>{{ session('google_registration')['name'] ?? 'Staff' }}</strong>! <br>
                        Please provide your official staff ID to finalize your account creation.
                    </p>
                </div>

                @if($errors->any())
                    <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 px-4 py-3 rounded-r-lg text-sm shadow-sm">
                        <ul class="list-disc list-inside ml-2 space-y-1 text-xs">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('auth.staff.complete') }}" class="space-y-5">
                    @csrf
                    
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">Employee ID Number</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <i class="fas fa-hashtag"></i>
                            </div>
                            <input type="text" name="employee_number" value="{{ old('employee_number') }}" required autofocus
                                   class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all placeholder-gray-400 bg-gray-50/50 focus:bg-white"
                                   placeholder="e.g. EMP-98765">
                        </div>
                        <p class="text-xs text-gray-400 mt-2">
                            This ID will be permanently linked to your Google account ({{ session('google_registration')['email'] ?? '' }}).
                        </p>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-3 rounded-lg font-semibold shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-0.5 active:scale-[0.98] flex items-center justify-center">
                            Continue to Enrollment <i class="fas fa-arrow-right ml-2 text-sm"></i>
                        </button>
                    </div>
                </form>
            </div>
            
        </div>
    </div>
</body>
</html>
