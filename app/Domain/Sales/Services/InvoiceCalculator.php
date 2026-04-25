<?php

namespace App\Domain\Sales\Services;

use App\Domain\Sales\Models\Invoice;
use App\Models\Setting;
use LogicException;

/**
 * InvoiceCalculator — recomputes invoice totals from line items.
 *
 * Mirrors QuoteCalculator but enforces the immutability rule: once an invoice
 * has been issued it is locked, and totals must not change behind the
 * accountant's back.
 *
 * Reference: .kiro/specs/ammrk-platform/tasks.md (Task 5.5)
 */
class InvoiceCalculator
{
    public function recalculate(Invoice $invoice): void
    {
        if ($invoice->status !== Invoice::STATUS_DRAFT) {
            throw new LogicException(
                "Cannot recalculate invoice #{$invoice->id} after issuance (status={$invoice->status})."
            );
        }

        $defaultRate = (float) Setting::get('default_tax_rate', 15);
        $isExempt = (bool) ($invoice->customer?->is_tax_exempt ?? false);

        $subtotal = 0.0;
        $discountTotal = 0.0;
        $taxTotal = 0.0;
        $grandTotal = 0.0;

        $invoice->loadMissing('items');

        foreach ($invoice->items as $item) {
            $effectiveRate = $isExempt ? 0.0 : (float) ($item->tax_rate ?? $defaultRate);
            if ((float) $item->tax_rate !== $effectiveRate) {
                $item->tax_rate = $effectiveRate;
                $item->save();
            }

            $qty = (float) $item->quantity;
            $unit = (float) $item->unit_price;
            $discount = (float) $item->discount_amount;

            $subtotal += $qty * $unit;
            $discountTotal += $discount;
            $taxTotal += (float) $item->tax_amount;
            $grandTotal += (float) $item->total;
        }

        $invoice->fill([
            'subtotal' => round($subtotal, 2),
            'discount_total' => round($discountTotal, 2),
            'tax_total' => round($taxTotal, 2),
            'grand_total' => round($grandTotal, 2),
        ])->save();
    }
}
