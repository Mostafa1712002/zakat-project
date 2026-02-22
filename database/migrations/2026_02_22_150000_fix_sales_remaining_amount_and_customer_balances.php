<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // إعادة حساب remaining_amount لكل فاتورة بناءً على المدفوعات الفعلية
        // أولاً: حساب إجمالي المدفوعات الفعلية لكل فاتورة من جدول payments
        DB::statement("
            UPDATE sales
            SET paid_amount = COALESCE((
                SELECT SUM(p.amount)
                FROM payments p
                WHERE p.sale_id = sales.id
                  AND p.status = 'completed'
            ), 0)
        ");

        // ثانياً: إعادة حساب remaining_amount
        DB::statement("
            UPDATE sales
            SET remaining_amount = GREATEST(total_amount - paid_amount, 0)
        ");

        // ثالثاً: تحديث حالة الدفع
        DB::statement("
            UPDATE sales
            SET payment_status = CASE
                WHEN paid_amount >= total_amount THEN 'paid'
                WHEN paid_amount > 0 THEN 'partial'
                WHEN due_date IS NOT NULL AND due_date < NOW() THEN 'overdue'
                ELSE 'unpaid'
            END
            WHERE status != 'cancelled'
        ");

        // رابعاً: إعادة حساب أرصدة جميع العملاء
        DB::statement("
            UPDATE customers
            SET current_balance = COALESCE((
                SELECT SUM(s.remaining_amount)
                FROM sales s
                WHERE s.customer_id = customers.id
                  AND s.status != 'cancelled'
                  AND s.payment_status IN ('unpaid', 'partial', 'overdue')
            ), 0)
        ");
    }

    public function down(): void
    {
        // لا يمكن التراجع عن إعادة الحساب
    }
};
