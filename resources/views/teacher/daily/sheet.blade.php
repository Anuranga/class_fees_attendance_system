@extends('layouts.app')

@section('title', 'Daily Attendance & Cash Collection Sheet')
@section('page-title', 'Daily Attendance & Cash Ledger')
@section('page-subtitle', 'Real-time daily attendance register and cash collections for ' . \Carbon\Carbon::parse($selectedDate)->format('l, F d, Y'))

@section('content')
<div class="space-y-6" x-data="{ activeTab: 'attendance', collectModalOpen: false, modalStudentId: '', modalClassId: '', modalStudentName: '', modalAmount: '' }">

    <!-- Top Filter Bar -->
    <div class="rounded-2xl bg-white border border-slate-200/80 p-5 shadow-xs">
        <form action="{{ route('teacher.daily.sheet') }}" method="GET" class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-xs font-mono font-bold text-slate-500 uppercase tracking-wider">Session Ledger</span>
                    @if($isToday)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            Today's Live Records
                        </span>
                    @endif
                </div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900">
                    {{ \Carbon\Carbon::parse($selectedDate)->format('l, F d, Y') }}
                </h1>
            </div>

            <!-- Filters -->
            <div class="flex flex-wrap items-center gap-2.5">
                <!-- Class Filter -->
                <select name="class_id" class="px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                    <option value="">All My Classes ({{ $classes->count() }})</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ $selectedClassId == $c->id ? 'selected' : '' }}>
                            {{ $c->name }} ({{ $c->grade_level }})
                        </option>
                    @endforeach
                </select>

                <!-- Date Picker -->
                <div class="flex items-center gap-1">
                    <a href="{{ route('teacher.daily.sheet', ['date' => \Carbon\Carbon::parse($selectedDate)->subDay()->toDateString(), 'class_id' => $selectedClassId]) }}" 
                       class="p-2 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-600 text-xs transition-colors" 
                       title="Previous Day">
                        <i class="fa-solid fa-chevron-left"></i>
                    </a>
                    
                    <input type="date" name="date" value="{{ $selectedDate }}" 
                           class="px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                    
                    <a href="{{ route('teacher.daily.sheet', ['date' => \Carbon\Carbon::parse($selectedDate)->addDay()->toDateString(), 'class_id' => $selectedClassId]) }}" 
                       class="p-2 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-600 text-xs transition-colors" 
                       title="Next Day">
                        <i class="fa-solid fa-chevron-right"></i>
                    </a>
                </div>

                <button type="submit" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold shadow-xs transition-all">
                    Filter
                </button>

                @if(!$isToday)
                    <a href="{{ route('teacher.daily.sheet', ['class_id' => $selectedClassId]) }}" class="px-3 py-2 rounded-xl bg-indigo-50 text-indigo-700 hover:bg-indigo-100 text-xs font-bold border border-indigo-200">
                        Go to Today
                    </a>
                @endif

                <button type="button" onclick="window.print()" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-all flex items-center gap-1.5" title="Print Daily Sheet">
                    <i class="fa-solid fa-print"></i>
                    <span class="hidden sm:inline">Print</span>
                </button>
            </div>
        </form>

        <!-- KPI Metric Cards Grid -->
        <div class="mt-5 pt-5 border-t border-slate-100 grid grid-cols-2 sm:grid-cols-4 gap-3">
            <!-- Total Cash In Hand -->
            <div class="rounded-xl p-4 bg-gradient-to-br from-emerald-500/10 to-teal-500/10 border border-emerald-500/20">
                <span class="text-[10px] font-bold text-emerald-800 uppercase tracking-wider">Total Cash Collected</span>
                <p class="text-xl sm:text-2xl font-black text-emerald-700 mt-1">LKR {{ number_format($dailyCashTotal, 2) }}</p>
                <p class="text-[11px] text-emerald-600 mt-0.5 font-medium">{{ $transactionsCount }} payment receipt(s) today</p>
            </div>

            <!-- Total Attendance -->
            <div class="rounded-xl p-4 bg-gradient-to-br from-indigo-500/10 to-blue-500/10 border border-indigo-500/20">
                <span class="text-[10px] font-bold text-indigo-800 uppercase tracking-wider">Class Attendance</span>
                <p class="text-xl sm:text-2xl font-black text-indigo-700 mt-1">{{ $attendanceCount }}</p>
                <p class="text-[11px] text-indigo-600 mt-0.5 font-medium">Students present or late</p>
            </div>

            <!-- Tute Distribution -->
            <div class="rounded-xl p-4 bg-gradient-to-br from-purple-500/10 to-fuchsia-500/10 border border-purple-500/20">
                <span class="text-[10px] font-bold text-purple-800 uppercase tracking-wider">Tute Materials Issued</span>
                <p class="text-xl sm:text-2xl font-black text-purple-700 mt-1">{{ $tuteCount }}</p>
                <p class="text-[11px] text-purple-600 mt-0.5 font-medium">Handouts recorded today</p>
            </div>

            <!-- Receipts Count -->
            <div class="rounded-xl p-4 bg-gradient-to-br from-amber-500/10 to-orange-500/10 border border-amber-500/20">
                <span class="text-[10px] font-bold text-amber-800 uppercase tracking-wider">Cash Receipts</span>
                <p class="text-xl sm:text-2xl font-black text-amber-700 mt-1">{{ $transactionsCount }}</p>
                <p class="text-[11px] text-amber-600 mt-0.5 font-medium">Invoices generated</p>
            </div>
        </div>
    </div>

    <!-- Segmented Navigation Tabs: Attendance vs Cash Ledger -->
    <div class="flex items-center gap-2 border-b border-slate-200">
        <button type="button" @click="activeTab = 'attendance'" 
                :class="activeTab === 'attendance' ? 'border-indigo-600 text-indigo-600 font-extrabold' : 'border-transparent text-slate-500 hover:text-slate-800 font-semibold'"
                class="py-3 px-4 border-b-2 text-sm flex items-center gap-2 transition-all">
            <i class="fa-solid fa-clipboard-user text-base"></i>
            <span>Daily Attendance Register ({{ $attendances->count() }})</span>
        </button>

        <button type="button" @click="activeTab = 'cash'" 
                :class="activeTab === 'cash' ? 'border-emerald-600 text-emerald-600 font-extrabold' : 'border-transparent text-slate-500 hover:text-slate-800 font-semibold'"
                class="py-3 px-4 border-b-2 text-sm flex items-center gap-2 transition-all">
            <i class="fa-solid fa-money-bill-wave text-base"></i>
            <span>Daily Cash Collections (LKR {{ number_format($dailyCashTotal, 2) }})</span>
        </button>
    </div>

    <!-- Tab 1: Daily Attendance Register -->
    <div x-show="activeTab === 'attendance'" class="space-y-4">
        <div class="rounded-2xl bg-white border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-200/80 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Students Attended on {{ \Carbon\Carbon::parse($selectedDate)->format('M d, Y') }}</h2>
                    <p class="text-xs text-slate-500">Includes check-in time, tutorial material handover, and fees recorded on this day</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[700px]">
                    <thead class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="py-3 px-4">Student</th>
                            <th class="py-3 px-4">Class</th>
                            <th class="py-3 px-4">Check-In Time</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">Tute Handout</th>
                            <th class="py-3 px-4">Cash Paid on this Day</th>
                            <th class="py-3 px-4">Marked By</th>
                            <th class="py-3 px-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @forelse($attendances as $record)
                            @php
                                $studentPayments = $paymentsByStudent->get($record->student_id, collect());
                                $paidToday = $studentPayments->isNotEmpty();
                            @endphp
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <!-- Student -->
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $record->student->avatar_url }}" alt="{{ $record->student->full_name }}" 
                                             class="w-8 h-8 rounded-full object-cover border border-slate-200 shadow-xs shrink-0 bg-slate-100"
                                             onerror="this.onerror=null; this.src='{{ asset('images/avatars/' . ($record->student->gender === 'female' ? 'female.svg' : 'male.svg')) }}';">
                                        <div>
                                            <p class="font-bold text-slate-900">{{ $record->student->full_name }}</p>
                                            <p class="text-[11px] text-slate-400 font-mono">{{ $record->student->admission_number }}</p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Class -->
                                <td class="py-3 px-4 font-semibold text-slate-800">
                                    {{ $record->classRoom->name }}
                                    <span class="text-[10px] text-slate-400 block font-normal">{{ $record->classRoom->grade_level }}</span>
                                </td>

                                <!-- Time -->
                                <td class="py-3 px-4 font-mono text-slate-700">
                                    {{ \Carbon\Carbon::parse($record->check_in_time)->format('h:i:s A') }}
                                </td>

                                <!-- Status -->
                                <td class="py-3 px-4">
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $record->status === 'late' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">
                                        {{ ucfirst($record->status) }}
                                    </span>
                                </td>

                                <!-- Tute Material -->
                                <td class="py-3 px-4">
                                    <form action="{{ route('teacher.attendance.toggle-tute', [$record->classRoom, $record]) }}" method="POST" class="inline">
                                        @csrf
                                        @if($record->tute_provided)
                                            <button type="submit" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-emerald-100 hover:bg-emerald-200 text-emerald-800 transition-colors" title="Click to toggle tute status">
                                                <i class="fa-solid fa-file-circle-check text-emerald-600"></i>
                                                <span>Issued</span>
                                            </button>
                                        @else
                                            <button type="submit" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-slate-100 hover:bg-slate-200 text-slate-600 transition-colors" title="Click to mark tute as issued">
                                                <i class="fa-solid fa-file-circle-xmark text-slate-400"></i>
                                                <span>No Tute</span>
                                            </button>
                                        @endif
                                    </form>
                                </td>

                                <!-- Cash Paid on this Day -->
                                <td class="py-3 px-4">
                                    @if($paidToday)
                                        @foreach($studentPayments as $sp)
                                            <div class="flex items-center gap-1.5 mb-1 last:mb-0">
                                                <span class="px-2 py-0.5 rounded-md text-[11px] font-bold bg-emerald-100 text-emerald-800">
                                                    LKR {{ number_format($sp->amount, 2) }}
                                                </span>
                                                <span class="text-[10px] text-slate-500 font-mono">
                                                    {{ $sp->isDaily() ? 'Daily' : $sp->payment_month }}
                                                </span>
                                                <a href="{{ route('teacher.fees.receipt', $sp) }}" target="_blank" class="text-indigo-600 hover:text-indigo-800 text-[10px] font-mono underline" title="View Receipt">
                                                    {{ $sp->receipt_no }}
                                                </a>
                                            </div>
                                        @endforeach
                                    @else
                                        <button type="button" 
                                                @click="modalStudentId = '{{ $record->student_id }}'; modalClassId = '{{ $record->class_id }}'; modalStudentName = '{{ addslashes($record->student->full_name) }}'; modalAmount = '{{ $record->classRoom->monthly_fee }}'; collectModalOpen = true"
                                                class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 transition-colors flex items-center gap-1">
                                            <i class="fa-solid fa-plus text-[10px]"></i>
                                            <span>Collect Fee</span>
                                        </button>
                                    @endif
                                </td>

                                <!-- Marked By -->
                                <td class="py-3 px-4 text-slate-600">
                                    {{ $record->markedBy?->name ?? 'System' }}
                                </td>

                                <!-- Action -->
                                <td class="py-3 px-4 text-right">
                                    <form action="{{ route('teacher.attendance.manual', $record->classRoom) }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="student_id" value="{{ $record->student_id }}">
                                        <input type="hidden" name="date" value="{{ $selectedDate }}">
                                        <input type="hidden" name="status" value="absent">
                                        <button type="submit" class="text-xs text-rose-600 hover:text-rose-800 font-semibold p-1" title="Mark Absent / Remove from list">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-slate-400">
                                    <i class="fa-solid fa-calendar-xmark text-3xl text-slate-300 mb-2 block"></i>
                                    <span>No attendance marked for {{ \Carbon\Carbon::parse($selectedDate)->format('F d, Y') }}.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tab 2: Daily Cash Collections Ledger -->
    <div x-show="activeTab === 'cash'" class="space-y-4" style="display: none;">
        <div class="rounded-2xl bg-white border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-200/80 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Cash Collections on {{ \Carbon\Carbon::parse($selectedDate)->format('M d, Y') }}</h2>
                    <p class="text-xs text-slate-500">Itemized ledger of all cash payments received on this specific date</p>
                </div>

                <div class="text-right">
                    <span class="text-xs text-slate-400 uppercase tracking-wider font-bold">Total Daily Cash</span>
                    <p class="text-lg font-black text-emerald-600">LKR {{ number_format($dailyCashTotal, 2) }}</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[700px]">
                    <thead class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="py-3 px-4">Receipt #</th>
                            <th class="py-3 px-4">Student</th>
                            <th class="py-3 px-4">Class</th>
                            <th class="py-3 px-4">Payment Type & Billing Month</th>
                            <th class="py-3 px-4">Amount</th>
                            <th class="py-3 px-4">Method</th>
                            <th class="py-3 px-4">Collected By</th>
                            <th class="py-3 px-4 text-right">Receipt</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @forelse($payments as $payment)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="py-3 px-4 font-mono font-bold text-indigo-600">
                                    {{ $payment->receipt_no }}
                                </td>
                                <td class="py-3 px-4">
                                    <p class="font-bold text-slate-900">{{ $payment->student->full_name }}</p>
                                    <p class="text-[11px] text-slate-400 font-mono">{{ $payment->student->admission_number }}</p>
                                </td>
                                <td class="py-3 px-4 font-semibold text-slate-800">
                                    {{ $payment->classRoom->name }}
                                </td>
                                <td class="py-3 px-4">
                                    @if($payment->isDaily())
                                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-purple-100 text-purple-800">
                                            Daily Session Fee
                                        </span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-100 text-indigo-800">
                                            Monthly: {{ \Carbon\Carbon::createFromFormat('Y-m', $payment->payment_month)->format('F Y') }}
                                        </span>
                                    @endif
                                    @if($payment->notes)
                                        <p class="text-[11px] text-slate-400 italic mt-0.5">{{ $payment->notes }}</p>
                                    @endif
                                </td>
                                <td class="py-3 px-4 font-mono font-black text-emerald-700 text-sm">
                                    LKR {{ number_format($payment->amount, 2) }}
                                </td>
                                <td class="py-3 px-4 font-semibold text-slate-700 uppercase text-[11px]">
                                    {{ $payment->payment_method }}
                                </td>
                                <td class="py-3 px-4 text-slate-600 font-medium">
                                    {{ $payment->collector?->name ?? $payment->teacher?->name }}
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <a href="{{ route('teacher.fees.receipt', $payment) }}" target="_blank" 
                                       class="px-2.5 py-1 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-xs inline-flex items-center gap-1">
                                        <i class="fa-solid fa-receipt text-[10px]"></i>
                                        <span>View</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-slate-400">
                                    <i class="fa-solid fa-money-bill-wave text-3xl text-slate-300 mb-2 block"></i>
                                    <span>No cash payments recorded on {{ \Carbon\Carbon::parse($selectedDate)->format('F d, Y') }}.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($payments->isNotEmpty())
                        <tfoot class="bg-slate-50 font-bold text-slate-800 border-t border-slate-200">
                            <tr>
                                <td colspan="4" class="py-3 px-4 text-right uppercase text-xs">Total Cash in Hand Today:</td>
                                <td class="py-3 px-4 font-mono font-black text-emerald-700 text-base">LKR {{ number_format($dailyCashTotal, 2) }}</td>
                                <td colspan="3"></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <!-- Quick Modal to Record Cash Fee from Daily Sheet -->
    <div x-show="collectModalOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
         style="display: none;">
        
        <div @click.away="collectModalOpen = false" 
             class="w-full max-w-md bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden">
            
            <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Record Fee Payment</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Student: <span class="font-bold text-indigo-600" x-text="modalStudentName"></span></p>
                </div>
                <button @click="collectModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form action="{{ route('teacher.fees.collect') }}" method="POST" class="p-5 space-y-4">
                @csrf
                <input type="hidden" name="class_id" :value="modalClassId">
                <input type="hidden" name="student_id" :value="modalStudentId">
                <input type="hidden" name="paid_date" value="{{ $selectedDate }}">

                <!-- Payment Type Toggle -->
                <div x-data="{ pType: 'monthly' }">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Payment Period / Type</label>
                    <div class="grid grid-cols-2 gap-2 mb-3">
                        <button type="button" @click="pType = 'monthly'" 
                                :class="pType === 'monthly' ? 'bg-indigo-600 text-white font-bold' : 'bg-slate-100 text-slate-700 font-semibold'"
                                class="py-2 px-3 rounded-xl text-xs transition-all">
                            Monthly Fee
                        </button>
                        <button type="button" @click="pType = 'daily'" 
                                :class="pType === 'daily' ? 'bg-indigo-600 text-white font-bold' : 'bg-slate-100 text-slate-700 font-semibold'"
                                class="py-2 px-3 rounded-xl text-xs transition-all">
                            Daily Session Fee
                        </button>
                    </div>
                    <input type="hidden" name="payment_type" :value="pType">

                    <!-- Month Picker (for Monthly Fee) -->
                    <div x-show="pType === 'monthly'" class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-700">Select Billing Month (Current or Overdue)</label>
                        <select name="payment_month" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                            @php
                                $cMonth = \Carbon\Carbon::now();
                            @endphp
                            <option value="{{ $cMonth->format('Y-m') }}" selected>{{ $cMonth->format('F Y') }} (Current Month)</option>
                            <option value="{{ $cMonth->copy()->subMonth()->format('Y-m') }}">{{ $cMonth->copy()->subMonth()->format('F Y') }} (Last Month / Overdue)</option>
                            <option value="{{ $cMonth->copy()->subMonths(2)->format('Y-m') }}">{{ $cMonth->copy()->subMonths(2)->format('F Y') }}</option>
                            <option value="{{ $cMonth->copy()->subMonths(3)->format('Y-m') }}">{{ $cMonth->copy()->subMonths(3)->format('F Y') }}</option>
                        </select>
                    </div>

                    <div x-show="pType === 'daily'" class="text-xs text-slate-500 bg-slate-50 p-2.5 rounded-xl border border-slate-200">
                        Recording daily session cash collection for <strong>{{ \Carbon\Carbon::parse($selectedDate)->format('M d, Y') }}</strong>
                    </div>
                </div>

                <!-- Amount -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Amount (LKR)</label>
                    <input type="number" step="0.01" name="amount" x-model="modalAmount" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm font-bold text-slate-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                </div>

                <!-- Payment Method -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Payment Method</label>
                    <select name="payment_method" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800">
                        <option value="cash" selected>Cash Handover</option>
                        <option value="bank_transfer">Bank Transfer / Online</option>
                        <option value="card">Card / POS</option>
                    </select>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" @click="collectModalOpen = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-xs font-semibold hover:bg-slate-50">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow-md shadow-emerald-600/30">
                        Save Cash Receipt
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
