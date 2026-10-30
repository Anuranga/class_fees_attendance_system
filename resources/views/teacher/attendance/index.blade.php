@extends('layouts.app')

@section('title', 'QR Attendance Scanner - ' . $classRoom->name)
@section('page-title', 'QR Attendance Scanner')
@section('page-subtitle', $classRoom->name . ' • Real-Time Session Check-In')

@section('content')
<div class="space-y-5" x-data="attendanceScanner()" x-init="initScanner()">

    <!-- Header Stats Banner -->
    <div class="rounded-2xl bg-white border border-slate-200/80 p-4 sm:p-6 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <div class="flex flex-wrap items-center gap-2 mb-1">
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                        {{ $classRoom->grade_level }}
                    </span>
                    <span class="text-xs font-mono text-slate-500 font-semibold">
                        <i class="fa-solid fa-calendar-day text-indigo-500 mr-1"></i>
                        {{ \Carbon\Carbon::parse($selectedDate)->format('D, M d, Y') }}
                    </span>
                    <span class="text-xs font-mono text-emerald-600 font-bold bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">
                        Fee: LKR {{ number_format($classRoom->monthly_fee, 2) }}/mo
                    </span>
                </div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">{{ $classRoom->name }}</h1>
            </div>

            <!-- Date Selector Filter Form -->
            <form action="{{ route('teacher.attendance.show', $classRoom) }}" method="GET" class="flex items-center gap-2">
                <input type="date" name="date" value="{{ $selectedDate }}" 
                       class="px-3 py-1.5 sm:py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                <button type="submit" class="px-3.5 py-1.5 sm:py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold shadow-xs transition-all">
                    Load
                </button>
                @if(!$isToday)
                    <a href="{{ route('teacher.attendance.show', $classRoom) }}" class="px-3 py-1.5 sm:py-2 rounded-xl bg-indigo-50 text-indigo-700 hover:bg-indigo-100 text-xs font-bold border border-indigo-200">
                        Today
                    </a>
                @endif
            </form>
        </div>

        <!-- Live Statistics Counter Pills (Reactive DOM targets) -->
        <div class="mt-4 pt-4 border-t border-slate-100 grid grid-cols-2 sm:grid-cols-5 gap-2.5 text-center">
            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                <span class="text-[10px] font-bold text-slate-400 uppercase">Enrolled</span>
                <p class="text-lg sm:text-xl font-black text-slate-800 mt-0.5" x-text="stats.total_enrolled">{{ $totalEnrolled }}</p>
            </div>
            <div class="p-2.5 rounded-xl bg-emerald-50 border border-emerald-100">
                <span class="text-[10px] font-bold text-emerald-700 uppercase">Present</span>
                <p class="text-lg sm:text-xl font-black text-emerald-800 mt-0.5" x-text="stats.present_count">{{ $presentCount }}</p>
            </div>
            <div class="p-2.5 rounded-xl bg-amber-50 border border-amber-100">
                <span class="text-[10px] font-bold text-amber-700 uppercase">Late</span>
                <p class="text-lg sm:text-xl font-black text-amber-800 mt-0.5" x-text="stats.late_count">{{ $lateCount }}</p>
            </div>
            <div class="p-2.5 rounded-xl bg-rose-50 border border-rose-100">
                <span class="text-[10px] font-bold text-rose-700 uppercase">Absent</span>
                <p class="text-lg sm:text-xl font-black text-rose-800 mt-0.5" x-text="stats.absent_count">{{ $absentCount }}</p>
            </div>
            <div class="p-2.5 rounded-xl bg-purple-50 border border-purple-100 col-span-2 sm:col-span-1">
                <span class="text-[10px] font-bold text-purple-700 uppercase">Turnout</span>
                <p class="text-lg sm:text-xl font-black text-purple-800 mt-0.5"><span x-text="stats.rate">{{ $attendanceRate }}</span>%</p>
            </div>
        </div>
    </div>

    <!-- Quick Action Row: Mark Attendance & Fee + Daily Sheet -->
    <div class="flex flex-wrap items-center justify-between gap-3">
        <button type="button" 
                @click="openMarkModal('', '', '{{ $classRoom->monthly_fee }}', false)"
                class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md shadow-indigo-600/30 flex items-center gap-2 transition-all hover:scale-105 active:scale-95">
            <i class="fa-solid fa-user-check"></i>
            <span>Mark Attendance & Collect Fee</span>
        </button>

        <a href="{{ route('teacher.daily.sheet', ['class_id' => $classRoom->id, 'date' => $selectedDate]) }}" 
           class="px-4 py-2.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 text-xs font-bold transition-all flex items-center gap-2 shadow-xs">
            <i class="fa-solid fa-calendar-check text-emerald-600"></i>
            <span>View Today's Cash & Attendance Sheet</span>
            <i class="fa-solid fa-arrow-right text-[10px] ml-1"></i>
        </a>
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
                <p class="font-extrabold text-base leading-tight" x-text="toast.title"></p>
                <p class="text-xs opacity-90 mt-0.5" x-text="toast.message"></p>
            </div>
        </div>

        <button @click="toast.show = false" class="text-white/80 hover:text-white p-1" aria-label="Close message">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <!-- Mobile Segmented Tab Selector (Phone Friendly) -->
    <div class="lg:hidden flex items-center bg-slate-200/90 p-1 rounded-xl text-xs font-bold gap-1 shadow-inner">
        <button type="button" @click="activeMobileTab = 'scanner'" 
                :class="activeMobileTab === 'scanner' ? 'bg-white text-indigo-700 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                class="flex-1 py-2 px-2.5 rounded-lg text-center flex items-center justify-center gap-1.5 transition-all">
            <i class="fa-solid fa-camera"></i>
            <span>Camera</span>
        </button>
        <button type="button" @click="activeMobileTab = 'feed'" 
                :class="activeMobileTab === 'feed' ? 'bg-white text-indigo-700 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                class="flex-1 py-2 px-2.5 rounded-lg text-center flex items-center justify-center gap-1.5 transition-all">
            <i class="fa-solid fa-list-check"></i>
            <span>Scanned (<span x-text="stats.present_count + stats.late_count"></span>)</span>
        </button>
        <button type="button" @click="activeMobileTab = 'roster'" 
                :class="activeMobileTab === 'roster' ? 'bg-white text-indigo-700 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                class="flex-1 py-2 px-2.5 rounded-lg text-center flex items-center justify-center gap-1.5 transition-all">
            <i class="fa-solid fa-users"></i>
            <span>Roster (<span x-text="stats.total_enrolled"></span>)</span>
        </button>
    </div>

    <!-- Main Scanner + Realtime Scanned Table Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">

        <!-- Column 1: Camera Scanner & Manual Input (5 Cols on desktop) -->
        <div class="lg:col-span-5 space-y-4" :class="activeMobileTab === 'scanner' ? 'block' : 'hidden lg:block'">
            
            <!-- Camera Scanner Container -->
            <div class="rounded-2xl bg-slate-900 border border-slate-800 shadow-xl overflow-hidden p-4 sm:p-5 text-white">
                
                <!-- Scanner Header & Controls -->
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full" :class="isScanning ? 'bg-emerald-400 animate-ping' : 'bg-slate-500'"></span>
                        <h2 class="text-xs sm:text-sm font-bold uppercase tracking-wider text-slate-200">Camera QR Scanner</h2>
                    </div>

                    <div class="flex items-center gap-1.5">
                        <!-- Camera Flip Button (Only shown if device has multiple cameras) -->
                        <button type="button" x-show="cameras.length > 1" @click="switchCamera()" 
                                class="p-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-xs border border-slate-700 transition-colors"
                                title="Switch Front/Back Camera">
                            <i class="fa-solid fa-camera-rotate"></i>
                        </button>

                        <!-- Flashlight / Torch Button (Phone Friendly) -->
                        <button type="button" x-show="isScanning && hasTorch" @click="toggleTorch()" 
                                :class="torchOn ? 'bg-amber-500 text-slate-950 font-bold' : 'bg-slate-800 text-slate-300 hover:text-white'"
                                class="p-1.5 rounded-lg text-xs border border-slate-700 transition-colors"
                                title="Toggle Flashlight / Torch">
                            <i class="fa-solid fa-bolt"></i>
                        </button>

                        <span class="text-[11px] text-indigo-400 font-mono" x-text="isScanning ? 'Scanning...' : 'Idle'"></span>
                    </div>
                </div>

                <!-- Video Viewport / Scanner Target Area -->
                <div class="relative rounded-xl overflow-hidden bg-slate-950 border border-slate-800 aspect-[4/3] sm:aspect-square max-h-[360px] flex items-center justify-center">
                    
                    <!-- HTML5 QRCode Reader Element -->
                    <div id="reader" class="w-full h-full"></div>

                    <!-- Idle Overlay when Scanner not running -->
                    <div x-show="!isScanning" class="absolute inset-0 bg-slate-950/90 flex flex-col items-center justify-center p-5 text-center z-10">
                        <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 flex items-center justify-center text-2xl sm:text-3xl mb-3">
                            <i class="fa-solid fa-camera"></i>
                        </div>
                        <h3 class="text-sm sm:text-base font-bold text-white">Mobile Camera Scanner</h3>
                        <p class="text-xs text-slate-400 mt-1 max-w-xs mb-4">Point your mobile phone camera at student QR code badges to mark attendance instantly.</p>
                        <button @click="startScanner()" 
                                class="px-5 py-2.5 sm:px-6 sm:py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-lg shadow-indigo-600/30 flex items-center gap-2 transition-all hover:scale-105 active:scale-95">
                            <i class="fa-solid fa-play"></i>
                            <span>Activate Phone Camera</span>
                        </button>
                    </div>

                    <!-- Active Scanner Targeting Reticle Overlay -->
                    <div x-show="isScanning" class="absolute inset-0 pointer-events-none flex items-center justify-center z-10" style="display: none;">
                        <div class="w-48 h-48 sm:w-56 sm:h-56 border-2 border-indigo-400/80 rounded-2xl relative shadow-2xl">
                            <!-- Reticle Corners -->
                            <div class="absolute -top-1 -left-1 w-5 h-5 border-t-4 border-l-4 border-cyan-400 rounded-tl-lg"></div>
                            <div class="absolute -top-1 -right-1 w-5 h-5 border-t-4 border-r-4 border-cyan-400 rounded-tr-lg"></div>
                            <div class="absolute -bottom-1 -left-1 w-5 h-5 border-b-4 border-l-4 border-cyan-400 rounded-bl-lg"></div>
                            <div class="absolute -bottom-1 -right-1 w-5 h-5 border-b-4 border-r-4 border-cyan-400 rounded-br-lg"></div>
                            <!-- Sweeping Laser Line -->
                            <div class="w-full h-0.5 bg-gradient-to-r from-transparent via-cyan-400 to-transparent absolute top-1/2 -translate-y-1/2 animate-pulse"></div>
                        </div>
                    </div>
                </div>

                <!-- Thumb-friendly Tute Handout Toggle -->
                <div class="mt-3 p-3 rounded-xl bg-slate-800/80 border border-slate-700/80 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-indigo-500/20 text-indigo-400 flex items-center justify-center text-sm">
                            <i class="fa-solid fa-book-open"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-white">Hand out Tute on Scan</p>
                            <p class="text-[10px] text-slate-400">Issue lesson material automatically</p>
                        </div>
                    </div>
                    <button type="button" @click="tuteChecked = !tuteChecked" 
                            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-hidden"
                            :class="tuteChecked ? 'bg-emerald-500' : 'bg-slate-600'"
                            role="switch" :aria-checked="tuteChecked">
                        <span class="sr-only">Toggle Tute Handout</span>
                        <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-lg ring-0 transition duration-200 ease-in-out"
                              :class="tuteChecked ? 'translate-x-5' : 'translate-x-0'"></span>
                    </button>
                </div>

                <!-- Camera Controls & Feedback Indicator -->
                <div class="mt-3 flex items-center justify-between gap-2 text-xs">
                    <button x-show="isScanning" @click="stopScanner()" 
                            class="px-3 py-1.5 rounded-xl bg-rose-600/20 hover:bg-rose-600/30 text-rose-300 border border-rose-500/30 font-semibold flex items-center gap-1.5 transition-all text-xs" style="display: none;">
                        <i class="fa-solid fa-stop"></i>
                        <span>Stop Camera</span>
                    </button>

                    <div class="flex items-center gap-2 text-slate-400 ml-auto text-[11px]">
                        <span class="inline-flex items-center gap-1 text-emerald-400">
                            <i class="fa-solid fa-volume-high"></i>
                            <i class="fa-solid fa-mobile-screen ml-1"></i>
                            <span>Beep & Vibration On</span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Instant Mobile Scan Result HUD Card (Heads-Up Display) -->
            <div x-show="latestStudent" 
                 x-transition:enter="transition ease-out duration-300 transform" 
                 x-transition:enter-start="opacity-0 -translate-y-2 scale-95" 
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100" 
                 class="rounded-2xl border-2 p-4 shadow-xl flex flex-col gap-3 transition-all"
                 :class="latestStatus === 'duplicate' ? 'border-amber-400 bg-amber-50/40 text-amber-950' : 'border-emerald-500 bg-emerald-50/40 text-emerald-950'"
                 style="display: none;">
                
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-full flex items-center justify-center font-bold text-sm text-white shadow"
                             :class="latestStatus === 'duplicate' ? 'bg-amber-500' : 'bg-emerald-600'">
                            <span x-text="latestStudent ? latestStudent.full_name.substring(0, 2).toUpperCase() : ''"></span>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="font-black text-base leading-tight text-slate-900" x-text="latestStudent?.full_name"></h3>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full uppercase"
                                      :class="latestStatus === 'duplicate' ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-emerald-100 text-emerald-800 border border-emerald-200'"
                                      x-text="latestStatus === 'duplicate' ? 'Duplicate' : 'Present'"></span>
                            </div>
                            <p class="text-xs text-slate-600 font-mono mt-0.5">
                                <span class="font-bold text-indigo-600" x-text="latestStudent?.uid"></span> &bull; 
                                <span x-text="latestStudent?.admission_number"></span>
                            </p>
                        </div>
                    </div>

                    <button @click="latestStudent = null" class="text-slate-400 hover:text-slate-600 p-1 text-sm" aria-label="Dismiss scan card">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <!-- Badges Strip: Tute Status & Fee Status -->
                <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-slate-200/80 text-xs font-semibold">
                    <!-- Tute Badge -->
                    <template x-if="latestAttendance?.tute_provided">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-100 text-emerald-800 text-[11px] font-bold">
                            <i class="fa-solid fa-file-circle-check text-emerald-600"></i>
                            <span>Tute Handed Out</span>
                        </span>
                    </template>
                    <template x-if="!latestAttendance?.tute_provided">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 text-slate-600 text-[11px] font-semibold">
                            <i class="fa-solid fa-file-circle-xmark text-slate-400"></i>
                            <span>No Tute</span>
                        </span>
                    </template>

                    <!-- Monthly Fee Status Badge -->
                    <template x-if="latestFeeStatus?.is_paid">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-100 text-emerald-800 text-[11px] font-bold">
                            <i class="fa-solid fa-circle-check text-emerald-600"></i>
                            <span x-text="'Fee Paid (' + latestFeeStatus?.month_label + ')'"></span>
                        </span>
                    </template>
                    <template x-if="latestFeeStatus && !latestFeeStatus.is_paid">
                        <div class="inline-flex items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-rose-100 text-rose-800 text-[11px] font-bold">
                                <i class="fa-solid fa-circle-exclamation text-rose-600"></i>
                                <span x-text="'Fee Due: LKR ' + latestFeeStatus?.monthly_fee"></span>
                            </span>
                            <button type="button" 
                                    @click="openMarkModal(latestStudent?.id, latestStudent?.full_name, latestFeeStatus?.monthly_fee, true)"
                                    class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-emerald-600 hover:bg-emerald-500 text-white shadow-xs transition-all flex items-center gap-1">
                                <i class="fa-solid fa-money-bill-wave text-[10px]"></i>
                                <span>Collect Fee Now</span>
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Manual Barcode Gun / Text Token Input Fallback -->
            <div class="rounded-2xl bg-white border border-slate-200/80 p-4 shadow-xs">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5 flex items-center gap-2">
                    <i class="fa-solid fa-barcode text-indigo-600"></i>
                    <span>Manual Token / Barcode Gun Input</span>
                </h3>
                <p class="text-[11px] text-slate-500 mb-2.5">Paste or type student token, Admission No, or Class UID below:</p>

                <form @submit.prevent="submitManualToken()" class="flex gap-2">
                    <input type="text" x-model="manualToken" placeholder="UID or Token (e.g. CLS-1-STD-0001)"
                           class="flex-1 px-3 py-2 rounded-xl border border-slate-300 text-xs font-mono text-slate-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
                           :disabled="processing">
                    <button type="submit" 
                            class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold transition-all disabled:opacity-50 shrink-0"
                            :disabled="processing || !manualToken.trim()">
                        <span x-show="!processing">Check-In</span>
                        <span x-show="processing"><i class="fa-solid fa-spinner animate-spin"></i></span>
                    </button>
                </form>
            </div>

        </div>

        <!-- Column 2: Live Real-Time Scanned Table (7 Cols on desktop) -->
        <div class="lg:col-span-7 space-y-4" :class="activeMobileTab !== 'scanner' ? 'block' : 'hidden lg:block'">
            
            <!-- Live Scanned Feed Table -->
            <div class="rounded-2xl bg-white border border-slate-200/80 shadow-xs overflow-hidden" 
                 :class="activeMobileTab === 'feed' || activeMobileTab === 'scanner' ? 'block' : 'hidden lg:block'">
                <div class="p-4 border-b border-slate-200/80 flex items-center justify-between">
                    <div>
                        <h2 class="text-sm sm:text-base font-bold text-slate-900">Scanned Students Feed (Live DOM)</h2>
                        <p class="text-xs text-slate-500">Updates instantly via AJAX when QR cards are scanned</p>
                    </div>

                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Real-time AJAX
                    </span>
                </div>

                <div class="overflow-x-auto max-h-[520px] overflow-y-auto">
                    <table class="w-full text-left border-collapse min-w-[500px]">
                        <thead class="sticky top-0 bg-slate-50/95 backdrop-blur-xs z-10 border-b border-slate-200/80">
                            <tr class="text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                <th class="py-2.5 px-3">Student</th>
                                <th class="py-2.5 px-3">Class UID</th>
                                <th class="py-2.5 px-3">Time</th>
                                <th class="py-2.5 px-3">Status</th>
                                <th class="py-2.5 px-3">Tute Material</th>
                                <th class="py-2.5 px-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody id="attendanceFeedBody" class="divide-y divide-slate-100 text-xs">
                            <!-- Dynamically updated DOM items -->
                            @forelse($attendances as $record)
                                <tr id="attendance-row-{{ $record->student_id }}" class="hover:bg-slate-50/70 transition-colors">
                                    <td class="py-2.5 px-3">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-xs shrink-0">
                                                {{ strtoupper(substr($record->student->full_name, 0, 2)) }}
                                            </div>
                                            <div>
                                                <p class="font-bold text-slate-900">{{ $record->student->full_name }}</p>
                                                <p class="text-[11px] text-slate-400 font-mono">{{ $record->student->admission_number }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-2.5 px-3 font-mono font-semibold text-indigo-600">
                                        {{ $enrollments->where('student_id', $record->student_id)->first()?->student_class_uid ?? 'N/A' }}
                                    </td>
                                    <td class="py-2.5 px-3 font-mono text-slate-700">
                                        {{ \Carbon\Carbon::parse($record->check_in_time)->format('h:i:s A') }}
                                    </td>
                                    <td class="py-2.5 px-3">
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ $record->status === 'late' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">
                                            {{ ucfirst($record->status) }}
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-3">
                                        <form action="{{ route('teacher.attendance.toggle-tute', [$classRoom, $record]) }}" method="POST" class="inline">
                                            @csrf
                                            @if($record->tute_provided)
                                                <button type="submit" class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-[11px] font-bold bg-emerald-100 hover:bg-emerald-200 text-emerald-800 transition-colors" title="Click to toggle tute status">
                                                    <i class="fa-solid fa-file-circle-check text-emerald-600"></i>
                                                    <span>Issued</span>
                                                </button>
                                            @else
                                                <button type="submit" class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-[11px] font-bold bg-slate-100 hover:bg-slate-200 text-slate-600 transition-colors" title="Click to mark tute as issued">
                                                    <i class="fa-solid fa-file-circle-xmark text-slate-400"></i>
                                                    <span>No Tute</span>
                                                </button>
                                            @endif
                                        </form>
                                    </td>
                                    <td class="py-2.5 px-3 text-right">
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
                                    <td colspan="6" class="py-10 text-center text-slate-400">
                                        <i class="fa-solid fa-qrcode text-3xl text-slate-300 mb-2 block"></i>
                                        <span>No attendance marked for this session yet. Point your camera at a student QR code to begin.</span>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Complete Enrolled Roster with Quick Manual Status Toggles -->
            <div class="rounded-2xl bg-white border border-slate-200/80 p-4 sm:p-5 shadow-xs"
                 :class="activeMobileTab === 'roster' || activeMobileTab === 'scanner' ? 'block' : 'hidden lg:block'">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Class Roster (Manual Check-In)</h3>
                        <p class="text-xs text-slate-500">Tap to mark present, late, or clear attendance without a card</p>
                    </div>
                </div>

                <div class="divide-y divide-slate-100 max-h-64 overflow-y-auto">
                    @foreach($enrollments as $item)
                        @php
                            $att = $attendanceMap->get($item->student_id);
                        @endphp
                        <div class="py-2 flex items-center justify-between gap-2 text-xs">
                            <div class="min-w-0">
                                <p class="font-bold text-slate-800 truncate">{{ $item->student->full_name }}</p>
                                <p class="text-slate-400 font-mono text-[10px]">{{ $item->student_class_uid }}</p>
                            </div>

                            <div class="flex items-center gap-1 shrink-0">
                                <button type="button" 
                                        @click="openMarkModal('{{ $item->student_id }}', '{{ addslashes($item->student->full_name) }}', '{{ $classRoom->monthly_fee }}', false)"
                                        class="px-2 py-1 rounded-md text-[11px] font-bold bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200 transition-colors flex items-center gap-1"
                                        title="Check-In and Optionally Collect Fee">
                                    <i class="fa-solid fa-receipt text-[10px]"></i>
                                    <span>Check-In + Fee</span>
                                </button>

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
                                                class="px-2 py-1 rounded-md text-[11px] font-bold bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 transition-colors"
                                                title="Mark Absent">
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

    <!-- Unified Mark Attendance & Collect Fee Modal -->
    <div x-show="markModalOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
         style="display: none;">
        
        <div @click.away="markModalOpen = false" 
             class="w-full max-w-lg bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden">
            
            <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Mark Attendance & Collect Fee</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Session: {{ $classRoom->name }} &bull; {{ \Carbon\Carbon::parse($selectedDate)->format('M d, Y') }}</p>
                </div>
                <button @click="markModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form action="{{ route('teacher.attendance.mark-with-fee', $classRoom) }}" method="POST" class="p-5 space-y-4">
                @csrf
                <input type="hidden" name="date" value="{{ $selectedDate }}">

                <!-- Student Selection -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Select Student *</label>
                    <select name="student_id" x-model="modalStudentId" required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                        <option value="">-- Choose Enrolled Student ({{ $enrollments->count() }}) --</option>
                        @foreach($enrollments as $enr)
                            <option value="{{ $enr->student_id }}">
                                {{ $enr->student->full_name }} ({{ $enr->student_class_uid }} &bull; {{ $enr->student->admission_number }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Attendance Status & Tute Handout (Compulsory Attendance Part) -->
                <div class="grid grid-cols-2 gap-3 p-3 rounded-xl bg-slate-50 border border-slate-200">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Attendance Status *</label>
                        <select name="status" class="w-full px-3 py-1.5 rounded-lg border border-slate-300 text-xs font-semibold text-slate-800">
                            <option value="present" selected>Present (On-Time)</option>
                            <option value="late">Late Arrival</option>
                        </select>
                    </div>

                    <div class="flex flex-col justify-center">
                        <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-slate-700 mt-3">
                            <input type="checkbox" name="tute_provided" value="1" checked class="w-4 h-4 rounded text-indigo-600 border-slate-300 focus:ring-indigo-500/20">
                            <span>Issue Tute / Material</span>
                        </label>
                    </div>
                </div>

                <!-- Fee Collection Section (Alternative / Optional) -->
                <div class="p-4 rounded-xl border-2 transition-all" :class="modalCollectFee ? 'border-emerald-500 bg-emerald-50/20' : 'border-slate-200 bg-white'">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <i class="fa-solid fa-money-bill-wave text-lg" :class="modalCollectFee ? 'text-emerald-600' : 'text-slate-400'"></i>
                            <div>
                                <label class="text-xs font-extrabold text-slate-900 cursor-pointer" @click="modalCollectFee = !modalCollectFee">
                                    Collect Fee at Check-In (Optional)
                                </label>
                                <p class="text-[11px] text-slate-500">Check if student is paying cash today</p>
                            </div>
                        </div>

                        <button type="button" @click="modalCollectFee = !modalCollectFee" 
                                class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-hidden"
                                :class="modalCollectFee ? 'bg-emerald-500' : 'bg-slate-300'">
                            <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-lg ring-0 transition duration-200 ease-in-out"
                                  :class="modalCollectFee ? 'translate-x-5' : 'translate-x-0'"></span>
                        </button>
                    </div>
                    <input type="hidden" name="collect_fee" :value="modalCollectFee ? '1' : '0'">

                    <!-- Expanded Fee Inputs -->
                    <div x-show="modalCollectFee" class="mt-4 pt-3 border-t border-emerald-200/60 space-y-3" style="display: none;">
                        <!-- Payment Period Toggle -->
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Billing Period / Type</label>
                            <div class="grid grid-cols-2 gap-2">
                                <button type="button" @click="modalPaymentType = 'monthly'" 
                                        :class="modalPaymentType === 'monthly' ? 'bg-emerald-600 text-white font-bold' : 'bg-slate-100 text-slate-700'"
                                        class="py-1.5 px-3 rounded-lg text-xs transition-all">
                                    Monthly Fee
                                </button>
                                <button type="button" @click="modalPaymentType = 'daily'" 
                                        :class="modalPaymentType === 'daily' ? 'bg-emerald-600 text-white font-bold' : 'bg-slate-100 text-slate-700'"
                                        class="py-1.5 px-3 rounded-lg text-xs transition-all">
                                    Daily Session Fee
                                </button>
                            </div>
                            <input type="hidden" name="payment_type" :value="modalPaymentType">
                        </div>

                        <!-- Monthly Billing Month Picker (Current or Last Month or Overdue) -->
                        <div x-show="modalPaymentType === 'monthly'">
                            <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Select Month to Settle</label>
                            <select name="payment_month" x-model="modalPaymentMonth" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800">
                                @foreach($recentMonths as $mKey => $mText)
                                    <option value="{{ $mKey }}">{{ $mText }}</option>
                                @endforeach
                            </select>
                            <p class="text-[10px] text-slate-500 mt-1">If student missed paying last month, select Last Month to credit arrears.</p>
                        </div>

                        <!-- Amount & Payment Method -->
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Amount (LKR) *</label>
                                <input type="number" step="0.01" min="0" name="amount" x-model="modalAmount"
                                       class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-bold text-slate-800">
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Payment Method</label>
                                <select name="payment_method" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800">
                                    <option value="cash" selected>Cash Handover</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <input type="text" name="notes" placeholder="Optional note (e.g. Paid in full for last month)"
                                   class="w-full px-3 py-1.5 rounded-lg border border-slate-300 text-xs text-slate-700">
                        </div>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" @click="markModalOpen = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-xs font-semibold hover:bg-slate-50">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md shadow-indigo-600/30">
                        Confirm Attendance & Save
                    </button>
                </div>
            </form>
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
        tuteChecked: true,
        lastScannedToken: null,
        lastScanTimestamp: 0,
        manualToken: '',
        scanEndpoint: '{{ route('teacher.attendance.scan', $classRoom) }}',
        csrfToken: '{{ csrf_token() }}',
        activeMobileTab: 'scanner',

        // Multi-Camera & Flashlight State
        cameras: [],
        selectedCameraId: null,
        hasTorch: false,
        torchOn: false,

        // Live HUD Result Card
        latestStudent: null,
        latestAttendance: null,
        latestFeeStatus: null,
        latestStatus: null,

        // Unified Mark Attendance & Collect Fee Modal
        markModalOpen: false,
        modalStudentId: '',
        modalStudentName: '',
        modalAmount: '{{ $classRoom->monthly_fee }}',
        modalCollectFee: false,
        modalPaymentType: '{{ $classRoom->isDailyFee() ? "daily" : "monthly" }}',
        modalPaymentMonth: '{{ \Carbon\Carbon::now()->format("Y-m") }}',

        openMarkModal(studentId = '', studentName = '', amount = '', collectFee = false) {
            this.modalStudentId = studentId || '';
            this.modalStudentName = studentName || '';
            this.modalAmount = amount || '{{ $classRoom->monthly_fee }}';
            this.modalCollectFee = !!collectFee;
            this.markModalOpen = true;
        },

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

        async initScanner() {
            try {
                if (window.Html5Qrcode) {
                    const devices = await window.Html5Qrcode.getCameras();
                    if (devices && devices.length > 0) {
                        this.cameras = devices;
                        // Auto-select preferred back/rear camera for mobile phones
                        const rearCamera = devices.find(d => 
                            d.label.toLowerCase().includes('back') || 
                            d.label.toLowerCase().includes('rear') || 
                            d.label.toLowerCase().includes('environment')
                        );
                        this.selectedCameraId = rearCamera ? rearCamera.id : devices[0].id;
                    }
                }
            } catch (e) {
                console.log('Camera enumeration info:', e);
            }
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

                // Phone-friendly QR box size
                const isMobile = window.innerWidth < 768;
                const qrBoxSize = isMobile ? Math.min(window.innerWidth - 60, 240) : 250;

                const config = { 
                    fps: 15, 
                    qrbox: { width: qrBoxSize, height: qrBoxSize },
                    aspectRatio: 1.0,
                    experimentalFeatures: {
                        useBarCodeDetectorIfSupported: true
                    }
                };

                const cameraSource = this.selectedCameraId 
                    ? this.selectedCameraId 
                    : { facingMode: "environment" };

                await this.html5QrCode.start(
                    cameraSource,
                    config,
                    (decodedText) => this.onScanSuccess(decodedText),
                    (errorMessage) => {
                        // ignorable per-frame parse notice
                    }
                );

                this.isScanning = true;

                // Inspect track capabilities for mobile torch
                setTimeout(() => {
                    this.checkTorchSupport();
                }, 600);

            } catch (err) {
                console.error("Camera startup error:", err);
                this.triggerToast('error', 'Camera Error', 'Could not access phone camera. Please grant camera permissions in your browser or try switching lenses.');
            }
        },

        async stopScanner() {
            if (this.html5QrCode && this.isScanning) {
                this.torchOn = false;
                await this.html5QrCode.stop();
                this.isScanning = false;
            }
        },

        async switchCamera() {
            if (this.cameras.length <= 1) return;
            const currentIndex = this.cameras.findIndex(c => c.id === this.selectedCameraId);
            const nextIndex = (currentIndex + 1) % this.cameras.length;
            this.selectedCameraId = this.cameras[nextIndex].id;

            if (this.isScanning) {
                await this.stopScanner();
                await this.startScanner();
                this.triggerToast('success', 'Camera Switched', `Using: ${this.cameras[nextIndex].label || 'Camera ' + (nextIndex + 1)}`);
            }
        },

        getVideoTrack() {
            const video = document.querySelector('#reader video');
            if (video && video.srcObject) {
                const stream = video.srcObject;
                const tracks = stream.getVideoTracks();
                return tracks.length > 0 ? tracks[0] : null;
            }
            return null;
        },

        checkTorchSupport() {
            try {
                const track = this.getVideoTrack();
                if (track && track.getCapabilities) {
                    const capabilities = track.getCapabilities();
                    this.hasTorch = !!capabilities.torch;
                } else {
                    this.hasTorch = false;
                }
            } catch (e) {
                this.hasTorch = false;
            }
        },

        async toggleTorch() {
            try {
                const track = this.getVideoTrack();
                if (track && track.applyConstraints) {
                    this.torchOn = !this.torchOn;
                    await track.applyConstraints({
                        advanced: [{ torch: this.torchOn }]
                    });
                }
            } catch (e) {
                console.warn('Torch error:', e);
                this.triggerToast('warning', 'Torch Notice', 'Torch is not supported on this camera lens.');
            }
        },

        async onScanSuccess(decodedText) {
            const now = Date.now();
            // Debounce repeated readings within 2.5 seconds
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
                    body: JSON.stringify({ qr_token: token, tute_provided: this.tuteChecked })
                });

                const data = await response.json();

                if (response.status === 200) {
                    // Mobile haptic vibration & sound
                    if (navigator.vibrate) navigator.vibrate([60, 40, 60]);
                    if (window.playSuccessBeep) window.playSuccessBeep();

                    // Update live HUD
                    this.latestStudent = data.student;
                    this.latestAttendance = data.attendance;
                    this.latestFeeStatus = data.fee_status;
                    this.latestStatus = 'success';

                    this.triggerToast('success', 'Checked In Successfully!', `${data.student.full_name} (${data.student.uid}) marked present`);

                    // Update live stats
                    if (data.stats) {
                        this.stats = data.stats;
                    }

                    // Update live DOM feed table
                    this.prependScannedRow(data.student, data.attendance);

                } else if (response.status === 409) {
                    // Duplicate scan today! Haptic buzz
                    if (navigator.vibrate) navigator.vibrate([150, 75, 150]);
                    if (window.playErrorBuzz) window.playErrorBuzz();

                    this.latestStudent = data.student;
                    this.latestAttendance = data.attendance;
                    this.latestFeeStatus = data.fee_status;
                    this.latestStatus = 'duplicate';

                    this.triggerToast('warning', 'Already Marked Present!', data.message);

                } else {
                    // Not found or error
                    if (navigator.vibrate) navigator.vibrate(200);
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
                <td class="py-2.5 px-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-xs shrink-0">
                            ${student.full_name.substring(0, 2).toUpperCase()}
                        </div>
                        <div>
                            <p class="font-bold text-slate-900">${student.full_name}</p>
                            <p class="text-[11px] text-slate-400 font-mono">${student.admission_number}</p>
                        </div>
                    </div>
                </td>
                <td class="py-2.5 px-3 font-mono font-semibold text-indigo-600">
                    ${student.uid}
                </td>
                <td class="py-2.5 px-3 font-mono text-slate-700">
                    ${attendance.check_in_time}
                </td>
                <td class="py-2.5 px-3">
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">
                        Present
                    </span>
                </td>
                <td class="py-2.5 px-3">
                    ${attendance.tute_provided 
                        ? '<span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-[11px] font-bold bg-emerald-100 text-emerald-800"><i class="fa-solid fa-file-circle-check text-emerald-600"></i> Issued</span>'
                        : '<span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-[11px] font-bold bg-slate-100 text-slate-500"><i class="fa-solid fa-file-circle-xmark text-slate-400"></i> No Tute</span>'
                    }
                </td>
                <td class="py-2.5 px-3 text-right">
                    <span class="text-xs text-emerald-600 font-bold"><i class="fa-solid fa-circle-check"></i> Scanned</span>
                </td>
            `;

            tbody.insertBefore(tr, tbody.firstChild);

            setTimeout(() => {
                tr.classList.remove('bg-emerald-50/80', 'animate-pulse');
            }, 3000);
        }
    };
}
</script>
@endpush
