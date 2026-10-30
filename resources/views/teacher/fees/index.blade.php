@extends('layouts.app')

@section('title', 'Fee Collection & Cash Tracking')
@section('page-title', 'Cash Collection & Fee Tracking')
@section('page-subtitle', 'Track student fee statuses, record cash collections, and issue instant receipts')

@section('content')
<div class="space-y-6" x-data="{ collectModal: false, selectedStudentId: '', selectedClassId: '{{ $selectedClassId ?: ($classes->first()?->id ?? '') }}', selectedAmount: '{{ $classes->first()?->monthly_fee ?? 0 }}' }">

    <!-- Top Filter & Action Bar -->
    <div class="rounded-2xl bg-white border border-slate-200/80 p-6 shadow-xs flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <div>
            <h1 class="text-xl font-black text-slate-900">Billing Cycle: {{ \Carbon\Carbon::createFromFormat('Y-m', $selectedMonth)->format('F Y') }}</h1>
            <p class="text-xs text-slate-500 mt-1">Review student payment status or filter by specific classes and months</p>
        </div>

        <!-- Filter Controls -->
        <form action="{{ route('teacher.fees.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
            <!-- Class Dropdown -->
            <select name="class_id" class="px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                <option value="">All My Classes</option>
                @foreach($classes as $c)
                    <option value="{{ $c->id }}" {{ $selectedClassId == $c->id ? 'selected' : '' }}>
                        {{ $c->name }} ({{ $c->grade_level }})
                    </option>
                @endforeach
            </select>

            <!-- Month Picker -->
            <input type="month" name="month" value="{{ $selectedMonth }}" 
                   class="px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">

            <!-- Status Filter -->
            <select name="status" class="px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                <option value="all" {{ $selectedStatus === 'all' ? 'selected' : '' }}>All Statuses</option>
                <option value="paid" {{ $selectedStatus === 'paid' ? 'selected' : '' }}>Paid Only</option>
                <option value="pending" {{ $selectedStatus === 'pending' ? 'selected' : '' }}>Pending Only</option>
            </select>

            <button type="submit" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold shadow-xs transition-all">
                Filter
            </button>

            <!-- Quick Log Modal Trigger -->
            <button type="button" @click="collectModal = true"
                    class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow-md shadow-emerald-600/30 flex items-center gap-1.5 transition-all">
                <i class="fa-solid fa-plus"></i>
                <span>Log Cash Fee</span>
            </button>
        </form>
    </div>

    <!-- Summary Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="rounded-2xl bg-white p-5 border border-slate-200/80 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Expected</span>
            <p class="text-2xl font-black text-slate-900 mt-1">LKR {{ number_format($totalPotential, 2) }}</p>
            <p class="text-[11px] text-slate-500 mt-0.5">Based on enrolled active students</p>
        </div>

        <div class="rounded-2xl bg-white p-5 border border-slate-200/80 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-600">Collected Cash</span>
            <p class="text-2xl font-black text-emerald-700 mt-1">LKR {{ number_format($totalCollected, 2) }}</p>
            <p class="text-[11px] text-emerald-600 mt-0.5 font-semibold">{{ $paidCount }} Students Paid</p>
        </div>

        <div class="rounded-2xl bg-white p-5 border border-slate-200/80 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-amber-600">Outstanding Balance</span>
            <p class="text-2xl font-black text-amber-700 mt-1">LKR {{ number_format(max(0, $totalPotential - $totalCollected), 2) }}</p>
            <p class="text-[11px] text-amber-600 mt-0.5 font-semibold">{{ $pendingCount }} Students Pending</p>
        </div>

        <div class="rounded-2xl bg-white p-5 border border-slate-200/80 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-600">Collection Rate</span>
            @php
                $rate = $totalPotential > 0 ? round(($totalCollected / $totalPotential) * 100, 1) : 0;
            @endphp
            <p class="text-2xl font-black text-indigo-700 mt-1">{{ min(100, $rate) }}%</p>
            <div class="w-full bg-slate-100 rounded-full h-1.5 mt-2 overflow-hidden">
                <div class="bg-indigo-600 h-1.5 rounded-full" style="width: {{ min(100, $rate) }}%"></div>
            </div>
        </div>
    </div>

    <!-- Student Fee Status Roster -->
    <div class="rounded-2xl bg-white border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-200/80 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-900">Student Fee Roster ({{ $feeRecords->count() }})</h2>
                <p class="text-xs text-slate-500">Visual status indicators: Paid vs Pending for {{ $selectedMonth }}</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <th class="py-3 px-4">Student & Class UID</th>
                        <th class="py-3 px-4">Class</th>
                        <th class="py-3 px-4">Monthly Fee</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Receipt & Paid Date</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($feeRecords as $record)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <!-- Student -->
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $record->student->avatar_url }}" alt="{{ $record->student->full_name }}" 
                                         class="w-8 h-8 rounded-full object-cover border border-slate-200 shadow-xs shrink-0 bg-slate-100"
                                         onerror="this.onerror=null; this.src='{{ asset('images/avatars/' . ($record->student->gender === 'female' ? 'female.svg' : 'male.svg')) }}';">
                                    <div>
                                        <p class="font-bold text-slate-900">{{ $record->student->full_name }}</p>
                                        <p class="text-[11px] text-slate-400 font-mono">{{ $record->student->admission_number }} &bull; UID: <span class="text-indigo-600">{{ $record->enrollment->student_class_uid }}</span></p>
                                    </div>
                                </div>
                            </td>

                            <!-- Class -->
                            <td class="py-3 px-4 font-medium text-slate-700">
                                {{ $record->class->name }}
                                <span class="text-[10px] text-slate-400 block font-normal">{{ $record->class->grade_level }}</span>
                            </td>

                            <!-- Monthly Fee -->
                            <td class="py-3 px-4 font-mono font-bold text-slate-800">
                                LKR {{ number_format($record->monthly_fee, 2) }}
                            </td>

                            <!-- Visual Status Indicator (Paid or Pending) -->
                            <td class="py-3 px-4">
                                @if($record->is_paid)
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

                            <!-- Receipt Info -->
                            <td class="py-3 px-4">
                                @if($record->is_paid)
                                    <p class="font-mono font-semibold text-indigo-700 text-[11px]">{{ $record->payment->receipt_no }}</p>
                                    <p class="text-[10px] text-slate-400">{{ $record->payment->paid_date->format('M d, Y') }} &bull; Cash</p>
                                @else
                                    <span class="text-slate-400 italic text-[11px]">Uncollected</span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="py-3 px-4 text-right">
                                @if($record->is_paid)
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('teacher.fees.receipt', $record->payment) }}" 
                                           class="px-2.5 py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-xs border border-indigo-200 transition-colors flex items-center gap-1"
                                           title="Print Receipt or Send WhatsApp Notification">
                                            <i class="fa-solid fa-receipt"></i>
                                            <span>Receipt</span>
                                        </a>

                                        <form action="{{ route('teacher.fees.destroy', $record->payment) }}" method="POST"
                                              onsubmit="return confirm('Void/delete receipt {{ $record->payment->receipt_no }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50" title="Void Payment">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <button @click="collectModal = true; selectedClassId = '{{ $record->class->id }}'; selectedStudentId = '{{ $record->student->id }}'; selectedAmount = '{{ $record->monthly_fee }}'"
                                            class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-xs transition-colors inline-flex items-center gap-1">
                                        <i class="fa-solid fa-hand-holding-dollar"></i>
                                        <span>Log Cash</span>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-slate-500">
                                No records match the selected filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- SIMPLE FEE LOGGING MODAL -->
    <!-- Select Student -> Choose Billing Month (YYYY-MM) -> Enter Amount -> Save -->
    <div x-show="collectModal" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
         style="display: none;">
        
        <div @click.away="collectModal = false" 
             class="w-full max-w-lg bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden">
            
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Log Cash Fee Payment</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Select student, choose billing month, and record payment</p>
                </div>
                <button @click="collectModal = false" class="text-slate-400 hover:text-slate-600 p-1">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form action="{{ route('teacher.fees.collect') }}" method="POST" class="p-6 space-y-4">
                @csrf

                <!-- Select Class -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Academic Class *</label>
                    <select name="class_id" x-model="selectedClassId" required
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 text-slate-800 text-sm">
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}" data-fee="{{ $c->monthly_fee }}">
                                {{ $c->name }} ({{ $c->grade_level }}) - LKR {{ number_format($c->monthly_fee, 2) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Select Student -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Select Enrolled Student *</label>
                    <select name="student_id" x-model="selectedStudentId" required
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 text-slate-800 text-sm">
                        <option value="">-- Choose Student --</option>
                        @foreach($enrollments as $enr)
                            <option value="{{ $enr->student_id }}">
                                {{ $enr->student->full_name }} ({{ $enr->student_class_uid }} - {{ $enr->classRoom->name }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Billing Month (YYYY-MM) -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Billing Month (YYYY-MM) *</label>
                        <input type="month" name="payment_month" value="{{ $selectedMonth }}" required
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 text-slate-800 text-sm">
                    </div>

                    <!-- Amount -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Cash Amount (LKR) *</label>
                        <input type="number" step="0.01" min="0" name="amount" x-model="selectedAmount" required
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 text-slate-800 text-sm">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Payment Date</label>
                        <input type="date" name="paid_date" value="{{ date('Y-m-d') }}"
                               class="w-full px-4 py-2 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 text-slate-800 text-xs">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Payment Method</label>
                        <select name="payment_method" class="w-full px-4 py-2 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 text-slate-800 text-xs">
                            <option value="cash" selected>Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="online">Online</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Optional Notes / Remarks</label>
                    <input type="text" name="notes" placeholder="e.g. Paid in full at center"
                           class="w-full px-4 py-2 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 text-slate-800 text-xs">
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="collectModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow-md shadow-emerald-600/30 flex items-center gap-2">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span>Save & Generate Receipt</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
