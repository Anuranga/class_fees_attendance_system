<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassRoom;
use App\Models\SchoolSetting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ClassController extends Controller
{
    /**
     * Display a listing of preschool classes for the current school.
     */
    public function index(Request $request): View
    {
        $school = SchoolSetting::get(Auth::user());
        $principal = Auth::user();

        $classes = ClassRoom::where('school_setting_id', $school->id)
            ->with(['teacher', 'assistants'])
            ->withCount(['students as active_students_count' => function ($q) {
                $q->where('class_student.status', 'active');
            }])
            ->orderBy('name')
            ->get();

        // Teachers available in this school to assign
        $teachers = User::where('role', 'teacher')
            ->where(function ($q) use ($school, $principal) {
                $q->where('school_setting_id', $school->id)
                    ->orWhere('teacher_id', $principal->id);
            })
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('admin.classes.index', [
            'school' => $school,
            'classes' => $classes,
            'teachers' => $teachers,
        ]);
    }

    /**
     * Store a newly created class in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $school = SchoolSetting::get(Auth::user());

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'grade_level' => ['nullable', 'string', 'max:100'],
            'monthly_fee' => ['required', 'numeric', 'min:0'],
            'fee_type' => ['required', 'in:monthly,daily,free'],
            'teacher_id' => ['nullable', 'exists:users,id'],
        ]);

        $teacherId = $validated['teacher_id'] ?: Auth::id();

        ClassRoom::create([
            'school_setting_id' => $school->id,
            'teacher_id' => $teacherId,
            'name' => trim($validated['name']),
            'grade_level' => $validated['grade_level'] ? trim($validated['grade_level']) : 'Preschool',
            'fee_type' => $validated['fee_type'],
            'monthly_fee' => $validated['monthly_fee'],
            'registration_token' => Str::random(24),
            'is_registration_open' => true,
        ]);

        return redirect()->route('admin.classes.index')
            ->with('success', "Class '{$validated['name']}' created successfully!");
    }

    /**
     * Update the specified class in storage.
     */
    public function update(Request $request, ClassRoom $class): RedirectResponse
    {
        $school = SchoolSetting::get(Auth::user());

        if ($class->school_setting_id && $class->school_setting_id !== $school->id) {
            abort(403, 'Unauthorized access to this class.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'grade_level' => ['nullable', 'string', 'max:100'],
            'monthly_fee' => ['required', 'numeric', 'min:0'],
            'fee_type' => ['required', 'in:monthly,daily,free'],
            'teacher_id' => ['nullable', 'exists:users,id'],
            'is_registration_open' => ['nullable', 'boolean'],
        ]);

        $class->update([
            'name' => trim($validated['name']),
            'grade_level' => $validated['grade_level'] ? trim($validated['grade_level']) : $class->grade_level,
            'fee_type' => $validated['fee_type'],
            'monthly_fee' => $validated['monthly_fee'],
            'teacher_id' => $validated['teacher_id'] ?: $class->teacher_id,
            'is_registration_open' => $request->has('is_registration_open'),
        ]);

        return redirect()->route('admin.classes.index')
            ->with('success', "Class '{$class->name}' updated successfully!");
    }

    /**
     * Remove the specified class from storage.
     */
    public function destroy(ClassRoom $class): RedirectResponse
    {
        $school = SchoolSetting::get(Auth::user());

        if ($class->school_setting_id && $class->school_setting_id !== $school->id) {
            abort(403, 'Unauthorized access to this class.');
        }

        $enrolledCount = $class->enrollments()->count();
        if ($enrolledCount > 0) {
            return back()->with('error', "Cannot delete class '{$class->name}' because it contains {$enrolledCount} enrolled student(s). Please transfer or remove students first.");
        }

        $name = $class->name;
        $class->delete();

        return redirect()->route('admin.classes.index')
            ->with('success', "Class '{$name}' deleted successfully.");
    }
}
