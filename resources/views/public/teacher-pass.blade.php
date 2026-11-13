@extends('layouts.public')

@section('title', 'Teacher ID Pass - ' . $teacher->name)

@section('content')
<div class="max-w-xl mx-auto py-6 sm:py-10 print:py-0 print:max-w-none">

    <!-- Screen-only Action Toolbar -->
    <div class="no-print mb-8 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-id-badge text-emerald-400"></i>
                <span>Official Faculty & Staff ID Pass</span>
            </h1>
            <p class="text-xs text-slate-400">Standard 3.7" &times; 2.6" format for physical lanyard badge printing</p>
        </div>

        <div class="flex items-center gap-2">
            <button onclick="window.print()" 
                    class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow-lg shadow-emerald-600/30 flex items-center gap-2 transition-all cursor-pointer">
                <i class="fa-solid fa-print"></i>
                <span>Print ID Badge (3.7" &times; 2.6")</span>
            </button>
        </div>
    </div>

    <!-- PRINT & DISPLAY PASS (Exact 3.7in x 2.6in Horizontal ID Card) -->
    <div class="id-card-print-area flex justify-center">
        <div class="id-card-container bg-white text-slate-900 rounded-2xl shadow-2xl border border-slate-200 p-[0.16in] flex flex-col justify-between relative overflow-hidden select-none">

            <!-- Top Header Banner (Emerald Gradient + Pill Badge) -->
            <div class="w-full rounded-xl px-3 py-1.5 flex items-center justify-between gap-2 shrink-0 text-white shadow-2xs"
                 style="background: linear-gradient(135deg, #059669 0%, #0d9488 50%, #047857 100%);">
                <div class="flex items-center gap-2 min-w-0">
                    <div class="w-5 h-5 rounded-lg bg-white/20 backdrop-blur-xs flex items-center justify-center text-[10px] text-white shrink-0 font-black">
                        <i class="fa-solid fa-school"></i>
                    </div>
                    <span class="text-[11px] font-extrabold tracking-wide uppercase truncate">
                        {{ $school->name ?? config('app.name', 'Preschool') }}
                    </span>
                </div>
                <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-white/20 text-white border border-white/30 shrink-0">
                    Faculty Staff
                </span>
            </div>

            <!-- Middle Section: Teacher Info + Central QR Code -->
            <div class="flex items-center justify-between gap-3 my-auto py-1">
                
                <!-- Left: Teacher Details -->
                <div class="flex-1 min-w-0 pr-1">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Instructor / Teacher</p>
                    <h2 class="text-sm font-black text-slate-900 leading-tight truncate">
                        {{ $teacher->name }}
                    </h2>

                    <div class="mt-2 space-y-1">
                        <div class="flex items-center gap-1.5 text-[10px] text-slate-600">
                            <i class="fa-solid fa-envelope text-slate-400 w-3 text-center"></i>
                            <span class="truncate font-mono">{{ $teacher->email }}</span>
                        </div>
                        <div class="flex items-center gap-1.5 text-[10px] text-slate-600">
                            <i class="fa-solid fa-phone text-slate-400 w-3 text-center"></i>
                            <span class="truncate font-mono">{{ $teacher->phone_number ?: 'Preschool Faculty' }}</span>
                        </div>
                        <div class="flex items-center gap-1.5 text-[10px] text-emerald-700 font-bold">
                            <i class="fa-solid fa-shield-halved text-emerald-500 w-3 text-center"></i>
                            <span>Authorized Staff Member</span>
                        </div>
                    </div>
                </div>

                <!-- Right: High Resolution QR Code -->
                <div class="flex flex-col items-center shrink-0">
                    <div class="p-1 rounded-xl bg-slate-50 border border-slate-200 shadow-2xs">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=160x160&data={{ urlencode($qrToken) }}" 
                             alt="Teacher QR Badge" 
                             class="w-24 h-24 rounded-lg object-contain bg-white">
                    </div>
                    <span class="text-[9px] font-mono font-bold text-slate-400 mt-1 tracking-wider">
                        {{ $qrToken }}
                    </span>
                </div>
            </div>

            <!-- Bottom Subfooter -->
            <div class="w-full pt-1.5 border-t border-slate-100 flex items-center justify-between text-[9px] text-slate-400 shrink-0">
                <span class="font-mono text-emerald-800 font-bold">
                    {{ $school->phone_number ? 'Tel: ' . $school->phone_number : 'Preschool Smart Campus' }}
                </span>
                <span class="font-semibold text-slate-500">Scan at Central Scanner</span>
            </div>

        </div>
    </div>

</div>

<style>
    .id-card-container {
        width: 3.7in;
        height: 2.6in;
        box-sizing: border-box;
    }

    @media print {
        body {
            background: white !important;
            color: black !important;
        }
        .no-print, header, nav, footer {
            display: none !important;
        }
        .id-card-print-area {
            padding: 0 !important;
            margin: 0 !important;
        }
        .id-card-container {
            border: 1px solid #cbd5e1 !important;
            box-shadow: none !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
    }
</style>
@endsection
