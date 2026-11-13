@extends('layouts.app')

@section('title', 'Application Details - ' . $application->application_number)
@section('page-title', 'Application Review')
@section('page-subtitle', 'Principal & Administrative Review for ' . $application->child_full_name)

@section('content')
@php
    $cleanWa = preg_replace('/[^0-9]/', '', $application->whatsapp_number);
    if (str_starts_with($cleanWa, '0') && strlen($cleanWa) === 10) {
        $cleanWa = '94' . substr($cleanWa, 1);
    }
    $sinhalaMessage = $application->getSinhalaSelectionMessage();
    $waSelectionLink = "https://wa.me/{$cleanWa}?text=" . urlencode($sinhalaMessage);
@endphp

<div class="space-y-6">

    <!-- Top Action & Navigation Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <a href="{{ route('admin.applications.index') }}" 
           class="inline-flex items-center gap-2 text-xs font-bold text-slate-600 hover:text-slate-900 transition-colors">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Back to All Applications</span>
        </a>

        <!-- Pre-generated WhatsApp Link for Principal -->
        <div class="flex items-center gap-2">
            <a href="{{ $waSelectionLink }}" target="_blank"
               class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md shadow-emerald-600/30 flex items-center justify-center gap-2 transition-all">
                <i class="fa-brands fa-whatsapp text-sm"></i>
                <span>Send WhatsApp Selection Message</span>
            </a>
        </div>
    </div>

    <!-- Application Details Card -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left 2 Cols: Child & Parent Information -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Child Profile Summary -->
            <div class="rounded-2xl bg-white border border-slate-200/80 p-6 shadow-xs space-y-5">
                <div class="flex items-start justify-between pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-2xl bg-slate-100 border border-slate-200 overflow-hidden flex items-center justify-center text-2xl font-bold text-slate-500 shrink-0">
                            @if($application->photo_path)
                                <img src="{{ asset('storage/' . $application->photo_path) }}" class="w-full h-full object-cover">
                            @else
                                <i class="fa-solid fa-child"></i>
                            @endif
                        </div>
                        <div>
                            <span class="text-[10px] uppercase font-bold text-slate-400 font-mono">{{ $application->application_number }}</span>
                            <h2 class="text-xl font-extrabold text-slate-900">{{ $application->child_full_name }}</h2>
                            <p class="text-xs text-slate-500">Calling Name: <strong class="text-slate-800">{{ $application->child_calling_name ?: 'N/A' }}</strong></p>
                        </div>
                    </div>

                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider
                        {{ $application->status === 'enrolled' ? 'bg-emerald-100 text-emerald-800' : ($application->status === 'approved' ? 'bg-cyan-100 text-cyan-800' : ($application->status === 'rejected' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800')) }}">
                        {{ $application->status }}
                    </span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-xs">
                    <div>
                        <span class="text-slate-400 block text-[11px]">Applied Level</span>
                        <span class="font-extrabold text-indigo-700">{{ $application->target_grade_level }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">Date of Birth</span>
                        <span class="font-bold text-slate-800">{{ $application->date_of_birth->format('M d, Y') }} ({{ $application->date_of_birth->age }} yrs old)</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">Gender</span>
                        <span class="font-bold text-slate-800">{{ ucfirst($application->gender) }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">Blood Group</span>
                        <span class="font-mono text-slate-800 font-semibold">{{ $application->blood_group ?: 'Not Specified' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">Submitted On</span>
                        <span class="text-slate-700">{{ $application->created_at->format('M d, Y - h:i A') }}</span>
                    </div>
                </div>

                @if($application->medical_notes)
                    <div class="p-3 rounded-xl bg-rose-50 border border-rose-200/80 text-rose-900 text-xs">
                        <span class="font-bold block mb-0.5"><i class="fa-solid fa-notes-medical text-rose-600 mr-1"></i>Medical / Allergy Notes:</span>
                        {{ $application->medical_notes }}
                    </div>
                @endif

                @if($application->authorized_pickup_persons)
                    <div class="p-3 rounded-xl bg-amber-50 border border-amber-200/80 text-amber-900 text-xs">
                        <span class="font-bold block mb-0.5"><i class="fa-solid fa-shield-halved text-amber-600 mr-1"></i>Authorized Pickup Persons:</span>
                        {{ $application->authorized_pickup_persons }}
                    </div>
                @endif
            </div>

            <!-- Parents & Contact Card -->
            <div class="rounded-2xl bg-white border border-slate-200/80 p-6 shadow-xs space-y-4">
                <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-800 border-b border-slate-100 pb-2">
                    Parents & Guardian Information
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100 space-y-1">
                        <span class="text-[11px] font-bold text-slate-400 uppercase">Father</span>
                        <p class="font-bold text-slate-900 text-sm">{{ $application->father_name ?: 'N/A' }}</p>
                        <p class="text-slate-600">Phone: <span class="font-mono">{{ $application->father_phone ?: 'N/A' }}</span></p>
                    </div>

                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100 space-y-1">
                        <span class="text-[11px] font-bold text-slate-400 uppercase">Mother</span>
                        <p class="font-bold text-slate-900 text-sm">{{ $application->mother_name ?: 'N/A' }}</p>
                        <p class="text-slate-600">Phone: <span class="font-mono">{{ $application->mother_phone ?: 'N/A' }}</span></p>
                    </div>

                    <div class="sm:col-span-2 p-3.5 rounded-xl bg-emerald-50/60 border border-emerald-200/80 flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-800">Primary WhatsApp Contact</span>
                            <p class="text-base font-extrabold font-mono text-emerald-900">{{ $application->whatsapp_number }}</p>
                            @if($application->email)
                                <p class="text-[11px] text-slate-500 mt-0.5">Email: {{ $application->email }}</p>
                            @endif
                        </div>
                        <a href="https://wa.me/{{ $cleanWa }}" target="_blank" class="px-3 py-1.5 rounded-lg bg-emerald-600 text-white font-bold text-xs flex items-center gap-1.5 shadow-xs">
                            <i class="fa-brands fa-whatsapp"></i> Chat on WhatsApp
                        </a>
                    </div>

                    <div class="sm:col-span-2">
                        <span class="text-slate-400 block text-[11px] font-bold uppercase">Permanent Home Address</span>
                        <p class="text-slate-700 mt-0.5">{{ $application->home_address }}</p>
                    </div>
                </div>
            </div>

            <!-- Descriptive Sinhala Selection & Checklist Message Preview Card -->
            <div class="rounded-2xl bg-white border border-slate-200/80 p-6 shadow-xs space-y-4" x-data="{ copied: false }">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2">
                        <i class="fa-brands fa-whatsapp text-emerald-600 text-lg"></i>
                        <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-800">
                            Pre-School Selection Message (Sinhala &amp; Checklist)
                        </h3>
                    </div>

                    <button @click="navigator.clipboard.writeText($refs.sinhalaText.value); copied = true; setTimeout(() => copied = false, 2500)"
                            class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5"
                            :class="copied ? 'bg-emerald-600 text-white' : 'bg-slate-100 hover:bg-slate-200 text-slate-700'">
                        <i :class="copied ? 'fa-solid fa-check' : 'fa-solid fa-copy'"></i>
                        <span x-text="copied ? 'Copied Text!' : 'Copy Message'"></span>
                    </button>
                </div>

                <p class="text-xs text-slate-500">
                    This official message confirms the child's selection, includes the checklist of required items to bring on day one, and provides the direct link to the child's QR ID Card pass.
                </p>

                <textarea x-ref="sinhalaText" rows="12" readonly
                          class="w-full p-4 rounded-xl bg-slate-50 border border-slate-200 text-xs font-sans text-slate-800 leading-relaxed font-medium focus:ring-0">{{ $sinhalaMessage }}</textarea>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ $waSelectionLink }}" target="_blank"
                       class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md shadow-emerald-600/30 flex items-center gap-2 transition-all">
                        <i class="fa-brands fa-whatsapp text-sm"></i>
                        <span>Send to {{ $application->whatsapp_number }} on WhatsApp</span>
                    </a>
                </div>
            </div>

        </div>

        <!-- Right Col: Approval, Class Allocation & Enrollment Actions -->
        <div class="space-y-6">

            <!-- Decision & Enrollment Panel -->
            <div class="rounded-2xl bg-white border border-slate-200/80 p-6 shadow-xs space-y-5">
                <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-800 border-b border-slate-100 pb-2">
                    Principal Selection & Enrollment
                </h3>

                @if($application->status === 'enrolled' && $application->student)
                    @php
                        $enrollment = $application->student->enrollments->first();
                    @endphp
                    <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 space-y-3">
                        <div class="flex items-center gap-2 text-emerald-800 font-bold text-xs">
                            <i class="fa-solid fa-circle-check text-base text-emerald-600"></i>
                            <span>Child is officially enrolled!</span>
                        </div>
                        <div class="text-xs text-slate-700 space-y-1">
                            <p>Admission No: <strong class="font-mono text-slate-900">{{ $application->student->admission_number }}</strong></p>
                            <p>Class Group: <strong class="text-indigo-700">{{ $application->assignedClass?->name }}</strong></p>
                        </div>
                        @if($enrollment)
                            <a href="{{ route('public.pass', $enrollment->qr_token) }}" target="_blank"
                               class="w-full px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold flex items-center justify-center gap-2 transition-all shadow-xs">
                                <i class="fa-solid fa-id-card"></i>
                                <span>Open Child's 3.7" &times; 2.6" QR Pass</span>
                            </a>
                        @endif
                    </div>
                @else
                    <!-- Approve & Enroll Form -->
                    <form action="{{ route('admin.applications.enroll', $application) }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                Assign Class Group *
                            </label>
                            <select name="class_id" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold focus:ring-2 focus:ring-indigo-500">
                                <option value="">-- Choose Class / Group --</option>
                                @foreach($classes as $cls)
                                    <option value="{{ $cls->id }}" {{ $cls->grade_level === $application->target_grade_level ? 'selected' : '' }}>
                                        {{ $cls->name }} ({{ $cls->grade_level }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                    Admission Number (Optional)
                                </label>
                                <span class="text-[11px] text-slate-400 font-mono">
                                    Next: <strong class="text-indigo-600">{{ \App\Models\Student::generateAdmissionNumber() }}</strong>
                                </span>
                            </div>
                            <input type="text" name="admission_number" value="{{ old('admission_number') }}"
                                   placeholder="Leave blank to auto-generate (e.g. {{ \App\Models\Student::generateAdmissionNumber() }})"
                                   class="w-full px-3 py-2 rounded-xl border {{ $errors->has('admission_number') ? 'border-rose-400 ring-2 ring-rose-200' : 'border-slate-300' }} text-xs font-mono">
                            @error('admission_number')
                                <p class="text-xs text-rose-500 font-semibold mt-1.5 flex items-center gap-1">
                                    <i class="fa-solid fa-circle-exclamation text-rose-500"></i>
                                    <span>{{ $message }}</span>
                                </p>
                            @enderror
                        </div>

                        <button type="submit" 
                                class="w-full py-3 rounded-xl bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-500 hover:to-emerald-400 text-white font-black text-xs shadow-md shadow-emerald-600/30 flex items-center justify-center gap-2 transition-all cursor-pointer">
                            <i class="fa-solid fa-user-check"></i>
                            <span>Approve & Enroll Child Now</span>
                        </button>
                    </form>
                @endif

                <!-- Status Update Form (Interview / Reject) -->
                <div class="pt-4 border-t border-slate-100">
                    <form action="{{ route('admin.applications.status', $application) }}" method="POST" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                Change Status
                            </label>
                            <select name="status" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold">
                                <option value="pending" {{ $application->status === 'pending' ? 'selected' : '' }}>Pending Review</option>
                                <option value="interview_scheduled" {{ $application->status === 'interview_scheduled' ? 'selected' : '' }}>Interview Scheduled</option>
                                <option value="approved" {{ $application->status === 'approved' ? 'selected' : '' }}>Approved (Selection Letter)</option>
                                <option value="rejected" {{ $application->status === 'rejected' ? 'selected' : '' }}>Rejected / Not Selected</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                Administrative Notes
                            </label>
                            <textarea name="admin_notes" rows="2" placeholder="Internal notes for teachers/admin..."
                                      class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">{{ $application->admin_notes }}</textarea>
                        </div>

                        <button type="submit" class="w-full py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs transition-colors cursor-pointer">
                            Update Status & Notes
                        </button>
                    </form>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
