@extends('layouts.app')

@section('title', 'Attendance Calendar - ' . $student->full_name . ' (' . $classRoom->name . ')')
@section('page-title', 'Student Attendance Calendar')
@section('page-subtitle', $student->full_name . ' • ' . $classRoom->name . ' (' . $classRoom->grade_level . ')')

@section('content')
<div class="space-y-6">

    <!-- Top Action Breadcrumb & Print Controls -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 print:hidden">
        <a href="{{ route('teacher.classes.show', $classRoom) }}" 
           class="inline-flex items-center gap-2 text-xs font-bold text-slate-600 hover:text-indigo-600 transition-colors">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Back to {{ $classRoom->name }}</span>
        </a>

        <div class="flex items-center gap-2.5">
            <button onclick="window.print()" 
                    class="px-4 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold border border-slate-200/80 shadow-xs flex items-center gap-2 transition-all">
                <i class="fa-solid fa-print text-slate-500"></i>
                <span>Print Report</span>
            </button>

            <a href="{{ route('public.pass', $enrollment->qr_token) }}" target="_blank"
               class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md shadow-indigo-600/30 flex items-center gap-2 transition-all">
                <i class="fa-solid fa-id-card"></i>
                <span>Student QR Pass</span>
            </a>
        </div>
    </div>

    <!-- Calendar Card Partial -->
    @include('teacher.classes.partials.student-calendar-card')

</div>
@endsection

@push('styles')
<style>
@media print {
    body { background-color: #fff !important; }
    aside, header, footer, .print\:hidden { display: none !important; }
    .shadow-xs, .shadow-md, .shadow-lg { box-shadow: none !important; }
    .border { border-color: #e2e8f0 !important; }
}
</style>
@endpush
