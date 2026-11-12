@extends('layouts.public')

@section('title', 'Check Admission Application Status')

@section('content')
<div class="max-w-2xl mx-auto py-8">

    <div class="text-center mb-8">
        <div class="w-12 h-12 rounded-2xl bg-amber-400/10 border border-amber-400/20 text-amber-400 flex items-center justify-center text-xl mx-auto mb-3 shadow">
            <i class="fa-solid fa-magnifying-glass"></i>
        </div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Admission Application Status</h1>
        <p class="text-xs sm:text-sm text-slate-400 mt-1">Search by your Application Number (e.g. APP-2026-0001) or WhatsApp Phone Number</p>
    </div>

    <!-- Search Box -->
    <div class="rounded-3xl bg-slate-800/80 border border-slate-700/80 p-6 shadow-2xl mb-8">
        <form action="{{ route('public.apply.status') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </div>
                <input type="text" name="query" value="{{ $query }}" required
                       placeholder="Enter APP-YYYY-XXXX or WhatsApp number..."
                       class="w-full pl-10 pr-4 py-3 rounded-xl bg-slate-900 border border-slate-700 text-white placeholder-slate-500 text-sm focus:border-amber-400 focus:ring-2 focus:ring-amber-400/20 transition-all">
            </div>
            <button type="submit" 
                    class="px-6 py-3 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-sm shadow-lg shadow-amber-500/20 flex items-center justify-center gap-2 transition-all">
                <span>Check Status</span>
                <i class="fa-solid fa-arrow-right text-xs"></i>
            </button>
        </form>
    </div>

    @if($query && !$application)
        <div class="rounded-2xl bg-slate-800/40 border border-slate-700/60 p-8 text-center">
            <div class="w-12 h-12 rounded-full bg-slate-700/50 text-slate-400 flex items-center justify-center text-xl mx-auto mb-3">
                <i class="fa-solid fa-file-circle-xmark"></i>
            </div>
            <p class="text-sm font-semibold text-white">No Application Found</p>
            <p class="text-xs text-slate-400 mt-1">Please verify the application number or WhatsApp contact entered, or submit a new application.</p>
            <a href="{{ route('public.apply') }}" class="inline-block mt-4 text-xs font-bold text-amber-400 hover:underline">Apply for Admission &rarr;</a>
        </div>
    @elseif($application)
        <div class="rounded-3xl bg-slate-800/90 border border-slate-700/90 p-6 shadow-2xl space-y-5">
            <div class="flex items-center justify-between pb-4 border-b border-slate-700/60">
                <div>
                    <span class="text-[10px] uppercase font-bold text-slate-400">Application Number</span>
                    <h3 class="text-xl font-black font-mono text-amber-400">{{ $application->application_number }}</h3>
                </div>

                @if($application->status === 'enrolled' || $application->status === 'approved')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        Selected / Enrolled
                    </span>
                @elseif($application->status === 'interview_scheduled')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-cyan-500/20 text-cyan-300 border border-cyan-500/30">
                        <span class="w-2 h-2 rounded-full bg-cyan-400"></span>
                        Interview Scheduled
                    </span>
                @elseif($application->status === 'rejected')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-500/20 text-rose-300 border border-rose-500/30">
                        <span class="w-2 h-2 rounded-full bg-rose-400"></span>
                        Not Selected
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                        <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                        Under Review by Principal
                    </span>
                @endif
            </div>

            <div class="grid grid-cols-2 gap-4 text-xs">
                <div>
                    <span class="text-slate-400 block text-[11px]">Child's Full Name</span>
                    <span class="font-bold text-white text-sm">{{ $application->child_full_name }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[11px]">Applied Program</span>
                    <span class="font-bold text-cyan-300">{{ $application->target_grade_level }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[11px]">Submission Date</span>
                    <span class="text-slate-300">{{ $application->created_at->format('M d, Y - h:i A') }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[11px]">WhatsApp Contact</span>
                    <span class="font-mono text-emerald-400 font-bold">{{ $application->whatsapp_number }}</span>
                </div>
            </div>

            @if($application->status === 'enrolled' || $application->status === 'approved')
                @php
                    $firstEnrollment = $application->student?->enrollments?->first();
                    $itemsToBring = $application->school?->items_to_bring_sinhala ?: \App\Models\SchoolSetting::defaultSinhalaItems();
                @endphp
                <div class="p-4 rounded-2xl bg-emerald-950/40 border border-emerald-500/30 space-y-3">
                    <div class="flex items-center gap-2 text-emerald-300 font-bold text-xs">
                        <i class="fa-solid fa-circle-check text-sm"></i>
                        <span>සුභ පැතුම්! ඔබගේ දරුවා පෙරපාසලට සාර්ථකව තෝරාගෙන ඇත.</span>
                    </div>

                    @if($application->student)
                        <div class="text-xs text-slate-300">
                            ඇතුළත් වීමේ අංකය: <strong class="text-white font-mono">{{ $application->student->admission_number }}</strong>
                            @if($application->assignedClass)
                                &bull; පන්තිය / කාණ්ඩය: <strong class="text-cyan-300">{{ $application->assignedClass->name }}</strong>
                            @endif
                        </div>
                    @endif

                    @if($itemsToBring)
                        <div class="pt-2 border-t border-emerald-500/20 text-xs">
                            <span class="font-bold text-emerald-300 block mb-1.5 flex items-center gap-1.5">
                                <i class="fa-solid fa-list-check"></i>
                                <span>පළමු දිනයේ රැගෙන ආ යුතු අත්‍යවශ්‍ය ද්‍රව්‍ය ලේඛනය:</span>
                            </span>
                            <pre class="font-sans text-[11px] text-slate-300 bg-slate-900/60 p-3 rounded-xl border border-emerald-500/20 whitespace-pre-line leading-relaxed">{{ $itemsToBring }}</pre>
                        </div>
                    @endif

                    @if($firstEnrollment)
                        <div class="pt-1">
                            <a href="{{ route('public.pass', $firstEnrollment->qr_token) }}" target="_blank"
                               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md transition-all">
                                <i class="fa-solid fa-id-card"></i>
                                <span>දරුවාගේ 3.7" &times; 2.6" QR හැඳුනුම්පත් පාස්පත බලන්න (View QR ID Pass)</span>
                            </a>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    @endif

</div>
@endsection

