<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\SchoolSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    /**
     * Show the public online admission application form (designed for WhatsApp distribution).
     */
    public function create(): View
    {
        $school = SchoolSetting::get();

        return view('public.apply', compact('school'));
    }

    /**
     * Store a submitted application.
     */
    public function store(Request $request): RedirectResponse
    {
        $school = SchoolSetting::get();

        if (! $school->is_admission_open) {
            return back()->with('error', 'Online admissions are currently closed for this academic period.');
        }

        $validated = $request->validate([
            'child_full_name' => ['required', 'string', 'max:255'],
            'child_calling_name' => ['nullable', 'string', 'max:100'],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'gender' => ['required', 'in:male,female'],
            'blood_group' => ['nullable', 'string', 'max:10'],
            'target_grade_level' => ['required', 'string', 'max:60'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:10240'],
            'photo_base64' => ['nullable', 'string'],
            'father_name' => ['nullable', 'string', 'max:255'],
            'father_phone' => ['nullable', 'string', 'max:40'],
            'father_occupation' => ['nullable', 'string', 'max:150'],
            'mother_name' => ['nullable', 'string', 'max:255'],
            'mother_phone' => ['nullable', 'string', 'max:40'],
            'mother_occupation' => ['nullable', 'string', 'max:150'],
            'whatsapp_number' => ['required', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:120'],
            'home_address' => ['required', 'string', 'max:1000'],
            'medical_notes' => ['nullable', 'string', 'max:1000'],
            'authorized_pickup_persons' => ['nullable', 'string', 'max:1000'],
        ]);

        // Process photo upload or base64 compression from camera
        $photoPath = null;
        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            $photoPath = $request->file('photo')->store('students', 'public');
        } elseif ($request->filled('photo_base64') && str_starts_with((string) $request->input('photo_base64'), 'data:image/')) {
            $base64Data = (string) $request->input('photo_base64');
            if (preg_match('/^data:image\/(\w+);base64,/', $base64Data, $type)) {
                $data = substr($base64Data, strpos($base64Data, ',') + 1);
                $decoded = base64_decode($data);
                if ($decoded !== false) {
                    $extension = strtolower($type[1]) === 'png' ? 'png' : 'jpg';
                    $filename = 'students/'.Str::random(40).'.'.$extension;
                    Storage::disk('public')->put($filename, $decoded);
                    $photoPath = $filename;
                }
            }
        }

        $appNumber = Application::generateApplicationNumber();

        $application = Application::create([
            'application_number' => $appNumber,
            'child_full_name' => $validated['child_full_name'],
            'child_calling_name' => $validated['child_calling_name'],
            'date_of_birth' => $validated['date_of_birth'],
            'gender' => $validated['gender'],
            'blood_group' => $validated['blood_group'] ?? null,
            'photo_path' => $photoPath,
            'target_grade_level' => $validated['target_grade_level'],
            'father_name' => $validated['father_name'] ?? null,
            'father_phone' => $validated['father_phone'] ?? null,
            'father_occupation' => $validated['father_occupation'] ?? null,
            'mother_name' => $validated['mother_name'] ?? null,
            'mother_phone' => $validated['mother_phone'] ?? null,
            'mother_occupation' => $validated['mother_occupation'] ?? null,
            'whatsapp_number' => trim($validated['whatsapp_number']),
            'email' => $validated['email'] ?? null,
            'home_address' => $validated['home_address'],
            'medical_notes' => $validated['medical_notes'] ?? null,
            'authorized_pickup_persons' => $validated['authorized_pickup_persons'] ?? null,
            'status' => 'pending',
        ]);

        return redirect()->route('public.apply.success', $application->application_number)
            ->with('success', 'Application submitted successfully! Our administration will review your details.');
    }

    /**
     * Show success confirmation with application receipt & WhatsApp share helper.
     */
    public function success(string $application_number): View
    {
        $application = Application::where('application_number', $application_number)->firstOrFail();
        $school = SchoolSetting::get();

        return view('public.apply-success', compact('application', 'school'));
    }

    /**
     * Parent lookup for application status.
     */
    public function status(Request $request): View
    {
        $query = $request->query('query');
        $application = null;
        $school = SchoolSetting::get();

        if ($query) {
            $application = Application::where('application_number', trim($query))
                ->orWhere('whatsapp_number', trim($query))
                ->first();
        }

        return view('public.apply-status', compact('application', 'query', 'school'));
    }
}
