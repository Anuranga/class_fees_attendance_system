<?php

namespace App\Http\Controllers;

use App\Models\SchoolSetting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return Auth::user()->isAdmin()
                ? redirect()->route('admin.dashboard')
                : redirect()->route('teacher.dashboard');
        }

        return view('auth.login');
    }

    public function showRegister(): View|RedirectResponse
    {
        if (Auth::check()) {
            return Auth::user()->isAdmin()
                ? redirect()->route('admin.dashboard')
                : redirect()->route('teacher.dashboard');
        }

        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'role' => ['nullable', 'string', 'in:teacher,principal'],
            'preschool_name' => ['nullable', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:40'],
        ]);

        $role = $validated['role'] ?? 'teacher';

        if ($role === 'principal') {
            $schoolName = ! empty($validated['preschool_name']) ? trim($validated['preschool_name']) : ($validated['name']."'s Pre-School");

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'admin',
                'status' => 'active',
                'can_collect_fees' => true,
                'can_manage_uniforms' => true,
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
                'vision' => 'To be a center of early childhood educational excellence where every child discovers joy in learning, creativity, and self-confidence.',
                'mission' => 'We provide a loving, safe, and stimulating environment guided by holistic early learning methodologies.',
                'about_us' => "Welcome to {$schoolName}. We offer quality pre-school education and child care.",
                'principal_message' => "Welcome to {$schoolName}! Every child is unique and full of potential.",
            ]);

            $user->update(['school_setting_id' => $school->id]);

            return redirect()->route('login')->with('success', "Pre-School '{$schoolName}' registered successfully! You can now log in as Principal.");
        }

        // Registering as Teacher
        $hasExistingPrincipal = User::whereIn('role', ['admin', 'principal'])->exists();
        $status = ! $hasExistingPrincipal ? 'active' : 'pending';

        $firstSchool = SchoolSetting::first();

        User::create([
            'school_setting_id' => $firstSchool?->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'teacher',
            'status' => $status,
            'can_collect_fees' => false,
            'can_manage_uniforms' => false,
        ]);

        $successMsg = ($status === 'active')
            ? 'Teacher account created and activated successfully! You can now log in.'
            : 'Your account has been registered successfully! It is currently pending administrator verification before sign in.';

        return redirect()->route('login')->with('success', $successMsg);
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();

            if (! $user->isActive()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                $message = match ($user->status) {
                    'pending' => 'Your teacher account is pending administrator approval. Please wait for an administrator to approve and activate your account.',
                    'blocked' => 'Your account has been temporarily suspended/blocked. Please contact the administrator.',
                    default => 'Your account is currently inactive. Please contact the administrator or your instructor.',
                };

                return back()->withErrors(['email' => $message])->onlyInput('email');
            }

            if ($user->isAssistant() && $user->teacher && ! $user->teacher->isActive()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'Your instructor account is currently inactive or suspended.',
                ])->onlyInput('email');
            }

            $request->session()->regenerate();

            if ($user->isAdmin()) {
                return redirect()->intended(route('admin.dashboard'))
                    ->with('success', 'Welcome back, Administrator '.$user->name.'!');
            }

            return redirect()->intended(route('teacher.dashboard'))
                ->with('success', 'Welcome back, '.$user->name.' ('.ucfirst($user->role).')!');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('info', 'You have been logged out.');
    }
}
