@extends('layouts.app')

@section('title', 'Paper Marks & Performance Graphs')
@section('page-title', 'Paper Marks & Performance Graphs')
@section('page-subtitle', 'Record exam/paper marks for students and visualize class & individual performance graphs')

@section('content')
<div class="space-y-6" x-data="{ createModal: false }">

    <!-- Top Filter & Action Bar -->
    <div class="rounded-2xl bg-white border border-slate-200/80 p-5 shadow-xs flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-black text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-file-pen text-indigo-600"></i>
                <span>Class Papers & Assessments ({{ $papers->count() }})</span>
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">Create a paper, enter marks for your batch, and automatically generate performance graphs.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <form action="{{ route('teacher.papers.index') }}" method="GET" class="flex items-center gap-2">
                <select name="class_id" onchange="this.form.submit()"
                        class="px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                    <option value="">All Batches ({{ $classes->count() }})</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ $selectedClassId == $c->id ? 'selected' : '' }}>
                            {{ $c->name }} ({{ $c->grade_level }})
                        </option>
                    @endforeach
                </select>
            </form>

            <button type="button" @click="createModal = true"
                    class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md shadow-indigo-600/30 flex items-center gap-2 transition-all cursor-pointer">
                <i class="fa-solid fa-plus"></i>
                <span>New Paper / Exam</span>
            </button>
        </div>
    </div>

    <!-- Batch Average Trend Graph + Quick Student Graph Launcher -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Class Average Trend Graph -->
        <div class="lg:col-span-2 rounded-2xl bg-white border border-slate-200/80 p-5 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-chart-line text-indigo-600"></i>
                        <span>Batch Average Score Trend (%)</span>
                    </h3>
                    <p class="text-[11px] text-slate-500">Chronological class average percentage across graded papers</p>
                </div>
            </div>

            @if(count($trendLabels) > 0)
                <div class="h-64 relative">
                    <canvas id="batchTrendChart"></canvas>
                </div>
            @else
                <div class="h-56 rounded-xl bg-slate-50 border border-dashed border-slate-200 flex flex-col items-center justify-center text-slate-400 text-xs text-center p-6">
                    <i class="fa-solid fa-chart-area text-3xl text-slate-300 mb-2"></i>
                    <p class="font-semibold text-slate-600">No graded paper data yet</p>
                    <p class="mt-1">Create a paper and save student marks to generate the trend graph automatically.</p>
                </div>
            @endif
        </div>

        <!-- Student Individual Graph Quick Selector -->
        <div class="rounded-2xl bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 text-white p-5 shadow-md flex flex-col justify-between">
            <div>
                <div class="w-10 h-10 rounded-xl bg-indigo-500/20 border border-indigo-400/30 text-indigo-300 flex items-center justify-center text-lg mb-3">
                    <i class="fa-solid fa-user-graduate"></i>
                </div>
                <h3 class="text-base font-extrabold text-white">Student Progress Graph</h3>
                <p class="text-xs text-slate-300 mt-1 leading-relaxed">
                    Select any student to view their individual paper-by-paper marks graph compared against the batch average.
                </p>
            </div>

            <div class="mt-5 space-y-3" x-data="{ selectedTarget: '' }">
                <label class="block text-[11px] font-bold uppercase tracking-wider text-indigo-300">Choose Student</label>
                <select x-model="selectedTarget"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800/90 border border-indigo-400/30 text-xs font-semibold text-white focus:ring-2 focus:ring-indigo-400 focus:outline-hidden">
                    <option value="">-- Select a Student --</option>
                    @foreach($enrollments as $en)
                        <option value="{{ route('teacher.papers.student-graph', [$en->classRoom, $en->student]) }}">
                            {{ $en->student->full_name }} — {{ $en->classRoom->name }}
                        </option>
                    @endforeach
                </select>

                <button type="button"
                        @click="if (selectedTarget) window.location.href = selectedTarget"
                        :disabled="!selectedTarget"
                        class="w-full py-2.5 rounded-xl bg-indigo-500 hover:bg-indigo-400 disabled:opacity-40 disabled:cursor-not-allowed text-white font-bold text-xs shadow-lg shadow-indigo-500/30 flex items-center justify-center gap-2 transition-all cursor-pointer">
                    <i class="fa-solid fa-chart-line"></i>
                    <span>Generate Student Graph</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Papers List Table -->
    <div class="rounded-2xl bg-white border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-200/80 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Recorded Papers & Exams</h3>
                <p class="text-xs text-slate-500">Click "Enter Marks & Graph" to record marks or inspect student results charts</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[700px]">
                <thead class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="py-3 px-4">Paper Title</th>
                        <th class="py-3 px-4">Batch / Class</th>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4 text-center">Max Marks</th>
                        <th class="py-3 px-4 text-center">Graded</th>
                        <th class="py-3 px-4 text-center">Batch Average</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($papers as $paper)
                        @php
                            $gradedCount = $paper->marks->filter(fn ($m) => ! $m->is_absent && $m->marks !== null)->count();
                            $avgPct = $paper->averagePercentage();
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-4">
                                <p class="font-bold text-slate-900">{{ $paper->title }}</p>
                                @if($paper->notes)
                                    <p class="text-[11px] text-slate-400 truncate max-w-xs">{{ $paper->notes }}</p>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-slate-800">
                                {{ $paper->classRoom->name }}
                                <span class="text-[10px] text-slate-400 block font-normal">{{ $paper->classRoom->grade_level }}</span>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-600">
                                {{ $paper->paper_date->format('M d, Y') }}
                            </td>
                            <td class="py-3.5 px-4 text-center font-mono font-bold text-slate-700">
                                {{ number_format((float) $paper->max_marks, 0) }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $gradedCount > 0 ? 'bg-indigo-50 text-indigo-700' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $gradedCount }} student(s)
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($avgPct !== null)
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-extrabold {{ $avgPct >= 75 ? 'bg-emerald-100 text-emerald-800' : ($avgPct >= 55 ? 'bg-blue-100 text-blue-800' : ($avgPct >= 35 ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800')) }}">
                                        {{ $avgPct }}%
                                    </span>
                                @else
                                    <span class="text-slate-400 italic">Pending</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('teacher.papers.show', $paper) }}"
                                       class="px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-[11px] shadow-xs inline-flex items-center gap-1.5 transition-all">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                        <span>Enter Marks & Graph</span>
                                    </a>

                                    <form action="{{ route('teacher.papers.destroy', $paper) }}" method="POST"
                                          data-confirm-title="Delete Paper & Marks?"
                                          data-confirm-message="All recorded student marks and graph data for this paper will be permanently removed."
                                          data-confirm-item="{{ $paper->title }} ({{ $paper->classRoom->name }})"
                                          data-confirm-button="Delete Paper"
                                          class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer" title="Delete Paper">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <i class="fa-solid fa-file-circle-plus text-3xl text-slate-300 mb-2 block"></i>
                                <p class="font-semibold text-slate-600">No papers created yet.</p>
                                <p class="text-xs mt-1">Click <strong>"New Paper / Exam"</strong> to add your first paper and mark student results.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create New Paper Modal -->
    <div x-show="createModal"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
         style="display: none;">
        <div @click.outside="createModal = false"
             class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-file-circle-plus text-indigo-600"></i>
                    <span>Create New Paper / Assessment</span>
                </h3>
                <button type="button" @click="createModal = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form action="{{ route('teacher.papers.store') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Batch / Class *</label>
                    <select name="class_id" required
                            class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-hidden">
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}" {{ $selectedClassId == $c->id ? 'selected' : '' }}>
                                {{ $c->name }} ({{ $c->grade_level }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Paper Title *</label>
                    <input type="text" name="title" required placeholder="e.g. Paper 01 - Unit Test, Term 1 MCQ"
                           class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-hidden">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Paper Date *</label>
                        <input type="date" name="paper_date" value="{{ date('Y-m-d') }}" required
                               class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-hidden">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Maximum Marks *</label>
                        <input type="number" name="max_marks" value="100" min="1" max="1000" step="0.5" required
                               class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-hidden">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Notes (Optional)</label>
                    <textarea name="notes" rows="2" placeholder="Topic covered or exam instructions..."
                              class="w-full text-xs px-3.5 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-hidden"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                    <button type="button" @click="createModal = false"
                            class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-50 cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md shadow-indigo-600/30 cursor-pointer">
                        Create & Enter Marks
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@push('scripts')
@if(count($trendLabels) > 0)
<script>
document.addEventListener('DOMContentLoaded', () => {
    const ctx = document.getElementById('batchTrendChart');
    if (!ctx || typeof window.Chart === 'undefined') return;

    new window.Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($trendLabels),
            datasets: [{
                label: 'Batch Average (%)',
                data: @json($trendAverages),
                borderColor: '#4f46e5',
                backgroundColor: 'rgba(79, 70, 229, 0.12)',
                borderWidth: 3,
                pointBackgroundColor: '#4f46e5',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 5,
                tension: 0.35,
                fill: true,
            }]
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
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (context) => `Batch Average: ${context.parsed.y}%`
                    }
                }
            }
        }
    });
});
</script>
@endif
@endpush
