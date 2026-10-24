@extends('layouts.app')

@section('title', $classRoom->name)
@section('page-title', $classRoom->name)
@section('page-subtitle', $classRoom->grade_level . ' • Fee: LKR ' . number_format($classRoom->monthly_fee, 2) . ' / month')

@section('content')
<div class="space-y-6" x-data="{ addStudentModal: false, editClassModal: false, manualMode: 'new' }">

    <!-- Class Header & Statistics Bar -->
    <div class="rounded-2xl bg-white border border-slate-200/80 p-6 shadow-xs flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                    {{ $classRoom->grade_level }}
                </span>

                @if($classRoom->is_registration_open)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Self-Registration Open
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                        Self-Registration Paused
                    </span>
                @endif
            </div>

            <h1 class="text-2xl font-black text-slate-900">{{ $classRoom->name }}</h1>

            <div class="mt-3 flex flex-wrap items-center gap-x-6 gap-y-2 text-xs text-slate-500">
                <span>Fee: <strong class="text-slate-800">LKR {{ number_format($classRoom->monthly_fee, 2) }}</strong></span>
                <span>Billing Month: <strong class="text-indigo-600 font-mono">{{ $currentMonth }}</strong></span>
                <span>Active Enrollments: <strong class="text-slate-800">{{ $totalEnrolled }}</strong></span>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('teacher.attendance.show', $classRoom) }}" 
               class="px-4 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-500 text-white text-xs font-bold shadow-md shadow-purple-600/30 flex items-center gap-2 transition-all">
                <i class="fa-solid fa-qrcode text-sm"></i>
                <span>Open QR Scanner</span>
            </a>

            <button @click="addStudentModal = true" 
                    class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md shadow-indigo-600/30 flex items-center gap-2 transition-all">
                <i class="fa-solid fa-user-plus"></i>
                <span>Add Student Manually</span>
            </button>

            <button @click="editClassModal = true" 
                    class="px-3 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold border border-slate-200 transition-all" title="Edit Class Details">
                <i class="fa-solid fa-gear"></i>
            </button>
        </div>
    </div>

    <!-- Self Registration Link Banner -->
    <div class="rounded-2xl bg-indigo-950 border border-indigo-500/30 p-5 text-white shadow-lg flex flex-col sm:flex-row sm:items-center justify-between gap-4"
         x-data="{ copied: false }">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-indigo-500/20 border border-indigo-500/30 text-indigo-400 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-link"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-white uppercase tracking-wider">Shareable Student Self-Registration URL</p>
                <p class="text-[11px] text-slate-300">Students register via this link to automatically generate their QR ID pass</p>
            </div>
        </div>

        <div class="flex items-center gap-2 max-w-md w-full sm:w-auto">
            <div class="flex-1 bg-slate-900/90 rounded-xl px-3 py-2 border border-slate-700 text-xs font-mono text-slate-300 truncate select-all">
                {{ $classRoom->join_url }}
            </div>
            <button @click="navigator.clipboard.writeText('{{ $classRoom->join_url }}'); copied = true; setTimeout(() => copied = false, 2500)"
                    class="px-3.5 py-2 rounded-xl text-xs font-bold text-white transition-all shrink-0 flex items-center gap-1.5"
                    :class="copied ? 'bg-emerald-600' : 'bg-indigo-600 hover:bg-indigo-500'">
                <i :class="copied ? 'fa-solid fa-check' : 'fa-solid fa-copy'"></i>
                <span x-text="copied ? 'Copied Link!' : 'Copy Link'"></span>
            </button>
            <form action="{{ route('teacher.classes.toggle-registration', $classRoom) }}" method="POST" class="shrink-0">
                @csrf
                <button type="submit" 
                        class="px-3 py-2 rounded-xl text-xs font-semibold border transition-all"
                        :class="'{{ $classRoom->is_registration_open }}' ? 'bg-slate-800 hover:bg-slate-700 border-slate-700 text-slate-300' : 'bg-emerald-600 hover:bg-emerald-500 border-emerald-500 text-white'"
                        title="{{ $classRoom->is_registration_open ? 'Pause registration link' : 'Open registration link' }}">
                    <i class="fa-solid {{ $classRoom->is_registration_open ? 'fa-pause' : 'fa-play' }}"></i>
                </button>
            </form>
        </div>
    </div>

    <!-- Monthly Payment Status Strip -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="rounded-2xl bg-white p-4 border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Enrolled</p>
                <p class="text-2xl font-black text-slate-900 mt-0.5">{{ $totalEnrolled }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center font-bold">
                <i class="fa-solid fa-users"></i>
            </div>
        </div>

        <div class="rounded-2xl bg-white p-4 border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-emerald-600">Paid for {{ $currentMonth }}</p>
                <p class="text-2xl font-black text-emerald-700 mt-0.5">{{ $paidCount }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                <i class="fa-solid fa-circle-check"></i>
            </div>
        </div>

        <div class="rounded-2xl bg-white p-4 border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-amber-600">Pending Fees</p>
                <p class="text-2xl font-black text-amber-700 mt-0.5">{{ $pendingCount }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                <i class="fa-solid fa-clock"></i>
            </div>
        </div>
    </div>

    <!-- Enrolled Students Table -->
    <div class="rounded-2xl bg-white border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-base font-bold text-slate-900">Enrolled Students ({{ $enrollments->count() }})</h2>
                <p class="text-xs text-slate-500">View student class UIDs, printable ID passes, and billing cycle status</p>
            </div>

            <div class="flex items-center gap-2">
                <span class="text-xs text-slate-500">Cycle: <strong class="text-slate-800 font-mono">{{ $currentMonth }}</strong></span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <th class="py-3 px-4">Student Details</th>
                        <th class="py-3 px-4">Class UID</th>
                        <th class="py-3 px-4">Joined Via</th>
                        <th class="py-3 px-4">{{ $currentMonth }} Fee Status</th>
                        <th class="py-3 px-4">Guardian Contact</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($enrollments as $enrollment)
                        @php
                            $isPaid = in_array($enrollment->student_id, $paidStudentIds);
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <!-- Student Details -->
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-xs shadow-xs shrink-0">
                                        {{ strtoupper(substr($enrollment->student->full_name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-900 text-sm">{{ $enrollment->student->full_name }}</p>
                                        <p class="text-slate-500 text-[11px] font-mono">{{ $enrollment->student->admission_number }} &bull; {{ $enrollment->student->contact_number }}</p>
                                    </div>
                                </div>
                            </td>

                            <!-- UID -->
                            <td class="py-3.5 px-4 font-mono font-semibold text-indigo-700">
                                {{ $enrollment->student_class_uid }}
                            </td>

                            <!-- Joined Via -->
                            <td class="py-3.5 px-4">
                                @if($enrollment->joined_via === 'link')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                        <i class="fa-solid fa-link text-[9px]"></i> Link
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                        <i class="fa-solid fa-hand text-[9px]"></i> Manual
                                    </span>
                                @endif
                            </td>

                            <!-- Fee Status Indicator (Paid vs Pending) -->
                            <td class="py-3.5 px-4">
                                @if($isPaid)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                        <i class="fa-solid fa-circle-check text-emerald-600"></i>
                                        <span>Paid</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                                        <i class="fa-solid fa-clock text-amber-600"></i>
                                        <span>Pending</span>
                                    </span>
                                @endif
                            </td>

                            <!-- Guardian Info -->
                            <td class="py-3.5 px-4 text-slate-600">
                                <p class="font-semibold text-slate-800">{{ $enrollment->student->guardian_name }}</p>
                                <p class="text-slate-500 font-mono">{{ $enrollment->student->guardian_phone }}</p>
                            </td>

                            <!-- Actions -->
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <!-- Print ID Pass -->
                                    <a href="{{ route('public.pass', $enrollment->qr_token) }}" target="_blank"
                                       class="px-2.5 py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-xs border border-indigo-200 transition-colors flex items-center gap-1"
                                       title="View & Print ID Pass">
                                        <i class="fa-solid fa-id-card"></i>
                                        <span>Pass</span>
                                    </a>

                                    <!-- Remove Student -->
                                    <form action="{{ route('teacher.classes.students.remove', [$classRoom, $enrollment->student]) }}" method="POST"
                                          onsubmit="return confirm('Remove student {{ $enrollment->student->full_name }} from this class?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors"
                                                title="Remove from class">
                                            <i class="fa-solid fa-trash-can text-sm"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-slate-500">
                                <i class="fa-solid fa-user-slash text-2xl text-slate-300 mb-2 block"></i>
                                <span>No students enrolled in this class yet. Share the registration link or add students manually.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Student Manually Modal -->
    <div x-show="addStudentModal" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
         style="display: none;">
        
        <div @click.away="addStudentModal = false" 
             class="w-full max-w-lg bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden">
            
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Manually Enroll Student</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Enroll an existing registered student or create a brand new student</p>
                </div>
                <button @click="addStudentModal = false" class="text-slate-400 hover:text-slate-600 p-1">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Mode Switcher Tabs -->
            <div class="p-4 bg-slate-50 border-b border-slate-100 flex gap-2">
                <button type="button" @click="manualMode = 'new'"
                        class="flex-1 py-2 rounded-xl text-xs font-bold transition-all"
                        :class="manualMode === 'new' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'">
                    <i class="fa-solid fa-user-plus mr-1"></i> Register New Student
                </button>
                <button type="button" @click="manualMode = 'existing'"
                        class="flex-1 py-2 rounded-xl text-xs font-bold transition-all"
                        :class="manualMode === 'existing' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'">
                    <i class="fa-solid fa-list mr-1"></i> Select Existing Student
                </button>
            </div>

            <form action="{{ route('teacher.classes.students.manual', $classRoom) }}" method="POST" class="p-6 space-y-4">
                @csrf
                <input type="hidden" name="mode" :value="manualMode">

                <!-- Mode 1: Register New Student -->
                <template x-if="manualMode === 'new'">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Full Name *</label>
                            <input type="text" name="full_name" required placeholder="e.g. Liam Chen"
                                   class="w-full px-4 py-2 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 text-slate-800 text-sm">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Contact Number / WhatsApp *</label>
                            <input type="tel" name="contact_number" required placeholder="e.g. +1 555-0103"
                                   class="w-full px-4 py-2 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 text-slate-800 text-sm">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Guardian Name *</label>
                                <input type="text" name="guardian_name" required placeholder="e.g. David Chen"
                                       class="w-full px-4 py-2 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 text-slate-800 text-sm">
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Guardian Phone *</label>
                                <input type="tel" name="guardian_phone" required placeholder="e.g. +1 555-0203"
                                       class="w-full px-4 py-2 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 text-slate-800 text-sm">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Custom Admission No. (Optional)</label>
                            <input type="text" name="admission_number" placeholder="Leave blank to auto-generate"
                                   class="w-full px-4 py-2 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 text-slate-800 text-sm">
                        </div>
                    </div>
                </template>

                <!-- Mode 2: Select Existing Student -->
                <template x-if="manualMode === 'existing'">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Select Student *</label>
                            @if($otherStudents->isNotEmpty())
                                <select name="student_id" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 text-slate-800 text-sm">
                                    <option value="">-- Choose existing registered student --</option>
                                    @foreach($otherStudents as $other)
                                        <option value="{{ $other->id }}">
                                            {{ $other->admission_number }} - {{ $other->full_name }} ({{ $other->contact_number }})
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-500 text-center">
                                    All available students are already enrolled in this class! Use "Register New Student" to add new students.
                                </div>
                            @endif
                        </div>
                    </div>
                </template>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="addStudentModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md shadow-indigo-600/30">Enroll Student</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Class Settings Modal -->
    <div x-show="editClassModal" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
         style="display: none;">
        
        <div @click.away="editClassModal = false" 
             class="w-full max-w-lg bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden">
            
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Edit Class Configuration</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Update class title, grade level, and monthly fee</p>
                </div>
                <button @click="editClassModal = false" class="text-slate-400 hover:text-slate-600 p-1">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form action="{{ route('teacher.classes.update', $classRoom) }}" method="POST" class="p-6 space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Class Name *</label>
                    <input type="text" name="name" value="{{ $classRoom->name }}" required
                           class="w-full px-4 py-2 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 text-slate-800 text-sm">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Grade Level *</label>
                        <input type="text" name="grade_level" value="{{ $classRoom->grade_level }}" required
                               class="w-full px-4 py-2 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 text-slate-800 text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Monthly Fee (LKR) *</label>
                        <input type="number" step="0.01" min="0" name="monthly_fee" value="{{ $classRoom->monthly_fee }}" required
                               class="w-full px-4 py-2 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 text-slate-800 text-sm">
                    </div>
                </div>

                <div class="pt-2">
                    <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700">
                        <input type="checkbox" name="is_registration_open" value="1" {{ $classRoom->is_registration_open ? 'checked' : '' }} class="w-4 h-4 rounded text-indigo-600 border-slate-300 focus:ring-indigo-500/20">
                        <span>Allow Public Self-Registration via Join Link</span>
                    </label>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="editClassModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md shadow-indigo-600/30">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
