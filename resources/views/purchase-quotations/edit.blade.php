@extends('layouts.app')

@section('title', 'تعديل تسعيرة المشتريات')

@section('content')
<div class="page-header">
    <div>
        <h1>✏️ تعديل تسعيرة مشتريات</h1>
        <p>{{ $purchase->invoice_number }}</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('purchase-quotations.show', $purchase) }}" class="btn">عرض</a>
        <a href="{{ route('purchase-quotations.index') }}" class="btn">← رجوع للتسعيرات</a>
    </div>
</div>

@php
    $items = old('items');
    if (!$items) {
        $items = $purchase->items->map(function ($item) {
            return [
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_cost ?? $item->unit_price,
            ];
        })->toArray();
    }
@endphp

<form action="{{ route('purchase-quotations.update', $purchase) }}" method="POST" id="purchaseQuotationForm">
    @csrf
    @method('PUT')

    <div class="grid-2">
        <div class="card">
            <div class="card-body">
                <h3 style="margin-bottom: 16px;">📋 بيانات التسعيرة</h3>

                <div class="form-group">
                    <label for="supplier_id" class="form-label">المورد *</label>
                    <select name="supplier_id" id="supplier_id" class="form-control" required>
                        <option value="">اختر المورد</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ old('supplier_id', $purchase->supplier_id) == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="invoice_date" class="form-label">تاريخ التسعيرة *</label>
                        <input type="date" name="invoice_date" id="invoice_date" class="form-control" value="{{ old('invoice_date', $purchase->invoice_date?->format('Y-m-d')) }}" required>
                    </div>
                    <div class="form-group">
                        <label for="due_date" class="form-label">صالحة حتى</label>
                        <input type="date" name="due_date" id="due_date" class="form-control" value="{{ old('due_date', $purchase->due_date?->format('Y-m-d')) }}">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="payment_type" class="form-label">نوع الدفع *</label>
                        <select name="payment_type" id="payment_type" class="form-control" required>
                            <option value="cash" {{ old('payment_type', $purchase->payment_type) == 'cash' ? 'selected' : '' }}>نقدي</option>
                            <option value="credit" {{ old('payment_type', $purchase->payment_type) == 'credit' ? 'selected' : '' }}>آجل</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="warehouse_id" class="form-label">المستودع *</label>
                        <select name="warehouse_id" id="warehouse_id" class="form-control" required>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" {{ old('warehouse_id', $purchase->warehouse_id) == $warehouse->id ? 'selected' : '' }}>
                                    {{ $warehouse->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="branch_id" class="form-label">الفرع</label>
                    <select name="branch_id" id="branch_id" class="form-control">
                        <option value="">اختر الفرع</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ old('branch_id', $purchase->branch_id) == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="notes" class="form-label">ملاحظات</label>
                    <textarea name="notes" id="notes" class="form-control" rows="2">{{ old('notes', $purchase->notes) }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="card" style="margin-top: 1.5rem;">
        <div class="card-body">
            <h3 style="margin-bottom: 16px;">🛒 أصناف التسعيرة</h3>

            <div class="table-container overflow-auto">
                <table class="table text-nowrap" id="itemsTable">
                    <thead>
                        <tr>
                            <th style="width: 35%;">الصنف</th>
                            <th style="width: 15%;">الكمية</th>
                            <th style="width: 15%;">سعر الوحدة</th>
                            <th style="width: 15%;">الإجمالي</th>
                            <th style="width: 5%;"></th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody">
                        @foreach($items as $index => $item)
                        <tr class="item-row" data-index="{{ $index }}">
                            <td>
                                <select name="items[{{ $index }}][product_id]" class="form-control product-select" required>
                                    <option value="">اختر الصنف</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" {{ ($item['product_id'] ?? null) == $product->id ? 'selected' : '' }}>
                                            {{ $product->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="number" name="items[{{ $index }}][quantity]" class="form-control quantity-input" value="{{ $item['quantity'] ?? 1 }}" min="0.001" step="0.001" required>
                            </td>
                            <td>
                                <input type="number" name="items[{{ $index }}][unit_price]" class="form-control price-input" value="{{ $item['unit_price'] ?? 0 }}" min="0" step="0.01" required>
                            </td>
                            <td>
                                <span class="row-total">0.00</span> ج.م
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-danger remove-row" style="display: none;">×</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5">
                                <button type="button" class="btn btn-sm" id="addRowBtn">+ إضافة صنف</button>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="totals-section">
                <div class="totals-row total-final"><span>الإجمالي:</span> <strong id="grandTotal">0.00</strong> ج.م</div>
            </div>
        </div>
    </div>

    <div class="card" style="margin-top: 1.5rem;">
        <div class="card-body">
            <button type="submit" class="btn btn-primary btn-lg">💾 تحديث التسعيرة</button>
            <a href="{{ route('purchase-quotations.show', $purchase) }}" class="btn btn-lg">إلغاء</a>
        </div>
    </div>
</form>

<style>
.grid-2 { display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 1.5rem; }
.totals-section { margin-top: 1.5rem; padding-top: 1rem; border-top: 2px solid var(--border-color); max-width: 300px; margin-right: auto; }
.totals-row { display: flex; justify-content: space-between; padding: 0.5rem 0; }
.total-final { font-size: 1.25rem; border-top: 2px solid var(--primary); padding-top: 1rem; margin-top: 0.5rem; }
.btn-lg { padding: 14px 32px; font-size: 1rem; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let rowIndex = document.querySelectorAll('.item-row').length;

    function calculateRowTotal(row) {
        const qty = parseFloat(row.querySelector('.quantity-input').value) || 0;
        const price = parseFloat(row.querySelector('.price-input').value) || 0;
        const total = qty * price;
        row.querySelector('.row-total').textContent = Math.max(0, total).toFixed(2);
        return Math.max(0, total);
    }

    function calculateTotals() {
        let total = 0;
        document.querySelectorAll('.item-row').forEach(row => { total += calculateRowTotal(row); });
        document.getElementById('grandTotal').textContent = total.toFixed(2);
    }

    function attachRowEvents(row) {
        row.querySelectorAll('.quantity-input, .price-input').forEach(input => {
            input.addEventListener('input', calculateTotals);
        });
    }

    document.getElementById('addRowBtn').addEventListener('click', function() {
        const tbody = document.getElementById('itemsBody');
        const newRow = document.querySelector('.item-row').cloneNode(true);
        newRow.setAttribute('data-index', rowIndex);
        newRow.querySelectorAll('[name]').forEach(input => {
            input.name = input.name.replace(/\[\d+\]/, '[' + rowIndex + ']');
            if (input.classList.contains('quantity-input')) input.value = 1;
            else if (input.classList.contains('product-select')) input.value = '';
            else input.value = 0;
        });
        newRow.querySelector('.row-total').textContent = '0.00';
        newRow.querySelector('.remove-row').style.display = 'inline-block';
        tbody.appendChild(newRow);
        rowIndex++;
        updateRemoveButtons();
        attachRowEvents(newRow);
        calculateTotals();
    });

    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-row')) {
            e.target.closest('.item-row').remove();
            calculateTotals();
            updateRemoveButtons();
        }
    });

    function updateRemoveButtons() {
        const rows = document.querySelectorAll('.item-row');
        rows.forEach(row => { row.querySelector('.remove-row').style.display = rows.length > 1 ? 'inline-block' : 'none'; });
    }

    document.querySelectorAll('.item-row').forEach(row => {
        attachRowEvents(row);
    });

    updateRemoveButtons();
    calculateTotals();
});
</script>
@endsection
