<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassRoom;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AssistantController extends Controller
{
    /**
     * Display assistants belonging to the current teacher.
     */
    public function index(): View
    {
        $teacher = Auth::user();

        if ($teacher->isAssistant()) {
            abort(403, 'Unauthorized. Assistant accounts cannot manage other assistants.');
        }

        $classes = ClassRoom::where('teacher_id', $teacher->id)
            ->orderBy('name')
            ->get();

        $assistants = User::where('role', 'assistant')
            ->where('teacher_id', $teacher->id)
            ->with('assignedClasses')
            ->withCount('attendancesMarked')
            ->latest()
            ->get();

        return view('teacher.assistants.index', compact('assistants', 'classes'));
    }

    /**
     * Create a new assistant account for the current teacher and assign selected classes.
     */
    public function store(Request $request): RedirectResponse
    {
        $teacher = Auth::user();

        if ($teacher->isAssistant()) {
            abort(403, 'Unauthorized.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'class_ids' => ['nullable', 'array'],
            'class_ids.*' => ['integer', 'exists:classes,id'],
        ]);

        // Only allow assigning classes owned by this teacher
        $validClassIds = ClassRoom::where('teacher_id', $teacher->id)
            ->whereIn('id', $validated['class_ids'] ?? [])
            ->pluck('id')
            ->toArray();

        $assistant = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'assistant',
            'status' => 'active',
            'teacher_id' => $teacher->id,
            'email_verified_at' => now(),
        ]);

        $assistant->assignedClasses()->sync($validClassIds);

        return back()->with('success', "Teaching Assistant account for '{$assistant->name}' created and assigned to ".count($validClassIds).' class(es)!');
    }

    /**
     * Update the classes assigned to an assistant.
     */
    public function updateClasses(Request $request, User $assistant): RedirectResponse
    {
        $this->authorizeOwner($assistant);

        $validated = $request->validate([
            'class_ids' => ['nullable', 'array'],
            'class_ids.*' => ['integer', 'exists:classes,id'],
        ]);

        $teacherId = $assistant->teacher_id ?: Auth::user()->effectiveTeacherId();

        $validClassIds = ClassRoom::where('teacher_id', $teacherId)
            ->whereIn('id', $validated['class_ids'] ?? [])
            ->pluck('id')
            ->toArray();

        $assistant->assignedClasses()->sync($validClassIds);

        return back()->with('success', "Assigned classes updated for '{$assistant->name}' (".count($validClassIds).' class(es) assigned).');
    }

    /**
     * Toggle active / inactive status of an assistant.
     */
    public function toggleStatus(User $assistant): RedirectResponse
    {
        $this->authorizeOwner($assistant);

        $newStatus = ($assistant->status === 'active') ? 'inactive' : 'active';
        $assistant->update(['status' => $newStatus]);

        return back()->with('success', "Assistant '{$assistant->name}' has been marked as {$newStatus}.");
    }

    /**
     * Delete an assistant account.
     */
    public function destroy(User $assistant): RedirectResponse
    {
        $this->authorizeOwner($assistant);

        $name = $assistant->name;
        $assistant->delete();

        return back()->with('info', "Assistant account for '{$name}' has been removed.");
    }

    protected function authorizeOwner(User $assistant): void
    {
        $teacher = Auth::user();

        if ($assistant->teacher_id !== $teacher->id && ! $teacher->isAdmin()) {
            abort(403, 'Unauthorized. You do not manage this assistant.');
        }
    }
}
