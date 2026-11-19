@extends('layouts.app')

@section('title', 'Paper Results Graph - ' . $student->full_name)
@section('page-title', 'Student Paper Results & Graph')
@section('page-subtitle', $student->full_name . ' • ' . $classRoom->name . ' (' . $classRoom->grade_level . ')')

@section('content')
<div class="space-y-6">

    <!-- Top Breadcrumb & Print Controls -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 print:hidden">
        <div class="flex items-center gap-4">
            <a href="{{ route('teacher.papers.index', ['class_id' => $classRoom->id]) }}"
               class="inline-flex items-center gap-2 text-xs font-bold text-slate-600 hover:text-indigo-600 transition-colors">
                <i class="fa-solid fa-arrow-left"></i>
                <span>All Papers</span>
            </a>
            <span class="text-slate-300">|</span>
            <a href="{{ route('teacher.classes.show', $classRoom) }}"
               class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-600 hover:text-indigo-600 transition-colors">
                <i class="fa-solid fa-users"></i>
                <span>{{ $classRoom->name }}</span>
            </a>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" onclick="window.print()"
                    class="px-4 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold border border-slate-200 shadow-xs flex items-center gap-2 transition-all cursor-pointer">
                <i class="fa-solid fa-print text-slate-500"></i>
                <span>Print Progress Report</span>
            </button>
        </div>
    </div>

    <!-- Student Header & Fast Switcher -->
    <div class="p-5 rounded-2xl bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white shadow-md flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <img src="{{ $student->avatar_url }}" alt="{{ $student->full_name }}"
                 class="w-14 h-14 rounded-2xl object-cover border-2 border-indigo-400/40 shadow-md bg-slate-800 shrink-0"
                 onerror="this.onerror=null; this.src='{{ asset('images/avatars/' . ($student->gender === 'female' ? 'female.svg' : 'male.svg')) }}';">
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
                <p class="mt-1 text-xs text-slate-300">
                    <i class="fa-solid fa-graduation-cap text-indigo-400 mr-1"></i>{{ $classRoom->name }} ({{ $classRoom->grade_level }})
                </p>
            </div>
        </div>

        <!-- Switch Student Dropdown -->
        <div class="flex items-center gap-2 self-start md:self-center shrink-0 print:hidden">
            <label class="text-[11px] font-bold uppercase tracking-wider text-indigo-300 hidden sm:inline">Switch Student:</label>
            <select onchange="window.location.href = this.value"
                    class="bg-slate-800/90 hover:bg-slate-800 border border-indigo-400/30 text-xs font-semibold text-white rounded-xl px-3 py-2 focus:ring-2 focus:ring-indigo-400 focus:outline-hidden max-w-xs cursor-pointer">
                @foreach($allClassStudents as $cs)
                    <option value="{{ route('teacher.papers.student-graph', [$classRoom, $cs->student]) }}" {{ $cs->student_id == $student->id ? 'selected' : '' }}>
                        {{ $cs->student->full_name }} ({{ $cs->student_class_uid }})
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="rounded-2xl bg-white border border-slate-200/80 p-4 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Papers Completed</span>
            <p class="text-xl font-black text-slate-900 mt-1">{{ $completedCount }} <span class="text-xs font-semibold text-slate-400">/ {{ $papersCount }}</span></p>
            <p class="text-[11px] text-slate-500 mt-0.5">Assessments taken</p>
        </div>

        <div class="rounded-2xl bg-gradient-to-br from-indigo-50 to-blue-50 border border-indigo-200/70 p-4 shadow-xs">
            <span class="text-[10px] font-bold text-indigo-700 uppercase tracking-wider">Overall Average</span>
            <p class="text-xl font-black text-indigo-800 mt-1">{{ $overallAverage }}%</p>
            <p class="text-[11px] text-indigo-600 mt-0.5">Grade: {{ \App\Models\Paper::gradeFromPercentage($completedCount > 0 ? $overallAverage : null) }}</p>
        </div>

        <div class="rounded-2xl bg-gradient-to-br from-emerald-50 to-teal-50 border border-emerald-200/70 p-4 shadow-xs">
            <span class="text-[10px] font-bold text-emerald-700 uppercase tracking-wider">Highest Score</span>
            <p class="text-xl font-black text-emerald-800 mt-1">{{ $bestScore }}%</p>
            <p class="text-[11px] text-emerald-600 mt-0.5">Personal best</p>
        </div>

        <div class="rounded-2xl bg-gradient-to-br from-purple-50 to-fuchsia-50 border border-purple-200/70 p-4 shadow-xs">
            <span class="text-[10px] font-bold text-purple-700 uppercase tracking-wider">Latest Paper</span>
            <p class="text-xl font-black text-purple-800 mt-1">{{ $latestScore }}%</p>
            <p class="text-[11px] text-purple-600 mt-0.5">Most recent score</p>
        </div>
    </div>

    <!-- Student Progress Graph (Student % vs Class Average %) -->
    <div class="rounded-2xl bg-white border border-slate-200/80 p-5 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4">
            <div>
                <h3 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-chart-line text-indigo-600"></i>
                    <span>Student Paper Marks Progress Graph (%)</span>
                </h3>
                <p class="text-xs text-slate-500">Compares {{ $student->full_name }}'s percentage against the batch average across papers</p>
            </div>
        </div>

        @if(count($chartLabels) > 0)
            <div class="h-80 relative">
                <canvas id="studentProgressChart"></canvas>
            </div>
        @else
            <div class="h-56 rounded-xl bg-slate-50 border border-dashed border-slate-200 flex flex-col items-center justify-center text-slate-400 text-xs text-center p-6">
                <i class="fa-solid fa-chart-line text-3xl text-slate-300 mb-2"></i>
                <p class="font-semibold text-slate-600">No paper results recorded for this class yet.</p>
                <a href="{{ route('teacher.papers.index', ['class_id' => $classRoom->id]) }}"
                   class="mt-3 px-4 py-2 rounded-xl bg-indigo-600 text-white font-bold text-xs">
                    Create a Paper & Enter Marks
                </a>
            </div>
        @endif
    </div>

    <!-- Detailed Paper Results Table -->
    <div class="rounded-2xl bg-white border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-200/80">
            <h3 class="text-sm font-bold text-slate-900">Paper-by-Paper Results Breakdown</h3>
            <p class="text-xs text-slate-500">Full assessment record with marks, percentage, grade, and class rank</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[680px]">
                <thead class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="py-3 px-4">Paper Title</th>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4 text-center">Marks</th>
                        <th class="py-3 px-4 text-center">Percentage</th>
                        <th class="py-3 px-4 text-center">Grade</th>
                        <th class="py-3 px-4 text-center">Batch Avg</th>
                        <th class="py-3 px-4 text-center">Class Rank</th>
                        <th class="py-3 px-4">Remarks</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($results as $row)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3 px-4 font-bold text-slate-900">
                                <a href="{{ route('teacher.papers.show', $row['paper']) }}" class="hover:text-indigo-600 hover:underline">
                                    {{ $row['paper']->title }}
                                </a>
                            </td>
                            <td class="py-3 px-4 font-mono text-slate-600">
                                {{ $row['paper']->paper_date->format('M d, Y') }}
                            </td>
                            <td class="py-3 px-4 text-center font-mono font-bold text-slate-900">
                                @if($row['mark'] && $row['mark']->is_absent)
                                    <span class="text-rose-500">Absent</span>
                                @elseif($row['mark'] && $row['mark']->marks !== null)
                                    {{ number_format((float) $row['mark']->marks, 1) }} / {{ number_format((float) $row['paper']->max_marks, 0) }}
                                @else
                                    <span class="text-slate-400">Not graded</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center font-mono font-extrabold text-indigo-700">
                                {{ $row['percentage'] !== null ? $row['percentage'].'%' : '—' }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-extrabold {{ $row['grade'] === 'A' ? 'bg-emerald-100 text-emerald-800' : ($row['grade'] === 'B' ? 'bg-blue-100 text-blue-800' : ($row['grade'] === 'C' ? 'bg-indigo-100 text-indigo-800' : ($row['grade'] === 'S' ? 'bg-amber-100 text-amber-800' : ($row['grade'] === 'W' ? 'bg-rose-100 text-rose-800' : 'bg-slate-100 text-slate-600')))) }}">
                                    {{ $row['grade'] }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center font-mono text-slate-500">
                                {{ $row['class_avg_pct'] !== null ? $row['class_avg_pct'].'%' : '—' }}
                            </td>
                            <td class="py-3 px-4 text-center font-bold text-slate-700">
                                @if($row['rank'] !== null)
                                    #{{ $row['rank'] }} <span class="text-[10px] font-normal text-slate-400">/ {{ $row['participants'] }}</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="py-3 px-4 text-slate-600 italic">
                                {{ $row['mark']?->remarks ?: '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-10 text-center text-slate-400">
                                No papers have been created for {{ $classRoom->name }} yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@push('styles')
<style>
@media print {
    body { background-color: #fff !important; }
    aside, header, footer, .print\:hidden { display: none !important; }
    .shadow-xs, .shadow-md, .shadow-lg { box-shadow: none !important; }
}
</style>
@endpush

@push('scripts')
@if(count($chartLabels) > 0)
<script>
document.addEventListener('DOMContentLoaded', () => {
    const ctx = document.getElementById('studentProgressChart');
    if (!ctx || typeof window.Chart === 'undefined') return;

    new window.Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($chartLabels),
            datasets: [
                {
                    label: @json($student->full_name) + ' (%)',
                    data: @json($studentPercentages),
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79, 70, 229, 0.14)',
                    borderWidth: 3,
                    pointBackgroundColor: '#4f46e5',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 6,
                    tension: 0.3,
                    fill: true,
                    spanGaps: true,
                },
                {
                    label: 'Batch Average (%)',
                    data: @json($classAveragePercentages),
                    borderColor: '#10b981',
                    backgroundColor: 'transparent',
                    borderWidth: 2,
                    borderDash: [6, 4],
                    pointBackgroundColor: '#10b981',
                    pointRadius: 4,
                    tension: 0.3,
                    fill: false,
                    spanGaps: true,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: { callback: (val) => val + '%' },
                    grid: { color: 'rgba(148, 163, 184, 0.15)' }
                },
                x: {
                    grid: { display: false }
                }
            },
            plugins: {
                legend: { position: 'top' },
                tooltip: {
                    callbacks: {
                        label: (context) => `${context.dataset.label}: ${context.parsed.y}%`
                    }
                }
            }
        }
    });
});
</script>
@endif
@endpush
