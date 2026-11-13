@extends('layouts.app')

@section('title', 'Central Universal QR Attendance')
@section('page-title', 'Central Attendance Kiosk (මධ්‍යම පැමිණීමේ ස්කෑනරය)')
@section('page-subtitle', 'Scan any student or teacher QR badge from this single central location. The system automatically categorizes and marks the appropriate class or staff attendance.')

@section('content')
<div class="space-y-6" x-data="universalScanner()">

    <!-- Top KPI Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-xs flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-graduation-cap"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase">Student Scans Today</p>
                <p class="text-2xl font-black text-slate-900" x-text="studentCount">{{ $studentScansToday }}</p>
            </div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-xs flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-chalkboard-user"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase">Teacher Scans Today</p>
                <p class="text-2xl font-black text-emerald-600" x-text="teacherCount">{{ $teacherScansToday }}</p>
            </div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-xs flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-qrcode"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase">Total Today</p>
                <p class="text-2xl font-black text-purple-600" x-text="totalCount">{{ $totalScansToday }}</p>
            </div>
        </div>
    </div>

    <!-- Main Scanner Interface -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Left 7 Cols: Camera / Scanner Kiosk -->
        <div class="lg:col-span-7 space-y-4">
            <div class="bg-slate-900 text-white rounded-3xl p-6 shadow-xl border border-slate-800 relative overflow-hidden">
                <div class="flex items-center justify-between pb-4 border-b border-slate-800">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        <h3 class="text-sm font-bold text-white tracking-wide">Universal Scanner Active</h3>
                    </div>
                    <span class="text-xs text-slate-400 font-mono">{{ $todayDate }}</span>
                </div>

                <!-- Video / Camera Preview Area -->
                <div class="mt-4 relative rounded-2xl overflow-hidden bg-black aspect-4/3 flex items-center justify-center border border-slate-800">
                    <div id="reader" class="w-full h-full"></div>
                    
                    <div x-show="!scannerStarted" class="absolute inset-0 flex flex-col items-center justify-center bg-slate-950/90 text-center p-6 space-y-3">
                        <div class="w-16 h-16 rounded-2xl bg-indigo-600/20 text-indigo-400 flex items-center justify-center text-2xl border border-indigo-500/30">
                            <i class="fa-solid fa-camera"></i>
                        </div>
                        <p class="text-sm font-bold text-white">Central Camera Scanner</p>
                        <p class="text-xs text-slate-400 max-w-xs">Start device camera to scan Student ID passes or Teacher QR badges automatically.</p>
                        <button type="button" @click="startCamera()" 
                                class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-lg shadow-indigo-600/30 flex items-center gap-2 cursor-pointer transition-all">
                            <i class="fa-solid fa-video"></i>
                            <span>Start Camera Scanner</span>
                        </button>
                    </div>

                    <div x-show="scannerStarted" class="absolute top-3 right-3 z-10">
                        <button type="button" @click="stopCamera()" 
                                class="px-3 py-1.5 rounded-lg bg-slate-900/80 hover:bg-rose-600 text-white text-[11px] font-bold border border-slate-700 transition-all flex items-center gap-1.5">
                            <i class="fa-solid fa-stop"></i>
                            <span>Stop Camera</span>
                        </button>
                    </div>
                </div>

                <!-- Handheld Barcode / Manual Input Bar -->
                <div class="mt-4">
                    <form @submit.prevent="submitManualToken()" class="flex items-center gap-2">
                        <div class="relative flex-1">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                                <i class="fa-solid fa-barcode text-sm"></i>
                            </div>
                            <input type="text" x-model="manualToken" x-ref="tokenInput"
                                   placeholder="Scan with USB barcode scanner or enter Student/Teacher code..."
                                   class="w-full pl-10 pr-4 py-3 rounded-xl bg-slate-800 border border-slate-700 text-white placeholder-slate-500 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none font-mono">
                        </div>
                        <button type="submit" 
                                class="px-5 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md transition-all shrink-0 cursor-pointer">
                            <span>Process Scan</span>
                        </button>
                    </form>
                    <p class="text-[11px] text-slate-400 mt-2 flex items-center gap-1.5">
                        <i class="fa-solid fa-circle-info text-indigo-400"></i>
                        <span>Accepts Student QR Tokens, Admission Numbers, Class UIDs, or Teacher Badges (TCH-XXXXX).</span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Right 5 Cols: Live Result Card & Feed -->
        <div class="lg:col-span-5 space-y-4">
            
            <!-- Result Banner / Feedback -->
            <div x-show="lastResult" x-cloak class="transition-all">
                <template x-if="lastResult && lastResult.success && lastResult.category === 'student'">
                    <div class="p-5 rounded-3xl bg-white border-2 border-indigo-500 shadow-xl space-y-4 animate-in fade-in">
                        <div class="flex items-center justify-between">
                            <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-indigo-100 text-indigo-800 flex items-center gap-1.5">
                                <i class="fa-solid fa-graduation-cap"></i>
                                <span>STUDENT / සිසුවා</span>
                            </span>
                            <span class="text-xs font-bold text-slate-500" x-text="lastResult.time"></span>
                        </div>

                        <div class="flex items-center gap-4">
                            <template x-if="lastResult.photo_url">
                                <img :src="lastResult.photo_url" class="w-14 h-14 rounded-2xl object-cover border border-slate-200 shadow-sm shrink-0">
                            </template>
                            <template x-if="!lastResult.photo_url">
                                <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-indigo-500 to-purple-600 text-white flex items-center justify-center font-bold text-lg shrink-0">
                                    <span x-text="lastResult.name.substring(0, 2).toUpperCase()"></span>
                                </div>
                            </template>
                            <div class="min-w-0 flex-1">
                                <h4 class="text-base font-extrabold text-slate-900 truncate" x-text="lastResult.name"></h4>
                                <p class="text-xs font-semibold text-indigo-600 truncate">
                                    <i class="fa-solid fa-shapes mr-1"></i>
                                    <span x-text="lastResult.class_name"></span>
                                </p>
                                <p class="text-[11px] text-slate-400 font-mono" x-text="'ID: ' + (lastResult.admission_number || 'N/A')"></p>
                            </div>
                        </div>

                        <!-- Fee Status Badge -->
                        <div class="p-3 rounded-2xl flex items-center justify-between"
                             :class="lastResult.fee_status === 'paid' ? 'bg-emerald-50 border border-emerald-200 text-emerald-800' : 'bg-amber-50 border border-amber-200 text-amber-800'">
                            <div class="flex items-center gap-2 text-xs font-bold">
                                <i class="fa-solid" :class="lastResult.fee_status === 'paid' ? 'fa-circle-check text-emerald-600' : 'fa-circle-exclamation text-amber-600'"></i>
                                <span x-text="lastResult.fee_status === 'paid' ? 'Monthly Fee Paid (ගාස්තු ගෙවා ඇත)' : 'Monthly Fee Pending (ගාස්තු ගෙවා නැත)'"></span>
                            </div>
                            <span class="text-xs font-extrabold" x-text="'LKR ' + Number(lastResult.monthly_fee).toFixed(2)"></span>
                        </div>

                        <p class="text-xs font-semibold text-slate-700 bg-slate-50 p-2.5 rounded-xl border border-slate-100" x-text="lastResult.message"></p>
                    </div>
                </template>

                <template x-if="lastResult && lastResult.success && lastResult.category === 'teacher'">
                    <div class="p-5 rounded-3xl bg-white border-2 border-emerald-500 shadow-xl space-y-4 animate-in fade-in">
                        <div class="flex items-center justify-between">
                            <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-emerald-100 text-emerald-800 flex items-center gap-1.5">
                                <i class="fa-solid fa-chalkboard-user"></i>
                                <span>TEACHER / ගුරුවරයා</span>
                            </span>
                            <span class="text-xs font-bold text-slate-500" x-text="lastResult.time"></span>
                        </div>

                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-emerald-500 to-teal-600 text-white flex items-center justify-center font-bold text-lg shrink-0">
                                <span x-text="lastResult.name.substring(0, 2).toUpperCase()"></span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <h4 class="text-base font-extrabold text-slate-900 truncate" x-text="lastResult.name"></h4>
                                <p class="text-xs font-semibold text-emerald-600">Preschool Faculty Member</p>
                                <p class="text-[11px] text-slate-400 font-mono" x-text="lastResult.email"></p>
                            </div>
                        </div>

                        <template x-if="lastResult.is_on_leave">
                            <div class="p-3 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center gap-2 text-xs font-bold">
                                <i class="fa-solid fa-triangle-exclamation text-rose-600"></i>
                                <span x-text="lastResult.leave_details"></span>
                            </div>
                        </template>

                        <div class="p-3 rounded-2xl bg-emerald-50 border border-emerald-200 flex items-center justify-between text-xs font-bold text-emerald-800">
                            <span class="flex items-center gap-1.5">
                                <i class="fa-solid fa-clock-rotate-left"></i>
                                <span x-text="lastResult.action === 'check_out' ? 'Checked Out (පිටවීම)' : 'Checked In (පැමිණීම)'"></span>
                            </span>
                            <span class="font-extrabold" x-text="lastResult.time"></span>
                        </div>

                        <p class="text-xs font-semibold text-slate-700 bg-slate-50 p-2.5 rounded-xl border border-slate-100" x-text="lastResult.message"></p>
                    </div>
                </template>

                <template x-if="lastResult && !lastResult.success">
                    <div class="p-5 rounded-3xl bg-rose-50 border-2 border-rose-300 shadow-lg space-y-2 animate-in fade-in">
                        <div class="flex items-center gap-2 text-rose-800 font-bold text-sm">
                            <i class="fa-solid fa-circle-xmark text-rose-600 text-base"></i>
                            <span>Scan Rejected / Invalid</span>
                        </div>
                        <p class="text-xs text-rose-700" x-text="lastResult.message"></p>
                    </div>
                </template>
            </div>

            <!-- Live Today's Feed -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-5 space-y-3">
                <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center justify-between">
                    <span>Recent Scans Today</span>
                    <i class="fa-solid fa-wave-square text-indigo-500"></i>
                </h4>

                <div class="space-y-2 max-h-96 overflow-y-auto pr-1">
                    <template x-for="(item, index) in liveFeed" :key="index">
                        <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between gap-3 text-xs">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <span class="w-2 h-2 rounded-full shrink-0"
                                      :class="item.category === 'teacher' ? 'bg-emerald-500' : 'bg-indigo-500'"></span>
                                <div class="truncate">
                                    <p class="font-bold text-slate-800 truncate" x-text="item.name"></p>
                                    <p class="text-[10px] text-slate-400 truncate" x-text="item.sub"></p>
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-md"
                                      :class="item.category === 'teacher' ? 'bg-emerald-100 text-emerald-800' : 'bg-indigo-100 text-indigo-800'"
                                      x-text="item.status"></span>
                                <p class="text-[9px] text-slate-400 mt-0.5" x-text="item.time"></p>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

        </div>

    </div>

</div>

<!-- Html5Qrcode Library for Camera Barcode & QR Reading -->
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

<script>
function universalScanner() {
    return {
        scannerStarted: false,
        html5QrCode: null,
        manualToken: '',
        lastResult: null,
        studentCount: {{ $studentScansToday }},
        teacherCount: {{ $teacherScansToday }},
        totalCount: {{ $totalScansToday }},
        liveFeed: @json($recentFeed),
        isProcessing: false,

        init() {
            // Focus manual input for barcode scanners
            this.$nextTick(() => {
                if (this.$refs.tokenInput) {
                    this.$refs.tokenInput.focus();
                }
            });
        },

        playBeep(success = true) {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.type = success ? 'sine' : 'sawtooth';
                osc.frequency.setValueAtTime(success ? 880 : 220, ctx.currentTime);
                gain.gain.setValueAtTime(0.15, ctx.currentTime);
                osc.start();
                osc.stop(ctx.currentTime + (success ? 0.15 : 0.3));
            } catch (e) {
                // AudioContext not supported or allowed
            }
        },

        startCamera() {
            if (this.html5QrCode) {
                return;
            }
            this.html5QrCode = new Html5Qrcode("reader");
            const config = { fps: 10, qrbox: { width: 250, height: 250 } };

            this.html5QrCode.start(
                { facingMode: "environment" },
                config,
                (decodedText) => {
                    if (!this.isProcessing) {
                        this.processToken(decodedText);
                    }
                },
                (errorMessage) => {
                    // scanning frame...
                }
            ).then(() => {
                this.scannerStarted = true;
            }).catch(err => {
                alert("Camera access failed: " + err);
            });
        },

        stopCamera() {
            if (this.html5QrCode) {
                this.html5QrCode.stop().then(() => {
                    this.html5QrCode.clear();
                    this.html5QrCode = null;
                    this.scannerStarted = false;
                });
            }
        },

        submitManualToken() {
            if (!this.manualToken.trim()) return;
            this.processToken(this.manualToken.trim());
            this.manualToken = '';
        },

        processToken(token) {
            this.isProcessing = true;

            fetch('{{ route("universal.attendance.scan.post") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ qr_token: token })
            })
            .then(res => res.json().then(data => ({ status: res.status, ok: res.ok, data: data })))
            .then(response => {
                const data = response.data;
                this.lastResult = data;
                
                if (response.ok && data.success) {
                    this.playBeep(true);

                    if (data.category === 'student') {
                        if (data.action === 'checked_in') {
                            this.studentCount++;
                            this.totalCount++;
                        }
                        this.liveFeed.unshift({
                            category: 'student',
                            name: data.name,
                            sub: data.class_name,
                            time: data.time,
                            status: data.action === 'already_marked' ? 'Already In' : 'Present',
                            badge_color: 'indigo'
                        });
                    } else if (data.category === 'teacher') {
                        if (data.action === 'check_in') {
                            this.teacherCount++;
                            this.totalCount++;
                        }
                        this.liveFeed.unshift({
                            category: 'teacher',
                            name: data.name,
                            sub: data.action === 'check_out' ? 'Checked Out' : 'Faculty Staff',
                            time: data.time,
                            status: data.action === 'check_out' ? 'Out' : 'In',
                            badge_color: 'emerald'
                        });
                    }
                } else {
                    this.playBeep(false);
                }

                // Refocus input field for next barcode scan
                setTimeout(() => {
                    this.isProcessing = false;
                    if (this.$refs.tokenInput) {
                        this.$refs.tokenInput.focus();
                    }
                }, 1200);
            })
            .catch(err => {
                this.playBeep(false);
                this.lastResult = {
                    success: false,
                    message: "Network or server connection failed. Please retry."
                };
                this.isProcessing = false;
            });
        }
    };
}
</script>
@endsection

