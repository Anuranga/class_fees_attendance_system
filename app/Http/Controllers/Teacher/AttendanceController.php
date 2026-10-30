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
use Illuminate\View\View;

class AttendanceController extends Controller
{
    /**
     * Show attendance marking page and camera QR scanner.
     */
    public function show(Request $request, ClassRoom $class): View
    {
        $this->authorizeTeacher($class);

        $date = $request->query('date', Carbon::today()->toDateString());
        $carbonDate = Carbon::parse($date);

        // All enrolled students in this class
        $enrollments = ClassStudent::with('student')
            ->where('class_id', $class->id)
            ->where('status', 'active')
            ->get();

        // Attendances for selected date
        $attendances = Attendance::with(['student', 'markedBy'])
            ->where('class_id', $class->id)
            ->where('date', $date)
            ->orderBy('check_in_time', 'desc')
            ->get();

        $attendanceMap = $attendances->keyBy('student_id');

        $totalEnrolled = $enrollments->count();
        $presentCount = $attendances->where('status', 'present')->count();
        $lateCount = $attendances->where('status', 'late')->count();
        $absentCount = max(0, $totalEnrolled - ($presentCount + $lateCount));
        $rate = $totalEnrolled > 0 ? round((($presentCount + $lateCount) / $totalEnrolled) * 100, 1) : 0;

        $paymentsOnDate = Payment::where('class_id', $class->id)
            ->whereDate('paid_date', $date)
            ->get()
            ->keyBy('student_id');

        // Recent 6 months for fee selection (including last month)
        $recentMonths = [];
        $cursorMonth = Carbon::now();
        for ($i = 0; $i < 6; $i++) {
            $key = $cursorMonth->copy()->subMonths($i)->format('Y-m');
            $label = $cursorMonth->copy()->subMonths($i)->format('F Y');
            if ($i === 0) {
                $label .= ' (Current Month)';
            } elseif ($i === 1) {
                $label .= ' (Last Month)';
            }
            $recentMonths[$key] = $label;
        }

        return view('teacher.attendance.index', [
            'classRoom' => $class,
            'selectedDate' => $date,
            'isToday' => ($date === Carbon::today()->toDateString()),
            'enrollments' => $enrollments,
            'attendances' => $attendances,
            'attendanceMap' => $attendanceMap,
            'paymentsOnDate' => $paymentsOnDate,
            'recentMonths' => $recentMonths,
            'totalEnrolled' => $totalEnrolled,
            'presentCount' => $presentCount,
            'lateCount' => $lateCount,
            'absentCount' => $absentCount,
            'attendanceRate' => $rate,
        ]);
    }

    /**
     * AJAX endpoint for QR scanner.
     * POST /teacher/classes/{class}/attendance/scan
     */
    public function scan(Request $request, ClassRoom $class): JsonResponse
    {
        $this->authorizeTeacher($class);

        $request->validate([
            'qr_token' => ['required', 'string'],
        ]);

        $qrToken = trim($request->input('qr_token'));
        $today = Carbon::today()->toDateString();
        $currentTime = Carbon::now()->toTimeString();

        // 1. Validate qr_token against class enrollments
        $enrollment = ClassStudent::with('student')
            ->where('class_id', $class->id)
            ->where('qr_token', $qrToken)
            ->first();

        if (! $enrollment) {
            // Also check if token matches student admission number or class UID for barcode scanner backup
            $enrollment = ClassStudent::with('student')
                ->where('class_id', $class->id)
                ->where(function ($q) use ($qrToken) {
                    $q->where('student_class_uid', $qrToken)
                        ->orWhereHas('student', function ($sq) use ($qrToken) {
                            $sq->where('admission_number', $qrToken);
                        });
                })->first();
        }

        if (! $enrollment) {
            return response()->json([
                'success' => false,
                'status' => 'not_found',
                'message' => 'QR Code is not registered or student is not enrolled in this class.',
            ], 404);
        }

        if ($enrollment->status !== 'active') {
            return response()->json([
                'success' => false,
                'status' => 'suspended',
                'message' => "Student {$enrollment->student->full_name} is suspended from this class.",
            ], 403);
        }

        $student = $enrollment->student;
        $currentMonth = Carbon::now()->format('Y-m');
        $hasPaidMonth = Payment::where('student_id', $student->id)
            ->where('class_id', $class->id)
            ->where('payment_month', $currentMonth)
            ->exists();
        $feeStatus = [
            'is_paid' => $hasPaidMonth,
            'monthly_fee' => (float) $class->monthly_fee,
            'month_label' => Carbon::now()->format('F Y'),
        ];

        // 2. Check for duplicate attendance today (prevent duplicate marking)
        $existing = Attendance::where('class_id', $class->id)
            ->where('student_id', $student->id)
            ->whereDate('date', $today)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'status' => 'duplicate',
                'message' => "Already Marked Present! {$student->full_name} was scanned at ".Carbon::parse($existing->check_in_time)->format('h:i:s A'),
                'student' => [
                    'id' => $student->id,
                    'full_name' => $student->full_name,
                    'admission_number' => $student->admission_number,
                    'uid' => $enrollment->student_class_uid,
                    'avatar_url' => $student->avatar_url,
                ],
                'attendance' => [
                    'check_in_time' => Carbon::parse($existing->check_in_time)->format('h:i:s A'),
                    'status' => $existing->status,
                    'tute_provided' => (bool) $existing->tute_provided,
                ],
                'fee_status' => $feeStatus,
            ], 409); // 409 Conflict
        }

        // 3. Determine if late or present based on time of day (or default to present)
        $status = 'present';
        $tuteProvided = $request->boolean('tute_provided');
        $tuteName = $request->input('tute_name');

        $attendance = Attendance::create([
            'class_id' => $class->id,
            'student_id' => $student->id,
            'date' => $today,
            'check_in_time' => $currentTime,
            'status' => $status,
            'tute_provided' => $tuteProvided,
            'tute_name' => $tuteName,
            'marked_by' => Auth::id(),
        ]);

        // Recalculate stats for live UI update
        $totalEnrolled = ClassStudent::where('class_id', $class->id)->where('status', 'active')->count();
        $presentCount = Attendance::where('class_id', $class->id)->whereDate('date', $today)->where('status', 'present')->count();
        $lateCount = Attendance::where('class_id', $class->id)->whereDate('date', $today)->where('status', 'late')->count();
        $absentCount = max(0, $totalEnrolled - ($presentCount + $lateCount));
        $rate = $totalEnrolled > 0 ? round((($presentCount + $lateCount) / $totalEnrolled) * 100, 1) : 0;

        return response()->json([
            'success' => true,
            'status' => 'marked',
            'message' => "Check-in Confirmed: {$student->full_name}".($tuteProvided ? ' (Tute Issued)' : ''),
            'student' => [
                'id' => $student->id,
                'full_name' => $student->full_name,
                'admission_number' => $student->admission_number,
                'uid' => $enrollment->student_class_uid,
                'avatar_url' => $student->avatar_url,
                'contact_number' => $student->contact_number,
                'guardian_phone' => $student->guardian_phone,
            ],
            'attendance' => [
                'id' => $attendance->id,
                'check_in_time' => Carbon::parse($currentTime)->format('h:i:s A'),
                'status' => $status,
                'tute_provided' => $attendance->tute_provided,
                'tute_name' => $attendance->tute_name,
                'date' => $today,
            ],
            'fee_status' => $feeStatus,
            'stats' => [
                'total_enrolled' => $totalEnrolled,
                'present_count' => $presentCount,
                'late_count' => $lateCount,
                'absent_count' => $absentCount,
                'rate' => $rate,
            ],
        ], 200);
    }

    /**
     * Manual mark toggle for teacher.
     */
    public function manualMark(Request $request, ClassRoom $class): RedirectResponse
    {
        $this->authorizeTeacher($class);

        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'date' => ['required', 'date'],
            'status' => ['required', 'in:present,late,absent,delete'],
            'tute_provided' => ['nullable', 'boolean'],
            'tute_name' => ['nullable', 'string', 'max:150'],
        ]);

        $studentId = $validated['student_id'];
        $date = $validated['date'];
        $status = $validated['status'];

        if ($status === 'delete' || $status === 'absent') {
            Attendance::where('class_id', $class->id)
                ->where('student_id', $studentId)
                ->where('date', $date)
                ->delete();

            return back()->with('info', 'Attendance record cleared/marked as absent.');
        }

        $attributes = [
            'check_in_time' => Carbon::now()->toTimeString(),
            'status' => $status,
            'marked_by' => Auth::id(),
        ];

        if ($request->has('tute_provided')) {
            $attributes['tute_provided'] = $request->boolean('tute_provided');
        }
        if ($request->filled('tute_name')) {
            $attributes['tute_name'] = $request->input('tute_name');
        }

        Attendance::updateOrCreate(
            [
                'class_id' => $class->id,
                'student_id' => $studentId,
                'date' => $date,
            ],
            $attributes
        );

        return back()->with('success', "Attendance updated to '{$status}'.");
    }

    /**
     * Quick 1-click toggle for Tute provision.
     */
    public function toggleTute(Request $request, ClassRoom $class, Attendance $attendance): RedirectResponse
    {
        $this->authorizeTeacher($class);

        $attendance->update([
            'tute_provided' => ! $attendance->tute_provided,
            'marked_by' => Auth::id(),
        ]);

        $state = $attendance->tute_provided ? 'marked as Issued' : 'marked as NOT Issued';

        return back()->with('success', "Tutorial sheet {$state} for {$attendance->student->full_name}.");
    }

    /**
     * Mark Attendance with optional Fee Collection in one unified modal.
     */
    public function markWithFee(Request $request, ClassRoom $class): RedirectResponse
    {
        $this->authorizeTeacher($class);

        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'date' => ['required', 'date'],
            'status' => ['required', 'in:present,late'],
            'tute_provided' => ['nullable', 'boolean'],
            'collect_fee' => ['nullable', 'boolean'],
            'payment_type' => ['nullable', 'in:monthly,daily'],
            'payment_month' => ['nullable', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['nullable', 'string'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $student = Student::findOrFail($validated['student_id']);
        $date = $validated['date'];
        $status = $validated['status'];
        $tuteProvided = $request->boolean('tute_provided');

        // 1. Compulsory attendance mark
        $attendance = Attendance::updateOrCreate(
            [
                'class_id' => $class->id,
                'student_id' => $student->id,
                'date' => $date,
            ],
            [
                'check_in_time' => Carbon::now()->toTimeString(),
                'status' => $status,
                'tute_provided' => $tuteProvided,
                'marked_by' => Auth::id(),
            ]
        );

        $message = "Attendance marked for {$student->full_name} ({$status})";
        if ($tuteProvided) {
            $message .= ' with Tute handed out';
        }

        // 2. Optional fee payment
        if ($request->boolean('collect_fee') && $request->filled('amount') && $request->input('amount') > 0) {
            $teacherId = Auth::user()->effectiveTeacherId();
            $paymentType = $request->input('payment_type', 'monthly');
            $paidDate = $date;
            $paymentMonth = $paymentType === 'daily'
                ? Carbon::parse($paidDate)->format('Y-m')
                : ($request->input('payment_month') ?: Carbon::now()->format('Y-m'));

            // If monthly, check if already recorded
            $alreadyPaid = false;
            if ($paymentType === 'monthly') {
                $alreadyPaid = Payment::where('class_id', $class->id)
                    ->where('student_id', $student->id)
                    ->where('payment_type', 'monthly')
                    ->where('payment_month', $paymentMonth)
                    ->exists();
            }

            if ($alreadyPaid) {
                $message .= ". Note: Monthly fee for {$paymentMonth} was already marked as paid.";
            } else {
                $receiptNo = Payment::generateReceiptNumber();
                $payment = Payment::create([
                    'class_id' => $class->id,
                    'student_id' => $student->id,
                    'teacher_id' => $teacherId,
                    'collected_by' => Auth::id(),
                    'payment_type' => $paymentType,
                    'amount' => $request->input('amount'),
                    'payment_month' => $paymentMonth,
                    'paid_date' => $paidDate,
                    'receipt_no' => $receiptNo,
                    'payment_method' => $request->input('payment_method', 'cash'),
                    'notes' => $request->input('notes') ?: ($paymentType === 'daily' ? "Daily fee on {$paidDate}" : "Fee for {$paymentMonth}"),
                ]);

                $message .= " & Cash Payment of LKR {$payment->amount} recorded (Receipt: {$receiptNo})";
            }
        }

        return back()->with('success', $message);
    }

    /**
     * Daily Attendance & Cash Collection Ledger Sheet.
     * Both teachers and assistants can filter by class and day.
     */
    public function dailySheet(Request $request): View
    {
        $teacherId = Auth::user()->effectiveTeacherId();
        $date = $request->query('date', Carbon::today()->toDateString());
        $selectedClassId = $request->query('class_id');

        // Teacher's classes
        $classes = ClassRoom::where('teacher_id', $teacherId)->get();
        $classIds = $classes->pluck('id')->toArray();

        // Scope to selected class or all teacher's classes
        $filteredClassIds = $selectedClassId ? [(int) $selectedClassId] : $classIds;

        // 1. Attendances for the selected date
        $attendances = Attendance::with(['student', 'classRoom', 'markedBy'])
            ->whereIn('class_id', $filteredClassIds)
            ->where('date', $date)
            ->orderBy('check_in_time', 'desc')
            ->get();

        // 2. Payments collected on this specific date
        $payments = Payment::with(['student', 'classRoom', 'collector'])
            ->whereIn('class_id', $filteredClassIds)
            ->whereDate('paid_date', $date)
            ->orderBy('created_at', 'desc')
            ->get();

        // Total cash collected on this date
        $dailyCashTotal = $payments->sum('amount');
        $attendanceCount = $attendances->whereIn('status', ['present', 'late'])->count();
        $tuteCount = $attendances->where('tute_provided', true)->count();
        $transactionsCount = $payments->count();

        // Map payments by student_id for the attendance table view
        $paymentsByStudent = $payments->groupBy('student_id');

        return view('teacher.daily.sheet', [
            'classes' => $classes,
            'selectedClassId' => $selectedClassId,
            'selectedDate' => $date,
            'isToday' => ($date === Carbon::today()->toDateString()),
            'attendances' => $attendances,
            'payments' => $payments,
            'paymentsByStudent' => $paymentsByStudent,
            'dailyCashTotal' => $dailyCashTotal,
            'attendanceCount' => $attendanceCount,
            'tuteCount' => $tuteCount,
            'transactionsCount' => $transactionsCount,
        ]);
    }

    protected function authorizeTeacher(ClassRoom $class): void
    {
        if ($class->teacher_id !== Auth::user()->effectiveTeacherId() && ! Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized. You do not own this class.');
        }
    }
}
