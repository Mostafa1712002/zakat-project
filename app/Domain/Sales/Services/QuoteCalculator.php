<?php

namespace App\Domain\Sales\Services;

use App\Domain\Sales\Models\Quote;
use App\Models\Setting;

/**
 * QuoteCalculator — recomputes quote-level totals from its items.
 *
 * Applies the customer's tax-exempt flag every recalculation (the flag may
 * change between draft saves), and uses Setting::get('default_tax_rate', 15)
 * as the fallback when an item arrives with NULL tax_rate.
 *
 * Reference: .kiro/specs/ammrk-platform/tasks.md (Task 5.2)
 */
class QuoteCalculator
{
    public function recalculate(Quote $quote): void
    {
        $defaultRate = (float) Setting::get('default_tax_rate', 15);
        $isExempt = (bool) ($quote->customer?->is_tax_exempt ?? false);

        $subtotal = 0.0;
        $discountTotal = 0.0;
        $taxTotal = 0.0;
        $grandTotal = 0.0;

        $quote->loadMissing('items');

        foreach ($quote->items as $item) {
            // Apply exempt / fallback rate, then re-save the item so the
            // boot hook recomputes tax_amount and total consistently.
            $effectiveRate = $isExempt ? 0.0 : (float) ($item->tax_rate ?? $defaultRate);
            if ((float) $item->tax_rate !== $effectiveRate) {
                $item->tax_rate = $effectiveRate;
                $item->save();
            }

            $qty = (float) $item->quantity;
            $unit = (float) $item->unit_price;
            $discount = (float) $item->discount_amount;

            $line = max(0, ($qty * $unit) - $discount);
            $subtotal += $qty * $unit;
            $discountTotal += $discount;
            $taxTotal += (float) $item->tax_amount;
            $grandTotal += (float) $item->total;
        }

        $quote->fill([
            'subtotal' => round($subtotal, 2),
            'discount_total' => round($discountTotal, 2),
            'tax_total' => round($taxTotal, 2),
            'grand_total' => round($grandTotal, 2),
        ])->save();
    }
}
