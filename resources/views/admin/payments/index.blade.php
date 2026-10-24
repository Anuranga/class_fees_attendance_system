@extends('layouts.app')

@section('title', 'Global Fee Payments')
@section('page-title', 'Global Cash Fee Ledger')
@section('page-subtitle', 'System-wide cash collection history across all teachers and classes')

@section('content')
<div class="space-y-6">

    <!-- Filters Bar -->
    <div class="rounded-2xl bg-white border border-slate-200/80 p-6 shadow-xs">
        <form action="{{ route('admin.payments.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
            
            <!-- Teacher Filter -->
            <select name="teacher_id" class="px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                <option value="">All Teachers</option>
                @foreach($teachers as $t)
                    <option value="{{ $t->id }}" {{ ($filters['teacher_id'] ?? '') == $t->id ? 'selected' : '' }}>
                        {{ $t->name }}
                    </option>
                @endforeach
            </select>

            <!-- Month Filter -->
            <input type="month" name="month" value="{{ $filters['month'] ?? '' }}" 
                   class="px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
                   placeholder="Billing Month">

            <!-- Search input -->
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" 
                   placeholder="Receipt # or Student Name..."
                   class="px-3.5 py-2 rounded-xl border border-slate-300 text-xs text-slate-700 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 flex-1 min-w-[200px]">

            <button type="submit" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold shadow-xs transition-all">
                Filter Records
            </button>

            @if(!empty($filters['teacher_id']) || !empty($filters['month']) || !empty($filters['search']))
                <a href="{{ route('admin.payments.index') }}" class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Payments Ledger Table -->
    <div class="rounded-2xl bg-white border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-200/80 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-900">Payment Transactions ({{ $payments->total() }})</h2>
                <p class="text-xs text-slate-500">Filtered Cash Inflow Total: <strong class="text-emerald-700 font-mono">LKR {{ number_format($totalCollected, 2) }}</strong></p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <th class="py-3 px-4">Receipt #</th>
                        <th class="py-3 px-4">Student</th>
                        <th class="py-3 px-4">Class & Teacher</th>
                        <th class="py-3 px-4">Billing Month</th>
                        <th class="py-3 px-4">Paid Date</th>
                        <th class="py-3 px-4 text-right">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($payments as $p)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-4 font-mono font-bold text-indigo-700">
                                {{ $p->receipt_no }}
                            </td>

                            <td class="py-3.5 px-4">
                                <span class="font-bold text-slate-900">{{ $p->student->full_name }}</span>
                                <span class="text-[11px] text-slate-400 font-mono block">{{ $p->student->admission_number }}</span>
                            </td>

                            <td class="py-3.5 px-4">
                                <span class="font-semibold text-slate-800">{{ $p->classRoom->name }}</span>
                                <span class="text-[11px] text-slate-400 block font-normal">Instructor: {{ $p->teacher->name }}</span>
                            </td>

                            <td class="py-3.5 px-4 font-mono font-semibold text-slate-700">
                                {{ $p->payment_month }}
                            </td>

                            <td class="py-3.5 px-4 text-slate-600 font-mono">
                                {{ $p->paid_date->format('M d, Y') }}
                            </td>

                            <td class="py-3.5 px-4 text-right font-mono font-extrabold text-emerald-700 text-sm">
                                LKR {{ number_format($p->amount, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-slate-500">No payment records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payments->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $payments->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
