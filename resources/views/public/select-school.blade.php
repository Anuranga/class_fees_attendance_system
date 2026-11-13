@extends('layouts.public')

@section('title', 'Select Your Pre-School - Online Admission')

@section('content')
<div class="max-w-4xl mx-auto py-8 sm:py-12 space-y-8">

    <div class="text-center space-y-2">
        <span class="inline-block px-3.5 py-1 rounded-full text-xs font-bold bg-amber-400/10 text-amber-300 border border-amber-400/20 mb-1">
            Pre-School Admissions &bull; පෙරපාසල් ඇතුළත් කිරීම්
        </span>
        <h1 class="text-2xl sm:text-4xl font-black text-white tracking-tight">Select Your Pre-School</h1>
        <p class="text-xs sm:text-sm text-slate-400 max-w-xl mx-auto">
            කරුණාකර ඔබගේ දරුවා ඇතුළත් කිරීමට බලාපොරොත්තු වන පෙරපාසල තෝරා අයදුම්පත සම්පූර්ණ කරන්න. (Please choose your pre-school to proceed with the online application.)
        </p>
    </div>

    <!-- Schools Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
        @forelse($schools as $sch)
            <div class="rounded-3xl bg-slate-800/80 border border-slate-700/80 p-6 flex flex-col justify-between shadow-xl hover:border-amber-400/50 transition-all group">
                <div class="space-y-4">
                    <div class="w-16 h-16 rounded-2xl bg-slate-900 border border-slate-700 overflow-hidden flex items-center justify-center p-2 text-2xl text-amber-400">
                        @if($sch->logo_path)
                            <img src="{{ asset('storage/' . $sch->logo_path) }}" class="w-full h-full object-contain">
                        @else
                            <i class="fa-solid fa-shapes"></i>
                        @endif
                    </div>

                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-amber-300 font-mono">Academic Year {{ $sch->academic_year }}</span>
                        <h2 class="text-lg font-extrabold text-white group-hover:text-amber-300 transition-colors">{{ $sch->name }}</h2>
                        <p class="text-xs text-slate-400 mt-1 line-clamp-2">{{ $sch->motto ?: 'Early Childhood Care & Development' }}</p>
                    </div>

                    @if($sch->contact_phone)
                        <p class="text-xs text-slate-400 flex items-center gap-1.5">
                            <i class="fa-solid fa-phone text-emerald-400 text-xs"></i>
                            <span class="font-mono">{{ $sch->contact_phone }}</span>
                        </p>
                    @endif
                </div>

                <div class="pt-6 border-t border-slate-700/60 mt-4">
                    @if($sch->is_admission_open)
                        <a href="{{ route('public.apply', $sch->slug ?: $sch->registration_token) }}"
                           class="w-full py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs shadow-md shadow-amber-500/20 flex items-center justify-center gap-2 transition-all">
                            <span>Apply for {{ $sch->name }}</span>
                            <i class="fa-solid fa-arrow-right text-xs"></i>
                        </a>
                    @else
                        <div class="w-full py-2.5 rounded-xl bg-slate-700/50 text-slate-400 font-bold text-xs text-center">
                            Admissions Currently Closed
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full rounded-2xl bg-slate-800/40 border border-slate-700/60 p-8 text-center text-slate-400 text-xs">
                No pre-schools registered yet.
            </div>
        @endforelse
    </div>

</div>
@endsection

