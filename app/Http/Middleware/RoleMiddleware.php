<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! auth()->check()) {
            return redirect()->route('login')->with('error', 'Please log in to continue.');
        }

        $user = auth()->user();

        // 1. Enforce active status
        if (! $user->isActive()) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('error', $user->isBlocked()
                ? 'Your account has been temporarily suspended. Please contact the administrator.'
                : 'Your account is currently inactive.');
        }

        // 2. Enforce parent teacher status for assistants
        if ($user->isAssistant() && $user->teacher && ! $user->teacher->isActive()) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('error', 'Your instructor account is currently inactive or suspended.');
        }

        // 3. Admin has global oversight access
        if ($user->isAdmin() && in_array('teacher', $roles)) {
            return $next($request);
        }

        // 4. Assistants can access teacher workspace routes
        if ($user->isAssistant() && in_array('teacher', $roles)) {
            return $next($request);
        }

        if (! in_array($user->role, $roles)) {
            abort(403, 'Unauthorized access: your role ('.$user->role.') cannot access this resource.');
        }

        return $next($request);
    }
}
