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

        // Admin can access both admin and teacher routes for oversight if needed,
        // or strictly follow roles. Let's allow admin full oversight access!
        if ($user->isAdmin() && in_array('teacher', $roles)) {
            return $next($request);
        }

        if (! in_array($user->role, $roles)) {
            abort(403, 'Unauthorized access: your role ('.$user->role.') cannot access this resource.');
        }

        return $next($request);
    }
}
