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

        $user = auth()->user();
        if (!$user) {
            abort(403, 'Unauthorized access.');
        }

        $allowedRoles = ['Admin', 'Kepala Sekolah'];
        if (!$user->hasAnyRole($allowedRoles)) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini. Hanya Admin dan Kepala Sekolah yang dapat mengakses pengaturan sistem.');
        }

        return $next($request);
    }
}
