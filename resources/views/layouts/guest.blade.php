<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'ChronoSync')</title>
    
    <!-- Global Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    
    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        body { font-family: 'Instrument Sans', sans-serif; }
    </style>
    
    <!-- Additional page-specific styles -->
    @stack('styles')
</head>
<body class="@yield('body-class', 'bg-gray-50 text-gray-800') antialiased">
    
    <!-- Page Content injected here -->
    @yield('content')
    
    <!-- Additional page-specific scripts -->
    @stack('scripts')
</body>
</html>
