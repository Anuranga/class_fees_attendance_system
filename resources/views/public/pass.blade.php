@extends('layouts.public')

@section('title', 'Student ID Pass - ' . $student->full_name)

@section('content')
<div class="max-w-xl mx-auto py-6 sm:py-10">

    <!-- Screen-only Action Toolbar -->
    <div class="no-print mb-8 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-id-card-clip text-indigo-400"></i>
                <span>Official Student ID Pass</span>
            </h1>
            <p class="text-xs text-slate-400">Present this card or QR code to the instructor at each session</p>
        </div>

        <div class="flex items-center gap-2">
            <button onclick="window.print()" 
                    class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-lg shadow-indigo-600/30 flex items-center gap-2 transition-all">
                <i class="fa-solid fa-print"></i>
                <span>Print Card / PDF</span>
            </button>
            <a href="{{ route('public.lookup') }}" 
               class="px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold border border-slate-700 transition-all">
                <i class="fa-solid fa-search"></i>
            </a>
        </div>
    </div>

    <!-- Alert Message if any -->
    @if(session('success'))
        <div class="no-print mb-6 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-300 text-xs font-medium flex items-center gap-3">
            <i class="fa-solid fa-circle-check text-emerald-400 text-base"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- PRINT & DISPLAY PASS (CR80 ID Card standard 3.375" x 2.125" aspect ratio formatted for print & display) -->
    <div class="id-card-print-area flex justify-center">
        <div class="w-full max-w-[380px] rounded-2xl bg-gradient-to-b from-slate-900 via-slate-900 to-indigo-950 border-2 border-indigo-500/40 shadow-2xl p-6 text-white relative overflow-hidden">
            
            <!-- Background Decorative Glow -->
            <div class="absolute -top-16 -right-16 w-36 h-36 bg-indigo-500/20 rounded-full blur-xl pointer-events-none"></div>
            <div class="absolute -bottom-16 -left-16 w-36 h-36 bg-purple-500/20 rounded-full blur-xl pointer-events-none"></div>

            <!-- Card Header: Institution & Class -->
            <div class="flex items-start justify-between border-b border-indigo-500/30 pb-3 mb-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg gradient-brand flex items-center justify-center text-white shadow text-sm">
                        <i class="fa-solid fa-graduation-cap"></i>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase tracking-widest text-indigo-400 font-extrabold">Student Pass</p>
                        <h2 class="text-sm font-bold text-white leading-tight truncate max-w-[190px]">{{ $classRoom->name }}</h2>
                    </div>
                </div>
                <div class="text-right">
                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                        {{ $classRoom->grade_level }}
                    </span>
                    <p class="text-[9px] text-slate-400 mt-0.5 font-mono">{{ $enrollment->student_class_uid }}</p>
                </div>
            </div>

            <!-- Card Center: Student Details & QR Code -->
            <div class="flex items-center gap-4 py-2">
                <!-- QR Code Box -->
                <div class="w-36 h-36 p-2 rounded-xl bg-white flex items-center justify-center shadow-lg border border-indigo-200 shrink-0">
                    <div class="w-full h-full flex items-center justify-center [&>svg]:w-full [&>svg]:h-full">
                        {!! $qrCodeSvg !!}
                    </div>
                </div>

                <!-- Student Information -->
                <div class="flex-1 min-w-0 space-y-2 text-left">
                    <div class="flex items-center gap-2.5">
                        <img src="{{ $student->avatar_url }}" alt="{{ $student->full_name }}" 
                             class="w-12 h-12 rounded-xl object-cover border-2 border-indigo-400/60 shadow-md bg-slate-800 shrink-0"
                             onerror="this.onerror=null; this.src='{{ asset('images/avatars/' . ($student->gender === 'female' ? 'female.svg' : 'male.svg')) }}';">
                        <div class="min-w-0">
                            <p class="text-[9px] uppercase tracking-wider text-slate-400 font-bold">Student Name</p>
                            <p class="text-sm font-black text-white truncate leading-snug">{{ $student->full_name }}</p>
                        </div>
                    </div>

                    <div>
                        <p class="text-[9px] uppercase tracking-wider text-slate-400 font-bold">Admission No.</p>
                        <p class="text-xs font-mono font-bold text-indigo-300 truncate">{{ $student->admission_number }}</p>
                    </div>

                    <div>
                        <p class="text-[9px] uppercase tracking-wider text-slate-400 font-bold">Class UID</p>
                        <p class="text-xs font-mono font-semibold text-emerald-400 truncate">{{ $enrollment->student_class_uid }}</p>
                    </div>
                </div>
            </div>

            <!-- Card Footer: Teacher & Guardian Emergency Phone -->
            <div class="mt-4 pt-3 border-t border-indigo-500/30 flex items-center justify-between text-[10px] text-slate-300">
                <div>
                    <span class="text-slate-400 block text-[9px]">Instructor</span>
                    <span class="font-semibold text-white">{{ $teacher->name }}</span>
                </div>
                <div class="text-right">
                    <span class="text-slate-400 block text-[9px]">Emergency Contact</span>
                    <span class="font-bold text-amber-300">{{ $student->guardian_phone }}</span>
                </div>
            </div>

            <!-- Bottom Hash Pill -->
            <div class="mt-2 text-center">
                <span class="text-[8px] font-mono text-slate-500 tracking-wider">
                    TOKEN: {{ substr($enrollment->qr_token, 0, 18) }}...
                </span>
            </div>

        </div>
    </div>

    <!-- Instructions for Student/Parent -->
    <div class="no-print mt-8 rounded-xl bg-slate-800/60 border border-slate-700/60 p-5 text-xs text-slate-300 space-y-2.5">
        <h3 class="font-bold text-white text-sm flex items-center gap-2">
            <i class="fa-solid fa-lightbulb text-amber-400"></i>
            <span>How to use your Student Pass</span>
        </h3>
        <p>1. <strong>Print or Save on Mobile:</strong> Click the "Print Card / PDF" button above. You can print it on paper/cardstock or save it as a PDF screenshot on your smartphone.</p>
        <p>2. <strong>Speedy Check-In:</strong> Upon arriving for class, present this QR code to the teacher's scanner device. The camera marks your attendance in under a second.</p>
        <p>3. <strong>Monthly Cash Fees:</strong> Your Unique Class UID (<code class="text-indigo-300">{{ $enrollment->student_class_uid }}</code>) is your payment reference when paying class fees.</p>
    </div>

</div>
@endsection
