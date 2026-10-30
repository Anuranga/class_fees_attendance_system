@extends('layouts.app')

@section('title', 'Receipt #' . $payment->receipt_no)
@section('page-title', 'Payment Receipt')
@section('page-subtitle', 'Receipt No: ' . $payment->receipt_no)

@section('content')
<div class="max-w-2xl mx-auto space-y-6" x-data="{ copiedMsg: false }">

    <!-- Action Toolbar (Hidden during Print) -->
    <div class="no-print flex flex-wrap items-center justify-between gap-4 bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
        <a href="{{ route('teacher.fees.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-slate-600 hover:text-slate-900">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Back to Fee Ledger</span>
        </a>

        <div class="flex items-center gap-2.5">
            <!-- WhatsApp Dispatch -->
            <a href="{{ $whatsAppUrl }}" target="_blank"
               class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow-md shadow-emerald-600/30 flex items-center gap-1.5 transition-all">
                <i class="fa-brands fa-whatsapp text-sm"></i>
                <span>Send WhatsApp Receipt</span>
            </a>

            <!-- Copy SMS Text -->
            <button @click="navigator.clipboard.writeText(`{{ addslashes($messageText) }}`); copiedMsg = true; setTimeout(() => copiedMsg = false, 2500)"
                    class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold border border-slate-200 flex items-center gap-1.5 transition-all">
                <i :class="copiedMsg ? 'fa-solid fa-check text-emerald-600' : 'fa-solid fa-copy'"></i>
                <span x-text="copiedMsg ? 'Text Copied!' : 'Copy Text'"></span>
            </button>

            <!-- Print Receipt -->
            <button onclick="window.print()" 
                    class="px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md shadow-indigo-600/30 flex items-center gap-1.5 transition-all">
                <i class="fa-solid fa-print"></i>
                <span>Print Receipt</span>
            </button>
        </div>
    </div>

    <!-- Official Printable Receipt Card -->
    <div class="receipt-print-area bg-white border border-slate-200/90 rounded-2xl p-8 sm:p-10 shadow-lg relative overflow-hidden">
        
        <!-- Header Strip -->
        <div class="border-b-2 border-dashed border-slate-200 pb-6 mb-6">
            <div class="flex items-start justify-between">
                <div>
                    <div class="flex items-center gap-2.5 mb-1.5">
                        <div class="w-8 h-8 rounded-lg gradient-brand flex items-center justify-center text-white text-sm font-bold shadow-xs">
                            <i class="fa-solid fa-graduation-cap"></i>
                        </div>
                        <span class="text-lg font-black text-slate-900 tracking-tight">EduTrack Academy</span>
                    </div>
                    <p class="text-xs text-slate-500">Official Cash Payment Acknowledgement</p>
                </div>

                <div class="text-right">
                    <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        PAID - CASH
                    </span>
                    <p class="text-xs font-mono font-bold text-slate-800 mt-1">{{ $payment->receipt_no }}</p>
                </div>
            </div>
        </div>

        <!-- Meta Details -->
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-6 text-xs">
            <div>
                <span class="text-slate-400 block font-semibold uppercase text-[10px]">Payment Date</span>
                <span class="font-bold text-slate-800 font-mono">{{ $payment->paid_date->format('M d, Y') }}</span>
            </div>
            <div>
                <span class="text-slate-400 block font-semibold uppercase text-[10px]">Billing Month</span>
                <span class="font-bold text-indigo-700 font-mono">{{ \Carbon\Carbon::createFromFormat('Y-m', $payment->payment_month)->format('F Y') }}</span>
            </div>
            <div>
                <span class="text-slate-400 block font-semibold uppercase text-[10px]">Payment Method</span>
                <span class="font-bold text-slate-800 uppercase">{{ $payment->payment_method }}</span>
            </div>
        </div>

        <!-- Student & Class Box -->
        <div class="rounded-xl bg-slate-50 p-4 border border-slate-100 mb-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div class="flex items-start gap-3">
                    <img src="{{ $payment->student->avatar_url }}" alt="{{ $payment->student->full_name }}" 
                         class="w-12 h-12 rounded-xl object-cover border border-slate-200 bg-white shadow-xs shrink-0"
                         onerror="this.onerror=null; this.src='{{ asset('images/avatars/' . ($payment->student->gender === 'female' ? 'female.svg' : 'male.svg')) }}';">
                    <div>
                        <span class="text-slate-400 block text-[10px] font-bold uppercase">Student Details</span>
                        <p class="text-sm font-black text-slate-900">{{ $payment->student->full_name }}</p>
                        <p class="text-slate-500 font-mono text-[11px]">Admission No: {{ $payment->student->admission_number }}</p>
                        <p class="text-slate-500 text-[11px]">Guardian: {{ $payment->student->guardian_name }} ({{ $payment->student->guardian_phone }})</p>
                    </div>
                </div>

                <div>
                    <span class="text-slate-400 block text-[10px] font-bold uppercase">Class & Instructor</span>
                    <p class="text-sm font-bold text-slate-900">{{ $payment->classRoom->name }}</p>
                    <p class="text-slate-500 text-[11px]">{{ $payment->classRoom->grade_level }}</p>
                    <p class="text-slate-600 text-[11px]">Teacher: <strong>{{ $payment->classRoom->teacher->name }}</strong></p>
                </div>
            </div>
        </div>

        <!-- Financial Breakdown Table -->
        <table class="w-full text-left text-xs mb-6">
            <thead>
                <tr class="border-b border-slate-200 text-slate-400 font-bold uppercase text-[10px]">
                    <th class="py-2">Description</th>
                    <th class="py-2 text-right">Amount (LKR)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <tr>
                    <td class="py-3">
                        <span class="font-bold text-slate-800">Monthly Tuition Fee</span>
                        <p class="text-[11px] text-slate-500">Period: {{ \Carbon\Carbon::createFromFormat('Y-m', $payment->payment_month)->format('F Y') }}</p>
                        @if($payment->notes)
                            <p class="text-[11px] text-slate-400 italic">Notes: {{ $payment->notes }}</p>
                        @endif
                    </td>
                    <td class="py-3 text-right font-mono font-bold text-slate-900">
                        {{ number_format($payment->amount, 2) }}
                    </td>
                </tr>
            </tbody>
            <tfoot>
                <tr class="border-t-2 border-slate-900">
                    <td class="pt-3 font-black text-sm text-slate-900">Total Cash Received:</td>
                    <td class="pt-3 text-right font-black text-base text-emerald-700 font-mono">
                        LKR {{ number_format($payment->amount, 2) }}
                    </td>
                </tr>
            </tfoot>
        </table>

        <!-- Signatures & Verification -->
        <div class="pt-6 border-t border-dashed border-slate-200 grid grid-cols-2 gap-8 text-[11px] text-slate-500">
            <div>
                <p class="text-slate-400 mb-8">Authorized Instructor Signature:</p>
                <div class="border-t border-slate-300 pt-1 font-semibold text-slate-700">
                    {{ $payment->classRoom->teacher->name }}
                </div>
            </div>

            <div class="text-right">
                <p class="text-slate-400 mb-8">Official Stamp / Date:</p>
                <div class="border-t border-slate-300 pt-1 font-mono text-slate-700">
                    {{ date('Y-m-d') }}
                </div>
            </div>
        </div>

        <div class="mt-6 text-center text-[10px] text-slate-400">
            Thank you for your prompt fee payment. Please retain this official receipt for your records.
        </div>
    </div>

</div>
@endsection
