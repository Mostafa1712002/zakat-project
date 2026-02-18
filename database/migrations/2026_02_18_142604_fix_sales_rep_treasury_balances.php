<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Fix treasury balances for sales reps with confirmed cash sales
        $repCashSales = DB::table('sales')
            ->whereNotNull('sales_rep_id')
            ->where('payment_type', 'cash')
            ->where('status', 'confirmed')
            ->select('sales_rep_id', DB::raw('SUM(total_amount) as total'))
            ->groupBy('sales_rep_id')
            ->get();

        foreach ($repCashSales as $row) {
            // Get current balance
            $currentBalance = DB::table('sales_reps')
                ->where('id', $row->sales_rep_id)
                ->value('treasury_balance') ?? 0;

            // Only update if balance is less than expected (avoid double-counting)
            if ($currentBalance < $row->total) {
                $addAmount = $row->total - $currentBalance;
                DB::table('sales_reps')
                    ->where('id', $row->sales_rep_id)
                    ->increment('treasury_balance', $addAmount);

                // Record transaction
                DB::table('sales_rep_transactions')->insert([
                    'sales_rep_id' => $row->sales_rep_id,
                    'type' => 'deposit',
                    'amount' => $addAmount,
                    'balance_after' => $row->total,
                    'description' => 'تصحيح رصيد الخزينة - مبيعات نقدية سابقة',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // Cannot reliably reverse this
    }
};
