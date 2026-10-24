<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassRoom;
use App\Models\ClassStudent;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Admin Global Overview Dashboard.
     */
    public function index(): View
    {
        $totalTeachers = User::where('role', 'teacher')->count();
        $totalClasses = ClassRoom::count();
        $totalStudents = Student::count();
        $totalEnrollments = ClassStudent::where('status', 'active')->count();

        $allTimeRevenue = Payment::sum('amount');
        $currentMonth = Carbon::now()->format('Y-m');
        $currentMonthRevenue = Payment::where('payment_month', $currentMonth)->sum('amount');

        // Past 6 months system-wide revenue trend
        $monthsTrend = [];
        $monthsLabels = [];
        $monthlyRevenues = [];

        for ($i = 5; $i >= 0; $i--) {
            $monthObj = Carbon::now()->subMonths($i);
            $mKey = $monthObj->format('Y-m');
            $monthsTrend[] = $mKey;
            $monthsLabels[] = $monthObj->format('M Y');

            $sum = Payment::where('payment_month', $mKey)->sum('amount');
            $monthlyRevenues[] = (float) $sum;
        }

        // Teachers performance table
        $teachers = User::where('role', 'teacher')
            ->withCount('classes')
            ->with(['classes' => function ($q) {
                $q->withCount('enrollments');
            }])
            ->get()
            ->map(function ($teacher) use ($currentMonth) {
                $teacher->total_students = ClassStudent::whereIn('class_id', $teacher->classes->pluck('id'))
                    ->where('status', 'active')
                    ->count();

                $teacher->current_month_revenue = Payment::where('teacher_id', $teacher->id)
                    ->where('payment_month', $currentMonth)
                    ->sum('amount');

                $teacher->total_revenue = Payment::where('teacher_id', $teacher->id)->sum('amount');

                return $teacher;
            });

        // Recent system-wide payments
        $recentPayments = Payment::with(['teacher', 'student', 'classRoom'])
            ->latest()
            ->limit(8)
            ->get();

        return view('admin.dashboard', [
            'totalTeachers' => $totalTeachers,
            'totalClasses' => $totalClasses,
            'totalStudents' => $totalStudents,
            'totalEnrollments' => $totalEnrollments,
            'allTimeRevenue' => $allTimeRevenue,
            'currentMonthRevenue' => $currentMonthRevenue,
            'currentMonthName' => Carbon::now()->format('F Y'),
            'monthsLabels' => $monthsLabels,
            'monthlyRevenues' => $monthlyRevenues,
            'teachers' => $teachers,
            'recentPayments' => $recentPayments,
        ]);
    }

    /**
     * Teacher management page.
     */
    public function teachers(): View
    {
        $teachers = User::where('role', 'teacher')
            ->withCount('classes')
            ->with(['classes' => function ($q) {
                $q->withCount('enrollments');
            }])
            ->latest()
            ->get();

        return view('admin.teachers.index', compact('teachers'));
    }

    /**
     * Store new teacher.
     */
    public function storeTeacher(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $teacher = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'teacher',
            'email_verified_at' => now(),
        ]);

        return back()->with('success', "Teacher account for {$teacher->name} created successfully!");
    }

    /**
     * Global Payments view with filters.
     */
    public function payments(Request $request): View
    {
        $query = Payment::with(['teacher', 'classRoom', 'student'])->latest();

        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }

        if ($request->filled('month')) {
            $query->where('payment_month', $request->month);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('receipt_no', 'like', "%{$search}%")
                    ->orWhereHas('student', function ($sq) use ($search) {
                        $sq->where('full_name', 'like', "%{$search}%")
                            ->orWhere('admission_number', 'like', "%{$search}%");
                    });
            });
        }

        $payments = $query->paginate(20)->withQueryString();
        $teachers = User::where('role', 'teacher')->get();
        $totalCollected = $query->sum('amount');

        return view('admin.payments.index', [
            'payments' => $payments,
            'teachers' => $teachers,
            'totalCollected' => $totalCollected,
            'filters' => $request->only(['teacher_id', 'month', 'search']),
        ]);
    }

    /**
     * Global Students directory.
     */
    public function students(Request $request): View
    {
        $query = Student::withCount(['enrollments' => function ($q) {
            $q->where('status', 'active');
        }])->with('classes.teacher')->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('admission_number', 'like', "%{$search}%")
                    ->orWhere('contact_number', 'like', "%{$search}%")
                    ->orWhere('guardian_phone', 'like', "%{$search}%");
            });
        }

        $students = $query->paginate(20)->withQueryString();

        return view('admin.students.index', compact('students'));
    }
}
