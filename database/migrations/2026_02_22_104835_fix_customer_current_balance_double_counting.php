<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Recalculate all customer current_balance values to fix double-counting bug.
     */
    public function up(): void
    {
        DB::statement("
            UPDATE customers SET current_balance = COALESCE((
                SELECT SUM(remaining_amount)
                FROM sales
                WHERE sales.customer_id = customers.id
                  AND sales.status != 'cancelled'
                  AND sales.payment_status IN ('unpaid', 'partial', 'overdue')
            ), 0)
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot reverse - balances were already incorrect before this fix
    }
};
