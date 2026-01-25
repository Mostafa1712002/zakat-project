<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\PartnerTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProfitDistributionController extends Controller
{
    public function index()
    {
        // Get all profit distributions grouped by period
        $distributions = PartnerTransaction::where('type', 'profit_share')
            ->select('period', DB::raw('SUM(amount) as total_amount'), DB::raw('COUNT(DISTINCT partner_id) as partners_count'), DB::raw('MIN(transaction_date) as distribution_date'))
            ->groupBy('period')
            ->orderBy('distribution_date', 'desc')
            ->paginate(20);

        return view('profit-distribution.index', compact('distributions'));
    }

    public function create()
    {
        $partners = Partner::active()->orderBy('name')->get();

        // Check if total ownership percentage is valid
        $totalPercentage = $partners->sum('ownership_percentage');

        return view('profit-distribution.create', compact('partners', 'totalPercentage'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'total_profit' => 'required|numeric|min:0.01',
            'period' => 'required|string|max:20',
            'distribution_date' => 'required|date',
            'payment_method' => 'required|in:cash,bank_transfer,check',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $partners = Partner::active()->get();

        if ($partners->isEmpty()) {
            return back()->with('error', 'لا يوجد شركاء نشطين لتوزيع الأرباح');
        }

        $totalPercentage = $partners->sum('ownership_percentage');

        if ($totalPercentage <= 0) {
            return back()->with('error', 'نسب ملكية الشركاء غير صحيحة');
        }

        DB::beginTransaction();

        try {
            $createdTransactions = [];

            foreach ($partners as $partner) {
                if ($partner->ownership_percentage <= 0) {
                    continue;
                }

                // Calculate partner's share based on their ownership percentage
                $partnerShare = ($partner->ownership_percentage / $totalPercentage) * $validated['total_profit'];

                $transaction = PartnerTransaction::create([
                    'partner_id' => $partner->id,
                    'transaction_number' => PartnerTransaction::generateTransactionNumber(),
                    'type' => 'profit_share',
                    'amount' => round($partnerShare, 2),
                    'transaction_date' => $validated['distribution_date'],
                    'period' => $validated['period'],
                    'payment_method' => $validated['payment_method'],
                    'description' => $validated['description'] ?? "توزيع أرباح {$validated['period']}",
                    'notes' => $validated['notes'],
                    'created_by' => auth()->id(),
                ]);

                $createdTransactions[] = $transaction;
            }

            DB::commit();

            return redirect()->route('profit-distribution.index')
                ->with('success', 'تم توزيع الأرباح بنجاح على ' . count($createdTransactions) . ' شريك');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'حدث خطأ أثناء توزيع الأرباح: ' . $e->getMessage());
        }
    }

    public function show($period)
    {
        $transactions = PartnerTransaction::with('partner')
            ->where('type', 'profit_share')
            ->where('period', $period)
            ->orderBy('amount', 'desc')
            ->get();

        if ($transactions->isEmpty()) {
            return redirect()->route('profit-distribution.index')
                ->with('error', 'لا توجد توزيعات لهذه الفترة');
        }

        $totalAmount = $transactions->sum('amount');

        return view('profit-distribution.show', compact('transactions', 'period', 'totalAmount'));
    }

    public function preview(Request $request)
    {
        $validated = $request->validate([
            'total_profit' => 'required|numeric|min:0.01',
        ]);

        $partners = Partner::active()->orderBy('name')->get();
        $totalPercentage = $partners->sum('ownership_percentage');

        $preview = $partners->map(function ($partner) use ($validated, $totalPercentage) {
            $share = $totalPercentage > 0
                ? ($partner->ownership_percentage / $totalPercentage) * $validated['total_profit']
                : 0;

            return [
                'id' => $partner->id,
                'name' => $partner->name,
                'percentage' => $partner->ownership_percentage,
                'share' => round($share, 2),
            ];
        })->filter(fn($p) => $p['percentage'] > 0);

        return response()->json([
            'success' => true,
            'partners' => $preview->values(),
            'total' => $validated['total_profit'],
        ]);
    }
}
