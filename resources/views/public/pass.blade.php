@extends('layouts.public')

@section('title', 'Student ID Pass - ' . $student->full_name)

@section('content')
<div class="max-w-xl mx-auto py-6 sm:py-10 print:py-0 print:max-w-none">

    <!-- Screen-only Action Toolbar -->
    <div class="no-print mb-8 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-id-card-clip text-indigo-400"></i>
                <span>Official Student ID Card</span>
            </h1>
            <p class="text-xs text-slate-400">Formatted to exact 3.7" &times; 2.6" inches for easy physical printing</p>
        </div>

        <div class="flex items-center gap-2">
            <button onclick="window.print()" 
                    class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-lg shadow-indigo-600/30 flex items-center gap-2 transition-all cursor-pointer">
                <i class="fa-solid fa-print"></i>
                <span>Print ID Card (3.7" &times; 2.6")</span>
            </button>
            <a href="{{ route('public.lookup') }}" 
               class="px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold border border-slate-700 transition-all"
               title="Find another pass">
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

    @if(session('info'))
        <div class="no-print mb-6 p-4 rounded-xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-300 text-xs font-medium flex items-center gap-3">
            <i class="fa-solid fa-circle-info text-indigo-400 text-base"></i>
            <span>{{ session('info') }}</span>
        </div>
    @endif

    @php
        $shortId = str_pad((string) $student->id, 4, '0', STR_PAD_LEFT);
        $gradeNumber = preg_replace('/^grade\s*/i', '', trim((string) $classRoom->grade_level));
        $issuedDate = ($enrollment->created_at ?? now())->format('M Y');
        $portalName = config('app.name', 'EduTrack');
        $portalDomain = parse_url(config('app.url'), PHP_URL_HOST) ?: 'edutrack.lk';
    @endphp

    <!-- PRINT & DISPLAY PASS (Exact 3.7in x 2.6in Horizontal ID Card) -->
    <div class="id-card-print-area flex justify-center">
        <div class="id-card-container bg-white text-slate-900 rounded-2xl shadow-2xl border border-slate-200 p-[0.16in] flex flex-col justify-between relative overflow-hidden select-none">

            <!-- Top EduTrack Brand Header Banner (Distinct Deep Indigo-Violet Gradient + Pill Badge) -->
            <div class="w-full rounded-xl px-3 py-1.5 flex items-center justify-between gap-2 shrink-0 text-white shadow-2xs"
                 style="background: linear-gradient(135deg, #312e81 0%, #4f46e5 55%, #7c3aed 100%) !important; -webkit-print-color-adjust: exact; print-color-adjust: exact;">
                <div class="flex items-center gap-2 min-w-0">
                    <div class="w-5 h-5 rounded-md bg-white/15 border border-white/25 flex items-center justify-center text-[10px] shrink-0">
                        <i class="fa-solid fa-graduation-cap"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[7.5px] uppercase tracking-[0.18em] text-indigo-200 font-bold leading-none">
                            {{ $portalName }} &bull; Student Pass
                        </p>
                        <h3 class="font-extrabold text-[11.5px] tracking-wide leading-tight truncate mt-0.5">
                            {{ $classRoom->name }}
                        </h3>
                    </div>
                </div>
                <span class="px-2 py-0.5 rounded-md bg-white/20 border border-white/30 text-[9.5px] font-extrabold tracking-wider uppercase leading-none whitespace-nowrap shrink-0">
                    {{ $classRoom->grade_level }}
                </span>
            </div>

            <!-- Middle Body: Left Photo + Right Details & QR -->
            <div class="flex items-stretch gap-3 my-auto py-1.5 flex-1 min-h-0">
                <!-- Left Student Photo -->
                <div class="w-[1.24in] h-[1.46in] rounded-xl overflow-hidden bg-slate-100 border-2 border-indigo-100 shrink-0 shadow-2xs">
                    <img src="{{ $student->avatar_url }}" 
                         alt="{{ $student->full_name }}" 
                         class="w-full h-full object-cover object-center"
                         onerror="this.onerror=null; this.src='{{ asset('images/avatars/' . ($student->gender === 'female' ? 'female.svg' : 'male.svg')) }}';">
                </div>

                <!-- Right Student Details & QR -->
                <div class="flex-1 min-w-0 flex flex-col justify-between py-0.5">
                    <!-- Top Student Info -->
                    <div class="min-w-0">
                        <h2 class="text-[14.5px] font-extrabold text-slate-900 leading-tight truncate">
                            {{ $student->full_name }}
                        </h2>
                        <p class="text-[10.5px] font-semibold text-indigo-700 leading-snug truncate mt-0.5">
                            {{ $teacher->name }}
                        </p>
                        <p class="text-[9px] text-slate-500 font-mono leading-snug truncate mt-0.5">
                            {{ $enrollment->student_class_uid }} &bull; {{ $student->admission_number }}
                        </p>
                        <div class="w-12 h-[2px] rounded-full bg-indigo-500/40 mt-1.5"
                             style="background-color: #818cf8 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact;"></div>
                    </div>

                    <!-- Bottom Row: ID/Grade on Left, QR Code on Right -->
                    <div class="flex items-end justify-between gap-2 mt-auto pt-1">
                        <div class="space-y-1 text-[10.5px] leading-none pb-0.5">
                            <div class="flex items-center gap-1.5">
                                <span class="text-slate-500 font-semibold w-9">ID:</span>
                                <span class="font-extrabold text-slate-900 font-mono">#{{ $shortId }}</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="text-slate-500 font-semibold w-9">Level:</span>
                                <span class="font-extrabold text-indigo-900">{{ $gradeNumber }}</span>
                            </div>
                        </div>

                        <!-- QR Code Box -->
                        <div class="w-[0.76in] h-[0.76in] p-1.5 rounded-xl bg-white border border-indigo-200 shadow-2xs flex items-center justify-center shrink-0">
                            <div class="w-full h-full flex items-center justify-center [&>svg]:w-full [&>svg]:h-full">
                                {!! $qrCodeSvg !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Footer: Domain Left & Issued Date Right -->
            <div class="flex items-center justify-between text-[9px] text-slate-400 font-semibold leading-none pt-1 border-t border-slate-100 shrink-0">
                <span>{{ $portalName }} ({{ $portalDomain }})</span>
                <span>Issued: {{ $issuedDate }}</span>
            </div>

        </div>
    </div>

    <!-- Instructions for Student/Parent -->
    <div class="no-print mt-8 rounded-xl bg-slate-800/60 border border-slate-700/60 p-5 text-xs text-slate-300 space-y-2.5">
        <h3 class="font-bold text-white text-sm flex items-center gap-2">
            <i class="fa-solid fa-lightbulb text-amber-400"></i>
            <span>How to print & use your Student ID Card</span>
        </h3>
        <p>1. <strong>Exact 3.7" &times; 2.6" Print Size:</strong> Click <strong>"Print ID Card"</strong> above. In the browser print dialog, ensure <em>Background Graphics</em> is enabled and scale is set to <em>100% / Default</em>.</p>
        <p>2. <strong>Speedy QR Check-In:</strong> Present this card at class entrance. The instructor's scanner reads the QR code in under a second.</p>
        <p>3. <strong>Student ID & Reference:</strong> Your card ID is <code class="text-indigo-300 font-bold">#{{ $shortId }}</code> (Class UID: <code class="text-indigo-300">{{ $enrollment->student_class_uid }}</code>, Admission: <code class="text-slate-300">{{ $student->admission_number }}</code>).</p>
    </div>

</div>
@endsection
