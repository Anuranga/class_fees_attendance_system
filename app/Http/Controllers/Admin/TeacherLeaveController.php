<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolSetting;
use App\Models\TeacherAttendance;
use App\Models\TeacherLeave;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TeacherLeaveController extends Controller
{
    /**
     * Display all teacher leaves and today's teacher attendance overview.
     */
    public function index(Request $request): View
    {
        $school = SchoolSetting::get(Auth::user());
        $status = $request->query('status');
        $tab = $request->query('tab', 'leaves'); // 'leaves' or 'attendance'

        $leavesQuery = TeacherLeave::where('school_setting_id', $school->id)
            ->with(['teacher', 'reviewer'])
            ->orderBy('created_at', 'desc');

        if ($status && in_array($status, ['pending', 'approved', 'rejected'])) {
            $leavesQuery->where('status', $status);
        }

        $leaves = $leavesQuery->paginate(15);

        $counts = [
            'all' => TeacherLeave::where('school_setting_id', $school->id)->count(),
            'pending' => TeacherLeave::where('school_setting_id', $school->id)->where('status', 'pending')->count(),
            'approved' => TeacherLeave::where('school_setting_id', $school->id)->where('status', 'approved')->count(),
            'rejected' => TeacherLeave::where('school_setting_id', $school->id)->where('status', 'rejected')->count(),
        ];

        // Today's teacher attendance records
        $today = Carbon::today()->toDateString();
        $teacherAttendances = TeacherAttendance::where('school_setting_id', $school->id)
            ->whereDate('date', $today)
            ->with(['teacher', 'markedBy'])
            ->orderBy('check_in_time', 'desc')
            ->get();

        return view('admin.teachers.leaves', [
            'school' => $school,
            'leaves' => $leaves,
            'counts' => $counts,
            'statusFilter' => $status,
            'tab' => $tab,
            'teacherAttendances' => $teacherAttendances,
            'todayDate' => Carbon::today()->format('F d, Y'),
        ]);
    }

    /**
     * Approve a teacher leave request.
     */
    public function approve(Request $request, TeacherLeave $leave): RedirectResponse
    {
        $school = SchoolSetting::get(Auth::user());

        if ($leave->school_setting_id !== $school->id) {
            abort(403);
        }

        $request->validate([
            'admin_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $leave->update([
            'status' => 'approved',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => Carbon::now(),
            'admin_notes' => $request->input('admin_notes') ?: 'Approved by Principal',
        ]);

        return back()->with('success', "Leave request for {$leave->teacher->name} has been approved.");
    }

    /**
     * Reject a teacher leave request.
     */
    public function reject(Request $request, TeacherLeave $leave): RedirectResponse
    {
        $school = SchoolSetting::get(Auth::user());

        if ($leave->school_setting_id !== $school->id) {
            abort(403);
        }

        $request->validate([
            'admin_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $leave->update([
            'status' => 'rejected',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => Carbon::now(),
            'admin_notes' => $request->input('admin_notes') ?: 'Declined by Principal',
        ]);

        return back()->with('info', "Leave request for {$leave->teacher->name} has been rejected.");
    }
}
