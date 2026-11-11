@extends('layouts.public')

@section('title', $school->name . ' - Early Childhood Excellence')

@section('content')
<div class="max-w-6xl mx-auto py-4 sm:py-8 space-y-12 sm:space-y-16">

    <!-- Hero Section -->
    <div class="rounded-3xl bg-gradient-to-br from-indigo-950 via-slate-900 to-indigo-900 border border-indigo-500/20 p-6 sm:p-12 shadow-2xl relative overflow-hidden">
        <!-- Playful Background Glows -->
        <div class="absolute -top-20 -right-20 w-80 h-80 bg-amber-400/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-20 -left-20 w-80 h-80 bg-pink-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="max-w-3xl relative z-10">
            <!-- Admission Open Badge -->
            @if($school->is_admission_open)
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-400/30 text-emerald-300 text-xs font-bold mb-6">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Admissions Open for Academic Year {{ $school->academic_year }}</span>
                </div>
            @endif

            <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black text-white tracking-tight leading-tight">
                {{ $school->name }}
            </h1>
            <p class="text-base sm:text-xl font-medium text-amber-300 mt-2">
                "{{ $school->motto }}"
            </p>

            <p class="mt-4 text-sm sm:text-base text-slate-300 leading-relaxed max-w-2xl">
                {{ $school->about_us }}
            </p>

            <!-- Action CTAs -->
            <div class="mt-8 flex flex-wrap items-center gap-3.5">
                <a href="{{ route('public.apply') }}" 
                   class="px-6 py-3.5 rounded-2xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-black text-sm shadow-lg shadow-amber-500/30 flex items-center gap-2.5 transition-all hover:scale-105">
                    <i class="fa-solid fa-file-pen text-base"></i>
                    <span>Apply for Admission Online</span>
                </a>

                <a href="{{ route('public.apply.status') }}" 
                   class="px-5 py-3.5 rounded-2xl bg-slate-800/90 hover:bg-slate-700 text-white font-bold text-sm border border-slate-700 flex items-center gap-2 transition-all">
                    <i class="fa-solid fa-magnifying-glass text-cyan-400"></i>
                    <span>Check Application Status</span>
                </a>

                <a href="{{ route('public.lookup') }}" 
                   class="px-5 py-3.5 rounded-2xl bg-indigo-600/30 hover:bg-indigo-600/50 text-indigo-200 font-bold text-sm border border-indigo-400/30 flex items-center gap-2 transition-all">
                    <i class="fa-solid fa-id-card text-indigo-400"></i>
                    <span>Child's QR ID Pass</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Vision & Mission Side-by-Side Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Vision -->
        <div class="rounded-3xl bg-slate-800/60 border border-slate-700/60 p-6 sm:p-8 flex flex-col justify-between hover:border-amber-400/40 transition-colors shadow-lg">
            <div>
                <div class="w-12 h-12 rounded-2xl bg-amber-400/10 text-amber-400 border border-amber-400/20 flex items-center justify-center text-xl mb-4">
                    <i class="fa-solid fa-eye"></i>
                </div>
                <h3 class="text-xl font-extrabold text-white">Our Vision</h3>
                <p class="text-sm text-slate-300 mt-3 leading-relaxed">
                    {{ $school->vision }}
                </p>
            </div>
            <div class="mt-6 pt-4 border-t border-slate-700/60 flex items-center gap-2 text-xs text-amber-300 font-semibold">
                <i class="fa-solid fa-star"></i>
                <span>Nurturing tomorrow's leaders</span>
            </div>
        </div>

        <!-- Mission -->
        <div class="rounded-3xl bg-slate-800/60 border border-slate-700/60 p-6 sm:p-8 flex flex-col justify-between hover:border-cyan-400/40 transition-colors shadow-lg">
            <div>
                <div class="w-12 h-12 rounded-2xl bg-cyan-400/10 text-cyan-400 border border-cyan-400/20 flex items-center justify-center text-xl mb-4">
                    <i class="fa-solid fa-bullseye"></i>
                </div>
                <h3 class="text-xl font-extrabold text-white">Our Mission</h3>
                <p class="text-sm text-slate-300 mt-3 leading-relaxed">
                    {{ $school->mission }}
                </p>
            </div>
            <div class="mt-6 pt-4 border-t border-slate-700/60 flex items-center gap-2 text-xs text-cyan-300 font-semibold">
                <i class="fa-solid fa-compass"></i>
                <span>Holistic child development</span>
            </div>
        </div>
    </div>

    <!-- Pre-School Learning Programs -->
    <div>
        <div class="text-center max-w-2xl mx-auto mb-8">
            <h2 class="text-2xl sm:text-3xl font-extrabold text-white">Our Early Learning Programs</h2>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">Carefully structured age-appropriate curriculum designed for social, emotional, and cognitive growth.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <!-- Playgroup -->
            <div class="rounded-2xl bg-slate-800/40 border border-slate-700/60 p-5 hover:border-pink-500/50 transition-all">
                <div class="w-10 h-10 rounded-xl bg-pink-500/10 text-pink-400 flex items-center justify-center text-lg mb-3">
                    <i class="fa-solid fa-shapes"></i>
                </div>
                <h4 class="font-bold text-white text-base">Playgroup</h4>
                <p class="text-xs text-pink-400 font-semibold mt-0.5">Age: 1.5 - 2.5 Years</p>
                <p class="text-xs text-slate-400 mt-2 leading-relaxed">Sensory exploration, motor skills, social interaction, and fun interactive play.</p>
            </div>

            <!-- Nursery -->
            <div class="rounded-2xl bg-slate-800/40 border border-slate-700/60 p-5 hover:border-amber-500/50 transition-all">
                <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center text-lg mb-3">
                    <i class="fa-solid fa-paintbrush"></i>
                </div>
                <h4 class="font-bold text-white text-base">Nursery</h4>
                <p class="text-xs text-amber-400 font-semibold mt-0.5">Age: 2.5 - 3.5 Years</p>
                <p class="text-xs text-slate-400 mt-2 leading-relaxed">Language immersion, phonics basics, art & music, and joyful learning routines.</p>
            </div>

            <!-- LKG / UKG -->
            <div class="rounded-2xl bg-slate-800/40 border border-slate-700/60 p-5 hover:border-cyan-500/50 transition-all">
                <div class="w-10 h-10 rounded-xl bg-cyan-500/10 text-cyan-400 flex items-center justify-center text-lg mb-3">
                    <i class="fa-solid fa-book-open"></i>
                </div>
                <h4 class="font-bold text-white text-base">Kindergarten (LKG & UKG)</h4>
                <p class="text-xs text-cyan-400 font-semibold mt-0.5">Age: 3.5 - 5.5 Years</p>
                <p class="text-xs text-slate-400 mt-2 leading-relaxed">Early numeracy, writing readiness, science exploration, and school transition skills.</p>
            </div>

            <!-- Daycare -->
            <div class="rounded-2xl bg-slate-800/40 border border-slate-700/60 p-5 hover:border-emerald-500/50 transition-all">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center text-lg mb-3">
                    <i class="fa-solid fa-shield-heart"></i>
                </div>
                <h4 class="font-bold text-white text-base">Daycare & Extended Care</h4>
                <p class="text-xs text-emerald-400 font-semibold mt-0.5">Full Day & After-School</p>
                <p class="text-xs text-slate-400 mt-2 leading-relaxed">Safe, caring home-away-from-home environment with nutritious meals and supervised rest.</p>
            </div>
        </div>
    </div>

    <!-- Principal's Message & Contact Strip -->
    <div class="rounded-3xl bg-slate-800/50 border border-slate-700/60 p-6 sm:p-10 flex flex-col md:flex-row items-center gap-8">
        <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-full bg-gradient-to-tr from-amber-400 to-pink-500 text-slate-900 flex items-center justify-center text-3xl font-black shrink-0 shadow-xl">
            <i class="fa-solid fa-heart"></i>
        </div>
        <div class="flex-1 min-w-0 text-center md:text-left">
            <h3 class="text-lg sm:text-xl font-bold text-white">Message from the Principal</h3>
            <p class="text-xs sm:text-sm text-slate-300 mt-2 italic leading-relaxed">
                "{{ $school->principal_message }}"
            </p>
            <div class="mt-4 flex flex-wrap items-center justify-center md:justify-start gap-4 text-xs text-slate-400">
                <span><i class="fa-solid fa-phone text-amber-400 mr-1.5"></i>{{ $school->contact_phone }}</span>
                <span><i class="fa-brands fa-whatsapp text-emerald-400 mr-1.5"></i>{{ $school->whatsapp_number }}</span>
                <span><i class="fa-solid fa-envelope text-cyan-400 mr-1.5"></i>{{ $school->contact_email }}</span>
                <span><i class="fa-solid fa-location-dot text-rose-400 mr-1.5"></i>{{ $school->address }}</span>
            </div>
        </div>
    </div>

</div>
@endsection
