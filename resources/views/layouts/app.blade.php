<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#4f46e5">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') - {{ config('app.name', 'EduTrack') }}</title>

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- FontAwesome 6 Icons CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
    @stack('styles')
</head>
<body class="h-full antialiased text-slate-800" x-data="{ sidebarOpen: false }">
    <div class="min-h-full flex flex-col lg:flex-row">

        <!-- Mobile Sidebar Backdrop -->
        <div x-show="sidebarOpen" 
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="sidebarOpen = false" 
             class="fixed inset-0 bg-slate-900/80 z-40 lg:hidden" 
             style="display: none;"></div>

        <!-- Sidebar Navigation -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
               class="fixed inset-y-0 left-0 z-50 w-72 bg-slate-900 text-slate-200 flex flex-col transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0 lg:z-auto shadow-2xl">
            
            <!-- Brand Header -->
            <div class="h-20 flex items-center justify-between px-6 border-b border-slate-800/80 bg-slate-950/40">
                <a href="{{ Auth::user()->isAdmin() ? route('admin.dashboard') : route('teacher.dashboard') }}" class="flex items-center gap-3 group">
                    <div class="w-10 h-10 rounded-xl gradient-brand flex items-center justify-center text-white shadow-lg shadow-indigo-500/30 group-hover:scale-105 transition-transform">
                        <i class="fa-solid fa-graduation-cap text-lg"></i>
                    </div>
                    <div>
                        <span class="font-extrabold text-lg text-white tracking-tight flex items-center gap-1.5">
                            EduTrack 
                            @if(Auth::user()->isAdmin())
                                <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/30">Admin</span>
                            @elseif(Auth::user()->isAssistant())
                                <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-cyan-500/20 text-cyan-300 border border-cyan-500/30">Assistant</span>
                            @else
                                <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">Teacher</span>
                            @endif
                        </span>
                        <p class="text-xs text-slate-400">Attendance & Fee Portal</p>
                    </div>
                </a>
                <button @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-white p-1">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- User Info Capsule -->
            <div class="px-6 py-4 mx-4 my-4 rounded-xl bg-slate-800/60 border border-slate-700/50 flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-sm shadow">
                    {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-white truncate">{{ Auth::user()->name }}</p>
                    @if(Auth::user()->isAssistant() && Auth::user()->teacher)
                        <p class="text-[11px] text-cyan-400 truncate">Assistant to {{ Auth::user()->teacher->name }}</p>
                    @else
                        <p class="text-xs text-slate-400 truncate">{{ Auth::user()->email }}</p>
                    @endif
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 px-4 space-y-1.5 overflow-y-auto">
                @if(Auth::user()->isAdmin())
                    <!-- Admin Navigation -->
                    <div class="px-3 pt-2 pb-1 text-xs font-semibold uppercase tracking-wider text-slate-400">Administration</div>
                    
                    <a href="{{ route('admin.dashboard') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <i class="fa-solid fa-chart-pie w-5 text-center text-base"></i>
                        <span>System Analytics</span>
                    </a>

                    <a href="{{ route('admin.teachers.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.teachers.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <i class="fa-solid fa-chalkboard-user w-5 text-center text-base"></i>
                        <span>Manage Teachers</span>
                    </a>

                    <a href="{{ route('admin.payments.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.payments.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <i class="fa-solid fa-money-bill-wave w-5 text-center text-base"></i>
                        <span>Global Payments</span>
                    </a>

                    <a href="{{ route('admin.students.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.students.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <i class="fa-solid fa-users w-5 text-center text-base"></i>
                        <span>Global Students</span>
                    </a>

                    <div class="pt-4 border-t border-slate-800/80 my-3"></div>
                    <div class="px-3 pb-1 text-xs font-semibold uppercase tracking-wider text-slate-400">Teacher View Mode</div>
                    <a href="{{ route('teacher.dashboard') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium text-slate-300 hover:bg-slate-800 hover:text-white transition-all">
                        <i class="fa-solid fa-arrow-right-arrow-left w-5 text-center text-base"></i>
                        <span>Switch to Teacher Portal</span>
                    </a>
                @else
                    <!-- Teacher Navigation -->
                    <div class="px-3 pt-2 pb-1 text-xs font-semibold uppercase tracking-wider text-slate-400">Teacher Workspace</div>

                    <a href="{{ route('teacher.dashboard') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('teacher.dashboard') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <i class="fa-solid fa-chart-line w-5 text-center text-base"></i>
                        <span>Dashboard & Analytics</span>
                    </a>

                    <a href="{{ route('teacher.classes.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('teacher.classes.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <i class="fa-solid fa-book-open-reader w-5 text-center text-base"></i>
                        <span>Classes & Students</span>
                    </a>

                    <a href="{{ route('teacher.fees.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('teacher.fees.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <i class="fa-solid fa-receipt w-5 text-center text-base"></i>
                        <span>Cash Collection & Fees</span>
                    </a>

                    @if(Auth::user()->isTeacher())
                        <a href="{{ route('teacher.assistants.index') }}" 
                           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('teacher.assistants.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <i class="fa-solid fa-users-gear w-5 text-center text-base"></i>
                            <span>Teaching Assistants</span>
                        </a>
                    @endif
                @endif

                <div class="pt-4 border-t border-slate-800/80 my-3"></div>
                <div class="px-3 pb-1 text-xs font-semibold uppercase tracking-wider text-slate-400">Public Links</div>

                <a href="{{ route('public.lookup') }}" target="_blank"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium text-slate-400 hover:bg-slate-800 hover:text-white transition-all">
                    <i class="fa-solid fa-id-card-clip w-5 text-center text-base text-cyan-400"></i>
                    <span>Student Pass Lookup</span>
                    <i class="fa-solid fa-arrow-up-right-from-square ml-auto text-xs text-slate-500"></i>
                </a>
            </nav>

            <!-- Bottom Logout Footer -->
            <div class="p-4 border-t border-slate-800/80 bg-slate-950/30">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" 
                            class="w-full flex items-center justify-center gap-2.5 px-4 py-2.5 rounded-xl text-sm font-medium text-rose-300 bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/20 transition-all">
                        <i class="fa-solid fa-right-from-bracket"></i>
                        <span>Sign Out</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-slate-50">
            
            <!-- Top Navbar -->
            <header class="h-16 sm:h-20 bg-white/90 backdrop-blur-md border-b border-slate-200/80 sticky top-0 z-30 px-4 sm:px-6 flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                    <button @click="sidebarOpen = true" class="lg:hidden text-slate-600 hover:text-slate-900 p-2 rounded-lg hover:bg-slate-100 shrink-0" aria-label="Open Navigation">
                        <i class="fa-solid fa-bars text-xl"></i>
                    </button>
                    <div class="min-w-0">
                        <h1 class="text-base sm:text-xl font-bold text-slate-900 tracking-tight truncate">@yield('page-title', 'Dashboard')</h1>
                        <p class="text-[11px] sm:text-xs text-slate-500 hidden sm:block truncate">@yield('page-subtitle', 'Academic & Financial Management System')</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Live Session
                    </span>

                    <a href="{{ route('home') }}" class="p-2 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-lg transition-colors" title="Home">
                        <i class="fa-solid fa-house"></i>
                    </a>
                </div>
            </header>

            <!-- Flash Alerts -->
            <div class="px-6 pt-5">
                @if(session('success'))
                    <div x-data="{ show: true }" x-show="show" class="mb-4 rounded-xl p-4 bg-emerald-50 border border-emerald-200 text-emerald-900 flex items-start gap-3 shadow-sm">
                        <i class="fa-solid fa-circle-check text-emerald-600 text-lg mt-0.5"></i>
                        <div class="flex-1 text-sm font-medium">{{ session('success') }}</div>
                        <button @click="show = false" class="text-emerald-500 hover:text-emerald-800"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                @endif

                @if(session('error'))
                    <div x-data="{ show: true }" x-show="show" class="mb-4 rounded-xl p-4 bg-rose-50 border border-rose-200 text-rose-900 flex items-start gap-3 shadow-sm">
                        <i class="fa-solid fa-circle-exclamation text-rose-600 text-lg mt-0.5"></i>
                        <div class="flex-1 text-sm font-medium">{{ session('error') }}</div>
                        <button @click="show = false" class="text-rose-500 hover:text-rose-800"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                @endif

                @if(session('info'))
                    <div x-data="{ show: true }" x-show="show" class="mb-4 rounded-xl p-4 bg-blue-50 border border-blue-200 text-blue-900 flex items-start gap-3 shadow-sm">
                        <i class="fa-solid fa-circle-info text-blue-600 text-lg mt-0.5"></i>
                        <div class="flex-1 text-sm font-medium">{{ session('info') }}</div>
                        <button @click="show = false" class="text-blue-500 hover:text-blue-800"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                @endif

                @if($errors->any())
                    <div x-data="{ show: true }" x-show="show" class="mb-4 rounded-xl p-4 bg-amber-50 border border-amber-200 text-amber-900 shadow-sm">
                        <div class="flex items-center gap-2 font-semibold text-sm mb-1">
                            <i class="fa-solid fa-triangle-exclamation text-amber-600"></i>
                            <span>Please review the input errors:</span>
                        </div>
                        <ul class="list-disc list-inside text-xs space-y-1 ml-4 text-amber-800">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            <!-- Page Body Content -->
            <main class="flex-1 p-3.5 sm:p-6 pb-28 lg:pb-8">
                @yield('content')
            </main>

            <!-- Footer -->
            <footer class="py-4 px-6 border-t border-slate-200/80 bg-white text-center text-xs text-slate-500 hidden sm:block">
                &copy; {{ date('Y') }} EduTrack - Student Attendance & Fee Collection Management System.
            </footer>

            <!-- Mobile Bottom Navigation Bar (Phone Friendly) -->
            <nav class="lg:hidden fixed bottom-0 inset-x-0 bg-white/95 backdrop-blur-md border-t border-slate-200/90 z-30 shadow-[0_-4px_20px_rgba(0,0,0,0.06)] pb-[env(safe-area-inset-bottom)]">
                <div class="grid grid-cols-5 items-center h-16 px-1">
                    @if(Auth::user()->isAdmin())
                        <!-- Admin Bottom Navigation -->
                        <a href="{{ route('admin.dashboard') }}" 
                           class="flex flex-col items-center justify-center py-1 {{ request()->routeIs('admin.dashboard') ? 'text-indigo-600 font-bold' : 'text-slate-500 hover:text-slate-800' }}">
                            <i class="fa-solid fa-chart-pie text-lg"></i>
                            <span class="text-[10px] mt-0.5 font-semibold">Overview</span>
                        </a>
                        <a href="{{ route('admin.teachers.index') }}" 
                           class="flex flex-col items-center justify-center py-1 {{ request()->routeIs('admin.teachers.*') ? 'text-indigo-600 font-bold' : 'text-slate-500 hover:text-slate-800' }}">
                            <i class="fa-solid fa-chalkboard-user text-lg"></i>
                            <span class="text-[10px] mt-0.5 font-semibold">Teachers</span>
                        </a>
                        <a href="{{ route('admin.payments.index') }}" 
                           class="flex flex-col items-center justify-center py-1 {{ request()->routeIs('admin.payments.*') ? 'text-indigo-600 font-bold' : 'text-slate-500 hover:text-slate-800' }}">
                            <i class="fa-solid fa-money-bill-wave text-lg"></i>
                            <span class="text-[10px] mt-0.5 font-semibold">Payments</span>
                        </a>
                        <a href="{{ route('admin.students.index') }}" 
                           class="flex flex-col items-center justify-center py-1 {{ request()->routeIs('admin.students.*') ? 'text-indigo-600 font-bold' : 'text-slate-500 hover:text-slate-800' }}">
                            <i class="fa-solid fa-users text-lg"></i>
                            <span class="text-[10px] mt-0.5 font-semibold">Students</span>
                        </a>
                        <button @click="sidebarOpen = true" 
                                class="flex flex-col items-center justify-center py-1 text-slate-500 hover:text-slate-800">
                            <i class="fa-solid fa-bars text-lg"></i>
                            <span class="text-[10px] mt-0.5 font-semibold">Menu</span>
                        </button>
                    @else
                        <!-- Teacher & Assistant Bottom Navigation -->
                        <a href="{{ route('teacher.dashboard') }}" 
                           class="flex flex-col items-center justify-center py-1 {{ request()->routeIs('teacher.dashboard') ? 'text-indigo-600 font-bold' : 'text-slate-500 hover:text-slate-800' }}">
                            <i class="fa-solid fa-chart-line text-lg"></i>
                            <span class="text-[10px] mt-0.5 font-semibold">Stats</span>
                        </a>
                        <a href="{{ route('teacher.classes.index') }}" 
                           class="flex flex-col items-center justify-center py-1 {{ request()->routeIs('teacher.classes.*') && !request()->routeIs('teacher.attendance.*') ? 'text-indigo-600 font-bold' : 'text-slate-500 hover:text-slate-800' }}">
                            <i class="fa-solid fa-book-open-reader text-lg"></i>
                            <span class="text-[10px] mt-0.5 font-semibold">Classes</span>
                        </a>
                        
                        <!-- Center Elevated QR Scan Button for Mobile Phone -->
                        <div class="relative flex items-center justify-center -top-3">
                            <a href="{{ route('teacher.classes.index') }}" 
                               class="w-12 h-12 rounded-full bg-gradient-to-tr from-indigo-600 to-purple-600 text-white flex items-center justify-center shadow-lg shadow-indigo-600/40 ring-4 ring-white active:scale-95 transition-transform"
                               title="Scan Attendance">
                                <i class="fa-solid fa-qrcode text-lg"></i>
                            </a>
                        </div>

                        <a href="{{ route('teacher.fees.index') }}" 
                           class="flex flex-col items-center justify-center py-1 {{ request()->routeIs('teacher.fees.*') ? 'text-indigo-600 font-bold' : 'text-slate-500 hover:text-slate-800' }}">
                            <i class="fa-solid fa-receipt text-lg"></i>
                            <span class="text-[10px] mt-0.5 font-semibold">Fees</span>
                        </a>
                        <button @click="sidebarOpen = true" 
                                class="flex flex-col items-center justify-center py-1 text-slate-500 hover:text-slate-800">
                            <i class="fa-solid fa-bars text-lg"></i>
                            <span class="text-[10px] mt-0.5 font-semibold">Menu</span>
                        </button>
                    @endif
                </div>
            </nav>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
