@extends('layouts.app')

@section('title', 'QR Attendance Scanner - ' . $classRoom->name)
@section('page-title', 'QR Attendance Scanner')
@section('page-subtitle', $classRoom->name . ' • Real-Time Session Check-In')

@section('content')
<div class="space-y-6" x-data="attendanceScanner()">

    <!-- Header Stats Banner -->
    <div class="rounded-2xl bg-white border border-slate-200/80 p-6 shadow-xs">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                        {{ $classRoom->grade_level }}
                    </span>
                    <span class="text-xs font-mono text-slate-500 font-semibold">
                        <i class="fa-solid fa-calendar-day text-indigo-500 mr-1"></i>
                        {{ \Carbon\Carbon::parse($selectedDate)->format('l, F d, Y') }}
                    </span>
                </div>
                <h1 class="text-2xl font-black text-slate-900">{{ $classRoom->name }}</h1>
            </div>

            <!-- Date Selector Filter Form -->
            <form action="{{ route('teacher.attendance.show', $classRoom) }}" method="GET" class="flex items-center gap-2">
                <input type="date" name="date" value="{{ $selectedDate }}" 
                       class="px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                <button type="submit" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold shadow-xs transition-all">
                    Load Session
                </button>
                @if(!$isToday)
                    <a href="{{ route('teacher.attendance.show', $classRoom) }}" class="px-3 py-2 rounded-xl bg-indigo-50 text-indigo-700 hover:bg-indigo-100 text-xs font-bold border border-indigo-200">
                        Today
                    </a>
                @endif
            </form>
        </div>

        <!-- Live Statistics Counter Pills (Reactive DOM targets) -->
        <div class="mt-6 pt-5 border-t border-slate-100 grid grid-cols-2 sm:grid-cols-5 gap-3 text-center">
            <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                <span class="text-[10px] font-bold text-slate-400 uppercase">Enrolled</span>
                <p class="text-xl font-black text-slate-800 mt-0.5" x-text="stats.total_enrolled">{{ $totalEnrolled }}</p>
            </div>
            <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-100">
                <span class="text-[10px] font-bold text-emerald-700 uppercase">Present</span>
                <p class="text-xl font-black text-emerald-800 mt-0.5" x-text="stats.present_count">{{ $presentCount }}</p>
            </div>
            <div class="p-3 rounded-xl bg-amber-50 border border-amber-100">
                <span class="text-[10px] font-bold text-amber-700 uppercase">Late</span>
                <p class="text-xl font-black text-amber-800 mt-0.5" x-text="stats.late_count">{{ $lateCount }}</p>
            </div>
            <div class="p-3 rounded-xl bg-rose-50 border border-rose-100">
                <span class="text-[10px] font-bold text-rose-700 uppercase">Absent</span>
                <p class="text-xl font-black text-rose-800 mt-0.5" x-text="stats.absent_count">{{ $absentCount }}</p>
            </div>
            <div class="p-3 rounded-xl bg-purple-50 border border-purple-100 col-span-2 sm:col-span-1">
                <span class="text-[10px] font-bold text-purple-700 uppercase">Turnout Rate</span>
                <p class="text-xl font-black text-purple-800 mt-0.5"><span x-text="stats.rate">{{ $attendanceRate }}</span>%</p>
            </div>
        </div>
    </div>

    <!-- Active Toast Banner for Scanner Feedback -->
    <div x-show="toast.show" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="rounded-2xl p-4 shadow-lg flex items-center justify-between gap-3 text-sm font-semibold transition-all"
         :class="{
            'bg-emerald-600 text-white shadow-emerald-600/30': toast.type === 'success',
            'bg-amber-500 text-white shadow-amber-500/30': toast.type === 'warning',
            'bg-rose-600 text-white shadow-rose-600/30': toast.type === 'error'
         }"
         style="display: none;">
        
        <div class="flex items-center gap-3">
            <i :class="{
                'fa-solid fa-circle-check text-xl': toast.type === 'success',
                'fa-solid fa-triangle-exclamation text-xl': toast.type === 'warning',
                'fa-solid fa-circle-xmark text-xl': toast.type === 'error'
            }"></i>
            <div>
                <p class="font-extrabold text-base" x-text="toast.title"></p>
                <p class="text-xs opacity-90" x-text="toast.message"></p>
            </div>
        </div>

        <button @click="toast.show = false" class="text-white/80 hover:text-white p-1">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <!-- Main Scanner + Realtime Scanned Table Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Column 1: Camera Scanner & Manual Input (5 Cols) -->
        <div class="lg:col-span-5 space-y-6">
            
            <!-- Camera Scanner Container -->
            <div class="rounded-2xl bg-slate-900 border border-slate-800 shadow-xl overflow-hidden p-6 text-white">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full" :class="isScanning ? 'bg-emerald-400 animate-ping' : 'bg-slate-500'"></span>
                        <h2 class="text-sm font-bold uppercase tracking-wider text-slate-200">Device Camera Scanner</h2>
                    </div>

                    <span class="text-xs text-indigo-400 font-mono" x-text="isScanning ? 'Scanner Active' : 'Scanner Idle'"></span>
                </div>

                <!-- Video Viewport / Scanner Target Area -->
                <div class="relative rounded-xl overflow-hidden bg-slate-950 border border-slate-800 aspect-square flex items-center justify-center">
                    
                    <!-- HTML5 QRCode Reader Element -->
                    <div id="reader" class="w-full h-full"></div>

                    <!-- Idle Overlay when Scanner not running -->
                    <div x-show="!isScanning" class="absolute inset-0 bg-slate-950/90 flex flex-col items-center justify-center p-6 text-center z-10">
                        <div class="w-16 h-16 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 flex items-center justify-center text-3xl mb-4">
                            <i class="fa-solid fa-camera"></i>
                        </div>
                        <h3 class="text-base font-bold text-white">Camera Scanner Off</h3>
                        <p class="text-xs text-slate-400 mt-1 max-w-xs mb-5">Click below to activate device camera and scan student QR code passes instantly.</p>
                        <button @click="startScanner()" 
                                class="px-6 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-lg shadow-indigo-600/30 flex items-center gap-2 transition-all hover:scale-105">
                            <i class="fa-solid fa-play"></i>
                            <span>Start Camera Scanner</span>
                        </button>
                    </div>

                    <!-- Active Scanner Targeting Reticle Overlay -->
                    <div x-show="isScanning" class="absolute inset-0 pointer-events-none flex items-center justify-center z-10" style="display: none;">
                        <div class="w-56 h-56 border-2 border-indigo-400/80 rounded-2xl relative shadow-2xl">
                            <!-- Reticle Corners -->
                            <div class="absolute -top-1 -left-1 w-6 h-6 border-t-4 border-l-4 border-cyan-400 rounded-tl-lg"></div>
                            <div class="absolute -top-1 -right-1 w-6 h-6 border-t-4 border-r-4 border-cyan-400 rounded-tr-lg"></div>
                            <div class="absolute -bottom-1 -left-1 w-6 h-6 border-b-4 border-l-4 border-cyan-400 rounded-bl-lg"></div>
                            <div class="absolute -bottom-1 -right-1 w-6 h-6 border-b-4 border-r-4 border-cyan-400 rounded-br-lg"></div>
                            <!-- Sweeping Laser Line -->
                            <div class="w-full h-0.5 bg-gradient-to-r from-transparent via-cyan-400 to-transparent absolute top-1/2 -translate-y-1/2 animate-pulse"></div>
                        </div>
                    </div>
                </div>

                <!-- Camera Controls & Feedback Indicator -->
                <div class="mt-4 flex items-center justify-between gap-3 text-xs">
                    <button x-show="isScanning" @click="stopScanner()" 
                            class="px-4 py-2 rounded-xl bg-rose-600/20 hover:bg-rose-600/30 text-rose-300 border border-rose-500/30 font-semibold flex items-center gap-1.5 transition-all" style="display: none;">
                        <i class="fa-solid fa-stop"></i>
                        <span>Stop Camera</span>
                    </button>

                    <div class="flex items-center gap-2 text-slate-400 ml-auto">
                        <i class="fa-solid fa-volume-high text-emerald-400"></i>
                        <span>Audio Beep Enabled</span>
                    </div>
                </div>
            </div>

            <!-- Manual Barcode Gun / Text Token Input Fallback -->
            <div class="rounded-2xl bg-white border border-slate-200/80 p-5 shadow-xs">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-600 mb-2 flex items-center gap-2">
                    <i class="fa-solid fa-barcode text-indigo-600"></i>
                    <span>Manual Token / Barcode Gun Input</span>
                </h3>
                <p class="text-[11px] text-slate-500 mb-3">Paste or scan student QR token, Admission No, or Class UID below and hit enter:</p>

                <form @submit.prevent="submitManualToken()" class="flex gap-2">
                    <input type="text" x-model="manualToken" placeholder="Enter QR Token / UID (e.g. CLS-1-STD-0001)"
                           class="flex-1 px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-mono text-slate-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
                           :disabled="processing">
                    <button type="submit" 
                            class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold transition-all disabled:opacity-50"
                            :disabled="processing || !manualToken.trim()">
                        <span x-show="!processing">Check-In</span>
                        <span x-show="processing"><i class="fa-solid fa-spinner animate-spin"></i></span>
                    </button>
                </form>
            </div>

        </div>

        <!-- Column 2: Live Real-Time Scanned Table (7 Cols) -->
        <div class="lg:col-span-7 space-y-6">
            <div class="rounded-2xl bg-white border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="p-5 border-b border-slate-200/80 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Scanned Students Feed (Live DOM)</h2>
                        <p class="text-xs text-slate-500">Updates instantly via AJAX when QR cards are recognized</p>
                    </div>

                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Real-time AJAX
                    </span>
                </div>

                <div class="overflow-x-auto max-h-[560px] overflow-y-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="sticky top-0 bg-slate-50/95 backdrop-blur-xs z-10 border-b border-slate-200/80">
                            <tr class="text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                <th class="py-3 px-4">Student</th>
                                <th class="py-3 px-4">Class UID</th>
                                <th class="py-3 px-4">Check-In Time</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4 text-right">Manual Action</th>
                            </tr>
                        </thead>
                        <tbody id="attendanceFeedBody" class="divide-y divide-slate-100 text-xs">
                            <!-- Dynamically updated DOM items -->
                            @forelse($attendances as $record)
                                <tr id="attendance-row-{{ $record->student_id }}" class="hover:bg-slate-50/70 transition-colors">
                                    <td class="py-3 px-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-xs shrink-0">
                                                {{ strtoupper(substr($record->student->full_name, 0, 2)) }}
                                            </div>
                                            <div>
                                                <p class="font-bold text-slate-900">{{ $record->student->full_name }}</p>
                                                <p class="text-[11px] text-slate-400 font-mono">{{ $record->student->admission_number }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 font-mono font-semibold text-indigo-600">
                                        {{ $enrollments->where('student_id', $record->student_id)->first()?->student_class_uid ?? 'N/A' }}
                                    </td>
                                    <td class="py-3 px-4 font-mono text-slate-700">
                                        {{ \Carbon\Carbon::parse($record->check_in_time)->format('h:i:s A') }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $record->status === 'late' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">
                                            {{ ucfirst($record->status) }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <form action="{{ route('teacher.attendance.manual', $classRoom) }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="student_id" value="{{ $record->student_id }}">
                                            <input type="hidden" name="date" value="{{ $selectedDate }}">
                                            <input type="hidden" name="status" value="absent">
                                            <button type="submit" class="text-xs text-rose-600 hover:text-rose-800 font-semibold p-1" title="Mark Absent / Clear">
                                                <i class="fa-solid fa-xmark"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr id="noScansRow">
                                    <td colspan="5" class="py-12 text-center text-slate-400">
                                        <i class="fa-solid fa-qrcode text-3xl text-slate-300 mb-2 block"></i>
                                        <span>No attendance marked for this session yet. Scan QR code or type token to begin.</span>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Complete Enrolled Roster with Quick Manual Status Toggles -->
            <div class="rounded-2xl bg-white border border-slate-200/80 p-5 shadow-xs">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Full Class Roster (Manual Overrides)</h3>
                        <p class="text-xs text-slate-500">Quickly toggle present, late, or absent for students without cards</p>
                    </div>
                </div>

                <div class="divide-y divide-slate-100 max-h-60 overflow-y-auto">
                    @foreach($enrollments as $item)
                        @php
                            $att = $attendanceMap->get($item->student_id);
                        @endphp
                        <div class="py-2.5 flex items-center justify-between gap-3 text-xs">
                            <div>
                                <span class="font-bold text-slate-800">{{ $item->student->full_name }}</span>
                                <span class="text-slate-400 font-mono text-[11px] ml-1.5">({{ $item->student_class_uid }})</span>
                            </div>

                            <div class="flex items-center gap-1.5">
                                <form action="{{ route('teacher.attendance.manual', $classRoom) }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="student_id" value="{{ $item->student_id }}">
                                    <input type="hidden" name="date" value="{{ $selectedDate }}">
                                    <input type="hidden" name="status" value="present">
                                    <button type="submit" 
                                            class="px-2 py-1 rounded-md text-[11px] font-bold border transition-colors {{ ($att && $att->status === 'present') ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 border-slate-200' }}">
                                        Present
                                    </button>
                                </form>

                                <form action="{{ route('teacher.attendance.manual', $classRoom) }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="student_id" value="{{ $item->student_id }}">
                                    <input type="hidden" name="date" value="{{ $selectedDate }}">
                                    <input type="hidden" name="status" value="late">
                                    <button type="submit" 
                                            class="px-2 py-1 rounded-md text-[11px] font-bold border transition-colors {{ ($att && $att->status === 'late') ? 'bg-amber-500 text-white border-amber-500' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 border-slate-200' }}">
                                        Late
                                    </button>
                                </form>

                                @if($att)
                                    <form action="{{ route('teacher.attendance.manual', $classRoom) }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="student_id" value="{{ $item->student_id }}">
                                        <input type="hidden" name="date" value="{{ $selectedDate }}">
                                        <input type="hidden" name="status" value="absent">
                                        <button type="submit" 
                                                class="px-2 py-1 rounded-md text-[11px] font-bold bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 transition-colors">
                                            Clear
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>

    </div>

</div>
@endsection

@push('scripts')
<script>
function attendanceScanner() {
    return {
        html5QrCode: null,
        isScanning: false,
        processing: false,
        lastScannedToken: null,
        lastScanTimestamp: 0,
        manualToken: '',
        scanEndpoint: '{{ route('teacher.attendance.scan', $classRoom) }}',
        csrfToken: '{{ csrf_token() }}',

        stats: {
            total_enrolled: {{ $totalEnrolled }},
            present_count: {{ $presentCount }},
            late_count: {{ $lateCount }},
            absent_count: {{ $absentCount }},
            rate: {{ $attendanceRate }}
        },

        toast: {
            show: false,
            type: 'success',
            title: '',
            message: '',
            timeout: null
        },

        triggerToast(type, title, message) {
            this.toast.type = type;
            this.toast.title = title;
            this.toast.message = message;
            this.toast.show = true;

            if (this.toast.timeout) clearTimeout(this.toast.timeout);
            this.toast.timeout = setTimeout(() => {
                this.toast.show = false;
            }, 4500);
        },

        async startScanner() {
            try {
                if (!window.Html5Qrcode) {
                    this.triggerToast('error', 'Scanner Not Ready', 'Html5Qrcode library not loaded.');
                    return;
                }

                if (!this.html5QrCode) {
                    this.html5QrCode = new window.Html5Qrcode("reader");
                }

                const config = { 
                    fps: 10, 
                    qrbox: { width: 250, height: 250 },
                    aspectRatio: 1.0
                };

                await this.html5QrCode.start(
                    { facingMode: "environment" },
                    config,
                    (decodedText) => this.onScanSuccess(decodedText),
                    (errorMessage) => {
                        // ignorable parse frame message
                    }
                );

                this.isScanning = true;
            } catch (err) {
                console.error("Camera startup error:", err);
                this.triggerToast('error', 'Camera Error', 'Could not access device camera. Please check camera permissions or use manual token input.');
            }
        },

        async stopScanner() {
            if (this.html5QrCode && this.isScanning) {
                await this.html5QrCode.stop();
                this.isScanning = false;
            }
        },

        async onScanSuccess(decodedText) {
            const now = Date.now();
            // Debounce duplicate camera readings within 2.5 seconds
            if (this.lastScannedToken === decodedText && (now - this.lastScanTimestamp) < 2500) {
                return;
            }

            this.lastScannedToken = decodedText;
            this.lastScanTimestamp = now;

            await this.sendScanRequest(decodedText);
        },

        async submitManualToken() {
            if (!this.manualToken.trim()) return;
            const token = this.manualToken.trim();
            this.manualToken = '';
            await this.sendScanRequest(token);
        },

        async sendScanRequest(token) {
            if (this.processing) return;
            this.processing = true;

            try {
                const response = await fetch(this.scanEndpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ qr_token: token })
                });

                const data = await response.json();

                if (response.status === 200) {
                    // Success! Play chime beep
                    if (window.playSuccessBeep) window.playSuccessBeep();

                    this.triggerToast('success', 'Checked In Successfully!', `${data.student.full_name} (${data.student.uid}) marked present at ${data.attendance.check_in_time}`);

                    // Update live stats
                    if (data.stats) {
                        this.stats = data.stats;
                    }

                    // Update live DOM feed table
                    this.prependScannedRow(data.student, data.attendance);

                } else if (response.status === 409) {
                    // Duplicate scan today! Play warning buzz
                    if (window.playErrorBuzz) window.playErrorBuzz();

                    this.triggerToast('warning', 'Already Marked Present!', data.message);

                } else {
                    // Not found or error! Play buzz
                    if (window.playErrorBuzz) window.playErrorBuzz();

                    this.triggerToast('error', 'Check-In Failed', data.message || 'Student QR Code could not be validated.');
                }

            } catch (err) {
                console.error("AJAX Scan error:", err);
                if (window.playErrorBuzz) window.playErrorBuzz();
                this.triggerToast('error', 'Network Error', 'Could not connect to attendance server.');
            } finally {
                this.processing = false;
            }
        },

        prependScannedRow(student, attendance) {
            const noRow = document.getElementById('noScansRow');
            if (noRow) noRow.remove();

            const existingRow = document.getElementById(`attendance-row-${student.id}`);
            if (existingRow) existingRow.remove();

            const tbody = document.getElementById('attendanceFeedBody');
            if (!tbody) return;

            const tr = document.createElement('tr');
            tr.id = `attendance-row-${student.id}`;
            tr.className = 'bg-emerald-50/80 transition-all duration-500 animate-pulse';

            tr.innerHTML = `
                <td class="py-3 px-4">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-xs shrink-0">
                            ${student.full_name.substring(0, 2).toUpperCase()}
                        </div>
                        <div>
                            <p class="font-bold text-slate-900">${student.full_name}</p>
                            <p class="text-[11px] text-slate-400 font-mono">${student.admission_number}</p>
                        </div>
                    </div>
                </td>
                <td class="py-3 px-4 font-mono font-semibold text-indigo-600">
                    ${student.uid}
                </td>
                <td class="py-3 px-4 font-mono text-slate-700">
                    ${attendance.check_in_time}
                </td>
                <td class="py-3 px-4">
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">
                        Present
                    </span>
                </td>
                <td class="py-3 px-4 text-right">
                    <span class="text-xs text-emerald-600 font-bold"><i class="fa-solid fa-circle-check"></i> Scanned</span>
                </td>
            `;

            tbody.insertBefore(tr, tbody.firstChild);

            // Remove pulse highlight after 3 seconds
            setTimeout(() => {
                tr.classList.remove('bg-emerald-50/80', 'animate-pulse');
            }, 3000);
        }
    };
}
</script>
@endpush
