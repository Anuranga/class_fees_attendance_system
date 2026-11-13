<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassRoom;
use App\Models\ClassStudent;
use App\Models\Payment;
use App\Models\SchoolSetting;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Admin Global Overview Dashboard.
     */
    public function index(): View
    {
        $school = SchoolSetting::get();
        $principal = Auth::user();

        // Classes belonging to this preschool
        $classesQuery = ClassRoom::where(function ($q) use ($school, $principal) {
            $q->where('school_setting_id', $school->id)
                ->orWhere('teacher_id', $principal->id);
        });
        $classIds = $classesQuery->pluck('id')->toArray();

        // Teachers belonging to this preschool (or pending unassigned applicants)
        $teachersQuery = User::where('role', 'teacher')
            ->where(function ($q) use ($school, $principal) {
                $q->where('school_setting_id', $school->id)
                    ->orWhere('teacher_id', $principal->id)
                    ->orWhere(function ($sub) {
                        $sub->whereNull('school_setting_id')->whereNull('teacher_id');
                    });
            });

        $totalTeachers = $teachersQuery->count();
        $pendingTeachersCount = (clone $teachersQuery)->where('status', 'pending')->count();
        $pendingTeachers = (clone $teachersQuery)->where('status', 'pending')->latest()->limit(5)->get();
        $totalClasses = count($classIds);

        $totalEnrollments = ClassStudent::whereIn('class_id', $classIds)->where('status', 'active')->count();
        $totalStudents = ClassStudent::whereIn('class_id', $classIds)->distinct('student_id')->count('student_id');

        $allTimeRevenue = Payment::whereIn('class_id', $classIds)->sum('amount');
        $currentMonth = Carbon::now()->format('Y-m');
        $currentMonthRevenue = Payment::whereIn('class_id', $classIds)->where('payment_month', $currentMonth)->sum('amount');

        // Past 6 months system-wide revenue trend for this preschool
        $monthsTrend = [];
        $monthsLabels = [];
        $monthlyRevenues = [];

        for ($i = 5; $i >= 0; $i--) {
            $monthObj = Carbon::now()->subMonths($i);
            $mKey = $monthObj->format('Y-m');
            $monthsTrend[] = $mKey;
            $monthsLabels[] = $monthObj->format('M Y');

            $sum = Payment::whereIn('class_id', $classIds)->where('payment_month', $mKey)->sum('amount');
            $monthlyRevenues[] = (float) $sum;
        }

        // Teachers performance table
        $teachers = $teachersQuery
            ->withCount(['classes' => function ($q) use ($classIds) {
                $q->whereIn('id', $classIds);
            }])
            ->with(['classes' => function ($q) use ($classIds) {
                $q->whereIn('id', $classIds)->withCount('enrollments');
            }])
            ->get()
            ->map(function ($teacher) use ($currentMonth) {
                $teacherClassIds = $teacher->classes->pluck('id')->toArray();
                $teacher->total_students = ClassStudent::whereIn('class_id', $teacherClassIds)
                    ->where('status', 'active')
                    ->distinct('student_id')
                    ->count('student_id');

                $teacher->current_month_revenue = Payment::where('teacher_id', $teacher->id)
                    ->where('payment_month', $currentMonth)
                    ->sum('amount');

                $teacher->total_revenue = Payment::where('teacher_id', $teacher->id)->sum('amount');

                return $teacher;
            });

        // Recent system-wide payments
        $recentPayments = Payment::whereIn('class_id', $classIds)
            ->with(['teacher', 'student', 'classRoom'])
            ->latest()
            ->limit(8)
            ->get();

        return view('admin.dashboard', [
            'totalTeachers' => $totalTeachers,
            'pendingTeachersCount' => $pendingTeachersCount,
            'pendingTeachers' => $pendingTeachers,
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
            'school' => $school,
        ]);
    }

    /**
     * Teacher management page with status filtering.
     */
    public function teachers(Request $request): View
    {
        $school = SchoolSetting::get();
        $principal = Auth::user();
        $statusFilter = $request->query('status');

        $query = User::where('role', 'teacher')
            ->where(function ($q) use ($school, $principal) {
                $q->where('school_setting_id', $school->id)
                    ->orWhere('teacher_id', $principal->id)
                    ->orWhere(function ($sub) {
                        $sub->whereNull('school_setting_id')->whereNull('teacher_id');
                    });
            })
            ->withCount('classes')
            ->with([
                'classes' => function ($q) {
                    $q->withCount('enrollments');
                },
                'assistants',
            ])
            ->latest();

        if ($statusFilter && in_array($statusFilter, ['pending', 'active', 'inactive', 'blocked'])) {
            $query->where('status', $statusFilter);
        }

        $teachers = $query->get();

        $scopedTeacherFilter = function ($q) use ($school, $principal) {
            $q->where('school_setting_id', $school->id)
                ->orWhere('teacher_id', $principal->id)
                ->orWhere(function ($sub) {
                    $sub->whereNull('school_setting_id')->whereNull('teacher_id');
                });
        };

        $counts = [
            'all' => User::where('role', 'teacher')->where($scopedTeacherFilter)->count(),
            'pending' => User::where('role', 'teacher')->where('status', 'pending')->where($scopedTeacherFilter)->count(),
            'active' => User::where('role', 'teacher')->where('status', 'active')->where($scopedTeacherFilter)->count(),
            'blocked' => User::where('role', 'teacher')->where('status', 'blocked')->where($scopedTeacherFilter)->count(),
            'inactive' => User::where('role', 'teacher')->where('status', 'inactive')->where($scopedTeacherFilter)->count(),
        ];

        return view('admin.teachers.index', compact('teachers', 'counts', 'statusFilter'));
    }

    /**
     * Store new teacher.
     */
    public function storeTeacher(Request $request): RedirectResponse
    {
        $school = SchoolSetting::get();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $teacher = User::create([
            'school_setting_id' => $school->id,
            'teacher_id' => Auth::id(),
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'teacher',
            'status' => 'active',
            'can_collect_fees' => $request->boolean('can_collect_fees', true),
            'can_manage_uniforms' => $request->boolean('can_manage_uniforms', true),
            'email_verified_at' => now(),
        ]);

        return back()->with('success', "Teacher account for {$teacher->name} created successfully!");
    }

    /**
     * Approve and activate a teacher account.
     */
    public function approveUser(User $user): RedirectResponse
    {
        $user->update(['status' => 'active']);

        return back()->with('success', "Teacher account for '{$user->name}' has been approved and activated successfully!");
    }

    /**
     * Update user account status (active, inactive, blocked, pending).
     */
    public function updateUserStatus(Request $request, User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot change your own administrator account status.');
        }

        $validated = $request->validate([
            'status' => ['required', 'in:active,inactive,blocked,pending'],
        ]);

        $user->update(['status' => $validated['status']]);

        $statusLabel = match ($validated['status']) {
            'pending' => 'Pending Approval',
            'blocked' => 'Temporarily Blocked',
            'inactive' => 'Inactive',
            default => 'Active',
        };

        return back()->with('success', "Status of {$user->name} ({$user->role}) has been updated to {$statusLabel}.");
    }

    /**
     * Delete an instructor or assistant account.
     */
    public function destroyUser(User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot delete your own administrator account.');
        }

        $name = $user->name;
        $role = ucfirst($user->role);
        $user->delete();

        return back()->with('info', "{$role} account for '{$name}' has been deleted.");
    }

    /**
     * Global Payments view with filters (scoped to this Pre-School).
     */
    public function payments(Request $request): View
    {
        $school = SchoolSetting::get();
        $principal = Auth::user();
        $classIds = ClassRoom::where('school_setting_id', $school->id)
            ->orWhere('teacher_id', $principal->id)
            ->pluck('id')
            ->toArray();

        $query = Payment::whereIn('class_id', $classIds)
            ->with(['teacher', 'classRoom', 'student'])
            ->latest();

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
        $teachers = User::where('role', 'teacher')
            ->where(fn ($q) => $q->where('school_setting_id', $school->id)->orWhere('teacher_id', $principal->id))
            ->get();
        $totalCollected = $query->sum('amount');

        return view('admin.payments.index', [
            'payments' => $payments,
            'teachers' => $teachers,
            'totalCollected' => $totalCollected,
            'filters' => $request->only(['teacher_id', 'month', 'search']),
        ]);
    }

    /**
     * Global Students directory (scoped to this Pre-School).
     */
    public function students(Request $request): View
    {
        $school = SchoolSetting::get();
        $principal = Auth::user();
        $classIds = ClassRoom::where('school_setting_id', $school->id)
            ->orWhere('teacher_id', $principal->id)
            ->pluck('id')
            ->toArray();

        $query = Student::whereHas('classes', function ($q) use ($classIds) {
            $q->whereIn('classes.id', $classIds);
        })
            ->withCount(['enrollments' => function ($q) {
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
