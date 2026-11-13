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
            if (Auth::user()->isSuperAdmin()) {
                return redirect()->route('super_admin.principals.index');
            }

            return Auth::user()->isAdmin()
                ? redirect()->route('admin.dashboard')
                : redirect()->route('teacher.dashboard');
        }

        return view('auth.login');
    }

    public function showRegister(): View|RedirectResponse
    {
        if (Auth::check()) {
            if (Auth::user()->isSuperAdmin()) {
                return redirect()->route('super_admin.principals.index');
            }

            return Auth::user()->isAdmin()
                ? redirect()->route('admin.dashboard')
                : redirect()->route('teacher.dashboard');
        }

        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'preschool_name' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'contact_phone' => ['nullable', 'string', 'max:40'],
        ]);

        $schoolName = trim($validated['preschool_name']);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'admin',
            'status' => 'pending', // Pending Super Admin verification!
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

        return redirect()->route('login')->with('success', "Pre-School '{$schoolName}' and Principal account registered successfully! Your account is pending Super Admin review and approval before you can sign in.");
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
                    'pending' => $user->isPrincipal()
                        ? 'Your Pre-School Principal account is pending review and approval by the Super Administrator. You will be able to log in once your school is activated.'
                        : 'Your teacher account is pending administrator approval. Please wait for your school Principal to approve and activate your account.',
                    'blocked' => 'Your account has been temporarily suspended/blocked. Please contact the administrator.',
                    default => 'Your account is currently inactive. Please contact the administrator.',
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

            if ($user->isSuperAdmin()) {
                return redirect()->intended(route('super_admin.principals.index'))
                    ->with('success', 'Welcome, Super Administrator!');
            }

            if ($user->isAdmin()) {
                return redirect()->intended(route('admin.dashboard'))
                    ->with('success', 'Welcome back, Principal '.$user->name.'!');
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
