<?php

namespace App\Console\Commands;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Purchase;
use App\Models\Customer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixDiscountTotals extends Command
{
    protected $signature = 'fix:discount-totals';
    protected $description = 'Fix per-item discount percentages and recalculate all sale/purchase totals';

    public function handle()
    {
        $this->info('=== Fixing existing data ===');

        // Step 1: Fix sale_items discount_amount (convert dollar amounts to percentage)
        $this->info('');
        $this->info('Step 1: Fixing sale_items discount_amount...');

        $fixedItems = 0;
        $skippedItems = 0;

        SaleItem::where('discount_amount', '>', 0)
            ->where('subtotal', '>', 0)
            ->chunk(200, function ($items) use (&$fixedItems, &$skippedItems) {
                foreach ($items as $item) {
                    // Calculate the actual discount that was applied
                    // total = subtotal - actual_discount + tax_amount
                    // actual_discount = subtotal - total + tax_amount
                    $actualDiscount = $item->subtotal - $item->total + $item->tax_amount;

                    if ($item->subtotal > 0) {
                        $correctPercentage = round(($actualDiscount / $item->subtotal) * 100, 2);
                    } else {
                        $correctPercentage = 0;
                    }

                    // Only update if the value is different (quotation items stored dollar amount)
                    if (abs($item->discount_amount - $correctPercentage) > 0.01) {
                        $this->line("  Item #{$item->id} (Sale #{$item->sale_id}): {$item->discount_amount} → {$correctPercentage}%");
                        $item->update(['discount_amount' => $correctPercentage]);
                        $fixedItems++;
                    } else {
                        $skippedItems++;
                    }
                }
            });

        $this->info("  Fixed: {$fixedItems} items, Already correct: {$skippedItems} items");

        // Step 2: Recalculate all sale totals
        $this->info('');
        $this->info('Step 2: Recalculating all sale totals...');

        $salesFixed = 0;
        $salesChanged = 0;

        Sale::with('items')->chunk(100, function ($sales) use (&$salesFixed, &$salesChanged) {
            foreach ($sales as $sale) {
                $oldSubtotal = $sale->subtotal;
                $oldTotal = $sale->total_amount;

                $sale->calculateTotals();
                $sale->save();

                if (abs($oldTotal - $sale->total_amount) > 0.01) {
                    $this->line("  Sale #{$sale->id} ({$sale->invoice_number}): subtotal {$oldSubtotal} → {$sale->subtotal}, total {$oldTotal} → {$sale->total_amount}");
                    $salesChanged++;
                }
                $salesFixed++;
            }
        });

        $this->info("  Processed: {$salesFixed} sales, Changed: {$salesChanged}");

        // Step 3: Recalculate all purchase totals (uses subtotal, not total - purchases don't have per-item discounts)
        $this->info('');
        $this->info('Step 3: Recalculating all purchase totals...');

        $purchasesFixed = 0;
        $purchasesChanged = 0;

        Purchase::with('items')->chunk(100, function ($purchases) use (&$purchasesFixed, &$purchasesChanged) {
            foreach ($purchases as $purchase) {
                $oldTotal = $purchase->total_amount;

                $purchase->calculateTotals();
                $purchase->save();

                if (abs($oldTotal - $purchase->total_amount) > 0.01) {
                    $this->line("  Purchase #{$purchase->id}: total {$oldTotal} → {$purchase->total_amount}");
                    $purchasesChanged++;
                }
                $purchasesFixed++;
            }
        });

        $this->info("  Processed: {$purchasesFixed} purchases, Changed: {$purchasesChanged}");

        // Step 4: Recalculate all customer balances
        $this->info('');
        $this->info('Step 4: Recalculating customer balances...');

        $customersFixed = 0;
        Customer::chunk(100, function ($customers) use (&$customersFixed) {
            foreach ($customers as $customer) {
                $customer->recalculateBalance();
                $customersFixed++;
            }
        });

        $this->info("  Recalculated: {$customersFixed} customers");

        $this->info('');
        $this->info('=== Done! All data fixed. ===');

        return 0;
    }
}
