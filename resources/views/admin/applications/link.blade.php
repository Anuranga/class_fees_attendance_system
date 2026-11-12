@extends('layouts.app')

@section('title', 'Apply Online Link - ' . $school->name)
@section('page-title', 'Apply Online Portal & Links')
@section('page-subtitle', 'Manage your pre-school\'s online registration form, custom WhatsApp link, and QR admission pass')

@section('content')
<div class="space-y-6">

    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2.5 shadow-xs">
            <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Hero Banner with Pre-School Identity & Status -->
    <div class="rounded-3xl bg-gradient-to-r from-emerald-600 via-teal-600 to-indigo-700 p-6 sm:p-8 text-white shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="flex items-start gap-4">
            <div class="w-16 h-16 rounded-2xl bg-white/10 border border-white/20 backdrop-blur-md flex items-center justify-center shrink-0 text-white text-2xl shadow-inner">
                @if($school->logo_path)
                    <img src="{{ asset('storage/' . $school->logo_path) }}" class="w-full h-full object-contain p-1 rounded-2xl">
                @else
                    <i class="fa-solid fa-shapes"></i>
                @endif
            </div>
            <div class="space-y-1">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="px-3 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-white/20 text-white backdrop-blur-xs">
                        {{ $school->academic_year }} Admissions
                    </span>
                    @if($school->is_admission_open)
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-emerald-400 text-slate-950 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-950 animate-ping"></span>
                            Admissions Open
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-rose-400 text-slate-950">
                            Admissions Closed
                        </span>
                    @endif
                </div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight">{{ $school->name }}</h1>
                <p class="text-xs text-white/80 max-w-xl leading-relaxed">
                    {{ $school->motto ?: 'Your dedicated pre-school student application portal. Each preschool has its own isolated link and applicants.' }}
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('public.apply', $school->slug ?: $school->registration_token) }}" target="_blank"
               class="px-4 py-2.5 rounded-xl bg-white text-emerald-800 hover:bg-slate-100 font-bold text-xs shadow-md transition-all flex items-center gap-2">
                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                <span>Open Parent Form</span>
            </a>
            <a href="{{ route('admin.applications.index') }}"
               class="px-4 py-2.5 rounded-xl bg-black/20 hover:bg-black/30 text-white font-bold text-xs border border-white/20 transition-all flex items-center gap-2">
                <i class="fa-solid fa-file-signature"></i>
                <span>Review Applications ({{ $counts['all'] }})</span>
            </a>
        </div>
    </div>

    <!-- 4 Quick Metric Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Total Registered</span>
            <span class="text-2xl font-black text-slate-900 mt-1 block">{{ $counts['all'] }}</span>
            <span class="text-[11px] text-slate-500">via this school link</span>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-amber-500 block">Pending Review</span>
            <span class="text-2xl font-black text-amber-600 mt-1 block">{{ $counts['pending'] }}</span>
            <span class="text-[11px] text-slate-500">awaiting decision</span>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-cyan-600 block">Approved</span>
            <span class="text-2xl font-black text-cyan-600 mt-1 block">{{ $counts['approved'] }}</span>
            <span class="text-[11px] text-slate-500">selection notice sent</span>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 block">Enrolled &amp; Pass Ready</span>
            <span class="text-2xl font-black text-emerald-600 mt-1 block">{{ $counts['enrolled'] }}</span>
            <span class="text-[11px] text-slate-500">with official QR pass</span>
        </div>
    </div>

    <!-- Main 2-Column Section: Links & QR Code Poster -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left 2 Cols: Sharable WhatsApp Link & Controls -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Primary WhatsApp Link Box -->
            <div class="rounded-3xl bg-white border border-slate-200/80 p-6 sm:p-8 shadow-xs space-y-5" x-data="{ copiedToken: false, copiedSlug: false }">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2">
                        <i class="fa-brands fa-whatsapp text-emerald-600 text-xl"></i>
                        <h2 class="text-base font-extrabold text-slate-800 uppercase tracking-wider">Your Pre-School WhatsApp Admission Link</h2>
                    </div>
                    <span class="text-xs font-bold text-slate-400">Shareable Everywhere</span>
                </div>

                <p class="text-xs text-slate-600 leading-relaxed">
                    Parents can complete the application in <strong>Sinhala or English</strong> on any mobile phone. When submitted, the application is stored exclusively in your pre-school account.
                </p>

                <!-- Token URL Field -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">
                        Primary Registration Link (Direct Token URL)
                    </label>
                    <div class="flex items-center gap-2">
                        <input type="text" readonly value="{{ url('/apply/' . $school->registration_token) }}"
                               class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-xs font-mono font-bold text-slate-800 select-all">
                        <button @click="navigator.clipboard.writeText('{{ url('/apply/' . $school->registration_token) }}'); copiedToken = true; setTimeout(() => copiedToken = false, 2500)"
                                type="button"
                                class="px-4 py-2.5 rounded-xl text-xs font-bold transition-all shrink-0 flex items-center gap-1.5 shadow-xs"
                                :class="copiedToken ? 'bg-emerald-600 text-white' : 'bg-slate-800 hover:bg-slate-700 text-white'">
                            <i :class="copiedToken ? 'fa-solid fa-check' : 'fa-solid fa-copy'"></i>
                            <span x-text="copiedToken ? 'Copied!' : 'Copy'"></span>
                        </button>
                    </div>
                </div>

                <!-- Friendly Slug URL Field -->
                @if($school->slug)
                    <div class="space-y-1.5 pt-2">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">
                            Friendly Branded Link (Custom Slug URL)
                        </label>
                        <div class="flex items-center gap-2">
                            <input type="text" readonly value="{{ url('/apply/' . $school->slug) }}"
                                   class="w-full px-4 py-2.5 rounded-xl bg-emerald-50/50 border border-emerald-300 text-xs font-mono font-bold text-emerald-900 select-all">
                            <button @click="navigator.clipboard.writeText('{{ url('/apply/' . $school->slug) }}'); copiedSlug = true; setTimeout(() => copiedSlug = false, 2500)"
                                    type="button"
                                    class="px-4 py-2.5 rounded-xl text-xs font-bold transition-all shrink-0 flex items-center gap-1.5 shadow-xs"
                                    :class="copiedSlug ? 'bg-emerald-600 text-white' : 'bg-emerald-700 hover:bg-emerald-600 text-white'">
                                <i :class="copiedSlug ? 'fa-solid fa-check' : 'fa-solid fa-copy'"></i>
                                <span x-text="copiedSlug ? 'Copied!' : 'Copy Slug Link'"></span>
                            </button>
                        </div>
                    </div>
                @endif

                <!-- One-Click WhatsApp Share Action -->
                @php
                    $activeUrl = url('/apply/' . ($school->slug ?: $school->registration_token));
                    $waInvitation = "සුභ දවසක්! {$school->name} සඳහා නව පෙරපාසල් ළමුන් ඇතුළත් කිරීම් දැන් ආරම්භ කර ඇත. පහත සබැඳියෙන් ඔබගේ දරුවාගේ අයදුම්පත පහසුවෙන්ම සම්පූර්ණ කරන්න:\n" . $activeUrl . "\n\nවිමසීම්: " . ($school->contact_phone ?: 'පාසල් කාර්යාලය');
                @endphp
                <div class="pt-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <p class="text-xs text-slate-500">
                        <i class="fa-solid fa-shield-halved text-emerald-600 mr-1"></i>
                        Other schools cannot see or access your applicants.
                    </p>

                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <a href="https://wa.me/?text={{ urlencode($waInvitation) }}" target="_blank"
                           class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md shadow-emerald-600/30 flex items-center justify-center gap-2 transition-all">
                            <i class="fa-brands fa-whatsapp text-sm"></i>
                            <span>Share on WhatsApp</span>
                        </a>

                        <form action="{{ route('admin.applications.regenerate-link') }}" method="POST" onsubmit="return confirm('Are you sure you want to generate a new registration link token? The old token link will no longer accept applications.');">
                            @csrf
                            <button type="submit"
                                    class="px-3 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-all"
                                    title="Regenerate token">
                                <i class="fa-solid fa-arrows-rotate"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Portal Customization Card (Slug, Academic Year, Status) -->
            <div class="rounded-3xl bg-white border border-slate-200/80 p-6 sm:p-8 shadow-xs space-y-4">
                <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-800 border-b border-slate-100 pb-2 flex items-center gap-2">
                    <i class="fa-solid fa-sliders text-indigo-600"></i>
                    <span>Portal &amp; Link Settings</span>
                </h3>

                <form action="{{ route('admin.applications.update-link-settings') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                Custom URL Slug (e.g. seven-bees, miracle) *
                            </label>
                            <div class="flex items-center rounded-xl border border-slate-300 bg-slate-50 overflow-hidden focus-within:ring-2 focus-within:ring-indigo-500">
                                <span class="px-3 text-xs text-slate-500 font-mono">/apply/</span>
                                <input type="text" name="slug" value="{{ old('slug', $school->slug) }}" required
                                       class="w-full py-2.5 pr-3 text-xs font-mono font-bold text-slate-800 bg-transparent border-0 focus:ring-0">
                            </div>
                            @error('slug')
                                <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                Academic Year *
                            </label>
                            <input type="text" name="academic_year" value="{{ old('academic_year', $school->academic_year) }}" required
                                   class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-bold text-slate-800 focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="flex items-center gap-2.5 cursor-pointer p-3 rounded-xl bg-slate-50 border border-slate-200">
                                <input type="checkbox" name="is_admission_open" value="1" {{ $school->is_admission_open ? 'checked' : '' }}
                                       class="w-4 h-4 rounded text-indigo-600 border-slate-300 focus:ring-indigo-500">
                                <div>
                                    <span class="text-xs font-bold text-slate-800 block">Accept Online Applications via this link</span>
                                    <span class="text-[11px] text-slate-500">If unchecked, parents visiting your link will see a polite notice that admissions are currently closed.</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="pt-2 flex items-center justify-end">
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-md transition-all">
                            Save Portal Link Settings
                        </button>
                    </div>
                </form>
            </div>

            <!-- Recent Applications Table for this Pre-School -->
            <div class="rounded-3xl bg-white border border-slate-200/80 p-6 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-clock-rotate-left text-slate-500"></i>
                        <span>Recent Applicants via Your Link</span>
                    </h3>
                    <a href="{{ route('admin.applications.index') }}" class="text-xs font-bold text-indigo-600 hover:underline">
                        View All ({{ $counts['all'] }}) &rarr;
                    </a>
                </div>

                @if($recentApplications->isEmpty())
                    <div class="p-6 text-center text-slate-400 text-xs">
                        No applications received yet. Copy your WhatsApp link above and share it with parents to start receiving submissions!
                    </div>
                @else
                    <div class="divide-y divide-slate-100 text-xs">
                        @foreach($recentApplications as $app)
                            <div class="py-3 flex items-center justify-between gap-3">
                                <div>
                                    <p class="font-bold text-slate-900 text-sm">{{ $app->child_full_name }}</p>
                                    <p class="text-[11px] text-slate-500 font-mono">{{ $app->application_number }} &bull; {{ $app->target_grade_level }} &bull; WhatsApp: {{ $app->whatsapp_number }}</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase
                                        {{ $app->status === 'enrolled' ? 'bg-emerald-100 text-emerald-800' : ($app->status === 'approved' ? 'bg-cyan-100 text-cyan-800' : ($app->status === 'rejected' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800')) }}">
                                        {{ $app->status }}
                                    </span>
                                    <a href="{{ route('admin.applications.show', $app) }}"
                                       class="px-3 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs">
                                        Review
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>

        <!-- Right Col: Printable QR Code Admission Notice Poster -->
        <div class="space-y-6">

            <div class="rounded-3xl bg-white border border-slate-200/80 p-6 shadow-xs text-center space-y-4">
                <span class="inline-block px-3 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-indigo-50 text-indigo-700">
                    Scannable QR Code
                </span>
                <h3 class="text-base font-extrabold text-slate-900">Entrance Noticeboard QR</h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Print and display this QR code at your pre-school gate or reception desk. Parents can scan it with any phone camera to apply immediately.
                </p>

                <!-- High Quality QR Code Generator using QuickChart API -->
                @php
                    $qrCodeUrl = 'https://quickchart.io/qr?text=' . urlencode($activeUrl) . '&size=240&margin=2';
                @endphp
                <div class="w-56 h-56 mx-auto p-3 rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50 flex items-center justify-center">
                    <img src="{{ $qrCodeUrl }}" alt="Pre-School Admission Link QR" class="w-full h-full object-contain rounded-xl">
                </div>

                <div class="text-[11px] font-mono text-slate-400 break-all px-2">
                    {{ $activeUrl }}
                </div>

                <div class="pt-2">
                    <button type="button" onclick="window.print()"
                            class="w-full py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition-all flex items-center justify-center gap-2">
                        <i class="fa-solid fa-print"></i>
                        <span>Print Admission QR Notice</span>
                    </button>
                </div>
            </div>

            <!-- Day One Checklist Reminder -->
            <div class="rounded-3xl bg-slate-900 text-white p-6 shadow-xl space-y-3">
                <div class="flex items-center gap-2 text-amber-300 text-xs font-bold uppercase tracking-wider">
                    <i class="fa-solid fa-list-check text-sm"></i>
                    <span>Day-One Checklist Preview</span>
                </div>
                <p class="text-xs text-slate-300 leading-relaxed">
                    When you approve or enroll a child, the parent automatically receives this Sinhala checklist on WhatsApp:
                </p>
                <div class="p-3 rounded-xl bg-slate-800/80 border border-slate-700 text-[11px] font-sans text-slate-200 leading-relaxed whitespace-pre-line max-h-48 overflow-y-auto">
                    {{ $school->items_to_bring_sinhala ?: \App\Models\SchoolSetting::defaultSinhalaItems() }}
                </div>
                <a href="{{ route('admin.settings.school') }}" class="inline-block text-xs font-bold text-amber-400 hover:underline">
                    Edit Checklist in School Settings &rarr;
                </a>
            </div>

        </div>

    </div>

</div>
@endsection
