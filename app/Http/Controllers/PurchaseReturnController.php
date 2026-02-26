<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\Supplier;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseReturnController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseReturn::with(['purchase', 'supplier'])
            ->orderBy('return_date', 'desc');

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('return_number', 'like', "%{$request->search}%")
                  ->orWhereHas('supplier', fn($q) => $q->where('name', 'like', "%{$request->search}%"))
                  ->orWhereHas('purchase', fn($q) => $q->where('invoice_number', 'like', "%{$request->search}%"));
            });
        }

        if ($request->supplier_id) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->date_from) {
            $query->whereDate('return_date', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('return_date', '<=', $request->date_to);
        }

        $purchaseReturns = $query->paginate(20);
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        return view('purchase-returns.index', compact('purchaseReturns', 'suppliers'));
    }

    public function create(Request $request)
    {
        $purchaseId = $request->query('purchase_id');
        if (!$purchaseId) {
            return redirect()->route('purchases.index')->with('error', 'يجب تحديد فاتورة الشراء');
        }

        $purchase = Purchase::with(['items.product', 'supplier', 'warehouse'])->findOrFail($purchaseId);

        if ($purchase->status !== 'received') {
            return back()->with('error', 'لا يمكن عمل مرتجع إلا لفاتورة مستلمة');
        }

        // Calculate returnable quantity per item
        $items = $purchase->items->map(function ($item) {
            $returnedQty = $item->returnItems()->sum('quantity');
            $item->returned_qty = $returnedQty;
            $item->returnable_qty = $item->quantity - $returnedQty;
            return $item;
        })->filter(fn($item) => $item->returnable_qty > 0);

        return view('purchase-returns.create', compact('purchase', 'items'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'purchase_id' => 'required|exists:purchases,id',
            'return_date' => 'required|date',
            'reason' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.purchase_item_id' => 'required|exists:purchase_items,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.reason' => 'nullable|string|max:255',
        ]);

        $purchase = Purchase::with(['items', 'warehouse', 'supplier'])->findOrFail($validated['purchase_id']);

        if ($purchase->status !== 'received') {
            return back()->with('error', 'لا يمكن عمل مرتجع إلا لفاتورة مستلمة');
        }

        // Validate returnable quantities
        foreach ($validated['items'] as $itemData) {
            $purchaseItem = $purchase->items->find($itemData['purchase_item_id']);
            if (!$purchaseItem) {
                return back()->withInput()->with('error', 'صنف غير موجود في الفاتورة');
            }
            $returnedQty = $purchaseItem->returnItems()->sum('quantity');
            $returnableQty = $purchaseItem->quantity - $returnedQty;
            if ($itemData['quantity'] > $returnableQty) {
                return back()->withInput()->with('error', "الكمية المرتجعة للصنف {$purchaseItem->product_name} أكبر من المتاح ({$returnableQty})");
            }
        }

        DB::beginTransaction();

        try {
            $purchaseReturn = PurchaseReturn::create([
                'return_number' => PurchaseReturn::generateReturnNumber(),
                'purchase_id' => $purchase->id,
                'supplier_id' => $purchase->supplier_id,
                'branch_id' => $purchase->branch_id,
                'warehouse_id' => $purchase->warehouse_id,
                'user_id' => auth()->id(),
                'return_date' => $validated['return_date'],
                'status' => PurchaseReturn::STATUS_COMPLETED,
                'reason' => $validated['reason'],
                'notes' => $validated['notes'],
            ]);

            foreach ($validated['items'] as $itemData) {
                $purchaseItem = $purchase->items->find($itemData['purchase_item_id']);

                PurchaseReturnItem::create([
                    'purchase_return_id' => $purchaseReturn->id,
                    'purchase_item_id' => $itemData['purchase_item_id'],
                    'product_id' => $purchaseItem->product_id,
                    'quantity' => $itemData['quantity'],
                    'unit_cost' => $purchaseItem->unit_cost,
                    'tax_amount' => 0,
                    'total' => $itemData['quantity'] * $purchaseItem->unit_cost,
                    'reason' => $itemData['reason'] ?? null,
                ]);
            }

            // Calculate totals
            $purchaseReturn->load('items');
            $purchaseReturn->calculateTotals();
            $purchaseReturn->refunded_amount = $purchaseReturn->total_amount;
            $purchaseReturn->save();

            // Deduct stock
            $warehouse = $purchase->warehouse;
            if ($warehouse) {
                foreach ($purchaseReturn->items as $returnItem) {
                    $product = $returnItem->product;
                    if ($product && $product->track_inventory) {
                        $warehouse->adjustStock(
                            $returnItem->product_id,
                            -$returnItem->quantity,
                            'purchase_return',
                            "مرتجع شراء {$purchaseReturn->return_number} من فاتورة {$purchase->invoice_number}",
                            $purchaseReturn->return_number
                        );
                    }
                }
            }

            // Adjust supplier balance (credit purchases)
            if ($purchase->payment_type === 'credit' && $purchase->supplier) {
                $purchase->supplier->decrement('current_balance', $purchaseReturn->total_amount);
            }

            DB::commit();

            return redirect()->route('purchase-returns.show', $purchaseReturn)
                ->with('success', 'تم إنشاء المرتجع بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'حدث خطأ أثناء إنشاء المرتجع: ' . $e->getMessage());
        }
    }

    public function show(PurchaseReturn $purchaseReturn)
    {
        $purchaseReturn->load(['purchase.supplier', 'items.product', 'supplier', 'warehouse']);

        return view('purchase-returns.show', compact('purchaseReturn'));
    }

    public function destroy(PurchaseReturn $purchaseReturn)
    {
        DB::beginTransaction();

        try {
            $purchaseReturn->load(['items.product', 'purchase']);

            // Reverse stock
            $warehouse = $purchaseReturn->warehouse;
            if ($warehouse) {
                foreach ($purchaseReturn->items as $returnItem) {
                    $product = $returnItem->product;
                    if ($product && $product->track_inventory) {
                        $warehouse->adjustStock(
                            $returnItem->product_id,
                            $returnItem->quantity,
                            'purchase_return_reversal',
                            "إلغاء مرتجع شراء {$purchaseReturn->return_number}",
                            $purchaseReturn->return_number
                        );
                    }
                }
            }

            // Reverse supplier balance
            $purchase = $purchaseReturn->purchase;
            if ($purchase && $purchase->payment_type === 'credit' && $purchaseReturn->supplier) {
                $purchaseReturn->supplier->increment('current_balance', $purchaseReturn->total_amount);
            }

            $purchaseReturn->items()->delete();
            $purchaseReturn->delete();

            DB::commit();

            return redirect()->route('purchase-returns.index')
                ->with('success', 'تم حذف المرتجع بنجاح وتم استعادة المخزون');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'حدث خطأ أثناء حذف المرتجع: ' . $e->getMessage());
        }
    }
}
