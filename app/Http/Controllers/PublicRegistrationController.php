<?php

namespace App\Http\Controllers;

use App\Models\ClassRoom;
use App\Models\ClassStudent;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PublicRegistrationController extends Controller
{
    /**
     * Show the public self-registration form for a class.
     */
    public function showJoinForm(string $registration_token): View
    {
        $classRoom = ClassRoom::with('teacher')->where('registration_token', $registration_token)->firstOrFail();

        return view('public.join', compact('classRoom'));
    }

    /**
     * Handle student self-registration and enrollment.
     */
    public function processJoin(Request $request, string $registration_token): RedirectResponse
    {
        $classRoom = ClassRoom::where('registration_token', $registration_token)->firstOrFail();

        if (! $classRoom->is_registration_open) {
            return back()->with('error', 'Registration for this class is currently closed by the instructor.');
        }

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'contact_number' => ['required', 'string', 'max:30'],
            'guardian_name' => ['required', 'string', 'max:255'],
            'guardian_phone' => ['required', 'string', 'max:30'],
            'admission_number' => ['nullable', 'string', 'max:50'],
        ]);

        // Clean phone numbers for better matching
        $contactNumber = trim($validated['contact_number']);
        $admissionNumber = ! empty($validated['admission_number']) ? trim($validated['admission_number']) : null;

        // Check if student exists by admission number OR contact number
        $student = null;
        if ($admissionNumber) {
            $student = Student::where('admission_number', $admissionNumber)->first();
        }

        if (! $student) {
            $student = Student::where('contact_number', $contactNumber)->first();
        }

        if (! $student) {
            // Create new student
            $student = Student::create([
                'admission_number' => $admissionNumber ?: Student::generateAdmissionNumber(),
                'full_name' => $validated['full_name'],
                'contact_number' => $contactNumber,
                'guardian_name' => $validated['guardian_name'],
                'guardian_phone' => trim($validated['guardian_phone']),
            ]);
        } else {
            // Update details if provided
            $student->update([
                'full_name' => $validated['full_name'],
                'guardian_name' => $validated['guardian_name'],
                'guardian_phone' => trim($validated['guardian_phone']),
            ]);
        }

        // Check if already enrolled in this specific class
        $enrollment = ClassStudent::where('class_id', $classRoom->id)
            ->where('student_id', $student->id)
            ->first();

        if ($enrollment) {
            return redirect()->route('public.pass', $enrollment->qr_token)
                ->with('info', "You are already enrolled in {$classRoom->name}! Here is your existing ID Pass.");
        }

        // Create new enrollment
        $uid = ClassStudent::generateUid($classRoom->id, $student->id);
        $qrToken = ClassStudent::generateQrToken();

        $enrollment = ClassStudent::create([
            'class_id' => $classRoom->id,
            'student_id' => $student->id,
            'student_class_uid' => $uid,
            'qr_token' => $qrToken,
            'joined_via' => 'link',
            'status' => 'active',
        ]);

        return redirect()->route('public.pass', $qrToken)
            ->with('success', "Registration successful! Welcome to {$classRoom->name}. Please download or print your official ID Pass.");
    }

    /**
     * Display printable / downloadable Student ID Pass.
     */
    public function showStudentPass(string $qr_token): View
    {
        $enrollment = ClassStudent::with(['classRoom.teacher', 'student'])
            ->where('qr_token', $qr_token)
            ->firstOrFail();

        // Render QR Code as SVG string
        $qrCodeSvg = QrCode::size(200)
            ->style('round')
            ->margin(1)
            ->generate($enrollment->qr_token);

        return view('public.pass', [
            'enrollment' => $enrollment,
            'classRoom' => $enrollment->classRoom,
            'student' => $enrollment->student,
            'teacher' => $enrollment->classRoom->teacher,
            'qrCodeSvg' => $qrCodeSvg,
        ]);
    }

    /**
     * Public Student lookup for finding and re-printing passes.
     */
    public function lookup(Request $request): View
    {
        $query = $request->query('query');
        $enrollments = collect();

        if ($query) {
            $studentIds = Student::where('admission_number', 'like', "%{$query}%")
                ->orWhere('contact_number', 'like', "%{$query}%")
                ->orWhere('full_name', 'like', "%{$query}%")
                ->pluck('id');

            $enrollments = ClassStudent::with(['classRoom.teacher', 'student'])
                ->whereIn('student_id', $studentIds)
                ->get();
        }

        return view('public.lookup', compact('enrollments', 'query'));
    }
}
