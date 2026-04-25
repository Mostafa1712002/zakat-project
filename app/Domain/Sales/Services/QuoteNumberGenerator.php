<?php

namespace App\Domain\Sales\Services;

use App\Domain\Sales\Models\Quote;
use App\Models\Setting;
use Illuminate\Support\Carbon;

/**
 * QuoteNumberGenerator — produces a year-scoped sequential quote number
 * (default format: Q-2026-0001). Prefix is sourced from settings so admins
 * can change it without code edits.
 *
 * Reference: .kiro/specs/ammrk-platform/tasks.md (Task 5.2)
 */
class QuoteNumberGenerator
{
    public function next(): string
    {
        $prefix = (string) Setting::get('quote_number_prefix', 'Q-');
        $year = Carbon::now()->year;

        // Atomic-enough for our scale: max(id) for the year + 1.
        $maxId = (int) Quote::withTrashed()
            ->whereYear('created_at', $year)
            ->max('id');
        $sequence = $maxId + 1;

        return sprintf('%s%d-%04d', $prefix, $year, $sequence);
    }
}
