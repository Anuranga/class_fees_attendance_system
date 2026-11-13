<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SchoolSetting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PrincipalManagementController extends Controller
{
    /**
     * Display all registered preschools and principals for Super Admin oversight.
     */
    public function index(Request $request): View
    {
        $statusFilter = $request->query('status');

        $query = User::where(function ($q) {
            $q->where('role', 'principal')
                ->orWhere('role', 'admin');
        })
            ->where('email', '!=', 'admin@edutrack.test')
            ->where('email', '!=', 'superadmin@edutrack.test')
            ->with(['school' => function ($q) {
                $q->withCount(['classes', 'staff']);
            }])
            ->latest();

        if ($statusFilter && in_array($statusFilter, ['pending', 'active', 'blocked', 'inactive'])) {
            $query->where('status', $statusFilter);
        }

        $principals = $query->paginate(15);

        $baseCountQuery = function ($status = null) {
            $q = User::where(function ($sub) {
                $sub->where('role', 'principal')
                    ->orWhere('role', 'admin');
            })
                ->where('email', '!=', 'admin@edutrack.test')
                ->where('email', '!=', 'superadmin@edutrack.test');

            if ($status) {
                $q->where('status', $status);
            }

            return $q->count();
        };

        $counts = [
            'all' => $baseCountQuery(),
            'pending' => $baseCountQuery('pending'),
            'active' => $baseCountQuery('active'),
            'blocked' => $baseCountQuery('blocked'),
        ];

        $totalSchools = SchoolSetting::count();
        $totalTeachers = User::where('role', 'teacher')->count();

        return view('super_admin.principals.index', [
            'principals' => $principals,
            'counts' => $counts,
            'statusFilter' => $statusFilter,
            'totalSchools' => $totalSchools,
            'totalTeachers' => $totalTeachers,
        ]);
    }

    /**
     * Approve and activate a pending preschool principal account.
     */
    public function approve(Request $request, User $user): RedirectResponse
    {
        $user->update(['status' => 'active']);

        if ($user->school) {
            $user->school->update(['is_admission_open' => true]);
        }

        $schoolName = $user->school?->name ?: $user->name;

        return back()->with('success', "Pre-School Principal '{$user->name}' for '{$schoolName}' has been approved and activated successfully!");
    }

    /**
     * Temporarily block a preschool principal account.
     */
    public function block(Request $request, User $user): RedirectResponse
    {
        $user->update(['status' => 'blocked']);

        return back()->with('info', "Principal account for '{$user->name}' has been blocked. They will not be able to log in.");
    }

    /**
     * Activate a blocked or inactive preschool principal account.
     */
    public function activate(Request $request, User $user): RedirectResponse
    {
        $user->update(['status' => 'active']);

        return back()->with('success', "Principal account for '{$user->name}' has been activated.");
    }

    /**
     * Store a new preschool principal directly from the Super Admin dashboard.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'preschool_name' => ['required', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:40'],
        ]);

        $schoolName = trim($validated['preschool_name']);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'admin',
            'status' => 'active', // Super admin created accounts are instantly active
            'can_collect_fees' => true,
            'can_manage_uniforms' => true,
            'email_verified_at' => now(),
        ]);

        $school = SchoolSetting::create([
            'user_id' => $user->id,
            'name' => $schoolName,
            'motto' => 'Nurturing Little Minds for a Brighter Tomorrow',
            'slug' => Str::slug($schoolName).'-'.Str::random(4),
            'registration_token' => Str::random(24),
            'contact_email' => $user->email,
            'contact_phone' => $validated['contact_phone'] ?? null,
            'whatsapp_number' => $validated['contact_phone'] ?? null,
            'academic_year' => date('Y').'/'.(date('Y') + 1),
            'is_admission_open' => true,
            'items_to_bring_sinhala' => SchoolSetting::defaultSinhalaItems(),
            'vision' => 'Center of early childhood excellence fostering creativity, joy, and emotional growth.',
            'mission' => 'Safe, nurturing, and joyful early learning environment.',
            'about_us' => "Welcome to {$schoolName}.",
            'principal_message' => "Welcome to {$schoolName}! Every child is full of potential.",
        ]);

        $user->update(['school_setting_id' => $school->id]);

        return back()->with('success', "Pre-School '{$schoolName}' and Principal '{$user->name}' created and activated successfully!");
    }

    /**
     * Delete a principal and their preschool account.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        $name = $user->name;
        $schoolName = $user->school?->name;

        if ($user->school) {
            $user->school->delete();
        }

        $user->delete();

        return back()->with('info', "Principal '{$name}'".($schoolName ? " and Pre-School '{$schoolName}'" : '').' have been removed.');
    }
}

