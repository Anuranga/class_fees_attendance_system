<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\ClassRoom;
use App\Models\ClassStudent;
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

        return view('teacher.attendance.index', [
            'classRoom' => $class,
            'selectedDate' => $date,
            'isToday' => ($date === Carbon::today()->toDateString()),
            'enrollments' => $enrollments,
            'attendances' => $attendances,
            'attendanceMap' => $attendanceMap,
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
                ],
                'attendance' => [
                    'check_in_time' => Carbon::parse($existing->check_in_time)->format('h:i:s A'),
                    'status' => $existing->status,
                ],
            ], 409); // 409 Conflict
        }

        // 3. Determine if late or present based on time of day (or default to present)
        $status = 'present';

        $attendance = Attendance::create([
            'class_id' => $class->id,
            'student_id' => $student->id,
            'date' => $today,
            'check_in_time' => $currentTime,
            'status' => $status,
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
            'message' => "Check-in Confirmed: {$student->full_name}",
            'student' => [
                'id' => $student->id,
                'full_name' => $student->full_name,
                'admission_number' => $student->admission_number,
                'uid' => $enrollment->student_class_uid,
                'contact_number' => $student->contact_number,
                'guardian_phone' => $student->guardian_phone,
            ],
            'attendance' => [
                'id' => $attendance->id,
                'check_in_time' => Carbon::parse($currentTime)->format('h:i:s A'),
                'status' => $status,
                'date' => $today,
            ],
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

        Attendance::updateOrCreate(
            [
                'class_id' => $class->id,
                'student_id' => $studentId,
                'date' => $date,
            ],
            [
                'check_in_time' => Carbon::now()->toTimeString(),
                'status' => $status,
                'marked_by' => Auth::id(),
            ]
        );

        return back()->with('success', "Attendance updated to '{$status}'.");
    }

    protected function authorizeTeacher(ClassRoom $class): void
    {
        if ($class->teacher_id !== Auth::id() && ! Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized. You do not own this class.');
        }
    }
}
