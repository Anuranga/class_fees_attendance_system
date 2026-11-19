@extends('layouts.app')

@section('title', $paper->title . ' - Marks & Graph')
@section('page-title', $paper->title)
@section('page-subtitle', $classRoom->name . ' (' . $classRoom->grade_level . ') • Paper Date: ' . $paper->paper_date->format('M d, Y') . ' • Max Marks: ' . number_format((float) $paper->max_marks, 0))

@section('content')
<div class="space-y-6">

    <!-- Top Breadcrumb & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 print:hidden">
        <a href="{{ route('teacher.papers.index', ['class_id' => $classRoom->id]) }}"
           class="inline-flex items-center gap-2 text-xs font-bold text-slate-600 hover:text-indigo-600 transition-colors">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Back to All Papers</span>
        </a>

        <div class="flex items-center gap-2">
            <button type="button" onclick="window.print()"
                    class="px-3.5 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 text-xs font-bold shadow-xs flex items-center gap-1.5 transition-all cursor-pointer">
                <i class="fa-solid fa-print text-slate-500"></i>
                <span>Print Results & Graph</span>
            </button>
        </div>
    </div>

    <!-- KPI Summary Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="rounded-2xl bg-white border border-slate-200/80 p-4 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Graded / Absent</span>
            <p class="text-xl font-black text-slate-900 mt-1">{{ $gradedCount }} <span class="text-xs font-bold text-rose-500">/ {{ $absentCount }} AB</span></p>
            <p class="text-[11px] text-slate-500 mt-0.5">Out of {{ $enrollments->count() }} enrolled</p>
        </div>

        <div class="rounded-2xl bg-gradient-to-br from-indigo-50 to-blue-50 border border-indigo-200/70 p-4 shadow-xs">
            <span class="text-[10px] font-bold text-indigo-700 uppercase tracking-wider">Class Average</span>
            <p class="text-xl font-black text-indigo-800 mt-1">{{ $avgMarks }} <span class="text-xs font-bold">({{ $avgPercent }}%)</span></p>
            <p class="text-[11px] text-indigo-600 mt-0.5">Out of {{ number_format((float) $paper->max_marks, 0) }} max marks</p>
        </div>

        <div class="rounded-2xl bg-gradient-to-br from-emerald-50 to-teal-50 border border-emerald-200/70 p-4 shadow-xs">
            <span class="text-[10px] font-bold text-emerald-700 uppercase tracking-wider">Highest Mark</span>
            <p class="text-xl font-black text-emerald-800 mt-1">{{ $highestMarks }}</p>
            <p class="text-[11px] text-emerald-600 mt-0.5">Top score in batch</p>
        </div>

        <div class="rounded-2xl bg-gradient-to-br from-amber-50 to-orange-50 border border-amber-200/70 p-4 shadow-xs">
            <span class="text-[10px] font-bold text-amber-700 uppercase tracking-wider">Lowest Mark</span>
            <p class="text-xl font-black text-amber-800 mt-1">{{ $lowestMarks }}</p>
            <p class="text-[11px] text-amber-600 mt-0.5">Among graded students</p>
        </div>
    </div>

    <!-- Performance Graphs Row -->
    @if($gradedCount > 0)
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Student Marks Bar Graph -->
            <div class="lg:col-span-2 rounded-2xl bg-white border border-slate-200/80 p-5 shadow-xs">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                            <i class="fa-solid fa-chart-column text-indigo-600"></i>
                            <span>Student Paper Marks Graph</span>
                        </h3>
                        <p class="text-[11px] text-slate-500">Ranked student scores compared against batch average ({{ $avgMarks }})</p>
                    </div>
                </div>
                <div class="h-72 relative">
                    <canvas id="paperMarksBarChart"></canvas>
                </div>
            </div>

            <!-- Grade Distribution Doughnut Chart -->
            <div class="rounded-2xl bg-white border border-slate-200/80 p-5 shadow-xs flex flex-col">
                <div class="mb-3">
                    <h3 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-chart-pie text-emerald-600"></i>
                        <span>Grade Distribution</span>
                    </h3>
                    <p class="text-[11px] text-slate-500">A (75+), B (65+), C (55+), S (35+), W (&lt;35)</p>
                </div>
                <div class="h-56 relative flex-1">
                    <canvas id="gradeDistributionChart"></canvas>
                </div>
                <div class="mt-3 pt-3 border-t border-slate-100 grid grid-cols-6 gap-1 text-center text-[11px] font-bold">
                    <div class="p-1 rounded bg-emerald-50 text-emerald-700">A: {{ $gradeDistribution['A'] }}</div>
                    <div class="p-1 rounded bg-blue-50 text-blue-700">B: {{ $gradeDistribution['B'] }}</div>
                    <div class="p-1 rounded bg-indigo-50 text-indigo-700">C: {{ $gradeDistribution['C'] }}</div>
                    <div class="p-1 rounded bg-amber-50 text-amber-700">S: {{ $gradeDistribution['S'] }}</div>
                    <div class="p-1 rounded bg-rose-50 text-rose-700">W: {{ $gradeDistribution['W'] }}</div>
                    <div class="p-1 rounded bg-slate-100 text-slate-600">AB: {{ $gradeDistribution['AB'] }}</div>
                </div>
            </div>
        </div>
    @else
        <div class="rounded-2xl bg-indigo-50/50 border border-dashed border-indigo-200 p-6 text-center text-xs text-indigo-700">
            <i class="fa-solid fa-chart-simple text-2xl text-indigo-400 mb-2 block"></i>
            <p class="font-bold">Enter student marks below and click "Save All Marks" to generate the Bar Chart & Grade Distribution Graph!</p>
        </div>
    @endif

    <!-- Bulk Mark Entry Table -->
    <div class="rounded-2xl bg-white border border-slate-200/80 shadow-xs overflow-hidden">
        <form action="{{ route('teacher.papers.save-marks', $paper) }}" method="POST">
            @csrf

            <div class="p-4 border-b border-slate-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/60">
                <div>
                    <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-pen-ruler text-indigo-600"></i>
                        <span>Mark Paper Results — {{ $classRoom->name }}</span>
                    </h2>
                    <p class="text-xs text-slate-500">Enter marks out of <strong>{{ number_format((float) $paper->max_marks, 0) }}</strong> or tick Absent, then save all at once.</p>
                </div>

                <button type="submit"
                        class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow-md shadow-emerald-600/30 flex items-center gap-2 transition-all cursor-pointer print:hidden">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>Save All Marks & Update Graph</span>
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[760px]">
                    <thead class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="py-3 px-4">#</th>
                            <th class="py-3 px-4">Student</th>
                            <th class="py-3 px-4 w-36">Marks (Max {{ number_format((float) $paper->max_marks, 0) }})</th>
                            <th class="py-3 px-4 text-center w-24">Absent</th>
                            <th class="py-3 px-4 text-center w-28">Result</th>
                            <th class="py-3 px-4">Remarks (Optional)</th>
                            <th class="py-3 px-4 text-right print:hidden">Progress Graph</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @forelse($enrollments as $index => $enrollment)
                            @php
                                $existing = $marksByStudent->get($enrollment->student_id);
                                $markVal = old("entries.{$index}.marks", $existing?->marks);
                                $isAbs = (bool) old("entries.{$index}.is_absent", $existing?->is_absent ?? false);
                                $remarksVal = old("entries.{$index}.remarks", $existing?->remarks);
                                $pct = ($existing && ! $existing->is_absent && $existing->marks !== null && (float) $paper->max_marks > 0)
                                    ? round(((float) $existing->marks / (float) $paper->max_marks) * 100, 1)
                                    : null;
                                $grade = $existing ? \App\Models\Paper::gradeFromPercentage($pct) : '—';
                            @endphp
                            <tr class="hover:bg-slate-50/60 transition-colors" x-data="{ absent: {{ $isAbs ? 'true' : 'false' }} }">
                                <td class="py-3 px-4 text-slate-400 font-mono">{{ $index + 1 }}</td>
                                <td class="py-3 px-4">
                                    <input type="hidden" name="entries[{{ $index }}][student_id]" value="{{ $enrollment->student_id }}">
                                    <div class="flex items-center gap-2.5">
                                        <img src="{{ $enrollment->student->avatar_url }}" alt="{{ $enrollment->student->full_name }}"
                                             class="w-8 h-8 rounded-full object-cover border border-slate-200 shrink-0 bg-slate-100"
                                             onerror="this.onerror=null; this.src='{{ asset('images/avatars/' . ($enrollment->student->gender === 'female' ? 'female.svg' : 'male.svg')) }}';">
                                        <div>
                                            <p class="font-bold text-slate-900">{{ $enrollment->student->full_name }}</p>
                                            <p class="text-[10px] text-slate-400 font-mono">{{ $enrollment->student->admission_number }} • {{ $enrollment->student_class_uid }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <input type="number"
                                           name="entries[{{ $index }}][marks]"
                                           value="{{ $isAbs ? '' : ($markVal !== null ? (float) $markVal : '') }}"
                                           min="0"
                                           max="{{ (float) $paper->max_marks }}"
                                           step="0.5"
                                           :disabled="absent"
                                           placeholder="0 - {{ number_format((float) $paper->max_marks, 0) }}"
                                           class="w-28 px-3 py-1.5 rounded-lg border border-slate-300 text-xs font-mono font-bold text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 disabled:bg-slate-100 disabled:text-slate-400">
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <input type="checkbox"
                                           name="entries[{{ $index }}][is_absent]"
                                           value="1"
                                           x-model="absent"
                                           class="w-4 h-4 rounded border-slate-300 text-rose-600 focus:ring-rose-500/30 cursor-pointer">
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if($existing && $existing->is_absent)
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-600">AB</span>
                                    @elseif($pct !== null)
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-extrabold {{ $grade === 'A' ? 'bg-emerald-100 text-emerald-800' : ($grade === 'B' ? 'bg-blue-100 text-blue-800' : ($grade === 'C' ? 'bg-indigo-100 text-indigo-800' : ($grade === 'S' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800'))) }}">
                                            {{ $pct }}% ({{ $grade }})
                                        </span>
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <input type="text"
                                           name="entries[{{ $index }}][remarks]"
                                           value="{{ $remarksVal }}"
                                           placeholder="Optional comment..."
                                           class="w-full px-3 py-1.5 rounded-lg border border-slate-200 text-xs text-slate-700 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                                </td>
                                <td class="py-3 px-4 text-right print:hidden">
                                    <a href="{{ route('teacher.papers.student-graph', [$classRoom, $enrollment->student]) }}"
                                       class="px-2.5 py-1 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 font-bold text-[11px] inline-flex items-center gap-1 transition-colors"
                                       title="View {{ $enrollment->student->full_name }}'s Progress Graph">
                                        <i class="fa-solid fa-chart-line text-[10px]"></i>
                                        <span>Graph</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-10 text-center text-slate-400">
                                    No active students enrolled in this class yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($enrollments->isNotEmpty())
                <div class="p-4 border-t border-slate-200/80 bg-slate-50/50 flex justify-end print:hidden">
                    <button type="submit"
                            class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow-md shadow-emerald-600/30 flex items-center gap-2 transition-all cursor-pointer">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span>Save All Marks & Update Graph</span>
                    </button>
                </div>
            @endif
        </form>
    </div>

</div>
@endsection

@push('scripts')
@if($gradedCount > 0)
<script>
document.addEventListener('DOMContentLoaded', () => {
    if (typeof window.Chart === 'undefined') return;

    const studentNames = @json($chartStudentNames);
    const studentScores = @json($chartStudentScores);
    const maxMarks = {{ (float) $paper->max_marks }};
    const avgMarks = {{ (float) $avgMarks }};

    // 1. Bar Chart of Student Marks vs Class Average
    const barCtx = document.getElementById('paperMarksBarChart');
    if (barCtx) {
        new window.Chart(barCtx, {
            type: 'bar',
            data: {
                labels: studentNames,
                datasets: [
                    {
                        label: 'Student Marks',
                        data: studentScores,
                        backgroundColor: studentScores.map(s => {
                            const pct = maxMarks > 0 ? (s / maxMarks) * 100 : 0;
                            if (pct >= 75) return 'rgba(16, 185, 129, 0.85)';
                            if (pct >= 55) return 'rgba(79, 70, 229, 0.85)';
                            if (pct >= 35) return 'rgba(245, 158, 11, 0.85)';
                            return 'rgba(244, 63, 94, 0.85)';
                        }),
                        borderRadius: 6,
                    },
                    {
                        label: 'Batch Average',
                        type: 'line',
                        data: studentNames.map(() => avgMarks),
                        borderColor: '#0f172a',
                        borderWidth: 2,
                        borderDash: [6, 4],
                        pointRadius: 0,
                        fill: false,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: maxMarks,
                        grid: { color: 'rgba(148, 163, 184, 0.15)' }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    }

    // 2. Grade Distribution Doughnut Chart
    const pieCtx = document.getElementById('gradeDistributionChart');
    if (pieCtx) {
        new window.Chart(pieCtx, {
            type: 'doughnut',
            data: {
                labels: ['A (75+)', 'B (65-74)', 'C (55-64)', 'S (35-54)', 'W (<35)', 'Absent'],
                datasets: [{
                    data: [
                        {{ $gradeDistribution['A'] }},
                        {{ $gradeDistribution['B'] }},
                        {{ $gradeDistribution['C'] }},
                        {{ $gradeDistribution['S'] }},
                        {{ $gradeDistribution['W'] }},
                        {{ $gradeDistribution['AB'] }}
                    ],
                    backgroundColor: ['#10b981', '#3b82f6', '#6366f1', '#f59e0b', '#f43f5e', '#94a3b8'],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '62%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 10, font: { size: 10 } }
                    }
                }
            }
        });
    }
});
</script>
@endif
@endpush
