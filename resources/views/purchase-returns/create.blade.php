@extends('layouts.app')

@section('title', 'مرتجع مشتريات جديد')

@section('content')
<div class="page-header">
    <div>
        <h1>↩️ مرتجع مشتريات جديد</h1>
        <p>إرجاع أصناف من فاتورة شراء مستلمة</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('purchases.show', $purchase) }}" class="btn">← رجوع للفاتورة</a>
    </div>
</div>

@if(session('error'))
<div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card">
    <div class="card-body">
        <h3>📋 بيانات الفاتورة الأصلية</h3>
        <div class="info-row">
            <span><strong>رقم الفاتورة:</strong> {{ $purchase->invoice_number }}</span>
            <span><strong>المورد:</strong> {{ $purchase->supplier->name ?? '-' }}</span>
            <span><strong>التاريخ:</strong> {{ $purchase->invoice_date?->format('Y-m-d') }}</span>
            <span><strong>المخزن:</strong> {{ $purchase->warehouse->name ?? '-' }}</span>
        </div>
    </div>
</div>

<form action="{{ route('purchase-returns.store') }}" method="POST" id="returnForm">
    @csrf
    <input type="hidden" name="purchase_id" value="{{ $purchase->id }}">

    <div class="card" style="margin-top: 1.5rem;">
        <div class="card-body">
            <h3>📝 بيانات المرتجع</h3>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">تاريخ المرتجع *</label>
                    <input type="date" name="return_date" class="form-control" value="{{ old('return_date', date('Y-m-d')) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">سبب الإرجاع</label>
                    <input type="text" name="reason" class="form-control" value="{{ old('reason') }}" placeholder="سبب عام للإرجاع">
                </div>
            </div>
        </div>
    </div>

    <div class="card" style="margin-top: 1.5rem;">
        <div class="card-body">
            <h3>🛒 الأصناف المراد إرجاعها</h3>

            <div class="table-container overflow-auto">
                <table class="table text-nowrap" id="returnItemsTable">
                    <thead>
                        <tr>
                            <th>
                                <input type="checkbox" id="selectAll" title="تحديد الكل">
                            </th>
                            <th>الصنف</th>
                            <th>الكمية الأصلية</th>
                            <th>المرتجع سابقاً</th>
                            <th>المتاح للإرجاع</th>
                            <th>الكمية المرتجعة</th>
                            <th>سعر الوحدة</th>
                            <th>الإجمالي</th>
                            <th>سبب الصنف</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $index => $item)
                        <tr class="return-item-row" data-unit-cost="{{ $item->unit_cost }}">
                            <td>
                                <input type="checkbox" class="item-checkbox" data-index="{{ $index }}">
                            </td>
                            <td><strong>{{ $item->product->name ?? $item->product_name }}</strong></td>
                            <td>{{ number_format($item->quantity, 2) }}</td>
                            <td>{{ number_format($item->returned_qty, 2) }}</td>
                            <td><strong>{{ number_format($item->returnable_qty, 2) }}</strong></td>
                            <td>
                                <input type="hidden" name="items[{{ $index }}][purchase_item_id]" value="{{ $item->id }}" disabled>
                                <input type="number" name="items[{{ $index }}][quantity]" class="form-control return-qty-input" value="0" min="0" max="{{ $item->returnable_qty }}" step="0.001" style="width: 100px;" disabled>
                            </td>
                            <td>{{ number_format($item->unit_cost, 2) }} ج.م</td>
                            <td><span class="return-row-total">0.00</span> ج.م</td>
                            <td>
                                <input type="text" name="items[{{ $index }}][reason]" class="form-control" placeholder="السبب" style="width: 140px;" disabled>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="totals-section">
                <div class="totals-row total-final"><span>إجمالي المرتجع:</span> <strong id="returnTotal">0.00</strong> ج.م</div>
            </div>
        </div>
    </div>

    <div class="card" style="margin-top: 1.5rem;">
        <div class="card-body">
            <div class="form-group">
                <label class="form-label">ملاحظات</label>
                <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
            </div>

            <div style="margin-top: 1rem;">
                <button type="submit" class="btn btn-primary btn-lg" id="submitBtn" disabled>↩️ تأكيد المرتجع</button>
                <a href="{{ route('purchases.show', $purchase) }}" class="btn btn-lg">إلغاء</a>
            </div>
        </div>
    </div>
</form>

<style>
.info-row { display: flex; gap: 2rem; flex-wrap: wrap; }
.info-row span { font-size: 0.9rem; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.card-body h3 { margin-bottom: 1rem; font-size: 1rem; }
.totals-section { margin-top: 1.5rem; padding-top: 1rem; border-top: 2px solid var(--border); max-width: 300px; margin-right: auto; }
.totals-row { display: flex; justify-content: space-between; padding: 0.5rem 0; }
.total-final { font-size: 1.25rem; border-top: 2px solid var(--primary); padding-top: 1rem; margin-top: 0.5rem; }
.btn-lg { padding: 14px 32px; font-size: 1rem; }
.return-item-row.selected { background: #eff6ff; }
.return-item-row td { vertical-align: middle; }
.alert { padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
.alert-danger { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

@media (max-width: 768px) {
    .form-row { grid-template-columns: 1fr; }
    .info-row { flex-direction: column; gap: 0.5rem; }
    .totals-section { max-width: 100%; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.item-checkbox');
    const selectAll = document.getElementById('selectAll');
    const submitBtn = document.getElementById('submitBtn');

    selectAll.addEventListener('change', function() {
        checkboxes.forEach(cb => {
            cb.checked = this.checked;
            toggleRow(cb);
        });
        updateSubmitButton();
        calculateTotal();
    });

    checkboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            toggleRow(this);
            updateSubmitButton();
            calculateTotal();
        });
    });

    function toggleRow(checkbox) {
        const row = checkbox.closest('.return-item-row');
        const inputs = row.querySelectorAll('input[name*="purchase_item_id"], input[name*="quantity"], input[name*="reason"]');

        if (checkbox.checked) {
            row.classList.add('selected');
            inputs.forEach(input => input.disabled = false);
            const qtyInput = row.querySelector('.return-qty-input');
            if (parseFloat(qtyInput.value) === 0) {
                qtyInput.value = qtyInput.max;
            }
            calculateRowTotal(row);
        } else {
            row.classList.remove('selected');
            inputs.forEach(input => input.disabled = true);
            row.querySelector('.return-qty-input').value = 0;
            row.querySelector('.return-row-total').textContent = '0.00';
        }
    }

    document.querySelectorAll('.return-qty-input').forEach(input => {
        input.addEventListener('input', function() {
            const max = parseFloat(this.max);
            if (parseFloat(this.value) > max) this.value = max;
            if (parseFloat(this.value) < 0) this.value = 0;
            calculateRowTotal(this.closest('.return-item-row'));
            calculateTotal();
        });
    });

    function calculateRowTotal(row) {
        const qty = parseFloat(row.querySelector('.return-qty-input').value) || 0;
        const unitCost = parseFloat(row.dataset.unitCost) || 0;
        const total = qty * unitCost;
        row.querySelector('.return-row-total').textContent = total.toFixed(2);
        return total;
    }

    function calculateTotal() {
        let total = 0;
        document.querySelectorAll('.return-item-row').forEach(row => {
            if (row.querySelector('.item-checkbox').checked) {
                total += calculateRowTotal(row);
            }
        });
        document.getElementById('returnTotal').textContent = total.toFixed(2);
    }

    function updateSubmitButton() {
        const anyChecked = [...checkboxes].some(cb => cb.checked);
        submitBtn.disabled = !anyChecked;
    }

    // Form validation
    document.getElementById('returnForm').addEventListener('submit', function(e) {
        const checkedItems = document.querySelectorAll('.item-checkbox:checked');
        if (checkedItems.length === 0) {
            e.preventDefault();
            alert('يرجى تحديد صنف واحد على الأقل');
            return;
        }

        let valid = true;
        checkedItems.forEach(cb => {
            const row = cb.closest('.return-item-row');
            const qty = parseFloat(row.querySelector('.return-qty-input').value) || 0;
            if (qty <= 0) {
                valid = false;
            }
        });

        if (!valid) {
            e.preventDefault();
            alert('يرجى إدخال كمية صحيحة لكل صنف محدد');
        }
    });
});
</script>
@endsection
