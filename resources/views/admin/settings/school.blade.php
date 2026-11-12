@extends('layouts.app')

@section('title', 'Pre-School Settings & Branding')
@section('page-title', 'Pre-School Identity & Settings')
@section('page-subtitle', 'Configure pre-school name, motto, logo, vision, mission, and contact details')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium flex items-center gap-2">
            <i class="fa-solid fa-circle-check text-emerald-600"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="rounded-3xl bg-white border border-slate-200/80 p-6 sm:p-10 shadow-xs">
        <form action="{{ route('admin.settings.school.update') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf
            @method('PUT')

            <!-- Identity Section -->
            <div class="space-y-4">
                <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-800 border-b border-slate-100 pb-2 flex items-center gap-2">
                    <i class="fa-solid fa-school text-indigo-600"></i>
                    <span>Pre-School Branding</span>
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
                </div>
            </div>

            <!-- Vision & Mission Section -->
            <div class="space-y-4">
                <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-800 border-b border-slate-100 pb-2 flex items-center gap-2">
                    <i class="fa-solid fa-bullseye text-amber-500"></i>
                    <span>Vision, Mission & Principal Note</span>
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

