<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Get Started - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f9fafb; /* Light gray background */
            position: relative;
        }
        .bg-pattern {
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%230f2a1d' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            position: absolute;
            inset: 0;
            z-index: -1;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-6">
    <div class="bg-pattern"></div>
    <div class="max-w-4xl w-full flex rounded-3xl overflow-hidden shadow-[0_32px_64px_-16px_rgba(0,0,0,0.1)] bg-white min-h-[600px] relative z-10">
        <!-- Left Side: Branding/Marketing -->
        <div class="hidden lg:flex lg:w-1/3 bg-[#0f2a1d] p-12 flex-col justify-between text-white relative overflow-hidden">
            <div class="relative z-10">
                <div class="w-12 h-12 bg-emerald-500 rounded-xl flex items-center justify-center mb-8 shadow-lg shadow-emerald-500/20">
                    <i class="fas fa-clock text-xl"></i>
                </div>
                <h2 class="text-3xl font-extrabold leading-tight mb-4">Start your 14-day free trial.</h2>
                <p class="text-emerald-200/70 text-sm leading-relaxed">
                    Join hundreds of Zimbabwean businesses automating their attendance with facial recognition.
                </p>
            </div>

            <div class="space-y-6 relative z-10">
                <div class="flex items-center space-x-4">
                    <div class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center">
                        <i class="fas fa-check text-xs text-emerald-400"></i>
                    </div>
                    <span class="text-xs font-medium text-white/80">No credit card required</span>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center">
                        <i class="fas fa-check text-xs text-emerald-400"></i>
                    </div>
                    <span class="text-xs font-medium text-white/80">Unlimited employees (Corporate)</span>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center">
                        <i class="fas fa-check text-xs text-emerald-400"></i>
                    </div>
                    <span class="text-xs font-medium text-white/80">Pay via EcoCash/ZIPIT</span>
                </div>
            </div>

            <!-- Abstract Background Shapes -->
            <div class="absolute -bottom-20 -left-20 w-64 h-64 bg-emerald-500/10 rounded-full blur-3xl"></div>
            <div class="absolute -top-20 -right-20 w-64 h-64 bg-emerald-500/10 rounded-full blur-3xl"></div>
        </div>

        <!-- Right Side: Form -->
        <div class="w-full lg:w-2/3 p-8 lg:p-12 overflow-y-auto">
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Create your workspace</h1>
                <p class="text-gray-500 text-sm">Enter your company details to get started.</p>
            </div>

            @if ($errors->any())
                <div class="mb-6 p-4 bg-rose-50 border border-rose-100 text-rose-700 rounded-xl text-sm animate-shake">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('onboarding.register') }}" method="POST" class="space-y-6">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Company Info -->
                    <div class="space-y-4">
                        <h3 class="text-xs font-bold text-emerald-600 uppercase tracking-widest">Company Details</h3>
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 mb-1">Company Name</label>
                            <input type="text" name="company_name" value="{{ old('company_name') }}" required placeholder="e.g. Acme Zimbabwe" class="w-full p-3 bg-gray-50 border @error('company_name') border-rose-500 @else border-gray-100 @enderror rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 mb-1">Desired Subdomain</label>
                            <div class="flex items-center">
                                <input type="text" name="subdomain" value="{{ old('subdomain') }}" required placeholder="acme" class="flex-1 p-3 bg-gray-50 border @error('subdomain') border-rose-500 @else border-gray-100 @enderror rounded-l-xl text-sm focus:ring-2 focus:ring-emerald-500 outline-none transition-all">
                                <span class="bg-gray-100 border-y border-r border-gray-100 p-3 text-sm text-gray-500 rounded-r-xl">.attenda.zw</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 mb-1">Company Email</label>
                            <input type="email" name="email" value="{{ old('email') }}" required placeholder="contact@acme.co.zw" class="w-full p-3 bg-gray-50 border @error('email') border-rose-500 @else border-gray-100 @enderror rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 outline-none transition-all">
                        </div>
                    </div>

                    <!-- Admin Info -->
                    <div class="space-y-4">
                        <h3 class="text-xs font-bold text-emerald-600 uppercase tracking-widest">Account Admin</h3>
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 mb-1">Full Name</label>
                            <input type="text" name="admin_name" value="{{ old('admin_name') }}" required placeholder="John Doe" class="w-full p-3 bg-gray-50 border @error('admin_name') border-rose-500 @else border-gray-100 @enderror rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 mb-1">Admin Email</label>
                            <input type="email" name="admin_email" value="{{ old('admin_email') }}" required placeholder="john@acme.co.zw" class="w-full p-3 bg-gray-50 border @error('admin_email') border-rose-500 @else border-gray-100 @enderror rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 mb-1">Password</label>
                            <input type="password" name="password" required class="w-full p-3 bg-gray-50 border border-gray-100 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 mb-1">Confirm Password</label>
                            <input type="password" name="password_confirmation" required class="w-full p-3 bg-gray-50 border border-gray-100 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 outline-none transition-all">
                        </div>
                    </div>
                </div>

                <div class="pt-6 border-t border-gray-50">
                    <button type="submit" class="w-full py-4 bg-[#0f2a1d] text-white font-bold rounded-2xl shadow-xl shadow-emerald-900/10 hover:bg-emerald-900 transform hover:-translate-y-0.5 transition-all">
                        Create Workspace
                    </button>
                    <p class="text-[10px] text-gray-400 text-center mt-4 px-8 leading-relaxed">
                        By clicking "Create Workspace", you agree to our <a href="#" class="underline">Terms of Service</a> and <a href="#" class="underline">Privacy Policy</a>. No payment information is required to start your trial.
                    </p>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
