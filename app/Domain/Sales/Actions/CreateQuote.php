<?php

namespace App\Domain\Sales\Actions;

use App\Domain\Sales\Models\Quote;
use App\Domain\Sales\Services\QuoteCalculator;
use App\Domain\Sales\Services\QuoteNumberGenerator;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * CreateQuote — creates a draft quote with line items and totals.
 *
 * Expected $data shape:
 *   customer_id (int, required)
 *   event_name (string, required)
 *   event_start_date, event_end_date (date|null)
 *   event_location, event_type (string|null)
 *   valid_until (date|null) — defaults to today + quote_validity_days
 *   notes (string|null)
 *   items (array<array{service_id:int, description?:string, quantity:float,
 *          unit_price:float, discount_amount?:float, tax_rate?:float,
 *          sort_order?:int}>)
 *
 * Reference: .kiro/specs/ammrk-platform/tasks.md (Task 5.3)
 */
class CreateQuote
{
    public function __construct(
        protected QuoteNumberGenerator $numberGenerator,
        protected QuoteCalculator $calculator,
    ) {
    }

    public function execute(array $data, User $creator): Quote
    {
        return DB::transaction(function () use ($data, $creator) {
            $validityDays = (int) Setting::get('quote_validity_days', 14);
            $defaultRate = (float) Setting::get('default_tax_rate', 15);

            $quote = Quote::create([
                'quote_number' => $this->numberGenerator->next(),
                'customer_id' => $data['customer_id'],
                'created_by' => $creator->getAuthIdentifier(),
                'event_name' => $data['event_name'],
                'event_start_date' => $data['event_start_date'] ?? null,
                'event_end_date' => $data['event_end_date'] ?? null,
                'event_location' => $data['event_location'] ?? null,
                'event_type' => $data['event_type'] ?? null,
                'valid_until' => $data['valid_until']
                    ?? Carbon::now()->addDays($validityDays)->toDateString(),
                'notes' => $data['notes'] ?? null,
                'status' => Quote::STATUS_DRAFT,
            ]);

            foreach ($data['items'] ?? [] as $i => $row) {
                $quote->items()->create([
                    'service_id' => $row['service_id'],
                    'description' => $row['description'] ?? null,
                    'quantity' => $row['quantity'] ?? 1,
                    'unit_price' => $row['unit_price'] ?? 0,
                    'discount_amount' => $row['discount_amount'] ?? 0,
                    'tax_rate' => $row['tax_rate'] ?? $defaultRate,
                    'sort_order' => $row['sort_order'] ?? $i,
                ]);
            }

            $this->calculator->recalculate($quote);

            return $quote->refresh();
        });
    }
}
