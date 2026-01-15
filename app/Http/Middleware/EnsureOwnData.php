<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware للتحقق من أن المندوب يصل فقط لبياناته
 */
class EnsureOwnData
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $model = null): Response
    {
        $user = $request->user();

        // السماح للسوبر أدمن والأدمن
        if ($user && ($user->isSuperAdmin() || $user->hasRole(['admin', 'branch_manager', 'accountant']))) {
            return $next($request);
        }

        // التحقق من المندوب
        if (!$user || !$user->isSalesRep() || !$user->salesRep) {
            abort(403, 'ليس لديك صلاحية للوصول لهذه الصفحة');
        }

        // إذا تم تحديد موديل، التحقق من الصلاحية
        if ($model) {
            $routeModel = $request->route($model);

            if ($routeModel && method_exists($routeModel, 'canCurrentUserAccess')) {
                if (!$routeModel->canCurrentUserAccess()) {
                    abort(403, 'ليس لديك صلاحية للوصول لهذا السجل');
                }
            }
        }

        return $next($request);
    }
}
