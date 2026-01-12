<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminMiddleware
{
    /**
     * التحقق من أن المستخدم هو مسؤول أعلى
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || !auth()->user()->isSuperAdmin()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'غير مصرح لك بالوصول إلى هذه الصفحة',
                ], 403);
            }

            abort(403, 'غير مصرح لك بالوصول إلى هذه الصفحة. هذه الصفحة متاحة فقط للمسؤول الأعلى.');
        }

        return $next($request);
    }
}
