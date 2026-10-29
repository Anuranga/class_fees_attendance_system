<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
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

        $assistants = User::where('role', 'assistant')
            ->where('teacher_id', $teacher->id)
            ->withCount('attendancesMarked')
            ->latest()
            ->get();

        return view('teacher.assistants.index', compact('assistants'));
    }

    /**
     * Create a new assistant account for the current teacher.
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
        ]);

        $assistant = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'assistant',
            'status' => 'active',
            'teacher_id' => $teacher->id,
            'email_verified_at' => now(),
        ]);

        return back()->with('success', "Teaching Assistant account for '{$assistant->name}' created successfully!");
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
