@extends('layouts.app')

@section('title', 'My Leaves & Time Off')
@section('page-title', 'Teacher Leaves & Applications (ගුරු නිවාඩු)')
@section('page-subtitle', 'Submit leave requests to the Principal, view review statuses, and track attendance history')

@section('content')
<div class="space-y-6" x-data="{ applyModal: false }">

    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-base font-bold text-slate-900">Leave Applications & History</h2>
            <p class="text-xs text-slate-500">All leave requests are reviewed and approved directly by your school Principal.</p>
        </div>

        <button @click="applyModal = true"
                class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md shadow-indigo-600/30 flex items-center justify-center gap-2 transition-all cursor-pointer">
            <i class="fa-solid fa-calendar-plus"></i>
            <span>Apply For Leave (නිවාඩු ඉල්ලුම් කරන්න)</span>
        </button>
    </div>

    <!-- Stat Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-hourglass-half"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase">Pending Requests</p>
                <p class="text-xl font-extrabold text-slate-900">{{ $pendingCount }}</p>
            </div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase">Approved This Year</p>
                <p class="text-xl font-extrabold text-emerald-600">{{ $totalApproved }}</p>
            </div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-calendar-days"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase">Total Applications</p>
                <p class="text-xl font-extrabold text-indigo-600">{{ $totalApplied }}</p>
            </div>
        </div>
    </div>

    <!-- Leave History Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Leave Applications</h3>
            <span class="text-xs text-slate-400">Total: {{ $leaves->total() }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200/80">
                    <tr>
                        <th class="px-4 py-3">Leave Type</th>
                        <th class="px-4 py-3">Dates</th>
                        <th class="px-4 py-3">Duration</th>
                        <th class="px-4 py-3">Reason / Details</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Principal Notes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($leaves as $leave)
                        @php
                            $start = \Carbon\Carbon::parse($leave->start_date);
                            $end = \Carbon\Carbon::parse($leave->end_date);
                            $days = $start->diffInDays($end) + 1;
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="px-4 py-3 font-bold text-slate-900">
                                <span class="px-2 py-0.5 rounded-md text-[11px] bg-indigo-50 text-indigo-700 border border-indigo-100">
                                    {{ $leave->leave_type }}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="font-medium text-slate-800">{{ $start->format('M d, Y') }}</div>
                                @if($leave->start_date !== $leave->end_date)
                                    <div class="text-[10px] text-slate-400">to {{ $end->format('M d, Y') }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="font-bold text-slate-700">{{ $days }} {{ Str::plural('Day', $days) }}</span>
                            </td>
                            <td class="px-4 py-3 max-w-xs truncate" title="{{ $leave->reason }}">
                                {{ $leave->reason }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if($leave->status === 'approved')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <i class="fa-solid fa-circle-check"></i> Approved
                                    </span>
                                @elseif($leave->status === 'rejected')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-50 text-rose-700 border border-rose-200">
                                        <i class="fa-solid fa-circle-xmark"></i> Rejected
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-50 text-amber-700 border border-amber-200">
                                        <i class="fa-solid fa-clock"></i> Pending Review
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-[11px] text-slate-500">
                                @if($leave->admin_notes)
                                    <span class="italic text-slate-700">"{{ $leave->admin_notes }}"</span>
                                    @if($leave->reviewer)
                                        <span class="block text-[9px] text-slate-400">— {{ $leave->reviewer->name }}</span>
                                    @endif
                                @else
                                    <span class="text-slate-300">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-400">
                                <i class="fa-solid fa-calendar-xmark text-2xl mb-2 text-slate-300 block"></i>
                                No leave applications submitted yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($leaves->hasPages())
            <div class="px-4 py-3 border-t border-slate-100">
                {{ $leaves->links() }}
            </div>
        @endif
    </div>

    <!-- Apply Leave Modal -->
    <div x-show="applyModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.outside="applyModal = false"
             class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 relative space-y-4">
            
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-calendar-plus text-indigo-600"></i>
                    <span>Apply For Leave (නිවාඩු අයදුම්පත)</span>
                </h3>
                <button @click="applyModal = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form action="{{ route('teacher.leaves.store') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Leave Type (නිවාඩු වර්ගය) *</label>
                    <select name="leave_type" required
                            class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        <option value="Casual">Casual Leave (අනියම් නිවාඩු)</option>
                        <option value="Medical">Medical / Sick Leave (වෛද්‍ය නිවාඩු)</option>
                        <option value="Annual">Annual / Vacation Leave (වාර්ෂික නිවාඩු)</option>
                        <option value="Duty">Duty Leave / Training (රාජකාරි නිවාඩු)</option>
                        <option value="Maternity">Maternity Leave (ප්‍රසූත නිවාඩු)</option>
                        <option value="Other">Other (වෙනත්)</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Start Date (ආරම්භක දිනය) *</label>
                        <input type="date" name="start_date" required value="{{ date('Y-m-d') }}"
                               class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">End Date (අවසන් දිනය) *</label>
                        <input type="date" name="end_date" required value="{{ date('Y-m-d') }}"
                               class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Reason / Explanation (හේතුව) *</label>
                    <textarea name="reason" rows="3" required placeholder="Please provide specific reason for leave request..."
                              class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="applyModal = false"
                            class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md shadow-indigo-600/30">
                        Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

