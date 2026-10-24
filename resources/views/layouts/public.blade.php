<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'EduTrack') - Student Portal</title>

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- FontAwesome 6 Icons CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
    @stack('styles')
</head>
<body class="h-full antialiased text-slate-100 flex flex-col justify-between selection:bg-indigo-500 selection:text-white">

    <!-- Top Minimal Public Bar -->
    <header class="no-print py-4 px-6 border-b border-slate-800/80 bg-slate-950/60 backdrop-blur-md sticky top-0 z-20">
        <div class="max-w-6xl mx-auto flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl gradient-brand flex items-center justify-center text-white shadow-md shadow-indigo-500/20">
                    <i class="fa-solid fa-graduation-cap"></i>
                </div>
                <span class="font-bold text-lg text-white tracking-tight">EduTrack</span>
            </a>

            <div class="flex items-center gap-4 text-sm font-medium">
                <a href="{{ route('public.lookup') }}" class="text-slate-300 hover:text-white transition-colors flex items-center gap-1.5">
                    <i class="fa-solid fa-id-card text-xs text-indigo-400"></i>
                    <span>Find My Pass</span>
                </a>
                <a href="{{ route('login') }}" class="px-4 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow transition-all">
                    Staff Portal
                </a>
            </div>
        </div>
    </header>

    <!-- Main Public Page -->
    <main class="flex-1 py-8 px-4 sm:px-6">
        @yield('content')
    </main>

    <!-- Public Footer -->
    <footer class="no-print py-6 text-center text-xs text-slate-500 border-t border-slate-800/60">
        <div class="max-w-6xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-2">
            <span>&copy; {{ date('Y') }} EduTrack Systems. All rights reserved.</span>
            <div class="flex items-center gap-4">
                <a href="{{ route('home') }}" class="hover:text-slate-400 transition-colors">Portal Home</a>
                <a href="{{ route('public.lookup') }}" class="hover:text-slate-400 transition-colors">Student Lookup</a>
                <a href="{{ route('login') }}" class="hover:text-slate-400 transition-colors">Teacher & Admin Sign In</a>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
