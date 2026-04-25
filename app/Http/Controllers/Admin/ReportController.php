<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Customer\Models\Customer;
use App\Domain\Sales\Models\Invoice;
use App\Domain\Sales\Models\Quote;
use App\Domain\Treasury\Models\Payment;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Admin Report Controller — Phase 7 (Tasks 7.2 + 7.3)
 *
 * Financial reports (revenue, VAT, customer debt) gated by `reports.financial`.
 * Operational reports (pending quotes, failed ZATCA, events calendar) gated by
 * `reports.operational`. CSV export reuses the same query pipeline.
 *
 * Reference: .kiro/specs/ammrk-platform/tasks.md (Tasks 7.2, 7.3)
 */
class ReportController extends Controller
{
    // -----------------------------------------------------------------------
    // 7.2 — Financial reports
    // -----------------------------------------------------------------------

    public function revenue(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        $query = Invoice::query()
            ->with('customer')
            ->issued()
            ->whereBetween('issued_at', [$from, $to])
            ->when($request->filled('customer_id'), fn ($q) => $q->where('customer_id', $request->customer_id))
            ->when($request->filled('event_type'), fn ($q) => $q->where('event_type', $request->event_type))
            ->orderByDesc('issued_at');

        $invoices = $query->get();
        $total = $invoices->sum('grand_total');
        $customers = Customer::orderBy('name')->get(['id', 'name']);

        return view('admin.reports.revenue', compact('invoices', 'total', 'customers', 'from', 'to'));
    }

    public function vat(Request $request)
    {
        [$from, $to] = $this->dateRange($request, 12);

        // Aggregate per month: total VAT collected and total cleared by ZATCA
        $rows = Invoice::issued()
            ->whereBetween('issued_at', [$from, $to])
            ->selectRaw("DATE_FORMAT(issued_at, '%Y-%m') as ym,
                         sum(tax_total) as total_vat,
                         sum(case when zatca_status = 'cleared' then tax_total else 0 end) as cleared_vat,
                         sum(case when zatca_status != 'cleared' then tax_total else 0 end) as pending_vat")
            ->groupBy('ym')
            ->orderBy('ym')
            ->get();

        return view('admin.reports.vat', compact('rows', 'from', 'to'));
    }

    public function customerDebt(Request $request)
    {
        $today = Carbon::today();

        // Pull all unpaid + partial invoices then bucket per customer.
        $invoices = Invoice::with('customer')
            ->whereIn('status', [Invoice::STATUS_ISSUED, Invoice::STATUS_PAID_PARTIAL])
            ->whereNotNull('issued_at')
            ->get();

        $buckets = []; // customer_id => [name, b0_30, b31_60, b61_90, b90_plus, total]

        foreach ($invoices as $inv) {
            $outstanding = (float) $inv->grand_total - (float) $inv->paid_amount;
            if ($outstanding <= 0) {
                continue;
            }
            $age = $inv->issued_at ? $today->diffInDays(Carbon::parse($inv->issued_at), false) * -1 : 0;
            $bucket = $age <= 30 ? 'b0_30' : ($age <= 60 ? 'b31_60' : ($age <= 90 ? 'b61_90' : 'b90_plus'));

            $cid = $inv->customer_id;
            if (!isset($buckets[$cid])) {
                $buckets[$cid] = [
                    'customer' => $inv->customer,
                    'b0_30' => 0,
                    'b31_60' => 0,
                    'b61_90' => 0,
                    'b90_plus' => 0,
                    'total' => 0,
                ];
            }
            $buckets[$cid][$bucket] += $outstanding;
            $buckets[$cid]['total'] += $outstanding;
        }

        // Sort by total descending
        uasort($buckets, fn ($a, $b) => $b['total'] <=> $a['total']);

        $grand = [
            'b0_30' => array_sum(array_column($buckets, 'b0_30')),
            'b31_60' => array_sum(array_column($buckets, 'b31_60')),
            'b61_90' => array_sum(array_column($buckets, 'b61_90')),
            'b90_plus' => array_sum(array_column($buckets, 'b90_plus')),
            'total' => array_sum(array_column($buckets, 'total')),
        ];

        return view('admin.reports.customer-debt', compact('buckets', 'grand'));
    }

    // -----------------------------------------------------------------------
    // 7.3 — Operational reports
    // -----------------------------------------------------------------------

    public function pendingQuotes(Request $request)
    {
        $quotes = Quote::with(['customer', 'creator'])
            ->where('status', Quote::STATUS_SUBMITTED)
            ->orderBy('created_at')
            ->get();

        return view('admin.reports.quotes-pending', compact('quotes'));
    }

    public function failedZatca(Request $request)
    {
        $invoices = Invoice::with('customer')
            ->where('zatca_status', Invoice::ZATCA_FAILED)
            ->orderByDesc('issued_at')
            ->get();

        return view('admin.reports.zatca-failed', compact('invoices'));
    }

    public function eventsCalendar(Request $request)
    {
        $start = Carbon::today();
        $end = $start->copy()->addDays(90);

        $invoices = Invoice::with('customer')
            ->whereNotNull('event_start_date')
            ->whereBetween('event_start_date', [$start, $end])
            ->orderBy('event_start_date')
            ->get();

        // Group by Y-m
        $byMonth = $invoices->groupBy(fn ($i) => Carbon::parse($i->event_start_date)->format('Y-m'));

        return view('admin.reports.events-calendar', compact('byMonth', 'start', 'end'));
    }

    // -----------------------------------------------------------------------
    // CSV export — handles all six report types
    // -----------------------------------------------------------------------

    public function export(Request $request, string $type): StreamedResponse
    {
        $filename = 'report-' . $type . '-' . Carbon::now()->format('Y-m-d') . '.csv';

        $callback = function () use ($type, $request) {
            $out = fopen('php://output', 'w');
            // BOM for Excel UTF-8
            fwrite($out, "\xEF\xBB\xBF");

            switch ($type) {
                case 'revenue':
                    fputcsv($out, ['Invoice', 'Date', 'Customer', 'Event', 'Subtotal', 'VAT', 'Total']);
                    [$from, $to] = $this->dateRange($request);
                    Invoice::with('customer')->issued()
                        ->whereBetween('issued_at', [$from, $to])
                        ->when($request->filled('customer_id'), fn ($q) => $q->where('customer_id', $request->customer_id))
                        ->when($request->filled('event_type'), fn ($q) => $q->where('event_type', $request->event_type))
                        ->orderByDesc('issued_at')
                        ->chunk(200, function ($chunk) use ($out) {
                            foreach ($chunk as $i) {
                                fputcsv($out, [
                                    $i->invoice_number,
                                    optional($i->issued_at)->format('Y-m-d'),
                                    optional($i->customer)->name,
                                    $i->event_name,
                                    $i->subtotal,
                                    $i->tax_total,
                                    $i->grand_total,
                                ]);
                            }
                        });
                    break;

                case 'vat':
                    fputcsv($out, ['Month', 'Total VAT', 'Cleared VAT', 'Pending VAT']);
                    [$from, $to] = $this->dateRange($request, 12);
                    $rows = Invoice::issued()
                        ->whereBetween('issued_at', [$from, $to])
                        ->selectRaw("DATE_FORMAT(issued_at, '%Y-%m') as ym,
                                     sum(tax_total) as total_vat,
                                     sum(case when zatca_status = 'cleared' then tax_total else 0 end) as cleared_vat,
                                     sum(case when zatca_status != 'cleared' then tax_total else 0 end) as pending_vat")
                        ->groupBy('ym')->orderBy('ym')->get();
                    foreach ($rows as $r) {
                        fputcsv($out, [$r->ym, $r->total_vat, $r->cleared_vat, $r->pending_vat]);
                    }
                    break;

                case 'customer-debt':
                    fputcsv($out, ['Customer', '0-30', '31-60', '61-90', '90+', 'Total']);
                    $today = Carbon::today();
                    $invoices = Invoice::with('customer')
                        ->whereIn('status', [Invoice::STATUS_ISSUED, Invoice::STATUS_PAID_PARTIAL])
                        ->whereNotNull('issued_at')->get();
                    $buckets = [];
                    foreach ($invoices as $inv) {
                        $out_amt = (float) $inv->grand_total - (float) $inv->paid_amount;
                        if ($out_amt <= 0) continue;
                        $age = $inv->issued_at ? abs($today->diffInDays(Carbon::parse($inv->issued_at), false)) : 0;
                        $b = $age <= 30 ? 0 : ($age <= 60 ? 1 : ($age <= 90 ? 2 : 3));
                        $name = optional($inv->customer)->name ?? '—';
                        if (!isset($buckets[$name])) $buckets[$name] = [0, 0, 0, 0];
                        $buckets[$name][$b] += $out_amt;
                    }
                    foreach ($buckets as $name => $bs) {
                        fputcsv($out, [$name, $bs[0], $bs[1], $bs[2], $bs[3], array_sum($bs)]);
                    }
                    break;

                case 'quotes-pending':
                    fputcsv($out, ['Quote', 'Customer', 'Event', 'Total', 'Created']);
                    Quote::with('customer')->where('status', Quote::STATUS_SUBMITTED)
                        ->chunk(200, function ($chunk) use ($out) {
                            foreach ($chunk as $q) {
                                fputcsv($out, [
                                    $q->quote_number,
                                    optional($q->customer)->name,
                                    $q->event_name,
                                    $q->grand_total,
                                    optional($q->created_at)->format('Y-m-d'),
                                ]);
                            }
                        });
                    break;

                case 'zatca-failed':
                    fputcsv($out, ['Invoice', 'Customer', 'Issued', 'Total', 'Warnings']);
                    Invoice::with('customer')->where('zatca_status', Invoice::ZATCA_FAILED)
                        ->chunk(200, function ($chunk) use ($out) {
                            foreach ($chunk as $i) {
                                fputcsv($out, [
                                    $i->invoice_number,
                                    optional($i->customer)->name,
                                    optional($i->issued_at)->format('Y-m-d'),
                                    $i->grand_total,
                                    is_array($i->zatca_warnings) ? json_encode($i->zatca_warnings, JSON_UNESCAPED_UNICODE) : (string) $i->zatca_warnings,
                                ]);
                            }
                        });
                    break;

                case 'events-calendar':
                    fputcsv($out, ['Date', 'Invoice', 'Customer', 'Event', 'Location']);
                    $start = Carbon::today();
                    $end = $start->copy()->addDays(90);
                    Invoice::with('customer')
                        ->whereNotNull('event_start_date')
                        ->whereBetween('event_start_date', [$start, $end])
                        ->orderBy('event_start_date')
                        ->chunk(200, function ($chunk) use ($out) {
                            foreach ($chunk as $i) {
                                fputcsv($out, [
                                    optional($i->event_start_date)->format('Y-m-d'),
                                    $i->invoice_number,
                                    optional($i->customer)->name,
                                    $i->event_name,
                                    $i->event_location,
                                ]);
                            }
                        });
                    break;

                default:
                    fputcsv($out, ['Unknown report type', $type]);
            }

            fclose($out);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    private function dateRange(Request $request, int $defaultMonths = 1): array
    {
        $from = $request->filled('from')
            ? Carbon::parse($request->input('from'))->startOfDay()
            : Carbon::now()->subMonths($defaultMonths)->startOfDay();

        $to = $request->filled('to')
            ? Carbon::parse($request->input('to'))->endOfDay()
            : Carbon::now()->endOfDay();

        return [$from, $to];
    }
}
