<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('sales') || !Schema::hasTable('customers')) {
            return;
        }

        $paymentsTableReady = Schema::hasTable('payments')
            && Schema::hasColumn('payments', 'sale_id')
            && Schema::hasColumn('payments', 'status')
            && Schema::hasColumn('payments', 'amount');

        // إعادة حساب remaining_amount لكل فاتورة بناءً على المدفوعات الفعلية
        // أولاً: حساب إجمالي المدفوعات الفعلية لكل فاتورة من جدول payments
        DB::table('sales')->orderBy('id')->get()->each(function ($sale) use ($paymentsTableReady) {
            $paidAmount = 0;

            if ($paymentsTableReady) {
                $paidAmount = (float) DB::table('payments')
                    ->where('sale_id', $sale->id)
                    ->where('status', 'completed')
                    ->sum('amount');
            }

            $remainingAmount = max((float) $sale->total_amount - $paidAmount, 0);

            $paymentStatus = 'unpaid';
            if ($paidAmount >= (float) $sale->total_amount) {
                $paymentStatus = 'paid';
            } elseif ($paidAmount > 0) {
                $paymentStatus = 'partial';
            } elseif ($sale->due_date && Carbon::parse($sale->due_date)->isPast()) {
                $paymentStatus = 'overdue';
            }

            if ($sale->status === 'cancelled') {
                $paymentStatus = $sale->payment_status;
            }

            DB::table('sales')
                ->where('id', $sale->id)
                ->update([
                    'paid_amount' => $paidAmount,
                    'remaining_amount' => $remainingAmount,
                    'payment_status' => $paymentStatus,
                ]);
        });

        // رابعاً: إعادة حساب أرصدة جميع العملاء
        DB::table('customers')->orderBy('id')->get()->each(function ($customer) {
            $balance = (float) DB::table('sales')
                ->where('customer_id', $customer->id)
                ->where('status', '!=', 'cancelled')
                ->whereIn('payment_status', ['unpaid', 'partial', 'overdue'])
                ->sum('remaining_amount');

            DB::table('customers')
                ->where('id', $customer->id)
                ->update(['current_balance' => $balance]);
        });
    }

    public function down(): void
    {
        // لا يمكن التراجع عن إعادة الحساب
    }
};
