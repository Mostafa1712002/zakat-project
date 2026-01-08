<?php

namespace App\Http\Controllers;

use App\Models\SalesRep;
use Illuminate\Http\Request;

class SalesRepController extends Controller
{
    public function index()
    {
        $salesReps = SalesRep::orderBy('name')->paginate(20);
        return view('sales-reps.index', compact('salesReps'));
    }

    public function create()
    {
        return view('sales-reps.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:sales_reps,code',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'commission_type' => 'nullable|in:percentage,fixed',
            'sales_target' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        SalesRep::create($validated);

        return redirect()->route('sales-reps.index')->with('success', 'تم إضافة المندوب بنجاح');
    }

    public function show(SalesRep $salesRep)
    {
        return view('sales-reps.show', compact('salesRep'));
    }

    public function edit(SalesRep $salesRep)
    {
        return view('sales-reps.edit', compact('salesRep'));
    }

    public function update(Request $request, SalesRep $salesRep)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:sales_reps,code,' . $salesRep->id,
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'commission_type' => 'nullable|in:percentage,fixed',
            'sales_target' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $salesRep->update($validated);

        return redirect()->route('sales-reps.index')->with('success', 'تم تحديث المندوب بنجاح');
    }

    public function destroy(SalesRep $salesRep)
    {
        $salesRep->delete();
        return redirect()->route('sales-reps.index')->with('success', 'تم حذف المندوب بنجاح');
    }
}
