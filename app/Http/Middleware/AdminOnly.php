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

        // If user has admin-level roles, allow access
        if ($user && $user->hasRole(['admin', 'branch_manager', 'accountant'])) {
            return $next($request);
        }

        // Block employees and sales reps without admin roles
        if ($user && ($user->isEmployee() || $user->isSalesRep())) {
            abort(403, 'ليس لديك صلاحية للوصول لهذه الصفحة');
        }

        return $next($request);
    }
}
