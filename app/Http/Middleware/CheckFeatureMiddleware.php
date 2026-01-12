<?php

namespace App\Http\Middleware;

use App\Models\Feature;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckFeatureMiddleware
{
    /**
     * التحقق من أن الميزة مفعلة
     */
    public function handle(Request $request, Closure $next, string $featureName): Response
    {
        if (!Feature::isEnabled($featureName)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'هذه الميزة معطلة حالياً',
                ], 403);
            }

            abort(403, 'هذه الميزة معطلة حالياً. يرجى التواصل مع المسؤول الأعلى لتفعيلها.');
        }

        return $next($request);
    }
}
