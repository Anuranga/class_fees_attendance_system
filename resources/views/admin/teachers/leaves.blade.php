@extends('layouts.app')

@section('title', 'Teacher Leaves & Attendance')
@section('page-title', 'Staff Attendance & Leaves (ගුරු නිවාඩු සහ පැමිණීම)')
@section('page-subtitle', 'Review teacher leave applications, approve or decline requests, and inspect daily faculty check-ins.')

@section('content')
<div class="space-y-6" x-data="{ 
    noteModal: false, 
    actionUrl: '', 
    actionType: 'approve', 
    teacherName: '', 
    openModal(url, type, name) {
        this.actionUrl = url;
        this.actionType = type;
        this.teacherName = name;
        this.noteModal = true;
    }
}">

    <!-- Top Tabs: Leaves Review vs Daily Attendance -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200 pb-4">
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.leaves.index', ['tab' => 'leaves']) }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 {{ $tab !== 'attendance' ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-600/20' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                <i class="fa-solid fa-calendar-check"></i>
                <span>Leave Applications</span>
                <span class="px-1.5 py-0.5 rounded-md text-[10px] {{ $tab !== 'attendance' ? 'bg-indigo-700 text-white' : 'bg-slate-100 text-slate-600' }}">
                    {{ $counts['pending'] }} Pending
                </span>
            </a>

            <a href="{{ route('admin.leaves.index', ['tab' => 'attendance']) }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 {{ $tab === 'attendance' ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-600/20' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                <i class="fa-solid fa-clock-rotate-left"></i>
                <span>Today's Staff Check-ins</span>
                <span class="px-1.5 py-0.5 rounded-md text-[10px] {{ $tab === 'attendance' ? 'bg-indigo-700 text-white' : 'bg-slate-100 text-slate-600' }}">
                    {{ $teacherAttendances->count() }} Present
                </span>
            </a>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('universal.attendance.scan') }}" 
               class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow-sm shadow-emerald-600/20 flex items-center gap-2 transition-all">
                <i class="fa-solid fa-qrcode"></i>
                <span>Open Central Scanner</span>
            </a>
        </div>
    </div>

    @if($tab === 'attendance')
        <!-- TODAY'S TEACHER ATTENDANCE LOG -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Teacher Attendance Log for {{ $todayDate }}</h3>
                    <p class="text-[11px] text-slate-400">Scanned at central kiosk or manually recorded</p>
                </div>
                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    {{ $teacherAttendances->count() }} Faculty Members Checked In
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200/80">
                        <tr>
                            <th class="px-4 py-3">Faculty / Teacher</th>
                            <th class="px-4 py-3">Check-In Time</th>
                            <th class="px-4 py-3">Check-Out Time</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Marked By</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($teacherAttendances as $att)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="px-4 py-3 font-bold text-slate-900 flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-600 text-white flex items-center justify-center font-bold text-xs shrink-0">
                                        {{ strtoupper(substr($att->teacher->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-800">{{ $att->teacher->name }}</p>
                                        <p class="text-[10px] text-slate-400 font-mono">{{ $att->teacher->email }}</p>
                                    </div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap font-semibold text-emerald-700">
                                    <i class="fa-solid fa-arrow-right-to-bracket text-emerald-500 mr-1"></i>
                                    {{ $att->check_in_time ? \Carbon\Carbon::parse($att->check_in_time)->format('h:i A') : 'N/A' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap font-semibold text-slate-700">
                                    @if($att->check_out_time)
                                        <i class="fa-solid fa-arrow-right-from-bracket text-slate-400 mr-1"></i>
                                        {{ \Carbon\Carbon::parse($att->check_out_time)->format('h:i A') }}
                                    @else
                                        <span class="text-slate-400 italic">Still On Duty</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        {{ ucfirst($att->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-[11px] text-slate-500">
                                    {{ $att->markedBy?->name ?: 'Central QR Kiosk' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-10 text-center text-slate-400">
                                    <i class="fa-solid fa-user-clock text-3xl mb-2 text-slate-300 block"></i>
                                    No teacher check-ins recorded for today yet. Use the Central QR Scanner to mark faculty attendance.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <!-- LEAVE APPLICATIONS TAB -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1">
            <a href="{{ route('admin.leaves.index') }}" 
               class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-2 {{ empty($statusFilter) ? 'bg-indigo-600 text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                <span>All Requests</span>
                <span class="px-1.5 py-0.5 rounded-md text-[10px] {{ empty($statusFilter) ? 'bg-indigo-700 text-white' : 'bg-slate-100 text-slate-600' }}">{{ $counts['all'] }}</span>
            </a>

            <a href="{{ route('admin.leaves.index', ['status' => 'pending']) }}" 
               class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-2 {{ $statusFilter === 'pending' ? 'bg-amber-600 text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                <span>Pending Review</span>
                <span class="px-1.5 py-0.5 rounded-md text-[10px] {{ $statusFilter === 'pending' ? 'bg-amber-700 text-white' : ($counts['pending'] > 0 ? 'bg-amber-100 text-amber-800 font-extrabold' : 'bg-slate-100 text-slate-600') }}">{{ $counts['pending'] }}</span>
            </a>

            <a href="{{ route('admin.leaves.index', ['status' => 'approved']) }}" 
               class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-2 {{ $statusFilter === 'approved' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                <span>Approved</span>
                <span class="px-1.5 py-0.5 rounded-md text-[10px] {{ $statusFilter === 'approved' ? 'bg-emerald-700 text-white' : 'bg-slate-100 text-slate-600' }}">{{ $counts['approved'] }}</span>
            </a>

            <a href="{{ route('admin.leaves.index', ['status' => 'rejected']) }}" 
               class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-2 {{ $statusFilter === 'rejected' ? 'bg-rose-600 text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                <span>Rejected</span>
                <span class="px-1.5 py-0.5 rounded-md text-[10px] {{ $statusFilter === 'rejected' ? 'bg-rose-700 text-white' : 'bg-slate-100 text-slate-600' }}">{{ $counts['rejected'] }}</span>
            </a>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200/80">
                        <tr>
                            <th class="px-4 py-3">Teacher</th>
                            <th class="px-4 py-3">Leave Type</th>
                            <th class="px-4 py-3">Date Range</th>
                            <th class="px-4 py-3">Days</th>
                            <th class="px-4 py-3">Reason / Details</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right">Actions</th>
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
                                <td class="px-4 py-3 font-bold text-slate-900 flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-600 text-white flex items-center justify-center font-bold text-xs shrink-0">
                                        {{ strtoupper(substr($leave->teacher->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-800">{{ $leave->teacher->name }}</p>
                                        <p class="text-[10px] text-slate-400 font-mono">{{ $leave->teacher->email }}</p>
                                    </div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded-md text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                        {{ $leave->leave_type }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="font-medium text-slate-800">{{ $start->format('M d, Y') }}</div>
                                    @if($leave->start_date !== $leave->end_date)
                                        <div class="text-[10px] text-slate-400">to {{ $end->format('M d, Y') }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap font-bold text-slate-700">
                                    {{ $days }} {{ Str::plural('Day', $days) }}
                                </td>
                                <td class="px-4 py-3 max-w-xs text-slate-700">
                                    <p class="truncate" title="{{ $leave->reason }}">{{ $leave->reason }}</p>
                                    @if($leave->admin_notes)
                                        <p class="text-[10px] text-slate-400 italic mt-0.5">Note: "{{ $leave->admin_notes }}"</p>
                                    @endif
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
                                            <i class="fa-solid fa-clock"></i> Pending
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-right">
                                    @if($leave->status === 'pending')
                                        <div class="flex items-center justify-end gap-1.5">
                                            <button type="button" 
                                                    @click="openModal('{{ route('admin.leaves.approve', $leave) }}', 'approve', '{{ addslashes($leave->teacher->name) }}')"
                                                    class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-[11px] shadow-xs flex items-center gap-1">
                                                <i class="fa-solid fa-check"></i>
                                                <span>Approve</span>
                                            </button>
                                            <button type="button" 
                                                    @click="openModal('{{ route('admin.leaves.reject', $leave) }}', 'reject', '{{ addslashes($leave->teacher->name) }}')"
                                                    class="px-2.5 py-1 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 font-bold text-[11px] border border-rose-200 flex items-center gap-1">
                                                <i class="fa-solid fa-xmark"></i>
                                                <span>Reject</span>
                                            </button>
                                        </div>
                                    @else
                                        <span class="text-[11px] text-slate-400">Reviewed by {{ $leave->reviewer?->name ?: 'Principal' }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-10 text-center text-slate-400">
                                    <i class="fa-solid fa-inbox text-3xl mb-2 text-slate-300 block"></i>
                                    No leave applications found for the selected status.
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
    @endif

    <!-- Approve / Reject Confirmation Modal with Note -->
    <div x-show="noteModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.outside="noteModal = false"
             class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100 relative space-y-4">
            
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <template x-if="actionType === 'approve'">
                        <span class="text-emerald-600 flex items-center gap-1.5">
                            <i class="fa-solid fa-circle-check"></i> Approve Leave Request
                        </span>
                    </template>
                    <template x-if="actionType === 'reject'">
                        <span class="text-rose-600 flex items-center gap-1.5">
                            <i class="fa-solid fa-circle-xmark"></i> Reject Leave Request
                        </span>
                    </template>
                </h3>
                <button @click="noteModal = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <p class="text-xs text-slate-600">
                You are about to <strong x-text="actionType"></strong> leave for <span class="font-bold text-slate-900" x-text="teacherName"></span>.
            </p>

            <form :action="actionUrl" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Principal Note / Reason (Optional)</label>
                    <textarea name="admin_notes" rows="2" placeholder="e.g. Approved with relievers arranged, or Please reschedule..."
                              class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="noteModal = false"
                            class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                        Cancel
                    </button>
                    <button type="submit"
                            :class="actionType === 'approve' ? 'bg-emerald-600 hover:bg-emerald-500 shadow-emerald-600/30' : 'bg-rose-600 hover:bg-rose-500 shadow-rose-600/30'"
                            class="px-5 py-2 rounded-xl text-white text-xs font-bold shadow-md transition-all">
                        Confirm <span class="capitalize" x-text="actionType"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

