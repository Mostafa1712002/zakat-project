<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Sales\Models\Invoice;
use App\Domain\Treasury\Models\Payment;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Admin Dashboard — Phase 7 (Task 7.1)
 *
 * Replaces the Phase 1 stub. Aggregates KPIs from invoices, payments, and
 * upcoming events. View: resources/views/dashboard.blade.php (existing route
 * name `dashboard`).
 *
 * Reference: .kiro/specs/ammrk-platform/tasks.md (Task 7.1)
 */
class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $now = Carbon::now();
        $monthStart = $now->copy()->startOfMonth();
        $monthEnd = $now->copy()->endOfMonth();

        // --- KPIs ----------------------------------------------------------
        $monthlyRevenue = Invoice::issued()
            ->whereBetween('issued_at', [$monthStart, $monthEnd])
            ->sum('grand_total');

        $outstandingDebt = Invoice::whereIn('status', [
                Invoice::STATUS_ISSUED,
                Invoice::STATUS_PAID_PARTIAL,
            ])
            ->get()
            ->sum(fn ($i) => (float) $i->grand_total - (float) $i->paid_amount);

        $paidThisMonth = Payment::whereBetween('payment_date', [$monthStart, $monthEnd])
            ->sum('amount');

        $upcomingEvents = Invoice::with('customer')
            ->whereNotNull('event_start_date')
            ->whereDate('event_start_date', '>=', $now->toDateString())
            ->orderBy('event_start_date')
            ->limit(10)
            ->get();

        // --- Recent activity ----------------------------------------------
        $recentInvoices = Invoice::with('customer')
            ->latest()
            ->limit(10)
            ->get();

        $recentPayments = Payment::with(['customer', 'invoice'])
            ->latest()
            ->limit(10)
            ->get();

        // --- Trend & breakdown --------------------------------------------
        $trendStart = $now->copy()->subMonths(11)->startOfMonth();
        $monthlyRows = Invoice::issued()
            ->whereNotNull('issued_at')
            ->where('issued_at', '>=', $trendStart)
            ->selectRaw("strftime('%Y-%m', issued_at) as ym, sum(grand_total) as total")
            ->groupBy('ym')
            ->pluck('total', 'ym');

        $revenueTrend = [];
        for ($i = 11; $i >= 0; $i--) {
            $key = $now->copy()->subMonths($i)->format('Y-m');
            $revenueTrend[] = [
                'label' => $now->copy()->subMonths($i)->format('M Y'),
                'value' => (float) ($monthlyRows[$key] ?? 0),
            ];
        }

        $statusBreakdown = Invoice::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return view('dashboard', [
            'monthly_revenue' => (float) $monthlyRevenue,
            'outstanding_debt' => (float) $outstandingDebt,
            'paid_this_month' => (float) $paidThisMonth,
            'upcoming_events' => $upcomingEvents,
            'recent_invoices' => $recentInvoices,
            'recent_payments' => $recentPayments,
            'revenue_trend' => $revenueTrend,
            'invoice_status_breakdown' => $statusBreakdown,
        ]);
    }
}
