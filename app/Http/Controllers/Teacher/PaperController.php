<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassRoom;
use App\Models\ClassStudent;
use App\Models\Paper;
use App\Models\PaperMark;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PaperController extends Controller
{
    /**
     * List all papers across accessible batches with a batch trend chart.
     */
    public function index(Request $request): View
    {
        $classes = Auth::user()->accessibleClassesQuery()->get();
        $classIds = $classes->pluck('id')->toArray();

        $selectedClassId = $request->query('class_id');
        if ($selectedClassId && in_array((int) $selectedClassId, $classIds, true)) {
            $filteredClassIds = [(int) $selectedClassId];
        } elseif ($selectedClassId) {
            abort(403, 'Unauthorized. You do not have access to this class.');
        } else {
            $filteredClassIds = $classIds;
        }

        $papers = Paper::with(['classRoom', 'marks'])
            ->whereIn('class_id', $filteredClassIds)
            ->orderByDesc('paper_date')
            ->orderByDesc('id')
            ->get();

        // Chronological trend chart for class average percentages across papers
        $chronologicalPapers = $papers->sortBy([
            ['paper_date', 'asc'],
            ['id', 'asc'],
        ])->values();

        $trendLabels = [];
        $trendAverages = [];
        foreach ($chronologicalPapers as $paper) {
            $avg = $paper->averagePercentage();
            if ($avg !== null) {
                $trendLabels[] = $paper->title.' ('.$paper->paper_date->format('M d').')';
                $trendAverages[] = $avg;
            }
        }

        // Enrolled students for quick jump to individual student progress graph
        $enrollments = ClassStudent::with(['student', 'classRoom'])
            ->whereIn('class_id', $filteredClassIds)
            ->where('status', 'active')
            ->get()
            ->sortBy(fn (ClassStudent $e): string => strtolower($e->student->full_name))
            ->values();

        return view('teacher.papers.index', [
            'classes' => $classes,
            'selectedClassId' => $selectedClassId,
            'papers' => $papers,
            'enrollments' => $enrollments,
            'trendLabels' => $trendLabels,
            'trendAverages' => $trendAverages,
        ]);
    }

    /**
     * Create a new paper/test for a class/batch and redirect to mark entry.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'class_id' => ['required', 'exists:classes,id'],
            'title' => ['required', 'string', 'max:255'],
            'paper_date' => ['required', 'date'],
            'max_marks' => ['required', 'numeric', 'min:1', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $class = ClassRoom::findOrFail($validated['class_id']);
        $this->authorizeTeacher($class);

        $paper = Paper::create([
            'class_id' => $class->id,
            'teacher_id' => Auth::user()->effectiveTeacherId(),
            'title' => trim($validated['title']),
            'paper_date' => $validated['paper_date'],
            'max_marks' => $validated['max_marks'],
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()
            ->route('teacher.papers.show', $paper)
            ->with('success', "Paper '{$paper->title}' created! Enter student marks below.");
    }

    /**
     * Show bulk mark entry table and paper performance graphs for a specific paper.
     */
    public function show(Paper $paper): View
    {
        $paper->load(['classRoom', 'marks.student']);
        $this->authorizeTeacher($paper->classRoom);

        $enrollments = ClassStudent::with('student')
            ->where('class_id', $paper->class_id)
            ->where('status', 'active')
            ->get()
            ->sortBy(fn (ClassStudent $e): string => strtolower($e->student->full_name))
            ->values();

        $marksByStudent = $paper->marks->keyBy('student_id');

        // Statistics & Graph Data
        $gradedMarks = $paper->marks->filter(fn (PaperMark $m): bool => ! $m->is_absent && $m->marks !== null);
        $gradedCount = $gradedMarks->count();
        $absentCount = $paper->marks->where('is_absent', true)->count();
        $maxMarks = (float) $paper->max_marks;

        $avgMarks = $gradedCount > 0 ? round((float) $gradedMarks->avg('marks'), 1) : 0;
        $avgPercent = ($gradedCount > 0 && $maxMarks > 0) ? round(($avgMarks / $maxMarks) * 100, 1) : 0;
        $highestMarks = $gradedCount > 0 ? (float) $gradedMarks->max('marks') : 0;
        $lowestMarks = $gradedCount > 0 ? (float) $gradedMarks->min('marks') : 0;

        // Grade distribution counts (A, B, C, S, W, AB)
        $gradeDistribution = ['A' => 0, 'B' => 0, 'C' => 0, 'S' => 0, 'W' => 0, 'AB' => $absentCount];
        foreach ($gradedMarks as $mark) {
            $pct = $maxMarks > 0 ? round(((float) $mark->marks / $maxMarks) * 100, 1) : 0;
            $grade = Paper::gradeFromPercentage($pct);
            if (isset($gradeDistribution[$grade])) {
                $gradeDistribution[$grade]++;
            }
        }

        // Student bar chart data (sorted highest to lowest marks)
        $rankedMarks = $gradedMarks->sortByDesc('marks')->values();
        $chartStudentNames = $rankedMarks->map(fn (PaperMark $m): string => $m->student?->full_name ?? 'Student')->values()->toArray();
        $chartStudentScores = $rankedMarks->map(fn (PaperMark $m): float => (float) $m->marks)->values()->toArray();

        return view('teacher.papers.show', [
            'paper' => $paper,
            'classRoom' => $paper->classRoom,
            'enrollments' => $enrollments,
            'marksByStudent' => $marksByStudent,
            'gradedCount' => $gradedCount,
            'absentCount' => $absentCount,
            'avgMarks' => $avgMarks,
            'avgPercent' => $avgPercent,
            'highestMarks' => $highestMarks,
            'lowestMarks' => $lowestMarks,
            'gradeDistribution' => $gradeDistribution,
            'chartStudentNames' => $chartStudentNames,
            'chartStudentScores' => $chartStudentScores,
        ]);
    }

    /**
     * Save or update student marks in bulk for a paper.
     */
    public function saveMarks(Request $request, Paper $paper): RedirectResponse
    {
        $paper->load('classRoom');
        $this->authorizeTeacher($paper->classRoom);

        $maxMarks = (float) $paper->max_marks;

        $validated = $request->validate([
            'entries' => ['required', 'array'],
            'entries.*.student_id' => ['required', 'exists:students,id'],
            'entries.*.marks' => ['nullable', 'numeric', 'min:0', 'max:'.$maxMarks],
            'entries.*.is_absent' => ['nullable', 'boolean'],
            'entries.*.remarks' => ['nullable', 'string', 'max:255'],
        ]);

        // Restrict updates strictly to students enrolled in this paper's class
        $enrolledIds = ClassStudent::where('class_id', $paper->class_id)
            ->pluck('student_id')
            ->all();

        $savedCount = 0;

        foreach ($validated['entries'] as $entry) {
            $studentId = (int) $entry['student_id'];
            if (! in_array($studentId, $enrolledIds, true)) {
                continue;
            }

            $isAbsent = ! empty($entry['is_absent']);
            $hasMarks = isset($entry['marks']) && $entry['marks'] !== '' && $entry['marks'] !== null;
            $remarks = isset($entry['remarks']) ? trim((string) $entry['remarks']) : null;

            if (! $isAbsent && ! $hasMarks) {
                PaperMark::where('paper_id', $paper->id)
                    ->where('student_id', $studentId)
                    ->delete();

                continue;
            }

            PaperMark::updateOrCreate(
                [
                    'paper_id' => $paper->id,
                    'student_id' => $studentId,
                ],
                [
                    'marks' => $isAbsent ? null : (float) $entry['marks'],
                    'is_absent' => $isAbsent,
                    'remarks' => $remarks ?: null,
                    'recorded_by' => Auth::id(),
                ]
            );

            $savedCount++;
        }

        return redirect()
            ->route('teacher.papers.show', $paper)
            ->with('success', "Saved marks for {$savedCount} student(s) in '{$paper->title}'. Graph updated!");
    }

    /**
     * Delete a paper and its associated marks.
     */
    public function destroy(Paper $paper): RedirectResponse
    {
        $paper->load('classRoom');
        $this->authorizeTeacher($paper->classRoom);

        $title = $paper->title;
        $classId = $paper->class_id;
        $paper->delete();

        return redirect()
            ->route('teacher.papers.index', ['class_id' => $classId])
            ->with('info', "Paper '{$title}' and its marks have been deleted.");
    }

    /**
     * Show an individual student's paper results history and interactive progress graph.
     */
    public function studentGraph(ClassRoom $class, Student $student): View
    {
        $this->authorizeTeacher($class);

        $enrollment = ClassStudent::with(['student', 'classRoom'])
            ->where('class_id', $class->id)
            ->where('student_id', $student->id)
            ->firstOrFail();

        $allClassStudents = ClassStudent::with('student')
            ->where('class_id', $class->id)
            ->where('status', 'active')
            ->get()
            ->sortBy(fn (ClassStudent $e): string => strtolower($e->student->full_name))
            ->values();

        // All papers in this class ordered chronologically
        $papers = Paper::with('marks')
            ->where('class_id', $class->id)
            ->orderBy('paper_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $results = [];
        $chartLabels = [];
        $studentPercentages = [];
        $classAveragePercentages = [];

        foreach ($papers as $paper) {
            $mark = $paper->marks->firstWhere('student_id', $student->id);
            $maxMarks = (float) $paper->max_marks;
            $classAvgPct = $paper->averagePercentage();

            // Compute rank among non-absent students
            $rank = null;
            $totalParticipants = $paper->marks->filter(fn (PaperMark $m): bool => ! $m->is_absent && $m->marks !== null)->count();
            if ($mark && ! $mark->is_absent && $mark->marks !== null) {
                $higherCount = $paper->marks
                    ->filter(fn (PaperMark $m): bool => ! $m->is_absent && $m->marks !== null && (float) $m->marks > (float) $mark->marks)
                    ->count();
                $rank = $higherCount + 1;
            }

            $studentPct = ($mark && ! $mark->is_absent && $mark->marks !== null && $maxMarks > 0)
                ? round(((float) $mark->marks / $maxMarks) * 100, 1)
                : null;

            $results[] = [
                'paper' => $paper,
                'mark' => $mark,
                'percentage' => $studentPct,
                'grade' => Paper::gradeFromPercentage($studentPct),
                'class_avg_pct' => $classAvgPct,
                'rank' => $rank,
                'participants' => $totalParticipants,
            ];

            $chartLabels[] = $paper->title.' ('.Carbon::parse($paper->paper_date)->format('M d').')';
            $studentPercentages[] = $studentPct;
            $classAveragePercentages[] = $classAvgPct;
        }

        $completedPercentages = collect($studentPercentages)->filter(fn ($v): bool => $v !== null);
        $overallAverage = $completedPercentages->isNotEmpty() ? round($completedPercentages->avg(), 1) : 0;
        $bestScore = $completedPercentages->isNotEmpty() ? $completedPercentages->max() : 0;
        $latestScore = $completedPercentages->isNotEmpty() ? $completedPercentages->last() : 0;

        return view('teacher.papers.student-graph', [
            'classRoom' => $class,
            'student' => $student,
            'enrollment' => $enrollment,
            'allClassStudents' => $allClassStudents,
            'results' => $results,
            'chartLabels' => $chartLabels,
            'studentPercentages' => $studentPercentages,
            'classAveragePercentages' => $classAveragePercentages,
            'overallAverage' => $overallAverage,
            'bestScore' => $bestScore,
            'latestScore' => $latestScore,
            'papersCount' => $papers->count(),
            'completedCount' => $completedPercentages->count(),
        ]);
    }

    protected function authorizeTeacher(ClassRoom $class): void
    {
        if (! Auth::user()->canAccessClass($class)) {
            abort(403, 'Unauthorized. You do not have access to this class.');
        }
    }
}
