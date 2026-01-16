<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminOrKepalaSekolah
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            abort(403, 'Unauthorized access.');
        }

        // Check if user has role
        $user = auth()->user();
        if (!$user->role) {
            abort(403, 'Unauthorized access.');
        }

        // Check if role is Admin or Kepala Sekolah
        $allowedRoles = ['Admin', 'Kepala Sekolah'];
        if (!in_array($user->role->role_name, $allowedRoles)) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini. Hanya Admin dan Kepala Sekolah yang dapat mengakses pengaturan sistem.');
        }

        return $next($request);
    }
}
