<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\PartnerTransaction;
use Illuminate\Http\Request;

class PartnerTransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = PartnerTransaction::with(['partner', 'creator'])
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc');

        // Filter by partner
        if ($request->filled('partner_id')) {
            $query->where('partner_id', $request->partner_id);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->whereDate('transaction_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('transaction_date', '<=', $request->to_date);
        }

        $transactions = $query->paginate(20)->withQueryString();
        $partners = Partner::active()->orderBy('name')->get();

        // Calculate totals
        $totals = [
            'withdrawals' => PartnerTransaction::withdrawals()->sum('amount'),
            'profit_shares' => PartnerTransaction::profitShares()->sum('amount'),
            'investments' => PartnerTransaction::where('type', 'investment')->sum('amount'),
        ];

        return view('partner-transactions.index', compact('transactions', 'partners', 'totals'));
    }

    public function create(Request $request)
    {
        $partners = Partner::active()->orderBy('name')->get();
        $selectedPartner = $request->partner_id;
        $selectedType = $request->type ?? 'withdrawal';

        return view('partner-transactions.create', compact('partners', 'selectedPartner', 'selectedType'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'partner_id' => 'required|exists:partners,id',
            'type' => 'required|in:withdrawal,profit_share,investment,return',
            'amount' => 'required|numeric|min:0.01',
            'transaction_date' => 'required|date',
            'period' => 'nullable|string|max:20',
            'payment_method' => 'required|in:cash,bank_transfer,check',
            'reference_number' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $validated['transaction_number'] = PartnerTransaction::generateTransactionNumber();
        $validated['created_by'] = auth()->id();

        PartnerTransaction::create($validated);

        $typeNames = PartnerTransaction::TYPES;
        $message = "تم تسجيل {$typeNames[$validated['type']]} بنجاح";

        return redirect()->route('partner-transactions.index')->with('success', $message);
    }

    public function show(PartnerTransaction $partnerTransaction)
    {
        $partnerTransaction->load(['partner', 'creator']);
        return view('partner-transactions.show', compact('partnerTransaction'));
    }

    public function edit(PartnerTransaction $partnerTransaction)
    {
        $partners = Partner::active()->orderBy('name')->get();
        return view('partner-transactions.edit', compact('partnerTransaction', 'partners'));
    }

    public function update(Request $request, PartnerTransaction $partnerTransaction)
    {
        $validated = $request->validate([
            'partner_id' => 'required|exists:partners,id',
            'type' => 'required|in:withdrawal,profit_share,investment,return',
            'amount' => 'required|numeric|min:0.01',
            'transaction_date' => 'required|date',
            'period' => 'nullable|string|max:20',
            'payment_method' => 'required|in:cash,bank_transfer,check',
            'reference_number' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $partnerTransaction->update($validated);

        return redirect()->route('partner-transactions.index')->with('success', 'تم تحديث المعاملة بنجاح');
    }

    public function destroy(PartnerTransaction $partnerTransaction)
    {
        $partnerTransaction->delete();
        return redirect()->route('partner-transactions.index')->with('success', 'تم حذف المعاملة بنجاح');
    }

    // Partner-specific transactions view
    public function partnerHistory(Partner $partner, Request $request)
    {
        $query = $partner->transactions()
            ->with('creator')
            ->orderBy('transaction_date', 'desc');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $transactions = $query->paginate(20)->withQueryString();

        // Calculate partner totals
        $totals = [
            'withdrawals' => $partner->transactions()->withdrawals()->sum('amount'),
            'profit_shares' => $partner->transactions()->profitShares()->sum('amount'),
            'investments' => $partner->transactions()->where('type', 'investment')->sum('amount'),
            'returns' => $partner->transactions()->where('type', 'return')->sum('amount'),
        ];

        return view('partner-transactions.partner-history', compact('partner', 'transactions', 'totals'));
    }
}
