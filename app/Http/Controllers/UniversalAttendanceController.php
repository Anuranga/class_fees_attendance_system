<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\ClassRoom;
use App\Models\ClassStudent;
use App\Models\Payment;
use App\Models\SchoolSetting;
use App\Models\TeacherAttendance;
use App\Models\TeacherLeave;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class UniversalAttendanceController extends Controller
{
    /**
     * Show central universal QR attendance scanner kiosk.
     */
    public function showScanner(Request $request): View
    {
        $user = Auth::user();
        $school = SchoolSetting::get($user);
        $today = Carbon::today()->toDateString();

        // Today's student attendance count for this school
        $schoolClassIds = ClassRoom::where('school_setting_id', $school->id)->pluck('id');
        $studentScansToday = Attendance::whereIn('class_id', $schoolClassIds)
            ->whereDate('date', $today)
            ->count();

        // Today's teacher attendance count for this school
        $teacherScansToday = TeacherAttendance::where('school_setting_id', $school->id)
            ->whereDate('date', $today)
            ->count();

        // Recent universal scan activity (both teachers and students)
        $recentStudentScans = Attendance::whereIn('class_id', $schoolClassIds)
            ->whereDate('date', $today)
            ->with(['student', 'classRoom'])
            ->latest('check_in_time')
            ->limit(8)
            ->get()
            ->map(function ($att) {
                return [
                    'category' => 'student',
                    'name' => $att->student?->full_name ?: 'Student',
                    'sub' => $att->classRoom?->name ?: 'Class',
                    'time' => $att->check_in_time ? Carbon::parse($att->check_in_time)->format('h:i A') : '',
                    'status' => 'Present',
                    'badge_color' => 'indigo',
                ];
            });

        $recentTeacherScans = TeacherAttendance::where('school_setting_id', $school->id)
            ->whereDate('date', $today)
            ->with('teacher')
            ->latest('check_in_time')
            ->limit(8)
            ->get()
            ->map(function ($att) {
                return [
                    'category' => 'teacher',
                    'name' => $att->teacher?->name ?: 'Teacher',
                    'sub' => $att->check_out_time ? 'Checked Out: '.Carbon::parse($att->check_out_time)->format('h:i A') : 'Faculty Staff',
                    'time' => $att->check_in_time ? Carbon::parse($att->check_in_time)->format('h:i A') : '',
                    'status' => $att->check_out_time ? 'Out' : 'In',
                    'badge_color' => 'emerald',
                ];
            });

        $recentFeed = $recentStudentScans->merge($recentTeacherScans)->take(10);

        return view('attendance.universal-scan', [
            'school' => $school,
            'todayDate' => Carbon::today()->format('l, F d, Y'),
            'studentScansToday' => $studentScansToday,
            'teacherScansToday' => $teacherScansToday,
            'totalScansToday' => $studentScansToday + $teacherScansToday,
            'recentFeed' => $recentFeed,
        ]);
    }

    /**
     * Central universal scan endpoint.
     * Detects whether the token belongs to a Student or a Teacher.
     */
    public function scan(Request $request): JsonResponse
    {
        $request->validate([
            'qr_token' => ['required', 'string'],
        ]);

        $token = trim($request->input('qr_token'));
        $today = Carbon::today()->toDateString();
        $currentTime = Carbon::now()->toTimeString();
        $user = Auth::user();
        $school = SchoolSetting::get($user);

        // -------------------------------------------------------------
        // 1. Check if token belongs to a Teacher / Staff Member
        // -------------------------------------------------------------
        $teacher = User::where('qr_token', $token)
            ->where(function ($q) use ($school) {
                $q->where('school_setting_id', $school->id)
                    ->orWhereNull('school_setting_id');
            })
            ->first();

        // Also check if token starts with TCH-
        if (! $teacher && Str::startsWith($token, 'TCH-')) {
            $teacher = User::where('qr_token', $token)->first();
        }

        if ($teacher) {
            if ($teacher->status === 'blocked') {
                return response()->json([
                    'success' => false,
                    'category' => 'teacher',
                    'status' => 'blocked',
                    'message' => "Teacher account for {$teacher->name} is blocked. Please contact the administrator.",
                ], 403);
            }

            // Check if teacher has an approved leave today
            $onLeave = TeacherLeave::where('teacher_id', $teacher->id)
                ->where('status', 'approved')
                ->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)
                ->first();

            $existingAttendance = TeacherAttendance::where('teacher_id', $teacher->id)
                ->whereDate('date', $today)
                ->first();

            if (! $existingAttendance) {
                // Record Check-In
                TeacherAttendance::create([
                    'school_setting_id' => $school->id,
                    'teacher_id' => $teacher->id,
                    'date' => $today,
                    'check_in_time' => $currentTime,
                    'status' => 'present',
                    'marked_by' => $user->id,
                ]);

                $action = 'check_in';
                $displayMessage = "ගුරුතුමිය/ගුරුතුමා {$teacher->name} පැමිණීම සාර්ථකව සටහන් විය! (Faculty Check-in Recorded)";
            } elseif ($existingAttendance->check_out_time === null) {
                // Record Check-Out
                $existingAttendance->update([
                    'check_out_time' => $currentTime,
                ]);

                $action = 'check_out';
                $displayMessage = "ගුරුතුමිය/ගුරුතුමා {$teacher->name} පිටවීම සාර්ථකව සටහන් විය! (Faculty Check-out Recorded)";
            } else {
                $action = 'already_completed';
                $displayMessage = "ගුරුතුමිය/ගුරුතුමා {$teacher->name} අද දින පැමිණීම සහ පිටවීම දැනටමත් සටහන් කර ඇත.";
            }

            return response()->json([
                'success' => true,
                'category' => 'teacher',
                'action' => $action,
                'name' => $teacher->name,
                'email' => $teacher->email,
                'role' => ucfirst($teacher->role),
                'check_in_time' => Carbon::now()->format('h:i A'),
                'is_on_leave' => (bool) $onLeave,
                'leave_details' => $onLeave ? "Approved Leave: {$onLeave->leave_type} ({$onLeave->reason})" : null,
                'message' => $displayMessage,
            ]);
        }

        // -------------------------------------------------------------
        // 2. Check if token belongs to a Student
        // -------------------------------------------------------------
        $enrollment = ClassStudent::with(['student', 'classRoom'])
            ->whereHas('classRoom', function ($q) use ($school) {
                $q->where('school_setting_id', $school->id);
            })
            ->where(function ($q) use ($token) {
                $q->where('qr_token', $token)
                    ->orWhere('student_class_uid', $token)
                    ->orWhereHas('student', function ($sq) use ($token) {
                        $sq->where('admission_number', $token);
                    });
            })
            ->first();

        // If not found in school class rooms, check across all enrollments if school is default
        if (! $enrollment) {
            $enrollment = ClassStudent::with(['student', 'classRoom'])
                ->where(function ($q) use ($token) {
                    $q->where('qr_token', $token)
                        ->orWhere('student_class_uid', $token)
                        ->orWhereHas('student', function ($sq) use ($token) {
                            $sq->where('admission_number', $token);
                        });
                })
                ->first();
        }

        if ($enrollment) {
            $student = $enrollment->student;
            $class = $enrollment->classRoom;

            if ($enrollment->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'category' => 'student',
                    'status' => 'suspended',
                    'message' => "Student {$student->full_name} is not currently active in class {$class->name}.",
                ], 403);
            }

            // Check if student attendance is already marked today for this class
            $existing = Attendance::where('class_id', $class->id)
                ->where('student_id', $student->id)
                ->whereDate('date', $today)
                ->first();

            $alreadyMarked = false;
            if (! $existing) {
                Attendance::create([
                    'class_id' => $class->id,
                    'student_id' => $student->id,
                    'date' => $today,
                    'check_in_time' => $currentTime,
                    'status' => 'present',
                    'marked_by' => $user->id,
                ]);
            } else {
                $alreadyMarked = true;
            }

            // Check fee payment for the current month
            $currentMonth = Carbon::now()->format('Y-m');
            $hasPaidMonth = Payment::where('student_id', $student->id)
                ->where('class_id', $class->id)
                ->where('payment_month', $currentMonth)
                ->exists();

            $message = $alreadyMarked
                ? "සිසුවාගේ පැමිණීම අද දින දැනටමත් සටහන් කර ඇත ({$class->name})."
                : "සිසුවා {$student->full_name} ({$class->name}) පැමිණීම සාර්ථකව සටහන් විය!";

            return response()->json([
                'success' => true,
                'category' => 'student',
                'action' => $alreadyMarked ? 'already_marked' : 'checked_in',
                'name' => $student->full_name,
                'admission_number' => $student->admission_number,
                'class_name' => $class->name,
                'grade_level' => $class->grade_level ?: 'Preschool',
                'photo_url' => $student->photo_path ? route('storage.students', basename($student->photo_path)) : null,
                'fee_status' => $hasPaidMonth ? 'paid' : 'pending',
                'monthly_fee' => (float) $class->monthly_fee,
                'check_in_time' => Carbon::now()->format('h:i A'),
                'message' => $message,
            ]);
        }

        // -------------------------------------------------------------
        // 3. Not Found
        // -------------------------------------------------------------
        return response()->json([
            'success' => false,
            'category' => 'unknown',
            'status' => 'not_found',
            'message' => 'QR කේතය හඳුනාගත නොහැක. කරුණාකර වලංගු ගුරු හෝ සිසු කාඩ්පතක් ස්කෑන් කරන්න. (QR Code not recognized in preschool system)',
        ], 404);
    }

    /**
     * Show printable Teacher QR Pass.
     */
    public function showTeacherPass(string $qr_token): View
    {
        $teacher = User::where('qr_token', $qr_token)->firstOrFail();
        $school = SchoolSetting::get($teacher);

        return view('public.teacher-pass', [
            'teacher' => $teacher,
            'school' => $school,
            'qrToken' => $teacher->qr_token,
        ]);
    }
}
