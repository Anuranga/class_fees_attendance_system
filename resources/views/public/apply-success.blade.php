@extends('layouts.public')

@section('title', 'Application Submitted - ' . $application->application_number)

@section('content')
<div class="max-w-xl mx-auto py-8 sm:py-12 text-center space-y-6">

    <div class="w-16 h-16 rounded-3xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center text-3xl mx-auto shadow-lg shadow-emerald-500/10">
        <i class="fa-solid fa-circle-check"></i>
    </div>

    <div>
        <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-amber-400/10 text-amber-300 border border-amber-400/20 mb-2">
            Application Received
        </span>
        <h1 class="text-2xl sm:text-3xl font-black text-white">Thank You, Parent!</h1>
        <p class="text-xs sm:text-sm text-slate-400 mt-1">
            Your admission application for <strong class="text-white">{{ $application->child_full_name }}</strong> has been recorded.
        </p>
    </div>

    <!-- Application Reference Card -->
    <div class="rounded-3xl bg-slate-800/80 border border-slate-700/80 p-6 text-left shadow-2xl space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-700/60">
            <div>
                <p class="text-[10px] uppercase font-bold text-slate-400">Application Number</p>
                <p class="text-lg font-black font-mono text-amber-400">{{ $application->application_number }}</p>
            </div>
            <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                Pending Review
            </span>
        </div>

        <div class="grid grid-cols-2 gap-3 text-xs">
            <div>
                <span class="text-slate-400 block text-[11px]">Child's Name</span>
                <span class="font-bold text-white">{{ $application->child_full_name }}</span>
            </div>
            <div>
                <span class="text-slate-400 block text-[11px]">Program</span>
                <span class="font-bold text-cyan-300">{{ $application->target_grade_level }}</span>
            </div>
            <div>
                <span class="text-slate-400 block text-[11px]">WhatsApp Contact</span>
                <span class="font-mono text-emerald-400 font-bold">{{ $application->whatsapp_number }}</span>
            </div>
            <div>
                <span class="text-slate-400 block text-[11px]">Date of Birth</span>
                <span class="text-slate-300">{{ $application->date_of_birth->format('M d, Y') }}</span>
            </div>
        </div>

        <div class="pt-3 border-t border-slate-700/60 text-xs text-slate-400 leading-relaxed">
            <i class="fa-solid fa-circle-info text-cyan-400 mr-1"></i>
            The Principal will review your application. You can check the approval status anytime or contact the school office.
        </div>
    </div>

    <!-- Next Actions -->
    <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-2">
        <a href="{{ route('public.apply.status', ['query' => $application->application_number]) }}" 
           class="w-full sm:w-auto px-6 py-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs border border-slate-700 transition-all flex items-center justify-center gap-2">
            <i class="fa-solid fa-magnifying-glass text-cyan-400"></i>
            <span>Track Application Status</span>
        </a>

        <a href="{{ route('home') }}" 
           class="w-full sm:w-auto px-6 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-lg transition-all flex items-center justify-center gap-2">
            <i class="fa-solid fa-house"></i>
            <span>Return to Homepage</span>
        </a>
    </div>

</div>
@endsection
