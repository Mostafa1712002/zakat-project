<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\SalesRep;
use Symfony\Component\HttpFoundation\Response;

class SalesRepAccessMiddleware
{
    /**
     * الصفحات المسموح للمندوب الوصول إليها
     */
    protected array $allowedRoutes = [
        'sales-rep.*',      // لوحة تحكم المندوب
        'profile.*',        // الملف الشخصي
        'logout',           // تسجيل الخروج
        'sales.create',     // إنشاء فاتورة بيع
        'sales.store',      // حفظ فاتورة بيع
        'sales.show',       // عرض فاتورة
        'sales.edit',       // تعديل فاتورة
        'sales.update',     // حفظ تعديل فاتورة
        'sales.confirm',    // تأكيد فاتورة بيع
        'sales.get-stock',  // جلب المخزون المتاح
        'customers.index',  // عرض العملاء
        'customers.create', // إضافة عميل
        'customers.store',  // حفظ عميل
        'customers.show',   // عرض عميل
        'customers.edit',   // تعديل عميل
        'customers.update', // حفظ تعديل عميل
        'customers.collect.form', // نموذج التحصيل
        'customers.collect', // تحصيل من عميل
        'sales.index',      // عرض قائمة المبيعات
        'products.index',   // عرض المنتجات (للاختيار في الفاتورة)
        'products.show',    // عرض منتج
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // تجاوز الـ SuperAdmin
        if (!$user || $user->isSuperAdmin()) {
            return $next($request);
        }

        // التحقق من أن المستخدم مندوب فقط
        if ($user->isSalesRep()) {
            // التحقق من وجود سجل مندوب مرتبط
            $salesRep = SalesRep::where('user_id', $user->id)->first();

            if (!$salesRep) {
                // المستخدم له دور مندوب لكن غير مربوط
                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => 'لم يتم ربط حسابك بمندوب مبيعات بعد. يرجى التواصل مع الإدارة.'
                    ], 403);
                }

                // عرض صفحة خطأ
                return response()->view('errors.sales-rep-not-linked', [], 403);
            }

            // التحقق من الـ Route المطلوب
            $currentRoute = $request->route()->getName();

            // السماح بالصفحات المحددة فقط
            foreach ($this->allowedRoutes as $allowedRoute) {
                if ($request->routeIs($allowedRoute)) {
                    return $next($request);
                }
            }

            // رفض الوصول لأي صفحة أخرى
            if ($request->expectsJson()) {
                return response()->json(['error' => 'غير مصرح لك بالوصول لهذه الصفحة'], 403);
            }

            abort(403, 'غير مصرح لك بالوصول لهذه الصفحة');
        }

        return $next($request);
    }
}
