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
            <a href="{{ route('login') }}" class="px-6 py-3.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold shadow-lg shadow-indigo-600/30 flex items-center gap-2.5 transition-all hover:scale-105">
                <i class="fa-solid fa-lock"></i>
                <span>Staff & Admin Sign In</span>
            </a>

            <a href="{{ route('public.lookup') }}" class="px-6 py-3.5 rounded-xl bg-slate-800/80 hover:bg-slate-700 border border-slate-700 text-cyan-300 font-semibold shadow flex items-center gap-2.5 transition-all hover:scale-105">
                <i class="fa-solid fa-qrcode"></i>
                <span>Find Student ID Pass</span>
            </a>
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
