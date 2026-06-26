<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server Error - Zou Attendance</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen p-4">
    <div class="max-w-md w-full text-center">
        <!-- Illustration -->
        <div class="mb-8 flex justify-center">
            <div class="h-24 w-24 bg-red-100 rounded-full flex items-center justify-center text-red-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
        </div>

        <h1 class="text-3xl font-bold text-gray-900 mb-2">Oops! We hit a snag.</h1>
        <p class="text-gray-500 mb-6">
            Something unexpected happened on our end. Don't worry, our technical team has already been automatically notified of this issue.
        </p>

        <div class="bg-white border border-gray-200 rounded-lg p-4 mb-8 shadow-sm">
            <p class="text-sm text-gray-500 uppercase tracking-wider font-semibold mb-1">Error Tracking Code</p>
            <p class="text-lg font-mono text-gray-900 select-all">{{ $reference_code ?? 'ERR-UNKNOWN' }}</p>
        </div>

        <a href="{{ url('/') }}" class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 transition duration-150">
            Return to Dashboard
        </a>
        
        <p class="mt-8 text-sm text-gray-400">
            If this problem persists, please contact support and provide your tracking code.
        </p>
    </div>
</body>
</html>
