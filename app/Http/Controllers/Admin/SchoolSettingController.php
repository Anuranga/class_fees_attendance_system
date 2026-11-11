<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SchoolSettingController extends Controller
{
    public function edit(): View
    {
        $school = SchoolSetting::get();

        return view('admin.settings.school', compact('school'));
    }

    public function update(Request $request): RedirectResponse
    {
        $school = SchoolSetting::get();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'motto' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:5120'],
            'vision' => ['nullable', 'string', 'max:3000'],
            'mission' => ['nullable', 'string', 'max:3000'],
            'about_us' => ['nullable', 'string', 'max:5000'],
            'principal_message' => ['nullable', 'string', 'max:5000'],
            'contact_phone' => ['nullable', 'string', 'max:40'],
            'whatsapp_number' => ['nullable', 'string', 'max:40'],
            'contact_email' => ['nullable', 'email', 'max:100'],
            'address' => ['nullable', 'string', 'max:500'],
            'academic_year' => ['nullable', 'string', 'max:30'],
            'is_admission_open' => ['nullable', 'boolean'],
        ]);

        $logoPath = $school->logo_path;
        if ($request->hasFile('logo') && $request->file('logo')->isValid()) {
            $logoPath = $request->file('logo')->store('school', 'public');
        }

        $school->update([
            'name' => $validated['name'],
            'motto' => $validated['motto'] ?? null,
            'logo_path' => $logoPath,
            'vision' => $validated['vision'] ?? null,
            'mission' => $validated['mission'] ?? null,
            'about_us' => $validated['about_us'] ?? null,
            'principal_message' => $validated['principal_message'] ?? null,
            'contact_phone' => $validated['contact_phone'] ?? null,
            'whatsapp_number' => $validated['whatsapp_number'] ?? null,
            'contact_email' => $validated['contact_email'] ?? null,
            'address' => $validated['address'] ?? null,
            'academic_year' => $validated['academic_year'] ?? '2026/2027',
            'is_admission_open' => $request->boolean('is_admission_open'),
        ]);

        return back()->with('success', 'Pre-school details, vision, mission and logo updated successfully!');
    }
}
