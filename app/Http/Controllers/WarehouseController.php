<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\Branch;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WarehouseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $warehouses = Warehouse::with('branch')
            ->withCount('inventoryLevels')
            ->latest()
            ->paginate(15);

        return view('warehouses.index', compact('warehouses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $branches = Branch::where('is_active', true)->get();

        return view('warehouses.create', compact('branches'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:warehouses,code',
            'branch_id' => 'nullable|exists:branches,id',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'manager_name' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['is_default'] = $request->boolean('is_default');

        if (empty($validated['code'])) {
            do {
                $validated['code'] = 'WH-' . Str::upper(Str::random(6));
            } while (Warehouse::where('code', $validated['code'])->exists());
        }

        if (empty($validated['branch_id'])) {
            $validated['branch_id'] = Branch::where('is_main', true)->value('id')
                ?? Branch::where('is_active', true)->value('id');
        }

        // If setting as default, remove default from other warehouses
        if ($validated['is_default']) {
            Warehouse::where('is_default', true)->update(['is_default' => false]);
        }

        Warehouse::create($validated);

        return redirect()->route('warehouses.index')
            ->with('success', 'تم إضافة المستودع بنجاح');
    }

    /**
     * Display the specified resource.
     */
    public function show(Warehouse $warehouse)
    {
        $warehouse->load(['branch', 'inventoryLevels.product']);

        return view('warehouses.show', compact('warehouse'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Warehouse $warehouse)
    {
        $branches = Branch::where('is_active', true)->get();

        return view('warehouses.edit', compact('warehouse', 'branches'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Warehouse $warehouse)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:warehouses,code,' . $warehouse->id,
            'branch_id' => 'nullable|exists:branches,id',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'manager_name' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_default'] = $request->boolean('is_default');

        if (empty($validated['code'])) {
            $validated['code'] = $warehouse->code;
        }

        if (empty($validated['code'])) {
            do {
                $validated['code'] = 'WH-' . Str::upper(Str::random(6));
            } while (Warehouse::where('code', $validated['code'])->exists());
        }

        if (empty($validated['branch_id'])) {
            $validated['branch_id'] = $warehouse->branch_id
                ?? Branch::where('is_main', true)->value('id')
                ?? Branch::where('is_active', true)->value('id');
        }

        // If setting as default, remove default from other warehouses
        if ($validated['is_default'] && !$warehouse->is_default) {
            Warehouse::where('is_default', true)->update(['is_default' => false]);
        }

        $warehouse->update($validated);

        return redirect()->route('warehouses.index')
            ->with('success', 'تم تحديث بيانات المستودع بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Warehouse $warehouse)
    {
        // Check if warehouse has inventory
        if ($warehouse->inventoryLevels()->where('quantity', '>', 0)->exists()) {
            return back()->with('error', 'لا يمكن حذف المستودع لأنه يحتوي على مخزون');
        }

        // Check if warehouse is default
        if ($warehouse->is_default) {
            return back()->with('error', 'لا يمكن حذف المستودع الافتراضي');
        }

        $warehouse->delete();

        return redirect()->route('warehouses.index')
            ->with('success', 'تم حذف المستودع بنجاح');
    }

    /**
     * Show the transfer form.
     */
    public function showTransferForm()
    {
        $warehouses = Warehouse::active()->get();
        $products = Product::active()->get();

        return view('warehouses.transfer', compact('warehouses', 'products'));
    }

    /**
     * AJAX: Get products with stock in a warehouse.
     */
    public function productsWithStock(Warehouse $warehouse)
    {
        $products = $warehouse->inventoryLevels()
            ->where('quantity', '>', 0)
            ->with('product')
            ->get()
            ->map(function ($level) {
                return [
                    'id' => $level->product->id,
                    'name' => $level->product->name,
                    'available' => $level->quantity,
                    'selling_price' => $level->product->selling_price,
                    'min_selling_price' => $level->product->min_selling_price ?? 0,
                    'track_inventory' => $level->product->track_inventory,
                ];
            });

        return response()->json($products);
    }

    /**
     * Transfer stock between warehouses (supports multi-item).
     */
    public function transfer(Request $request)
    {
        $validated = $request->validate([
            'from_warehouse_id' => 'required|exists:warehouses,id',
            'to_warehouse_id' => 'required|exists:warehouses,id|different:from_warehouse_id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'notes' => 'nullable|string',
        ]);

        $fromWarehouse = Warehouse::findOrFail($validated['from_warehouse_id']);

        DB::beginTransaction();

        try {
            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);

                // Check available stock
                $availableStock = $fromWarehouse->getAvailableStock($product->id);
                if ($availableStock < $item['quantity']) {
                    throw new \Exception('الكمية المطلوبة غير متوفرة للصنف: ' . $product->name . '. المتاح: ' . $availableStock);
                }

                // Record the transfer movement
                StockMovement::recordMovement([
                    'product_id' => $item['product_id'],
                    'warehouse_id' => $validated['from_warehouse_id'],
                    'to_warehouse_id' => $validated['to_warehouse_id'],
                    'type' => StockMovement::TYPE_TRANSFER,
                    'quantity' => $item['quantity'],
                    'unit_cost' => $product->cost_price,
                    'user_id' => auth()->id(),
                    'reason' => 'تحويل مخزون',
                    'notes' => $validated['notes'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()->route('warehouses.index')
                ->with('success', 'تم تحويل ' . count($validated['items']) . ' صنف بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء تحويل المخزون: ' . $e->getMessage());
        }
    }
}
