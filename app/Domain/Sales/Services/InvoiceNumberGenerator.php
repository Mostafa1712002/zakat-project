<?php

namespace App\Domain\Sales\Services;

use App\Domain\Sales\Models\Invoice;
use App\Models\Setting;
use Illuminate\Support\Carbon;

/**
 * InvoiceNumberGenerator — produces a year-scoped sequential invoice number
 * (default format: INV-2026-0001). Prefix is sourced from settings.
 *
 * Reference: .kiro/specs/ammrk-platform/tasks.md (Task 5.5)
 */
class InvoiceNumberGenerator
{
    public function next(): string
    {
        $prefix = (string) Setting::get('invoice_number_prefix', 'INV-');
        $year = Carbon::now()->year;

        $maxId = (int) Invoice::withTrashed()
            ->whereYear('created_at', $year)
            ->max('id');
        $sequence = $maxId + 1;

        return sprintf('%s%d-%04d', $prefix, $year, $sequence);
    }
}
