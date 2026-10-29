<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\ClassRoom;
use App\Models\ClassStudent;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $teacher = $user->effectiveTeacher() ?? $user;
        $teacherId = $user->effectiveTeacherId();

        // Teacher's classes
        $classes = ClassRoom::where('teacher_id', $teacherId)->get();
        $classIds = $classes->pluck('id')->toArray();

        // 1. KPI Cards
        $totalClasses = $classes->count();

        $totalEnrollments = ClassStudent::whereIn('class_id', $classIds)
            ->where('status', 'active')
            ->count();

        $currentMonth = Carbon::now()->format('Y-m');
        $currentMonthRevenue = Payment::where('teacher_id', $teacherId)
            ->where('payment_month', $currentMonth)
            ->sum('amount');

        $todayDate = Carbon::today()->toDateString();
        $todayAttendanceCount = Attendance::whereIn('class_id', $classIds)
            ->where('date', $todayDate)
            ->whereIn('status', ['present', 'late'])
            ->count();

        // 2. Chart 1: Monthly Cash Collection trends over the past 6 to 12 months
        $monthsTrend = [];
        $monthsLabels = [];
        $monthlyCollections = [];

        for ($i = 5; $i >= 0; $i--) {
            $monthObj = Carbon::now()->subMonths($i);
            $mKey = $monthObj->format('Y-m');
            $monthsTrend[] = $mKey;
            $monthsLabels[] = $monthObj->format('M Y');

            $sum = Payment::where('teacher_id', $teacherId)
                ->where('payment_month', $mKey)
                ->sum('amount');
            $monthlyCollections[] = (float) $sum;
        }

        // 3. Chart 2: Paid vs. Unpaid student ratio for active month
        $paidStudentIds = Payment::where('teacher_id', $teacherId)
            ->where('payment_month', $currentMonth)
            ->pluck('student_id')
            ->unique();

        $allEnrolledStudentIds = ClassStudent::whereIn('class_id', $classIds)
            ->where('status', 'active')
            ->pluck('student_id')
            ->unique();

        $paidCount = $allEnrolledStudentIds->intersect($paidStudentIds)->count();
        $unpaidCount = max(0, $allEnrolledStudentIds->count() - $paidCount);

        // 4. Chart 3: Weekly attendance rate percentage per class
        // Past 7 days
        $weekDays = [];
        $weekLabels = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = Carbon::today()->subDays($i);
            $weekDays[] = $d->toDateString();
            $weekLabels[] = $d->format('D, M d');
        }

        $palette = ['#4f46e5', '#06b6d4', '#10b981', '#f59e0b', '#ec4899', '#8b5cf6'];
        $attendanceDatasets = [];

        foreach ($classes as $idx => $cls) {
            $clsEnrollmentCount = ClassStudent::where('class_id', $cls->id)->where('status', 'active')->count();
            $dataPoints = [];

            foreach ($weekDays as $day) {
                if ($clsEnrollmentCount === 0) {
                    $dataPoints[] = 0;

                    continue;
                }
                $attCount = Attendance::where('class_id', $cls->id)
                    ->where('date', $day)
                    ->whereIn('status', ['present', 'late'])
                    ->count();

                $pct = round(($attCount / $clsEnrollmentCount) * 100, 1);
                $dataPoints[] = min(100, $pct);
            }

            $color = $palette[$idx % count($palette)];
            $attendanceDatasets[] = [
                'label' => $cls->name,
                'data' => $dataPoints,
                'borderColor' => $color,
                'backgroundColor' => $color.'20',
                'tension' => 0.35,
                'fill' => true,
            ];
        }

        // Recent 5 payments
        $recentPayments = Payment::with(['student', 'classRoom'])
            ->where('teacher_id', $teacherId)
            ->latest()
            ->limit(5)
            ->get();

        // Recent attendances today
        $recentAttendances = Attendance::with(['student', 'classRoom'])
            ->whereIn('class_id', $classIds)
            ->where('date', $todayDate)
            ->latest()
            ->limit(6)
            ->get();

        return view('teacher.dashboard', [
            'teacher' => $teacher,
            'totalClasses' => $totalClasses,
            'totalEnrollments' => $totalEnrollments,
            'currentMonthRevenue' => $currentMonthRevenue,
            'todayAttendanceCount' => $todayAttendanceCount,
            'currentMonth' => $currentMonth,
            'currentMonthName' => Carbon::now()->format('F Y'),
            'monthsLabels' => $monthsLabels,
            'monthlyCollections' => $monthlyCollections,
            'paidCount' => $paidCount,
            'unpaidCount' => $unpaidCount,
            'weekLabels' => $weekLabels,
            'attendanceDatasets' => $attendanceDatasets,
            'recentPayments' => $recentPayments,
            'recentAttendances' => $recentAttendances,
            'classes' => $classes,
        ]);
    }
}
