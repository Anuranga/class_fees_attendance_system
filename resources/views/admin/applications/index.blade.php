@extends('layouts.app')

@section('title', 'Online Admission Applications')
@section('page-title', 'Admission Applications')
@section('page-subtitle', 'Review parent online submissions, schedule interviews, and approve student enrollments')

@section('content')
<div class="space-y-6">

    <!-- KPI Status Tabs Strip -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
        <a href="{{ route('admin.applications.index', ['status' => 'all']) }}" 
           class="p-4 rounded-2xl border transition-all {{ $status === 'all' ? 'bg-indigo-600 text-white border-indigo-600 shadow-md' : 'bg-white text-slate-800 border-slate-200 hover:bg-slate-50' }}">
            <span class="text-[10px] uppercase font-bold tracking-wider opacity-80 block">All Applications</span>
            <span class="text-2xl font-black mt-1 block">{{ $counts['all'] }}</span>
        </a>

        <a href="{{ route('admin.applications.index', ['status' => 'pending']) }}" 
           class="p-4 rounded-2xl border transition-all {{ $status === 'pending' ? 'bg-amber-500 text-slate-950 border-amber-500 shadow-md font-bold' : 'bg-white text-slate-800 border-slate-200 hover:bg-slate-50' }}">
            <span class="text-[10px] uppercase font-bold tracking-wider opacity-80 block">Pending Review</span>
            <span class="text-2xl font-black mt-1 block text-amber-600">{{ $counts['pending'] }}</span>
        </a>

        <a href="{{ route('admin.applications.index', ['status' => 'approved']) }}" 
           class="p-4 rounded-2xl border transition-all {{ $status === 'approved' ? 'bg-cyan-600 text-white border-cyan-600 shadow-md' : 'bg-white text-slate-800 border-slate-200 hover:bg-slate-50' }}">
            <span class="text-[10px] uppercase font-bold tracking-wider opacity-80 block">Approved</span>
            <span class="text-2xl font-black mt-1 block text-cyan-600">{{ $counts['approved'] }}</span>
        </a>

        <a href="{{ route('admin.applications.index', ['status' => 'enrolled']) }}" 
           class="p-4 rounded-2xl border transition-all {{ $status === 'enrolled' ? 'bg-emerald-600 text-white border-emerald-600 shadow-md' : 'bg-white text-slate-800 border-slate-200 hover:bg-slate-50' }}">
            <span class="text-[10px] uppercase font-bold tracking-wider opacity-80 block">Enrolled & QR Ready</span>
            <span class="text-2xl font-black mt-1 block text-emerald-600">{{ $counts['enrolled'] }}</span>
        </a>

        <a href="{{ route('admin.applications.index', ['status' => 'rejected']) }}" 
           class="p-4 rounded-2xl border transition-all {{ $status === 'rejected' ? 'bg-rose-600 text-white border-rose-600 shadow-md' : 'bg-white text-slate-800 border-slate-200 hover:bg-slate-50' }}">
            <span class="text-[10px] uppercase font-bold tracking-wider opacity-80 block">Not Selected</span>
            <span class="text-2xl font-black mt-1 block text-rose-600">{{ $counts['rejected'] }}</span>
        </a>
    </div>

    <!-- Search & Filter Bar -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-4">
        <form action="{{ route('admin.applications.index') }}" method="GET" class="flex-1 w-full sm:max-w-md flex items-center gap-2">
            <input type="hidden" name="status" value="{{ $status }}">
            <div class="relative flex-1">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search child name, app number, WhatsApp..."
                       class="w-full pl-9 pr-4 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-hidden">
            </div>
            <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition-colors">
                Search
            </button>
        </form>

        <!-- Sharable WhatsApp Admission Link Pill -->
        <div class="flex items-center gap-2" x-data="{ copied: false }">
            <span class="text-xs text-slate-500 font-semibold hidden md:inline">Admission Form Link:</span>
            <button @click="navigator.clipboard.writeText('{{ $school->registration_url }}'); copied = true; setTimeout(() => copied = false, 2500)"
                    type="button"
                    class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 shadow-xs"
                    :class="copied ? 'bg-emerald-600 text-white' : 'bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200'">
                <i :class="copied ? 'fa-solid fa-check' : 'fa-solid fa-copy text-emerald-600'"></i>
                <span x-text="copied ? 'Copied Link!' : 'Copy WhatsApp Form Link'"></span>
            </button>

            @php
                $waShareText = "පෙරපාසල් නව ළමුන් ඇතුළත් කිරීම් සඳහා වන අයදුම්පත මෙතැනින් සම්පූර්ණ කරන්න:\n" . $school->registration_url;
            @endphp
            <a href="https://wa.me/?text={{ urlencode($waShareText) }}" target="_blank"
               class="px-3 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold transition-all flex items-center gap-1.5 shadow-xs"
               title="Share on WhatsApp">
                <i class="fa-brands fa-whatsapp text-sm"></i>
                <span class="hidden sm:inline">Share on WhatsApp</span>
            </a>
        </div>
    </div>

    <!-- Applications Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <th class="py-3.5 px-4">Application</th>
                        <th class="py-3.5 px-4">Child Details</th>
                        <th class="py-3.5 px-4">Program</th>
                        <th class="py-3.5 px-4">Parent WhatsApp</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4">Submitted</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($applications as $app)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-900">
                                {{ $app->application_number }}
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-slate-100 border border-slate-200 shrink-0 overflow-hidden flex items-center justify-center font-bold text-slate-600">
                                        @if($app->photo_path)
                                            <img src="{{ asset('storage/' . $app->photo_path) }}" class="w-full h-full object-cover">
                                        @else
                                            <i class="fa-solid fa-child"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-900 text-sm">{{ $app->child_full_name }}</p>
                                        <p class="text-[11px] text-slate-400">
                                            {{ $app->gender === 'female' ? 'Girl' : 'Boy' }} &bull; DOB: {{ $app->date_of_birth->format('Y-m-d') }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded-md text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                    {{ $app->target_grade_level }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-mono">
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $app->whatsapp_number) }}" target="_blank" 
                                   class="text-emerald-700 hover:text-emerald-900 font-bold flex items-center gap-1">
                                    <i class="fa-brands fa-whatsapp text-emerald-600"></i>
                                    <span>{{ $app->whatsapp_number }}</span>
                                </a>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($app->status === 'enrolled')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">
                                        <i class="fa-solid fa-check text-[9px]"></i> Enrolled
                                    </span>
                                @elseif($app->status === 'approved')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-cyan-100 text-cyan-800">
                                        <i class="fa-solid fa-thumbs-up text-[9px]"></i> Approved
                                    </span>
                                @elseif($app->status === 'interview_scheduled')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-100 text-blue-800">
                                        <i class="fa-solid fa-calendar text-[9px]"></i> Interview
                                    </span>
                                @elseif($app->status === 'rejected')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800">
                                        <i class="fa-solid fa-xmark text-[9px]"></i> Rejected
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800">
                                        <i class="fa-solid fa-clock text-[9px]"></i> Pending
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-slate-500 text-[11px]">
                                {{ $app->created_at->format('M d, Y') }}
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <a href="{{ route('admin.applications.show', $app) }}" 
                                   class="px-3 py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-xs border border-indigo-200 transition-colors inline-flex items-center gap-1">
                                    <span>Review</span>
                                    <i class="fa-solid fa-chevron-right text-[10px]"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <i class="fa-solid fa-inbox text-3xl mb-2 text-slate-300 block"></i>
                                <span>No admission applications found for the selected criteria.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $applications->links() }}
        </div>
    </div>

</div>
@endsection

