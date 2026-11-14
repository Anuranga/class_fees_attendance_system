<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'EduTrack') - Student Portal</title>

    <!-- Theme Initialization Script (Prevents flash of wrong theme) -->
    <script>
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            // Default to cheerful, friendly Light Theme for pre-school
            document.documentElement.classList.remove('dark');
        }
    </script>

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
<body class="h-full antialiased bg-slate-50 text-slate-800 dark:bg-slate-900 dark:text-slate-100 flex flex-col justify-between selection:bg-indigo-500 selection:text-white transition-colors duration-200">

    <!-- Top Minimal Public Bar -->
    <header class="no-print py-4 px-4 sm:px-6 border-b border-slate-200/80 dark:border-slate-800/80 bg-white/90 dark:bg-slate-950/70 backdrop-blur-md sticky top-0 z-20 transition-colors">
        <div class="max-w-6xl mx-auto flex items-center justify-between gap-3">
            <a href="{{ route('home') }}" class="flex items-center gap-3 shrink-0">
                <div class="w-9 h-9 rounded-xl gradient-brand flex items-center justify-center text-white shadow-md shadow-indigo-500/20">
                    <i class="fa-solid fa-shapes"></i>
                </div>
                <div>
                    @if(isset($school) && request()->routeIs('public.apply*') && !empty($school->name))
                        <span class="font-bold text-base sm:text-lg text-slate-900 dark:text-white tracking-tight">{{ $school->name }}</span>
                    @else
                        <span class="font-extrabold text-base sm:text-lg text-slate-900 dark:text-white tracking-tight">SmartPreSchool <span class="text-indigo-600 dark:text-indigo-400 font-semibold text-xs sm:text-sm">Hub</span></span>
                    @endif
                </div>
            </a>

            <div class="flex items-center gap-2 sm:gap-4 text-xs sm:text-sm font-medium">
                <a href="{{ route('home') }}#development" class="text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white transition-colors hidden md:flex items-center gap-1.5">
                    <i class="fa-solid fa-child-reaching text-xs text-pink-500"></i>
                    <span>Child Development</span>
                </a>
                <a href="{{ route('home') }}#programs" class="text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white transition-colors hidden sm:flex items-center gap-1.5">
                    <i class="fa-solid fa-shapes text-xs text-amber-500"></i>
                    <span>Programs</span>
                </a>
                <a href="{{ route('home') }}#preschools" class="text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white transition-colors hidden sm:flex items-center gap-1.5">
                    <i class="fa-solid fa-school text-xs text-cyan-500"></i>
                    <span>Pre-Schools</span>
                </a>
                <a href="{{ route('public.apply.status') }}" class="text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white transition-colors flex items-center gap-1.5">
                    <i class="fa-solid fa-magnifying-glass text-xs text-emerald-500"></i>
                    <span>App Status</span>
                </a>
                <a href="{{ route('public.lookup') }}" class="text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white transition-colors flex items-center gap-1.5">
                    <i class="fa-solid fa-id-card text-xs text-indigo-500"></i>
                    <span>QR Pass</span>
                </a>

                <!-- Theme Switcher Toggle (Light ☀️ / Dark 🌙) -->
                <button type="button" 
                        onclick="toggleTheme()" 
                        class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-amber-400 hover:bg-slate-200 dark:hover:bg-slate-700 flex items-center justify-center transition-all shadow-xs cursor-pointer shrink-0"
                        title="Toggle Light / Dark Theme"
                        aria-label="Toggle Theme">
                    <i class="fa-solid fa-sun hidden dark:block text-amber-400 text-xs sm:text-sm"></i>
                    <i class="fa-solid fa-moon block dark:hidden text-indigo-600 text-xs sm:text-sm"></i>
                </button>

                <a href="{{ route('login') }}" class="px-3.5 py-1.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white text-xs font-bold shadow-md shadow-indigo-600/30 flex items-center gap-1.5 transition-all shrink-0">
                    <i class="fa-solid fa-chalkboard-user text-xs"></i>
                    <span>Staff Portal</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Public Page -->
    <main class="flex-1 py-8 px-4 sm:px-6">
        @yield('content')
    </main>

    <!-- Public Footer -->
    <footer class="no-print py-6 text-center text-xs text-slate-500 dark:text-slate-400 border-t border-slate-200 dark:border-slate-800/60 bg-white/60 dark:bg-slate-950/40 transition-colors">
        <div class="max-w-6xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-2">
            <span>&copy; {{ date('Y') }} SmartPreSchool Early Childhood Development Hub. All rights reserved.</span>
            <div class="flex items-center gap-4">
                <a href="{{ route('home') }}" class="hover:text-slate-900 dark:hover:text-slate-300 transition-colors">Portal Home</a>
                <a href="{{ route('public.apply') }}" class="hover:text-slate-900 dark:hover:text-slate-300 transition-colors">Select Pre-School</a>
                <a href="{{ route('public.lookup') }}" class="hover:text-slate-900 dark:hover:text-slate-300 transition-colors">Student Lookup</a>
                <a href="{{ route('login') }}" class="hover:text-slate-900 dark:hover:text-slate-300 transition-colors font-semibold text-indigo-600 dark:text-indigo-400">Staff Portal (Login)</a>
                <a href="{{ route('register') }}" class="hover:text-slate-900 dark:hover:text-slate-300 transition-colors">Principal Register</a>
            </div>
        </div>
    </footer>

    <script>
        function toggleTheme() {
            const isDark = document.documentElement.classList.toggle('dark');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
        }
    </script>
</body>
</html>
