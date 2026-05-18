<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Expired - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #0f2a1d; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-2xl overflow-hidden p-8 text-center">
        <div class="w-20 h-20 bg-rose-100 text-rose-600 rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="fas fa-calendar-times text-4xl"></i>
        </div>
        
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Subscription Expired</h1>
        <p class="text-gray-600 mb-8">
            Your access to <strong>{{ $tenant->company_name }}</strong> has been suspended due to an expired subscription.
        </p>

        <div class="bg-gray-50 rounded-xl p-6 mb-8 text-left">
            <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-4">How to Renew</h2>
            <ul class="space-y-4">
                <li class="flex items-start">
                    <div class="w-6 h-6 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mr-3 shrink-0">1</div>
                    <p class="text-sm text-gray-700">Check your latest invoice in the billing portal.</p>
                </li>
                <li class="flex items-start">
                    <div class="w-6 h-6 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mr-3 shrink-0">2</div>
                    <p class="text-sm text-gray-700">Send payment via <strong>EcoCash</strong> or <strong>ZIPIT</strong>.</p>
                </li>
                <li class="flex items-start">
                    <div class="w-6 h-6 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mr-3 shrink-0">3</div>
                    <p class="text-sm text-gray-700">Upload your proof of payment reference.</p>
                </li>
            </ul>
        </div>

        <div class="space-y-4">
            <a href="{{ route('billing.index') }}" class="block w-full py-3 px-4 bg-[#0f2a1d] text-white font-semibold rounded-lg hover:bg-opacity-90 transition-all shadow-lg">
                Go to Billing Portal
            </a>
            
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="text-sm text-gray-500 hover:text-gray-700 font-medium">
                    Sign out
                </button>
            </form>
        </div>

        <p class="mt-8 text-xs text-gray-400">
            If you have already paid, please allow up to 2 hours for our finance team to confirm your transaction.
        </p>
    </div>
</body>
</html>
