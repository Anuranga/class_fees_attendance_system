@extends('layouts.app')

@section('title', 'Manage Classes')
@section('page-title', 'My Academic Classes')
@section('page-subtitle', 'Manage classes, student enrollment links, and class configurations')

@section('content')
<div class="space-y-6" x-data="{ createModalOpen: false }">

    <!-- Header & Action Row -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-slate-900">Active Classes ({{ $classes->count() }})</h2>
            <p class="text-xs text-slate-500">Each class has an isolated student register, QR session scanner, and dedicated fee ledger</p>
        </div>

        <button @click="createModalOpen = true" 
                class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md shadow-indigo-600/30 flex items-center justify-center gap-2 transition-all">
            <i class="fa-solid fa-plus"></i>
            <span>Create New Class</span>
        </button>
    </div>

    <!-- Classes Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($classes as $class)
            <div class="rounded-2xl bg-white border border-slate-200/80 p-6 shadow-xs hover:shadow-md transition-all flex flex-col justify-between">
                <div>
                    <!-- Top Status & Grade Level -->
                    <div class="flex items-center justify-between gap-2 mb-3">
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                            {{ $class->grade_level }}
                        </span>

                        <div class="flex items-center gap-2">
                            @if($class->is_registration_open)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    Link Open
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                    Link Paused
                                </span>
                            @endif
                        </div>
                    </div>

                    <h3 class="text-lg font-bold text-slate-900 leading-snug">{{ $class->name }}</h3>
                    <p class="text-xs font-semibold text-emerald-600 mt-1">
                        LKR {{ number_format($class->monthly_fee, 2) }} 
                        <span class="text-slate-400 font-normal">/ {{ $class->isDailyFee() ? 'session' : 'month' }}</span>
                        @if($class->isDailyFee())
                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-md bg-purple-100 text-purple-700 ml-1">Daily Collection</span>
                        @endif
                    </p>

                    <!-- Stats Strip -->
                    <div class="mt-4 py-3 px-4 rounded-xl bg-slate-50 border border-slate-100 grid grid-cols-2 gap-2 text-center">
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase">Enrolled</span>
                            <p class="text-base font-black text-slate-800">{{ $class->students_count }}</p>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase">Monthly Cycle</span>
                            <p class="text-xs font-bold text-indigo-600 mt-1 font-mono">{{ $currentMonth }}</p>
                        </div>
                    </div>

                    <!-- Shareable Registration Link Box -->
                    <div class="mt-4" x-data="{ copied: false }">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">
                            Self-Registration Link
                        </label>
                        <div class="flex items-center gap-1.5 p-1.5 rounded-xl bg-slate-100/90 border border-slate-200">
                            <input type="text" readonly value="{{ $class->join_url }}" 
                                   class="w-full bg-transparent border-0 text-[11px] font-mono text-slate-600 truncate focus:ring-0 px-2 select-all">
                            
                            <button @click="navigator.clipboard.writeText('{{ $class->join_url }}'); copied = true; setTimeout(() => copied = false, 2500)"
                                    class="px-2.5 py-1 rounded-lg text-xs font-bold text-white transition-all shrink-0 flex items-center gap-1"
                                    :class="copied ? 'bg-emerald-600' : 'bg-indigo-600 hover:bg-indigo-500'">
                                <i :class="copied ? 'fa-solid fa-check' : 'fa-solid fa-copy'" class="text-[10px]"></i>
                                <span x-text="copied ? 'Copied!' : 'Copy'"></span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Action Button Cluster -->
                <div class="mt-6 pt-4 border-t border-slate-100 flex flex-col gap-2">
                    <div class="grid grid-cols-3 gap-1.5">
                        <a href="{{ route('teacher.attendance.show', $class) }}" 
                           class="py-2 px-2 rounded-xl bg-purple-50 hover:bg-purple-100 text-purple-700 text-xs font-bold border border-purple-200 flex items-center justify-center gap-1 transition-colors" title="QR Attendance & Scanner">
                            <i class="fa-solid fa-qrcode text-xs"></i>
                            <span>Scan</span>
                        </a>

                        <a href="{{ route('teacher.daily.sheet', ['class_id' => $class->id]) }}" 
                           class="py-2 px-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-800 text-xs font-bold border border-emerald-200 flex items-center justify-center gap-1 transition-colors" title="Daily Sheet & Cash">
                            <i class="fa-solid fa-calendar-check text-xs"></i>
                            <span>Daily Cash</span>
                        </a>

                        <a href="{{ route('teacher.classes.show', $class) }}" 
                           class="py-2 px-2 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-bold border border-indigo-200 flex items-center justify-center gap-1 transition-colors">
                            <i class="fa-solid fa-users text-xs"></i>
                            <span>Students</span>
                        </a>
                    </div>

                    <form action="{{ route('teacher.classes.toggle-registration', $class) }}" method="POST">
                        @csrf
                        <button type="submit" 
                                class="w-full py-1.5 px-3 rounded-xl text-[11px] font-semibold text-slate-600 hover:bg-slate-100 transition-colors flex items-center justify-center gap-1.5">
                            <i class="fa-solid {{ $class->is_registration_open ? 'fa-pause' : 'fa-play' }}"></i>
                            <span>{{ $class->is_registration_open ? 'Pause Self-Registration' : 'Re-open Self-Registration' }}</span>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-full rounded-2xl bg-white border border-slate-200 p-12 text-center">
                <div class="w-16 h-16 rounded-2xl bg-indigo-50 text-indigo-500 flex items-center justify-center text-3xl mx-auto mb-4">
                    <i class="fa-solid fa-chalkboard"></i>
                </div>
                <h3 class="text-base font-bold text-slate-800">No classes created yet</h3>
                <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">Create your first class to generate a self-registration link and begin tracking student attendance and fees.</p>
                <button @click="createModalOpen = true" class="mt-5 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md shadow-indigo-600/30 transition-all inline-flex items-center gap-2">
                    <i class="fa-solid fa-plus"></i>
                    <span>Create Class Now</span>
                </button>
            </div>
        @endforelse
    </div>

    <!-- Create Class Modal -->
    <div x-show="createModalOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
         style="display: none;">
        
        <div @click.away="createModalOpen = false" 
             class="w-full max-w-lg bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden">
            
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Create Academic Class</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Setup a new class and configure its fee structure</p>
                </div>
                <button @click="createModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form action="{{ route('teacher.classes.store') }}" method="POST" class="p-6 space-y-4">
                @csrf

                <div>
                    <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Class Name *</label>
                    <input type="text" id="name" name="name" required placeholder="e.g. Pure Mathematics 2026 Batch"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 text-slate-800 text-sm">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="grade_level" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Grade / Level *</label>
                        <input type="text" id="grade_level" name="grade_level" required placeholder="e.g. Grade 12"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 text-slate-800 text-sm">
                    </div>

                    <div>
                        <label for="fee_type" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Billing Model *</label>
                        <select id="fee_type" name="fee_type" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 text-slate-800 text-sm font-semibold">
                            <option value="monthly" selected>Monthly Fee</option>
                            <option value="daily">Daily / Session Fee</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="monthly_fee" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Fee Amount (LKR) *</label>
                    <input type="number" step="0.01" min="0" id="monthly_fee" name="monthly_fee" required placeholder="e.g. 3500.00 or 500.00"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 text-slate-800 text-sm">
                </div>

                <div class="pt-2">
                    <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700">
                        <input type="checkbox" name="is_registration_open" value="1" checked class="w-4 h-4 rounded text-indigo-600 border-slate-300 focus:ring-indigo-500/20">
                        <span>Enable Student Self-Registration Join Link Immediately</span>
                    </label>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="createModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md shadow-indigo-600/30">Create Class</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
