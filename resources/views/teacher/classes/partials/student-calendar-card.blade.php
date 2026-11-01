<div class="space-y-6">

    <!-- Student Header & Fast Switcher Bar -->
    <div class="p-5 rounded-2xl bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white shadow-md flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="relative shrink-0">
                <img src="{{ $student->avatar_url }}" alt="{{ $student->full_name }}" 
                     class="w-14 h-14 rounded-2xl object-cover border-2 border-indigo-400/40 shadow-md bg-slate-800"
                     onerror="this.onerror=null; this.src='{{ asset('images/avatars/' . ($student->gender === 'female' ? 'female.svg' : 'male.svg')) }}';">
                <span class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full flex items-center justify-center text-[10px] {{ $student->gender === 'female' ? 'bg-pink-500' : 'bg-blue-500' }} text-white shadow">
                    <i class="fa-solid {{ $student->gender === 'female' ? 'fa-venus' : 'fa-mars' }}"></i>
                </span>
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="text-lg sm:text-xl font-black text-white tracking-tight">{{ $student->full_name }}</h2>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-500/30 text-indigo-200 border border-indigo-400/30">
                        {{ $enrollment->student_class_uid }}
                    </span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-700/60 text-slate-300 font-mono">
                        {{ $student->admission_number }}
                    </span>
                </div>
                <div class="mt-1 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-300">
                    <span><i class="fa-solid fa-graduation-cap text-indigo-400 mr-1"></i>{{ $classRoom->name }} ({{ $classRoom->grade_level }})</span>
                    @if($student->contact_number)
                        <span><i class="fa-solid fa-phone text-slate-400 mr-1"></i>{{ $student->contact_number }}</span>
                    @endif
                    @if($student->guardian_name)
                        <span><i class="fa-solid fa-user-shield text-slate-400 mr-1"></i>{{ $student->guardian_name }} ({{ $student->guardian_phone }})</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Student Switcher Dropdown (Inside Class) -->
        <div class="flex items-center gap-2 self-start md:self-center shrink-0">
            <label class="text-[11px] font-bold uppercase tracking-wider text-indigo-300 hidden sm:inline">Switch Student:</label>
            <select onchange="window.switchStudentCalendar ? window.switchStudentCalendar(this.value, '{{ $currentMonth }}') : window.location.href='{{ url('/teacher/classes/' . $classRoom->id . '/students') }}/' + this.value + '/attendance?month={{ $currentMonth }}'"
                    class="bg-slate-800/90 hover:bg-slate-800 border border-indigo-400/30 text-xs font-semibold text-white rounded-xl px-3 py-2 focus:ring-2 focus:ring-indigo-400 focus:outline-hidden max-w-xs cursor-pointer">
                @foreach($allClassStudents as $cs)
                    <option value="{{ $cs->student_id }}" {{ $cs->student_id == $student->id ? 'selected' : '' }}>
                        {{ $cs->student->full_name }} ({{ $cs->student_class_uid }})
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Month Navigation Bar -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <!-- Prev / Next Controls -->
        <div class="flex items-center gap-2">
            <button type="button" 
                    onclick="window.switchStudentCalendar ? window.switchStudentCalendar({{ $student->id }}, '{{ $prevMonth }}') : window.location.href='?month={{ $prevMonth }}'"
                    class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs flex items-center gap-2 transition-colors"
                    title="Previous Month">
                <i class="fa-solid fa-chevron-left text-xs"></i>
                <span class="hidden sm:inline">Prev Month</span>
            </button>

            <div class="flex items-center gap-2 px-4 py-1.5 rounded-xl bg-indigo-50 border border-indigo-200/80 text-indigo-900 font-extrabold text-sm sm:text-base">
                <i class="fa-solid fa-calendar-day text-indigo-600"></i>
                <span>{{ $monthLabel }}</span>
            </div>

            <button type="button" 
                    onclick="window.switchStudentCalendar ? window.switchStudentCalendar({{ $student->id }}, '{{ $nextMonth }}') : window.location.href='?month={{ $nextMonth }}'"
                    class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs flex items-center gap-2 transition-colors"
                    title="Next Month">
                <span class="hidden sm:inline">Next Month</span>
                <i class="fa-solid fa-chevron-right text-xs"></i>
            </button>
        </div>

        <!-- Jump to Today / Full Page Actions -->
        <div class="flex items-center gap-2">
            @php
                $thisMonthStr = \Carbon\Carbon::now()->format('Y-m');
            @endphp
            @if($currentMonth !== $thisMonthStr)
                <button type="button"
                        onclick="window.switchStudentCalendar ? window.switchStudentCalendar({{ $student->id }}, '{{ $thisMonthStr }}') : window.location.href='?month={{ $thisMonthStr }}'"
                        class="px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold transition-all shadow-xs flex items-center gap-1.5">
                    <i class="fa-solid fa-bullseye"></i>
                    <span>This Month</span>
                </button>
            @endif

            <a href="{{ route('public.pass', $enrollment->qr_token) }}" target="_blank"
               class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold border border-slate-200 transition-colors flex items-center gap-1.5"
               title="View Printable Student QR Pass">
                <i class="fa-solid fa-id-card text-indigo-600"></i>
                <span>QR Pass</span>
            </a>

            <a href="{{ route('teacher.classes.students.attendance', [$classRoom, $student, 'month' => $currentMonth]) }}"
               target="_blank"
               class="px-3 py-1.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-bold border border-indigo-200 transition-colors flex items-center gap-1.5"
               title="Open Dedicated Full Page View">
                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                <span>Full Page</span>
            </a>
        </div>
    </div>

    <!-- Monthly Statistics KPI Tiles -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <!-- Sessions Held -->
        <div class="rounded-2xl bg-white p-3.5 border border-slate-200/80 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Class Sessions</span>
            <div class="flex items-baseline gap-2 mt-1">
                <span class="text-xl sm:text-2xl font-black text-slate-800">{{ $totalSessionsHeld }}</span>
                <span class="text-[10px] text-slate-400">held</span>
            </div>
            <span class="text-[10px] text-slate-500 mt-0.5 block truncate">Total in {{ $monthLabel }}</span>
        </div>

        <!-- Present -->
        <div class="rounded-2xl bg-emerald-50/70 p-3.5 border border-emerald-200/80 shadow-xs">
            <span class="text-[10px] font-bold text-emerald-700 uppercase tracking-wider block">Present</span>
            <div class="flex items-baseline gap-2 mt-1">
                <span class="text-xl sm:text-2xl font-black text-emerald-800">{{ $presentCount }}</span>
                <span class="text-[10px] text-emerald-600">days</span>
            </div>
            <span class="text-[10px] text-emerald-700 font-semibold mt-0.5 block">Attended on time</span>
        </div>

        <!-- Late -->
        <div class="rounded-2xl bg-amber-50/70 p-3.5 border border-amber-200/80 shadow-xs">
            <span class="text-[10px] font-bold text-amber-700 uppercase tracking-wider block">Late</span>
            <div class="flex items-baseline gap-2 mt-1">
                <span class="text-xl sm:text-2xl font-black text-amber-800">{{ $lateCount }}</span>
                <span class="text-[10px] text-amber-600">days</span>
            </div>
            <span class="text-[10px] text-amber-700 font-semibold mt-0.5 block">Check-in marked late</span>
        </div>

        <!-- Missed / Absent -->
        <div class="rounded-2xl bg-rose-50/70 p-3.5 border border-rose-200/80 shadow-xs">
            <span class="text-[10px] font-bold text-rose-700 uppercase tracking-wider block">Missed / Absent</span>
            <div class="flex items-baseline gap-2 mt-1">
                <span class="text-xl sm:text-2xl font-black text-rose-800">{{ $missedSessions }}</span>
                <span class="text-[10px] text-rose-600">sessions</span>
            </div>
            <span class="text-[10px] text-rose-700 font-semibold mt-0.5 block">Not present in class</span>
        </div>

        <!-- Attendance Rate -->
        <div class="rounded-2xl bg-indigo-50/70 p-3.5 border border-indigo-200/80 shadow-xs">
            <span class="text-[10px] font-bold text-indigo-700 uppercase tracking-wider block">Monthly Rate</span>
            <div class="flex items-baseline gap-1 mt-1">
                <span class="text-xl sm:text-2xl font-black text-indigo-800">{{ $attendanceRate }}%</span>
            </div>
            <div class="w-full bg-indigo-200/70 rounded-full h-1.5 mt-1.5 overflow-hidden">
                <div class="bg-indigo-600 h-1.5 rounded-full" style="width: {{ min(100, $attendanceRate) }}%"></div>
            </div>
        </div>

        <!-- Monthly Fee Status -->
        <div class="rounded-2xl {{ $monthlyPayment ? 'bg-emerald-50/70 border-emerald-200/80' : 'bg-amber-50/70 border-amber-200/80' }} p-3.5 border shadow-xs">
            <span class="text-[10px] font-bold {{ $monthlyPayment ? 'text-emerald-700' : 'text-amber-700' }} uppercase tracking-wider block">Monthly Fee</span>
            @if($monthlyPayment)
                <div class="flex items-baseline gap-1 mt-1">
                    <span class="text-base sm:text-lg font-black text-emerald-800">Paid</span>
                </div>
                <span class="text-[10px] text-emerald-700 font-mono font-bold mt-0.5 block truncate">#{{ $monthlyPayment->receipt_no }}</span>
            @else
                <div class="flex items-baseline gap-1 mt-1">
                    <span class="text-base sm:text-lg font-black text-amber-800">Pending</span>
                </div>
                <span class="text-[10px] text-amber-700 font-bold mt-0.5 block">LKR {{ number_format($classRoom->monthly_fee, 2) }}</span>
            @endif
        </div>
    </div>

    <!-- Interactive Monthly Calendar Grid Card -->
    <div class="rounded-2xl bg-white border border-slate-200/80 shadow-xs overflow-hidden" 
         x-data="{ activeDay: null }">

        <div class="p-4 sm:p-5 border-b border-slate-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/60">
            <div>
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa-regular fa-calendar-days text-indigo-600"></i>
                    <span>Monthly Attendance Calendar</span>
                </h3>
                <p class="text-xs text-slate-500">Color-coded daily status for {{ $student->full_name }}</p>
            </div>

            <!-- Legend Pills -->
            <div class="flex flex-wrap items-center gap-2 text-[11px] font-semibold text-slate-600">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200/80">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Present
                </span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-amber-50 text-amber-800 border border-amber-200/80">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> Late
                </span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-rose-50 text-rose-800 border border-rose-200/80">
                    <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span> Missed / Absent
                </span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-800 border border-indigo-200/80">
                    <i class="fa-solid fa-book text-[10px] text-indigo-600"></i> Tute Given
                </span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-blue-50 text-blue-800 border border-blue-200/80">
                    <i class="fa-solid fa-money-bill-wave text-[10px] text-blue-600"></i> Fee Paid
                </span>
            </div>
        </div>

        <!-- 7-Column Calendar Grid -->
        <div class="p-3 sm:p-5">
            <!-- Day of Week Header -->
            <div class="grid grid-cols-7 gap-1 sm:gap-2 mb-2 text-center text-[11px] font-extrabold uppercase tracking-wider text-slate-500">
                <div class="py-2 rounded-lg bg-slate-100/80">Mon</div>
                <div class="py-2 rounded-lg bg-slate-100/80">Tue</div>
                <div class="py-2 rounded-lg bg-slate-100/80">Wed</div>
                <div class="py-2 rounded-lg bg-slate-100/80">Thu</div>
                <div class="py-2 rounded-lg bg-slate-100/80">Fri</div>
                <div class="py-2 rounded-lg bg-indigo-50/70 text-indigo-700">Sat</div>
                <div class="py-2 rounded-lg bg-indigo-50/70 text-indigo-700">Sun</div>
            </div>

            <!-- Calendar Days Cells -->
            <div class="grid grid-cols-7 gap-1 sm:gap-2">
                @foreach($calendarDays as $cell)
                    @php
                        $isCurrentMonth = $cell['is_current_month'];
                        $status = $cell['status'];
                        $attendance = $cell['attendance'];
                        $payments = $cell['payments'];
                        $hasPayments = !empty($payments);
                    @endphp

                    <div @if($isCurrentMonth) @click="activeDay = {{ json_encode($cell) }}" @endif
                         class="min-h-[78px] sm:min-h-[96px] p-1.5 sm:p-2 rounded-xl border transition-all flex flex-col justify-between select-none
                                {{ !$isCurrentMonth ? 'bg-slate-50/40 border-slate-100 text-slate-300 pointer-events-none' : 'cursor-pointer hover:shadow-md' }}
                                {{ $cell['is_today'] ? 'ring-2 ring-indigo-500 ring-offset-1' : '' }}
                                @if($isCurrentMonth)
                                    @if($status === 'present')
                                        bg-emerald-50/60 border-emerald-300 hover:bg-emerald-50
                                    @elseif($status === 'late')
                                        bg-amber-50/60 border-amber-300 hover:bg-amber-50
                                    @elseif($status === 'absent' || $status === 'missed')
                                        bg-rose-50/60 border-rose-300 hover:bg-rose-50
                                    @elseif($cell['session_held'])
                                        bg-slate-100/80 border-slate-300
                                    @else
                                        bg-white border-slate-200/80 hover:border-slate-300
                                    @endif
                                @endif
                                ">

                        <!-- Date Number & Today Pill -->
                        <div class="flex items-center justify-between">
                            <span class="text-xs sm:text-sm font-extrabold font-mono {{ $isCurrentMonth ? ($cell['is_today'] ? 'text-indigo-600' : 'text-slate-800') : 'text-slate-300' }}">
                                {{ $cell['day'] }}
                            </span>

                            @if($cell['is_today'])
                                <span class="px-1.5 py-0.2 rounded-md bg-indigo-600 text-white text-[9px] font-black uppercase tracking-wider shadow-2xs">
                                    Today
                                </span>
                            @endif
                        </div>

                        <!-- Status Indicators inside the tile -->
                        <div class="space-y-1 my-1">
                            @if($isCurrentMonth)
                                @if($status === 'present')
                                    <div class="px-1.5 py-0.5 rounded-md bg-emerald-600 text-white text-[10px] font-bold flex items-center justify-between shadow-2xs">
                                        <span class="flex items-center gap-1">
                                            <i class="fa-solid fa-check text-[9px]"></i>
                                            <span class="hidden sm:inline">Present</span>
                                        </span>
                                        @if($attendance && $attendance['check_in_time'])
                                            <span class="font-mono text-[9px] opacity-90 hidden sm:inline">{{ $attendance['check_in_time'] }}</span>
                                        @endif
                                    </div>
                                    @if($attendance && $attendance['tute_provided'])
                                        <div class="px-1.5 py-0.5 rounded-md bg-indigo-100 text-indigo-800 text-[9px] font-bold flex items-center gap-1 truncate">
                                            <i class="fa-solid fa-book text-[8px] text-indigo-600"></i>
                                            <span class="truncate">{{ $attendance['tute_name'] ?: 'Tute' }}</span>
                                        </div>
                                    @endif

                                @elseif($status === 'late')
                                    <div class="px-1.5 py-0.5 rounded-md bg-amber-500 text-white text-[10px] font-bold flex items-center justify-between shadow-2xs">
                                        <span class="flex items-center gap-1">
                                            <i class="fa-solid fa-clock text-[9px]"></i>
                                            <span class="hidden sm:inline">Late</span>
                                        </span>
                                        @if($attendance && $attendance['check_in_time'])
                                            <span class="font-mono text-[9px] opacity-90 hidden sm:inline">{{ $attendance['check_in_time'] }}</span>
                                        @endif
                                    </div>

                                @elseif($status === 'absent' || $status === 'missed')
                                    <div class="px-1.5 py-0.5 rounded-md bg-rose-500 text-white text-[10px] font-bold flex items-center gap-1 shadow-2xs">
                                        <i class="fa-solid fa-xmark text-[9px]"></i>
                                        <span class="hidden sm:inline">{{ $status === 'absent' ? 'Absent' : 'Missed' }}</span>
                                    </div>

                                @elseif($cell['session_held'])
                                    <span class="text-[9px] font-semibold text-slate-400 block truncate">Class Held</span>
                                @endif

                                <!-- Payment Indicator -->
                                @if($hasPayments)
                                    <div class="px-1.5 py-0.5 rounded-md bg-blue-100 text-blue-800 text-[9px] font-bold flex items-center gap-1 truncate" title="Fee Payment Made">
                                        <i class="fa-solid fa-coins text-[8px] text-blue-600"></i>
                                        <span class="hidden sm:inline font-mono">LKR {{ number_format($payments[0]['amount'], 0) }}</span>
                                    </div>
                                @endif
                            @endif
                        </div>

                        <!-- Micro bottom dot for clean look -->
                        <div class="flex items-center justify-end">
                            @if($isCurrentMonth && ($status === 'present' || $status === 'late' || $status === 'absent' || $status === 'missed'))
                                <span class="w-1.5 h-1.5 rounded-full {{ $status === 'present' ? 'bg-emerald-500' : ($status === 'late' ? 'bg-amber-500' : 'bg-rose-500') }}"></span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Selected Day Breakdown Drawer / Panel -->
        <div x-show="activeDay" 
             x-transition 
             class="border-t border-slate-200/80 p-4 sm:p-5 bg-gradient-to-r from-indigo-50/70 via-white to-slate-50 flex flex-col md:flex-row md:items-center justify-between gap-4"
             style="display: none;">
            <div class="flex items-start sm:items-center gap-3">
                <div class="w-11 h-11 rounded-2xl flex items-center justify-center text-lg shrink-0 shadow-xs"
                     :class="activeDay?.status === 'present' ? 'bg-emerald-600 text-white' : (activeDay?.status === 'late' ? 'bg-amber-500 text-white' : (activeDay?.status === 'absent' || activeDay?.status === 'missed' ? 'bg-rose-500 text-white' : 'bg-slate-200 text-slate-600'))">
                    <template x-if="activeDay?.status === 'present'"><i class="fa-solid fa-circle-check"></i></template>
                    <template x-if="activeDay?.status === 'late'"><i class="fa-solid fa-clock"></i></template>
                    <template x-if="activeDay?.status === 'absent' || activeDay?.status === 'missed'"><i class="fa-solid fa-circle-xmark"></i></template>
                    <template x-if="activeDay?.status === 'no_class'"><i class="fa-regular fa-calendar"></i></template>
                </div>

                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-sm sm:text-base font-extrabold text-slate-900" x-text="activeDay?.date"></span>
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wider"
                              :class="activeDay?.status === 'present' ? 'bg-emerald-100 text-emerald-800' : (activeDay?.status === 'late' ? 'bg-amber-100 text-amber-800' : (activeDay?.status === 'absent' || activeDay?.status === 'missed' ? 'bg-rose-100 text-rose-800' : 'bg-slate-100 text-slate-600'))"
                              x-text="activeDay?.status === 'present' ? 'Present' : (activeDay?.status === 'late' ? 'Late Check-in' : (activeDay?.status === 'absent' ? 'Marked Absent' : (activeDay?.status === 'missed' ? 'Missed Class Session' : 'No Class Held')))"></span>
                        <template x-if="activeDay?.is_today">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-indigo-600 text-white uppercase tracking-wider">Today</span>
                        </template>
                    </div>

                    <div class="mt-1 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-600">
                        <template x-if="activeDay?.attendance?.check_in_time">
                            <span><i class="fa-regular fa-clock text-indigo-600 mr-1"></i>Check-in: <strong class="font-mono text-slate-800" x-text="activeDay?.attendance?.check_in_time"></strong></span>
                        </template>
                        <template x-if="activeDay?.attendance?.marked_by">
                            <span><i class="fa-solid fa-user-check text-slate-400 mr-1"></i>Marked by: <span class="font-semibold text-slate-800" x-text="activeDay?.attendance?.marked_by"></span></span>
                        </template>
                        <template x-if="activeDay?.attendance?.tute_provided">
                            <span class="text-indigo-700 font-semibold"><i class="fa-solid fa-book mr-1"></i>Material: <span x-text="activeDay?.attendance?.tute_name || 'Provided'"></span></span>
                        </template>
                        <template x-if="activeDay?.payments?.length > 0">
                            <span class="text-blue-700 font-bold"><i class="fa-solid fa-receipt mr-1"></i>Receipt: <span class="font-mono" x-text="activeDay?.payments[0]?.receipt_no"></span> (LKR <span x-text="activeDay?.payments[0]?.amount"></span>)</span>
                        </template>
                    </div>
                </div>
            </div>

            <button type="button" @click="activeDay = null" 
                    class="px-3 py-1.5 rounded-xl bg-slate-200/80 hover:bg-slate-300 text-slate-700 font-bold text-xs self-start md:self-center transition-colors">
                Close Detail
            </button>
        </div>
    </div>

    <!-- Monthly Chronological Log Table -->
    <div class="rounded-2xl bg-white border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-slate-200/80 flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-900">Attendance Log History</h3>
                <p class="text-xs text-slate-500">Chronological records for {{ $student->full_name }} in {{ $monthLabel }}</p>
            </div>
            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                {{ $monthlyLogs->count() }} Records
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <th class="py-3 px-4">Session Date</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Check-In Time</th>
                        <th class="py-3 px-4">Tute / Learning Material</th>
                        <th class="py-3 px-4">Marked By</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($monthlyLogs as $log)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3 px-4 font-semibold text-slate-800">
                                <span class="font-mono">{{ \Carbon\Carbon::parse($log->date)->format('Y-m-d') }}</span>
                                <span class="text-slate-400 font-normal ml-1">({{ \Carbon\Carbon::parse($log->date)->format('l') }})</span>
                            </td>
                            <td class="py-3 px-4">
                                @if($log->status === 'present')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">
                                        <i class="fa-solid fa-check text-[9px]"></i> Present
                                    </span>
                                @elseif($log->status === 'late')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800">
                                        <i class="fa-solid fa-clock text-[9px]"></i> Late
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800">
                                        <i class="fa-solid fa-xmark text-[9px]"></i> Absent
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 font-mono font-semibold text-slate-700">
                                {{ \Carbon\Carbon::parse($log->check_in_time)->format('h:i:s A') }}
                            </td>
                            <td class="py-3 px-4">
                                @if($log->tute_provided)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                        <i class="fa-solid fa-book text-[9px]"></i>
                                        <span>{{ $log->tute_name ?: 'Provided' }}</span>
                                    </span>
                                @else
                                    <span class="text-slate-400">None</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-slate-600 font-medium">
                                {{ $log->markedBy?->name ?? 'System' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-500">
                                <i class="fa-regular fa-calendar-xmark text-2xl text-slate-300 mb-1 block"></i>
                                <span>No attendance records recorded for this student in {{ $monthLabel }}.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
