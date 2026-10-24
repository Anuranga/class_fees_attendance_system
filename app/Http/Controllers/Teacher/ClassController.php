<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassRoom;
use App\Models\ClassStudent;
use App\Models\Payment;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ClassController extends Controller
{
    /**
     * Display a listing of teacher's classes.
     */
    public function index(): View
    {
        $teacherId = Auth::id();
        $classes = ClassRoom::withCount(['students' => function ($q) {
            $q->where('class_student.status', 'active');
        }])->where('teacher_id', $teacherId)->latest()->get();

        $currentMonth = Carbon::now()->format('Y-m');

        return view('teacher.classes.index', compact('classes', 'currentMonth'));
    }

    /**
     * Store a newly created class.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'grade_level' => ['required', 'string', 'max:100'],
            'monthly_fee' => ['required', 'numeric', 'min:0'],
            'is_registration_open' => ['nullable', 'boolean'],
        ]);

        $classRoom = ClassRoom::create([
            'teacher_id' => Auth::id(),
            'name' => $validated['name'],
            'grade_level' => $validated['grade_level'],
            'monthly_fee' => $validated['monthly_fee'],
            'registration_token' => Str::random(24),
            'is_registration_open' => $request->boolean('is_registration_open', true),
        ]);

        return redirect()->route('teacher.classes.show', $classRoom)
            ->with('success', "Class '{$classRoom->name}' created successfully! Share the join link or add students manually.");
    }

    /**
     * Display class details, student enrollments, fee indicators, and management tools.
     */
    public function show(ClassRoom $class): View
    {
        $this->authorizeTeacher($class);

        $currentMonth = Carbon::now()->format('Y-m');

        // Fetch enrollments with student data
        $enrollments = ClassStudent::with('student')
            ->where('class_id', $class->id)
            ->get();

        // Get paid student IDs for current month
        $paidStudentIds = Payment::where('class_id', $class->id)
            ->where('payment_month', $currentMonth)
            ->pluck('student_id')
            ->toArray();

        // Available existing students not yet enrolled in this class (for quick add dropdown)
        $enrolledStudentIds = $enrollments->pluck('student_id')->toArray();
        $otherStudents = Student::whereNotIn('id', $enrolledStudentIds)
            ->orderBy('full_name')
            ->limit(50)
            ->get();

        $totalEnrolled = $enrollments->where('status', 'active')->count();
        $paidCount = count(array_intersect($enrolledStudentIds, $paidStudentIds));
        $pendingCount = max(0, $totalEnrolled - $paidCount);

        return view('teacher.classes.show', [
            'classRoom' => $class,
            'enrollments' => $enrollments,
            'paidStudentIds' => $paidStudentIds,
            'otherStudents' => $otherStudents,
            'currentMonth' => $currentMonth,
            'totalEnrolled' => $totalEnrolled,
            'paidCount' => $paidCount,
            'pendingCount' => $pendingCount,
        ]);
    }

    /**
     * Update class settings.
     */
    public function update(Request $request, ClassRoom $class): RedirectResponse
    {
        $this->authorizeTeacher($class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'grade_level' => ['required', 'string', 'max:100'],
            'monthly_fee' => ['required', 'numeric', 'min:0'],
            'is_registration_open' => ['nullable', 'boolean'],
        ]);

        $class->update([
            'name' => $validated['name'],
            'grade_level' => $validated['grade_level'],
            'monthly_fee' => $validated['monthly_fee'],
            'is_registration_open' => $request->boolean('is_registration_open'),
        ]);

        return back()->with('success', 'Class details updated successfully.');
    }

    /**
     * Toggle registration open/closed.
     */
    public function toggleRegistration(ClassRoom $class): RedirectResponse
    {
        $this->authorizeTeacher($class);

        $class->update([
            'is_registration_open' => ! $class->is_registration_open,
        ]);

        $status = $class->is_registration_open ? 'opened' : 'closed';

        return back()->with('success', "Self-registration link is now {$status}.");
    }

    /**
     * Manually add existing or create new student and enroll in class.
     */
    public function enrollStudentManual(Request $request, ClassRoom $class): RedirectResponse
    {
        $this->authorizeTeacher($class);

        $mode = $request->input('mode', 'new'); // 'new' or 'existing'

        if ($mode === 'existing') {
            $request->validate([
                'student_id' => ['required', 'exists:students,id'],
            ]);
            $student = Student::findOrFail($request->student_id);
        } else {
            $validated = $request->validate([
                'full_name' => ['required', 'string', 'max:255'],
                'contact_number' => ['required', 'string', 'max:30'],
                'guardian_name' => ['required', 'string', 'max:255'],
                'guardian_phone' => ['required', 'string', 'max:30'],
                'admission_number' => ['nullable', 'string', 'max:50'],
            ]);

            $student = Student::create([
                'admission_number' => $validated['admission_number'] ?: Student::generateAdmissionNumber(),
                'full_name' => $validated['full_name'],
                'contact_number' => trim($validated['contact_number']),
                'guardian_name' => $validated['guardian_name'],
                'guardian_phone' => trim($validated['guardian_phone']),
            ]);
        }

        // Check if already enrolled
        $exists = ClassStudent::where('class_id', $class->id)
            ->where('student_id', $student->id)
            ->exists();

        if ($exists) {
            return back()->with('error', "Student {$student->full_name} is already enrolled in this class.");
        }

        $uid = ClassStudent::generateUid($class->id, $student->id);
        $qrToken = ClassStudent::generateQrToken();

        ClassStudent::create([
            'class_id' => $class->id,
            'student_id' => $student->id,
            'student_class_uid' => $uid,
            'qr_token' => $qrToken,
            'joined_via' => 'manual',
            'status' => 'active',
        ]);

        return back()->with('success', "Student {$student->full_name} has been enrolled successfully!");
    }

    /**
     * Remove or toggle student status.
     */
    public function removeStudent(ClassRoom $class, Student $student): RedirectResponse
    {
        $this->authorizeTeacher($class);

        ClassStudent::where('class_id', $class->id)
            ->where('student_id', $student->id)
            ->delete();

        return back()->with('success', "Student {$student->full_name} was removed from this class.");
    }

    /**
     * Enforce teacher multi-tenant boundary.
     */
    protected function authorizeTeacher(ClassRoom $class): void
    {
        if ($class->teacher_id !== Auth::id() && ! Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized. You do not own this class.');
        }
    }
}
