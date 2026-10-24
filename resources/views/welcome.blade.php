@extends('layouts.public')

@section('title', 'Welcome')

@section('content')
<div class="max-w-6xl mx-auto py-6 sm:py-12">

    <!-- Hero Section -->
    <div class="text-center max-w-3xl mx-auto mb-16">
        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-indigo-300 text-xs font-semibold mb-6 shadow-sm">
            <span class="w-2 h-2 rounded-full bg-indigo-400 animate-ping"></span>
            Laravel 11 &bull; QR Attendance &bull; Cash Flow Analytics
        </div>

        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-white tracking-tight leading-tight">
            Academic Attendance & <br>
            <span class="bg-gradient-to-r from-indigo-400 via-purple-300 to-cyan-400 bg-clip-text text-transparent">Fee Management</span> Made Seamless
        </h1>

        <p class="mt-6 text-base sm:text-lg text-slate-400 leading-relaxed">
            A role-based web platform designed for private academic institutes and tutors. Manage classes, enable student self-enrollment with automatic QR-coded ID passes, scan attendance via camera in real-time, and log cash fee collections with instant receipt dispatch.
        </p>

        <!-- Main Action Buttons -->
        <div class="mt-8 flex flex-wrap items-center justify-center gap-4">
            <a href="{{ route('quick.login', 'teacher') }}" class="px-6 py-3.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold shadow-lg shadow-indigo-600/30 flex items-center gap-2.5 transition-all hover:scale-105">
                <i class="fa-solid fa-chalkboard-user"></i>
                <span>Enter Teacher Dashboard</span>
            </a>

            <a href="{{ route('quick.login', 'admin') }}" class="px-6 py-3.5 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-white font-semibold shadow-lg flex items-center gap-2.5 transition-all hover:scale-105">
                <i class="fa-solid fa-shield-halved text-amber-400"></i>
                <span>Admin Global Oversight</span>
            </a>

            <a href="{{ route('public.lookup') }}" class="px-6 py-3.5 rounded-xl bg-slate-800/80 hover:bg-slate-700 border border-slate-700 text-cyan-300 font-semibold shadow flex items-center gap-2.5 transition-all hover:scale-105">
                <i class="fa-solid fa-qrcode"></i>
                <span>Find Student ID Pass</span>
            </a>
        </div>
    </div>

    <!-- Quick Access Demo Roles Cards -->
    <div class="mb-16">
        <h2 class="text-xs font-extrabold uppercase tracking-widest text-indigo-400 text-center mb-6">One-Click Testing Access</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <!-- Teacher 1 -->
            <div class="rounded-2xl bg-slate-800/70 border border-slate-700/80 p-6 flex flex-col justify-between hover:border-indigo-500/50 transition-all shadow-xl">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">Teacher Tenant #1</span>
                        <i class="fa-solid fa-square-poll-vertical text-indigo-400 text-lg"></i>
                    </div>
                    <h3 class="text-lg font-bold text-white">Prof. Alan Turing</h3>
                    <p class="text-xs text-slate-400 mt-1">teacher@edutrack.test &bull; password</p>
                    <p class="text-xs text-slate-300 mt-3 leading-relaxed">
                        Manages Pure Mathematics & Computer Science classes. Has 20+ students, active attendance records, and monthly fee collections.
                    </p>
                </div>
                <div class="mt-6 pt-4 border-t border-slate-700/60">
                    <a href="{{ route('quick.login', 'teacher') }}" class="w-full py-2.5 rounded-xl bg-indigo-600/90 hover:bg-indigo-600 text-white text-xs font-bold flex items-center justify-center gap-2 transition-all">
                        <span>Sign In as Alan Turing</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </div>

            <!-- Teacher 2 -->
            <div class="rounded-2xl bg-slate-800/70 border border-slate-700/80 p-6 flex flex-col justify-between hover:border-purple-500/50 transition-all shadow-xl">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-purple-500/20 text-purple-300 border border-purple-500/30">Teacher Tenant #2</span>
                        <i class="fa-solid fa-laptop-code text-purple-400 text-lg"></i>
                    </div>
                    <h3 class="text-lg font-bold text-white">Dr. Grace Hopper</h3>
                    <p class="text-xs text-slate-400 mt-1">grace@edutrack.test &bull; password</p>
                    <p class="text-xs text-slate-300 mt-3 leading-relaxed">
                        Manages Information & Communication Technology. Demonstrates multi-tenant isolation where teachers see only their own data.
                    </p>
                </div>
                <div class="mt-6 pt-4 border-t border-slate-700/60">
                    <a href="{{ route('quick.login', 'grace') }}" class="w-full py-2.5 rounded-xl bg-purple-600/90 hover:bg-purple-600 text-white text-xs font-bold flex items-center justify-center gap-2 transition-all">
                        <span>Sign In as Grace Hopper</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </div>

            <!-- Admin -->
            <div class="rounded-2xl bg-slate-800/70 border border-slate-700/80 p-6 flex flex-col justify-between hover:border-amber-500/50 transition-all shadow-xl">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">System Admin</span>
                        <i class="fa-solid fa-crown text-amber-400 text-lg"></i>
                    </div>
                    <h3 class="text-lg font-bold text-white">System Administrator</h3>
                    <p class="text-xs text-slate-400 mt-1">admin@edutrack.test &bull; password</p>
                    <p class="text-xs text-slate-300 mt-3 leading-relaxed">
                        Global oversight across all teachers, classes, student admissions, and institute-wide financial cash flows.
                    </p>
                </div>
                <div class="mt-6 pt-4 border-t border-slate-700/60">
                    <a href="{{ route('quick.login', 'admin') }}" class="w-full py-2.5 rounded-xl bg-amber-600/90 hover:bg-amber-600 text-white text-xs font-bold flex items-center justify-center gap-2 transition-all">
                        <span>Sign In as Administrator</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </div>

        </div>
    </div>

    <!-- Core Pillars Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

        <div class="rounded-2xl bg-slate-800/50 border border-slate-700/50 p-6">
            <div class="w-12 h-12 rounded-xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 flex items-center justify-center text-xl mb-4">
                <i class="fa-solid fa-link"></i>
            </div>
            <h4 class="text-base font-bold text-white">Self-Registration Links</h4>
            <p class="text-xs text-slate-400 mt-2 leading-relaxed">
                Teachers share a dedicated `/join/{token}` link. Students register themselves, generate their unique class UID, and receive a digital ID pass instantly.
            </p>
        </div>

        <div class="rounded-2xl bg-slate-800/50 border border-slate-700/50 p-6">
            <div class="w-12 h-12 rounded-xl bg-cyan-500/10 border border-cyan-500/20 text-cyan-400 flex items-center justify-center text-xl mb-4">
                <i class="fa-solid fa-camera"></i>
            </div>
            <h4 class="text-base font-bold text-white">HTML5-QRCode Scanner</h4>
            <p class="text-xs text-slate-400 mt-2 leading-relaxed">
                Device camera scanner with Web Audio chime for success, buzzer for duplicates, and real-time DOM table updates without page reloads.
            </p>
        </div>

        <div class="rounded-2xl bg-slate-800/50 border border-slate-700/50 p-6">
            <div class="w-12 h-12 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center text-xl mb-4">
                <i class="fa-solid fa-receipt"></i>
            </div>
            <h4 class="text-base font-bold text-white">Cash Fees & WhatsApp</h4>
            <p class="text-xs text-slate-400 mt-2 leading-relaxed">
                Log cash fee payments with 1 click, track monthly Paid vs Pending status, and dispatch printable receipts and instant WhatsApp notifications.
            </p>
        </div>

        <div class="rounded-2xl bg-slate-800/50 border border-slate-700/50 p-6">
            <div class="w-12 h-12 rounded-xl bg-purple-500/10 border border-purple-500/20 text-purple-400 flex items-center justify-center text-xl mb-4">
                <i class="fa-solid fa-chart-simple"></i>
            </div>
            <h4 class="text-base font-bold text-white">Chart.js Analytics</h4>
            <p class="text-xs text-slate-400 mt-2 leading-relaxed">
                Visual cash flow trends over 6-12 months, doughnut chart for paid vs unpaid students, and weekly class attendance percentages.
            </p>
        </div>

    </div>

</div>
@endsection
