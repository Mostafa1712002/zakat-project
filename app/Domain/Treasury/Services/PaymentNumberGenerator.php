<?php

namespace App\Domain\Treasury\Services;

use App\Domain\Treasury\Models\Payment;
use App\Models\Setting;
use Illuminate\Support\Carbon;

/**
 * PaymentNumberGenerator — produces a year-scoped sequential payment
 * number (default format: PAY-2026-0001). Prefix is sourced from settings.
 *
 * Reference: .kiro/specs/ammrk-platform/tasks.md (Task 6.2)
 */
class PaymentNumberGenerator
{
    public function next(): string
    {
        $prefix = (string) Setting::get('payment_number_prefix', 'PAY-');
        $year = Carbon::now()->year;

        $maxId = (int) Payment::withTrashed()
            ->whereYear('created_at', $year)
            ->max('id');
        $sequence = $maxId + 1;

        return sprintf('%s%d-%04d', $prefix, $year, $sequence);
    }
}
