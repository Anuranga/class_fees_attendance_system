<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\SchoolSetting;
use App\Models\TeacherLeave;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LeaveController extends Controller
{
    /**
     * Display teacher's leave history and application form.
     */
    public function index(): View
    {
        $teacher = Auth::user();
        $school = SchoolSetting::get($teacher);

        $leaves = TeacherLeave::where('teacher_id', $teacher->id)
            ->with('reviewer')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $currentYear = Carbon::now()->year;
        $totalApplied = TeacherLeave::where('teacher_id', $teacher->id)
            ->whereYear('start_date', $currentYear)
            ->count();

        $totalApproved = TeacherLeave::where('teacher_id', $teacher->id)
            ->where('status', 'approved')
            ->whereYear('start_date', $currentYear)
            ->count();

        $pendingCount = TeacherLeave::where('teacher_id', $teacher->id)
            ->where('status', 'pending')
            ->count();

        return view('teacher.leaves.index', [
            'leaves' => $leaves,
            'totalApplied' => $totalApplied,
            'totalApproved' => $totalApproved,
            'pendingCount' => $pendingCount,
            'school' => $school,
        ]);
    }

    /**
     * Store a new teacher leave application.
     */
    public function store(Request $request): RedirectResponse
    {
        $teacher = Auth::user();
        $school = SchoolSetting::get($teacher);

        $validated = $request->validate([
            'leave_type' => ['required', 'string', 'in:Casual,Medical,Annual,Maternity,Duty,Other'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        TeacherLeave::create([
            'school_setting_id' => $school->id,
            'teacher_id' => $teacher->id,
            'leave_type' => $validated['leave_type'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'reason' => trim($validated['reason']),
            'status' => 'pending',
        ]);

        return redirect()->route('teacher.leaves.index')
            ->with('success', 'Leave request submitted successfully. The principal has been notified.');
    }
}
