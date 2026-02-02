<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOnly
{
    /**
     * Handle an incoming request.
     * Block access for employee-only users (not admin/super admin)
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // If user is super admin, allow access
        if ($user && $user->isSuperAdmin()) {
            return $next($request);
        }

        // If user is employee only (not admin), deny access
        if ($user && $user->isEmployee() && !$user->hasRole('admin')) {
            abort(403, 'ليس لديك صلاحية للوصول لهذه الصفحة');
        }

        return $next($request);
    }
}
