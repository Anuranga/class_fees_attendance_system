<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ClassRoom;
use App\Models\ClassStudent;
use App\Models\SchoolSetting;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ApplicationManagementController extends Controller
{
    /**
     * List all applications with status filters.
     */
    public function index(Request $request): View
    {
        $status = $request->query('status', 'all');
        $search = $request->query('search');

        $query = Application::with(['assignedClass', 'student'])->latest();

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('application_number', 'like', "%{$search}%")
                    ->orWhere('child_full_name', 'like', "%{$search}%")
                    ->orWhere('whatsapp_number', 'like', "%{$search}%");
            });
        }

        $applications = $query->paginate(15);
        $counts = [
            'all' => Application::count(),
            'pending' => Application::where('status', 'pending')->count(),
            'approved' => Application::where('status', 'approved')->count(),
            'enrolled' => Application::where('status', 'enrolled')->count(),
            'rejected' => Application::where('status', 'rejected')->count(),
        ];

        return view('admin.applications.index', compact('applications', 'status', 'search', 'counts'));
    }

    /**
     * Show single application details for Principal review.
     */
    public function show(Application $application): View
    {
        $application->load(['assignedClass', 'student.classes', 'reviewer']);
        $classes = ClassRoom::orderBy('name')->get();
        $school = SchoolSetting::get();

        return view('admin.applications.show', compact('application', 'classes', 'school'));
    }

    /**
     * Update application status / admin notes.
     */
    public function updateStatus(Request $request, Application $application): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,interview_scheduled,approved,rejected'],
            'admin_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $application->update([
            'status' => $validated['status'],
            'admin_notes' => $validated['admin_notes'] ?? $application->admin_notes,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', "Application {$application->application_number} updated to '{$validated['status']}'.");
    }

    /**
     * Approve and automatically enroll the child into a class, creating the Student and QR Pass.
     */
    public function enroll(Request $request, Application $application): RedirectResponse
    {
        $validated = $request->validate([
            'class_id' => ['required', 'exists:classes,id'],
            'admission_number' => ['nullable', 'string', 'max:50'],
        ]);

        $classRoom = ClassRoom::findOrFail($validated['class_id']);

        // 1. Create or link Student record
        $student = $application->student;
        if (! $student) {
            $admNumber = ! empty($validated['admission_number'])
                ? trim($validated['admission_number'])
                : Student::generateAdmissionNumber();

            $guardianName = $application->father_name ?: ($application->mother_name ?: 'Parent');
            $guardianPhone = $application->whatsapp_number;

            $student = Student::create([
                'admission_number' => $admNumber,
                'full_name' => $application->child_full_name,
                'date_of_birth' => $application->date_of_birth,
                'gender' => $application->gender,
                'blood_group' => $application->blood_group,
                'allergies_medical_notes' => $application->medical_notes,
                'photo_path' => $application->photo_path,
                'contact_number' => $application->whatsapp_number,
                'guardian_name' => $guardianName,
                'guardian_phone' => $guardianPhone,
                'emergency_contact_name' => $application->mother_name ?: $application->father_name,
                'emergency_contact_phone' => $application->mother_phone ?: $application->father_phone,
                'authorized_pickup_persons' => $application->authorized_pickup_persons,
                'home_address' => $application->home_address,
            ]);
        }

        // 2. Enroll child in class
        $enrollment = ClassStudent::firstOrCreate([
            'class_id' => $classRoom->id,
            'student_id' => $student->id,
        ], [
            'student_class_uid' => ClassStudent::generateUid($classRoom->id, $student->id),
            'qr_token' => ClassStudent::generateQrToken(),
            'joined_via' => 'link',
            'status' => 'active',
        ]);

        // 3. Update application state
        $application->update([
            'status' => 'enrolled',
            'assigned_class_id' => $classRoom->id,
            'created_student_id' => $student->id,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', "Child successfully enrolled! Generated Admission No: {$student->admission_number}. QR Pass is ready.");
    }
}
