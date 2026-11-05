<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\ClassRoom;
use App\Models\ClassStudent;
use App\Models\Payment;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ClassController extends Controller
{
    /**
     * Display a listing of teacher's classes (or assistant's assigned classes).
     */
    public function index(): View
    {
        $classes = Auth::user()->accessibleClassesQuery()
            ->withCount(['students' => function ($q) {
                $q->where('class_student.status', 'active');
            }])->latest()->get();

        $currentMonth = Carbon::now()->format('Y-m');

        return view('teacher.classes.index', compact('classes', 'currentMonth'));
    }

    /**
     * Store a newly created class.
     */
    public function store(Request $request): RedirectResponse
    {
        if (Auth::user()->isAssistant()) {
            abort(403, 'Unauthorized. Assistants cannot create new classes.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'grade_level' => ['required', 'string', 'max:100'],
            'fee_type' => ['nullable', 'in:monthly,daily'],
            'monthly_fee' => ['required', 'numeric', 'min:0'],
            'is_registration_open' => ['nullable', 'boolean'],
        ]);

        $classRoom = ClassRoom::create([
            'teacher_id' => Auth::user()->effectiveTeacherId(),
            'name' => $validated['name'],
            'grade_level' => $validated['grade_level'],
            'fee_type' => $validated['fee_type'] ?? 'monthly',
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
            'fee_type' => ['nullable', 'in:monthly,daily'],
            'monthly_fee' => ['required', 'numeric', 'min:0'],
            'is_registration_open' => ['nullable', 'boolean'],
        ]);

        $class->update([
            'name' => $validated['name'],
            'grade_level' => $validated['grade_level'],
            'fee_type' => $validated['fee_type'] ?? $class->fee_type,
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
                'gender' => ['nullable', 'string', 'in:male,female'],
                'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:10240'],
                'photo_base64' => ['nullable', 'string'],
                'contact_number' => ['required', 'string', 'max:30'],
                'guardian_name' => ['required', 'string', 'max:255'],
                'guardian_phone' => ['required', 'string', 'max:30'],
                'admission_number' => ['nullable', 'string', 'max:50'],
            ]);

            $photoPath = null;
            if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
                $photoPath = $request->file('photo')->store('students', 'public');
            } elseif ($request->filled('photo_base64') && str_starts_with((string) $request->input('photo_base64'), 'data:image/')) {
                $base64Data = (string) $request->input('photo_base64');
                if (preg_match('/^data:image\/(\w+);base64,/', $base64Data, $type)) {
                    $data = substr($base64Data, strpos($base64Data, ',') + 1);
                    $decoded = base64_decode($data);
                    if ($decoded !== false) {
                        $extension = strtolower($type[1]) === 'png' ? 'png' : 'jpg';
                        $filename = 'students/'.Str::random(40).'.'.$extension;
                        Storage::disk('public')->put($filename, $decoded);
                        $photoPath = $filename;
                    }
                }
            } elseif ($request->hasFile('photo') && ! $request->file('photo')->isValid()) {
                return back()->withInput()->withErrors(['photo' => 'Photo upload failed: '.$request->file('photo')->getErrorMessage()]);
            }

            $student = Student::create([
                'admission_number' => (! empty($validated['admission_number'])) ? $validated['admission_number'] : Student::generateAdmissionNumber(),
                'full_name' => $validated['full_name'],
                'gender' => $validated['gender'] ?? 'male',
                'photo_path' => $photoPath,
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
     * Display student attendance details and interactive monthly calendar.
     */
    public function studentAttendance(Request $request, ClassRoom $class, Student $student): View|JsonResponse
    {
        $this->authorizeTeacher($class);

        // Verify student enrollment in this specific class
        $enrollment = ClassStudent::with(['classRoom', 'student'])
            ->where('class_id', $class->id)
            ->where('student_id', $student->id)
            ->firstOrFail();

        $monthParam = $request->query('month', Carbon::now()->format('Y-m'));
        try {
            $monthCarbon = Carbon::createFromFormat('Y-m', $monthParam)->startOfMonth();
        } catch (\Exception $e) {
            $monthCarbon = Carbon::now()->startOfMonth();
            $monthParam = $monthCarbon->format('Y-m');
        }

        $startDate = $monthCarbon->copy()->startOfMonth();
        $endDate = $monthCarbon->copy()->endOfMonth();
        $today = Carbon::today()->toDateString();

        // All active students enrolled in this class for fast switcher
        $allClassStudents = ClassStudent::with('student')
            ->where('class_id', $class->id)
            ->where('status', 'active')
            ->get()
            ->sortBy(fn ($e) => strtolower($e->student->full_name))
            ->values();

        // 1. All class session dates conducted for this class during this month
        $classSessionDates = Attendance::where('class_id', $class->id)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->distinct()
            ->pluck('date')
            ->map(fn ($d) => Carbon::parse($d)->toDateString())
            ->unique()
            ->values()
            ->toArray();

        // 2. Attendance records for this student in this class during this month
        $studentAttendances = Attendance::with('markedBy')
            ->where('class_id', $class->id)
            ->where('student_id', $student->id)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->keyBy(fn ($att) => Carbon::parse($att->date)->toDateString());

        // 3. Fee payments
        $monthlyPayment = Payment::where('class_id', $class->id)
            ->where('student_id', $student->id)
            ->where('payment_month', $monthParam)
            ->first();

        $paymentsOnDates = Payment::where('class_id', $class->id)
            ->where('student_id', $student->id)
            ->whereBetween('paid_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->groupBy(fn ($p) => Carbon::parse($p->paid_date)->toDateString());

        // 4. Statistics calculation
        $totalSessionsHeld = count($classSessionDates);
        $presentCount = $studentAttendances->where('status', 'present')->count();
        $lateCount = $studentAttendances->where('status', 'late')->count();
        $attendedCount = $presentCount + $lateCount;

        $missedSessions = 0;
        foreach ($classSessionDates as $sessionDate) {
            if (! $studentAttendances->has($sessionDate)) {
                $missedSessions++;
            } elseif ($studentAttendances->get($sessionDate)->status === 'absent') {
                $missedSessions++;
            }
        }

        $attendanceRate = $totalSessionsHeld > 0 ? round(($attendedCount / $totalSessionsHeld) * 100, 1) : 0;
        $tutesCount = $studentAttendances->where('tute_provided', true)->count();

        // All-time attendance rate for this student in this class
        $allTimeSessions = Attendance::where('class_id', $class->id)
            ->distinct()
            ->pluck('date')
            ->count();

        $allTimeAttended = Attendance::where('class_id', $class->id)
            ->where('student_id', $student->id)
            ->whereIn('status', ['present', 'late'])
            ->count();

        $allTimeRate = $allTimeSessions > 0 ? round(($allTimeAttended / $allTimeSessions) * 100, 1) : 0;

        // 5. Build Calendar Days (Mon - Sun, ISO 8601: Mon=1, Sun=7)
        $firstDayOfWeekIso = $startDate->dayOfWeekIso;
        $leadingCount = $firstDayOfWeekIso - 1;
        $prevMonthEnd = $startDate->copy()->subDay();

        $calendarDays = [];

        // Preceding days from previous month
        for ($i = $leadingCount - 1; $i >= 0; $i--) {
            $pDate = $prevMonthEnd->copy()->subDays($i);
            $calendarDays[] = [
                'day' => $pDate->day,
                'date' => $pDate->toDateString(),
                'is_current_month' => false,
                'is_today' => false,
                'is_future' => true,
                'session_held' => false,
                'status' => 'outside',
                'attendance' => null,
                'payments' => [],
            ];
        }

        // Current month days
        for ($d = 1; $d <= $startDate->daysInMonth; $d++) {
            $dateObj = $startDate->copy()->day($d);
            $dateStr = $dateObj->toDateString();
            $isToday = ($dateStr === $today);
            $isFuture = $dateObj->isFuture();
            $sessionHeld = in_array($dateStr, $classSessionDates);
            $attendance = $studentAttendances->get($dateStr);
            $payments = $paymentsOnDates->get($dateStr, collect());

            $status = 'no_class';
            if ($attendance) {
                $status = $attendance->status; // 'present', 'late', 'absent'
            } elseif ($sessionHeld) {
                $status = 'missed';
            }

            $calendarDays[] = [
                'day' => $d,
                'date' => $dateStr,
                'day_name' => $dateObj->format('D'),
                'is_current_month' => true,
                'is_today' => $isToday,
                'is_future' => $isFuture,
                'session_held' => $sessionHeld,
                'status' => $status,
                'attendance' => $attendance ? [
                    'id' => $attendance->id,
                    'check_in_time' => Carbon::parse($attendance->check_in_time)->format('h:i A'),
                    'status' => $attendance->status,
                    'tute_provided' => (bool) $attendance->tute_provided,
                    'tute_name' => $attendance->tute_name,
                    'marked_by' => $attendance->markedBy?->name ?? 'System',
                ] : null,
                'payments' => $payments->map(fn ($p) => [
                    'id' => $p->id,
                    'amount' => (float) $p->amount,
                    'receipt_no' => $p->receipt_no,
                    'fee_type' => $p->payment_type ?? 'monthly',
                ])->values()->toArray(),
            ];
        }

        // Trailing days from next month
        $trailingCount = (7 - (count($calendarDays) % 7)) % 7;
        for ($j = 1; $j <= $trailingCount; $j++) {
            $nDate = $endDate->copy()->addDays($j);
            $calendarDays[] = [
                'day' => $nDate->day,
                'date' => $nDate->toDateString(),
                'is_current_month' => false,
                'is_today' => false,
                'is_future' => true,
                'session_held' => false,
                'status' => 'outside',
                'attendance' => null,
                'payments' => [],
            ];
        }

        $prevMonth = $monthCarbon->copy()->subMonth()->format('Y-m');
        $nextMonth = $monthCarbon->copy()->addMonth()->format('Y-m');
        $monthLabel = $monthCarbon->format('F Y');

        // Detailed session logs
        $monthlyLogs = Attendance::with('markedBy')
            ->where('class_id', $class->id)
            ->where('student_id', $student->id)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->orderBy('date', 'desc')
            ->get();

        $data = [
            'classRoom' => $class,
            'student' => $student,
            'enrollment' => $enrollment,
            'allClassStudents' => $allClassStudents,
            'currentMonth' => $monthParam,
            'monthLabel' => $monthLabel,
            'prevMonth' => $prevMonth,
            'nextMonth' => $nextMonth,
            'calendarDays' => $calendarDays,
            'totalSessionsHeld' => $totalSessionsHeld,
            'presentCount' => $presentCount,
            'lateCount' => $lateCount,
            'missedSessions' => $missedSessions,
            'attendedCount' => $attendedCount,
            'attendanceRate' => $attendanceRate,
            'allTimeSessions' => $allTimeSessions,
            'allTimeAttended' => $allTimeAttended,
            'allTimeRate' => $allTimeRate,
            'tutesCount' => $tutesCount,
            'monthlyPayment' => $monthlyPayment,
            'monthlyLogs' => $monthlyLogs,
        ];

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'html' => view('teacher.classes.partials.student-calendar-card', $data)->render(),
                'student_name' => $student->full_name,
                'month_label' => $monthLabel,
            ]);
        }

        return view('teacher.classes.student-attendance', $data);
    }

    /**
     * Enforce teacher & assistant class assignment boundary.
     */
    protected function authorizeTeacher(ClassRoom $class): void
    {
        if (! Auth::user()->canAccessClass($class)) {
            abort(403, 'Unauthorized. You do not have access to this class.');
        }
    }
}
