<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use Illuminate\Http\Request;

class PartnerController extends Controller
{
    public function index(Request $request)
    {
        $query = Partner::query()->orderBy('name');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('partner_code', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->has('is_active') && $request->is_active !== '') {
            $query->where('is_active', $request->is_active);
        }

        $partners = $query->paginate(20)->withQueryString();

        // Calculate totals
        $totals = [
            'total_investment' => Partner::sum('initial_investment'),
            'total_partners' => Partner::active()->count(),
        ];

        return view('partners.index', compact('partners', 'totals'));
    }

    public function create()
    {
        return view('partners.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'national_id' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'ownership_percentage' => 'required|numeric|min:0|max:100',
            'initial_investment' => 'nullable|numeric|min:0',
            'join_date' => 'nullable|date',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $validated['partner_code'] = Partner::generatePartnerCode();
        $validated['is_active'] = $request->has('is_active');

        Partner::create($validated);

        return redirect()->route('partners.index')->with('success', 'تم إضافة الشريك بنجاح');
    }

    public function show(Partner $partner)
    {
        $partner->load('transactions');

        $totals = [
            'withdrawals' => $partner->total_withdrawals,
            'profit_shares' => $partner->total_profit_shares,
            'investments' => $partner->total_investments,
            'returns' => $partner->total_returns,
        ];

        return view('partners.show', compact('partner', 'totals'));
    }

    public function edit(Partner $partner)
    {
        return view('partners.edit', compact('partner'));
    }

    public function update(Request $request, Partner $partner)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'national_id' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'ownership_percentage' => 'required|numeric|min:0|max:100',
            'initial_investment' => 'nullable|numeric|min:0',
            'join_date' => 'nullable|date',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $partner->update($validated);

        return redirect()->route('partners.index')->with('success', 'تم تحديث بيانات الشريك بنجاح');
    }

    public function destroy(Partner $partner)
    {
        $partner->delete();
        return redirect()->route('partners.index')->with('success', 'تم حذف الشريك بنجاح');
    }
}
