@extends('layouts.public')

@section('title', 'Student Pass Lookup')

@section('content')
<div class="max-w-2xl mx-auto py-8">

    <div class="text-center mb-8">
        <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 flex items-center justify-center text-xl mx-auto mb-3 shadow">
            <i class="fa-solid fa-id-card"></i>
        </div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Student Pass Lookup</h1>
        <p class="text-xs text-slate-400 mt-1">Search by Student Admission No., Phone Number, or Full Name to retrieve your active passes</p>
    </div>

    <!-- Search Box -->
    <div class="rounded-2xl bg-slate-800/80 border border-slate-700/80 p-6 shadow-2xl mb-8">
        <form action="{{ route('public.lookup') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </div>
                <input type="text" name="query" value="{{ $query }}" required
                       placeholder="Enter Admission No. (e.g. ADM-00001) or Phone..."
                       class="w-full pl-10 pr-4 py-3 rounded-xl bg-slate-900 border border-slate-700 text-white placeholder-slate-500 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30 transition-all">
            </div>
            <button type="submit" 
                    class="px-6 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-sm shadow-lg shadow-indigo-600/30 flex items-center justify-center gap-2 transition-all">
                <span>Find Passes</span>
                <i class="fa-solid fa-arrow-right text-xs"></i>
            </button>
        </form>

        <div class="mt-3 flex items-center gap-2 text-xs text-slate-400">
            <span>Quick demo search:</span>
            <a href="{{ route('public.lookup', ['query' => 'ADM-00001']) }}" class="text-indigo-400 hover:underline">ADM-00001</a>
            <span>&bull;</span>
            <a href="{{ route('public.lookup', ['query' => 'Alexander']) }}" class="text-indigo-400 hover:underline">Alexander</a>
            <span>&bull;</span>
            <a href="{{ route('public.lookup', ['query' => '555-0101']) }}" class="text-indigo-400 hover:underline">555-0101</a>
        </div>
    </div>

    <!-- Search Results -->
    @if($query)
        <div>
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4">
                Found {{ $enrollments->count() }} enrolled {{ Str::plural('class', $enrollments->count()) }} for "{{ $query }}"
            </h2>

            @if($enrollments->isEmpty())
                <div class="rounded-2xl bg-slate-800/40 border border-slate-700/60 p-8 text-center">
                    <div class="w-12 h-12 rounded-full bg-slate-700/50 text-slate-400 flex items-center justify-center text-xl mx-auto mb-3">
                        <i class="fa-solid fa-user-slash"></i>
                    </div>
                    <p class="text-sm font-semibold text-white">No enrolled passes found</p>
                    <p class="text-xs text-slate-400 mt-1">Please verify your admission number or contact the class teacher.</p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($enrollments as $item)
                        <div class="rounded-2xl bg-slate-800/70 border border-slate-700/80 p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:border-indigo-500/50 transition-all shadow-lg">
                            <div class="flex items-start gap-4">
                                <img src="{{ $item->student->avatar_url }}" alt="{{ $item->student->full_name }}" 
                                     class="w-12 h-12 rounded-xl object-cover border border-indigo-400/40 bg-slate-900 shadow shrink-0"
                                     onerror="this.onerror=null; this.src='{{ asset('images/avatars/' . ($item->student->gender === 'female' ? 'female.svg' : 'male.svg')) }}';">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-base font-bold text-white">{{ $item->student->full_name }}</h3>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                                            {{ $item->student->admission_number }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-300 mt-1 font-medium">{{ $item->classRoom->name }} ({{ $item->classRoom->grade_level }})</p>
                                    <p class="text-[11px] text-slate-400 mt-0.5">Instructor: {{ $item->classRoom->teacher->name }} &bull; UID: <span class="font-mono text-emerald-400">{{ $item->student_class_uid }}</span></p>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 sm:self-center">
                                <a href="{{ route('public.pass', $item->qr_token) }}" 
                                   class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold flex items-center gap-2 shadow transition-all">
                                    <i class="fa-solid fa-id-card"></i>
                                    <span>View & Print ID Pass</span>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

</div>
@endsection
