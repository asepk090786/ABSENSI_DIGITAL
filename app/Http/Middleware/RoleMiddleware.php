<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     * Usage in routes: ->middleware('role:Admin') or 'role:Admin|Guru'
     */
    public function handle(Request $request, Closure $next, $roles = null)
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        if (! $roles) {
            return $next($request);
        }

        $allowed = array_map('trim', explode('|', $roles));
        $user = auth()->user();

        if ($user && $user->hasAnyRole($allowed)) {
            return $next($request);
        }

        abort(403, 'Unauthorized');
    }
}
