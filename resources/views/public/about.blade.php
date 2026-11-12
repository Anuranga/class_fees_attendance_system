@extends('layouts.public')

@section('title', 'About Us, Vision & Mission - ' . $school->name)

@section('content')
<div class="max-w-4xl mx-auto py-6 sm:py-10 space-y-10">

    <div class="text-center">
        <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-400 hover:text-indigo-300 mb-3 transition-colors">
            <i class="fa-solid fa-arrow-left"></i> Back to Homepage
        </a>
        <h1 class="text-3xl sm:text-4xl font-black text-white">{{ $school->name }}</h1>
        <p class="text-sm text-amber-300 font-medium mt-1">"{{ $school->motto }}"</p>
    </div>

    <!-- About Section -->
    <div class="rounded-3xl bg-slate-800/60 border border-slate-700/60 p-6 sm:p-10 shadow-xl space-y-6">
        <div>
            <h2 class="text-xl font-bold text-white flex items-center gap-2">
                <i class="fa-solid fa-school text-indigo-400"></i>
                <span>About Our Pre-School</span>
            </h2>
            <p class="text-sm text-slate-300 mt-3 leading-relaxed">
                {{ $school->about_us }}
            </p>
        </div>

        <div class="pt-6 border-t border-slate-700/60 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="p-5 rounded-2xl bg-slate-900/60 border border-amber-400/20">
                <h3 class="font-bold text-amber-300 text-base flex items-center gap-2">
                    <i class="fa-solid fa-eye"></i> Our Vision
                </h3>
                <p class="text-xs sm:text-sm text-slate-300 mt-2 leading-relaxed">
                    {{ $school->vision }}
                </p>
            </div>

            <div class="p-5 rounded-2xl bg-slate-900/60 border border-cyan-400/20">
                <h3 class="font-bold text-cyan-300 text-base flex items-center gap-2">
                    <i class="fa-solid fa-bullseye"></i> Our Mission
                </h3>
                <p class="text-xs sm:text-sm text-slate-300 mt-2 leading-relaxed">
                    {{ $school->mission }}
                </p>
            </div>
        </div>

        <div class="pt-6 border-t border-slate-700/60">
            <h3 class="font-bold text-white text-base flex items-center gap-2">
                <i class="fa-solid fa-quote-left text-pink-400"></i>
                <span>Principal's Welcome Note</span>
            </h3>
            <p class="text-sm text-slate-300 mt-2 italic leading-relaxed">
                "{{ $school->principal_message }}"
            </p>
        </div>
    </div>

    <!-- Contact & Application Link -->
    <div class="text-center pt-2">
        <a href="{{ route('public.apply') }}" class="inline-flex items-center gap-2 px-8 py-3.5 rounded-2xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-sm shadow-xl transition-all">
            <i class="fa-solid fa-file-pen"></i>
            <span>Submit Child Admission Application</span>
        </a>
    </div>

</div>
@endsection

