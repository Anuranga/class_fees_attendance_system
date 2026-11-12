@extends('layouts.app')

@section('title', 'Pre-School Settings & Branding')
@section('page-title', 'Pre-School Identity & Settings')
@section('page-subtitle', 'Configure pre-school branding, logo, WhatsApp admission link, vision, mission, and day-one items checklist')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2">
            <i class="fa-solid fa-circle-check text-emerald-600"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- WhatsApp Shareable Admission Link Banner -->
    <div class="p-6 rounded-3xl bg-gradient-to-r from-emerald-600 via-teal-600 to-indigo-700 text-white shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-5"
         x-data="{ copied: false }">
        <div class="space-y-1">
            <span class="inline-block px-3 py-1 rounded-full text-[11px] font-extrabold uppercase tracking-wider bg-white/20 text-white backdrop-blur-xs">
                SaaS WhatsApp Registration Link
            </span>
            <h2 class="text-xl font-black">Pre-School Online Admission Link</h2>
            <p class="text-xs text-white/80 max-w-xl">
                Send this link to parents on WhatsApp, Facebook, or your school website so they can submit their child's details directly in Sinhala/English.
            </p>
            <div class="pt-2">
                <span class="inline-block px-3 py-1.5 rounded-xl bg-black/20 text-xs font-mono font-bold text-emerald-200 break-all select-all">
                    {{ $school->registration_url }}
                </span>
            </div>
        </div>

        <div class="flex items-center gap-2.5 shrink-0">
            <button @click="navigator.clipboard.writeText('{{ $school->registration_url }}'); copied = true; setTimeout(() => copied = false, 2500)"
                    type="button"
                    class="px-4 py-2.5 rounded-xl bg-white text-emerald-800 hover:bg-slate-100 font-bold text-xs shadow-md transition-all flex items-center gap-2">
                <i :class="copied ? 'fa-solid fa-check text-emerald-600' : 'fa-solid fa-copy text-slate-700'"></i>
                <span x-text="copied ? 'Copied Link!' : 'Copy Link'"></span>
            </button>

            @php
                $waShareText = "පෙරපාසල් ළමා ඇතුළත් කිරීම් සඳහා මාර්ගගත අයදුම්පත මෙතැනින් සම්පූර්ණ කරන්න:\n" . $school->registration_url;
            @endphp
            <a href="https://wa.me/?text={{ urlencode($waShareText) }}" target="_blank"
               class="px-4 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-xs shadow-md transition-all flex items-center gap-2">
                <i class="fa-brands fa-whatsapp text-sm"></i>
                <span>Share on WhatsApp</span>
            </a>
        </div>
    </div>

    <!-- Main Settings Form -->
    <div class="rounded-3xl bg-white border border-slate-200/80 p-6 sm:p-10 shadow-xs">
        <form action="{{ route('admin.settings.school.update') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf
            @method('PUT')

            <!-- Identity Section -->
            <div class="space-y-4">
                <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-800 border-b border-slate-100 pb-2 flex items-center gap-2">
                    <i class="fa-solid fa-school text-indigo-600"></i>
                    <span>Pre-School Identity &amp; Logo</span>
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Pre-School Name *</label>
                        <input type="text" name="name" value="{{ old('name', $school->name) }}" required
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm font-bold text-slate-900 focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Motto / Tagline</label>
                        <input type="text" name="motto" value="{{ old('motto', $school->motto) }}" placeholder="e.g. Nurturing Little Minds for a Brighter Tomorrow"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm text-slate-800 focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Academic Year</label>
                        <input type="text" name="academic_year" value="{{ old('academic_year', $school->academic_year) }}" placeholder="e.g. 2026/2027"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm font-mono text-slate-800 focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Online Admissions Status</label>
                        <label class="flex items-center gap-2 mt-2 cursor-pointer">
                            <input type="checkbox" name="is_admission_open" value="1" {{ $school->is_admission_open ? 'checked' : '' }}
                                   class="w-4 h-4 rounded text-indigo-600 border-slate-300 focus:ring-indigo-500">
                            <span class="text-xs font-semibold text-slate-700">Accept Online Applications via Public Form</span>
                        </label>
                    </div>

                    <!-- Logo Upload -->
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Pre-School Logo</label>
                        <div class="flex items-center gap-4 p-3 rounded-2xl border border-dashed border-slate-200 bg-slate-50/60">
                            <div class="w-16 h-16 rounded-xl overflow-hidden bg-white border border-slate-200 flex items-center justify-center shrink-0">
                                @if($school->logo_path)
                                    <img src="{{ asset('storage/' . $school->logo_path) }}" class="w-full h-full object-contain p-1">
                                @else
                                    <i class="fa-solid fa-school text-slate-400 text-xl"></i>
                                @endif
                            </div>
                            <div>
                                <input type="file" name="logo" id="school_logo" accept="image/*" class="text-xs text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                                <p class="text-[11px] text-slate-400 mt-1">Recommended: Square or transparent PNG/JPG logo (Max: 5MB)</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Day-One Essential Items Checklist (For WhatsApp Selection Message) -->
            <div class="space-y-4">
                <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-800 border-b border-slate-100 pb-2 flex items-center gap-2">
                    <i class="fa-solid fa-list-check text-emerald-600"></i>
                    <span>Day-One Essential Items Checklist (WhatsApp Notification)</span>
                </h3>
                <p class="text-xs text-slate-500">
                    This checklist is automatically included in the WhatsApp selection message sent to the parent when their child is approved or enrolled.
                </p>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            පළමු දිනයේ රැගෙන ආ යුතු අත්‍යවශ්‍ය ද්‍රව්‍ය ලේඛනය (Checklist in Sinhala) *
                        </label>
                        <textarea name="items_to_bring_sinhala" rows="8"
                                  placeholder="1. උප්පැන්න සහතිකයේ පිටපතක්...&#10;2. දරුවාගේ ඡායාරූප...&#10;3. පානීය ජල බෝතලයක්..."
                                  class="w-full px-4 py-3 rounded-xl border border-slate-300 text-xs font-sans text-slate-800 leading-relaxed focus:ring-2 focus:ring-indigo-500">{{ old('items_to_bring_sinhala', $school->items_to_bring_sinhala ?: \App\Models\SchoolSetting::defaultSinhalaItems()) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Checklist of Items to Bring (English - Optional)
                        </label>
                        <textarea name="items_to_bring_english" rows="4"
                                  placeholder="1. Copy of birth certificate&#10;2. Extra set of clothes..."
                                  class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-sans text-slate-800 leading-relaxed focus:ring-2 focus:ring-indigo-500">{{ old('items_to_bring_english', $school->items_to_bring_english) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Vision & Mission Section -->
            <div class="space-y-4">
                <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-800 border-b border-slate-100 pb-2 flex items-center gap-2">
                    <i class="fa-solid fa-bullseye text-amber-500"></i>
                    <span>Vision, Mission &amp; Principal Note</span>
                </h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Our Vision</label>
                        <textarea name="vision" rows="3" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs text-slate-800 focus:ring-2 focus:ring-indigo-500">{{ old('vision', $school->vision) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Our Mission</label>
                        <textarea name="mission" rows="3" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs text-slate-800 focus:ring-2 focus:ring-indigo-500">{{ old('mission', $school->mission) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">About Us / Overview</label>
                        <textarea name="about_us" rows="3" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs text-slate-800 focus:ring-2 focus:ring-indigo-500">{{ old('about_us', $school->about_us) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Principal's Message</label>
                        <textarea name="principal_message" rows="3" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs text-slate-800 focus:ring-2 focus:ring-indigo-500">{{ old('principal_message', $school->principal_message) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Contact & Social Details -->
            <div class="space-y-4">
                <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-800 border-b border-slate-100 pb-2 flex items-center gap-2">
                    <i class="fa-solid fa-phone text-emerald-500"></i>
                    <span>Official Contact Details</span>
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Contact Phone</label>
                        <input type="text" name="contact_phone" value="{{ old('contact_phone', $school->contact_phone) }}"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm text-slate-800 focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Official WhatsApp Number</label>
                        <input type="text" name="whatsapp_number" value="{{ old('whatsapp_number', $school->whatsapp_number) }}"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm text-slate-800 focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Official Email Address</label>
                        <input type="email" name="contact_email" value="{{ old('contact_email', $school->contact_email) }}"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm text-slate-800 focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Physical Address</label>
                        <input type="text" name="address" value="{{ old('address', $school->address) }}"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm text-slate-800 focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end">
                <button type="submit" class="px-6 py-3 rounded-2xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-md transition-all cursor-pointer">
                    Save Pre-School Settings
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
