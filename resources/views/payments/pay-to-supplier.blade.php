@extends('layouts.app')

@section('title', 'دفع لمورد')

@section('content')
<div class="page-header">
    <div>
        <h1>دفع لمورد</h1>
        <p>تسجيل دفعة للمورد: {{ $supplier->name }}</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('suppliers.show', $supplier) }}" class="btn">← رجوع</a>
    </div>
</div>

<!-- Supplier Info -->
<div class="card mb-4">
    <div class="card-header">
        <h3 class="card-title">بيانات المورد</h3>
    </div>
    <div class="card-body">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
            <div>
                <span class="text-muted">اسم المورد:</span>
                <strong>{{ $supplier->name }}</strong>
            </div>
            <div>
                <span class="text-muted">كود المورد:</span>
                <strong>{{ $supplier->code }}</strong>
            </div>
            <div>
                <span class="text-muted">الرصيد المستحق:</span>
                <strong class="text-danger">{{ number_format($supplier->current_balance) }} ج.م</strong>
            </div>
            <div>
                <span class="text-muted">الهاتف:</span>
                <strong>{{ $supplier->phone ?? $supplier->mobile ?? '-' }}</strong>
            </div>
        </div>
    </div>
</div>

<!-- Unpaid Purchases -->
@if(isset($unpaidPurchases) && $unpaidPurchases->count() > 0)
<div class="card mb-4">
    <div class="card-header">
        <h3 class="card-title">🧾 فواتير الشراء المستحقة</h3>
    </div>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>رقم الفاتورة</th>
                    <th>التاريخ</th>
                    <th>تاريخ الاستحقاق</th>
                    <th>الإجمالي</th>
                    <th>المدفوع</th>
                    <th>المتبقي</th>
                    <th>الحالة</th>
                </tr>
            </thead>
            <tbody>
                @foreach($unpaidPurchases as $purchase)
                <tr class="purchase-row {{ $purchase->due_date && $purchase->due_date < now() ? 'overdue-row' : '' }}"
                    data-id="{{ $purchase->id }}"
                    data-remaining="{{ $purchase->remaining_amount }}"
                    style="cursor: pointer;">
                    <td><code>{{ $purchase->invoice_number }}</code></td>
                    <td>{{ $purchase->invoice_date?->format('Y-m-d') }}</td>
                    <td>
                        {{ $purchase->due_date?->format('Y-m-d') ?? '-' }}
                        @if($purchase->due_date && $purchase->due_date < now())
                            <span class="badge badge-danger">متأخرة</span>
                        @endif
                    </td>
                    <td>{{ number_format($purchase->total_amount, 2) }} ج.م</td>
                    <td>{{ number_format($purchase->paid_amount, 2) }} ج.م</td>
                    <td><strong class="text-danger">{{ number_format($purchase->remaining_amount, 2) }} ج.م</strong></td>
                    <td>
                        @if($purchase->payment_status === 'unpaid')
                            <span class="badge badge-danger">غير مدفوعة</span>
                        @elseif($purchase->payment_status === 'partial')
                            <span class="badge badge-warning">جزئي</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-body" style="background: #f8f9fa; border-top: 1px solid var(--border);">
        <small class="text-muted">💡 اضغط على فاتورة لتحديدها للدفع، أو اترك بدون تحديد للدفع على الحساب العام</small>
    </div>
</div>
@endif

<!-- Payment Form -->
<div class="card">
    <div class="card-body">
        <form action="{{ route('suppliers.pay', $supplier) }}" method="POST">
            @csrf
            <input type="hidden" name="purchase_id" id="purchase_id" value="{{ old('purchase_id') }}">

            <!-- Selected Purchase Info -->
            <div id="selectedPurchaseInfo" class="alert alert-info mb-4" style="display: none;">
                <strong>📋 الفاتورة المحددة:</strong>
                <span id="selectedPurchaseNumber"></span>
                <button type="button" class="btn btn-sm" onclick="clearSelectedPurchase()" style="float: left;">✕ إلغاء التحديد</button>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="amount" class="form-label">المبلغ *</label>
                    <input type="number" step="0.01" name="amount" id="amount" class="form-control"
                           value="{{ old('amount', $supplier->current_balance) }}" min="0.01" required>
                    @error('amount')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="payment_date" class="form-label">تاريخ الدفع *</label>
                    <input type="date" name="payment_date" id="payment_date" class="form-control"
                           value="{{ old('payment_date', date('Y-m-d')) }}" required>
                    @error('payment_date')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="method" class="form-label">طريقة الدفع *</label>
                    <select name="method" id="method" class="form-control" required onchange="toggleCheckFields()">
                        <option value="cash" {{ old('method') === 'cash' ? 'selected' : '' }}>نقدي</option>
                        <option value="bank_transfer" {{ old('method') === 'bank_transfer' ? 'selected' : '' }}>تحويل بنكي</option>
                        <option value="instapay" {{ old('method') === 'instapay' ? 'selected' : '' }}>انستا باي</option>
                        <option value="vodafone_cash" {{ old('method') === 'vodafone_cash' ? 'selected' : '' }}>فودافون كاش</option>
                        <option value="check" {{ old('method') === 'check' ? 'selected' : '' }}>شيك</option>
                        <option value="card" {{ old('method') === 'card' ? 'selected' : '' }}>بطاقة</option>
                        <option value="other" {{ old('method') === 'other' ? 'selected' : '' }}>أخرى</option>
                    </select>
                    @error('method')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="reference_number" class="form-label">رقم المرجع</label>
                    <input type="text" name="reference_number" id="reference_number" class="form-control"
                           value="{{ old('reference_number') }}">
                    @error('reference_number')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Check Fields -->
            <div id="checkFields" style="display: none;">
                <div class="form-row">
                    <div class="form-group">
                        <label for="check_number" class="form-label">رقم الشيك</label>
                        <input type="text" name="check_number" id="check_number" class="form-control"
                               value="{{ old('check_number') }}">
                        @error('check_number')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="check_date" class="form-label">تاريخ استحقاق الشيك</label>
                        <input type="date" name="check_date" id="check_date" class="form-control"
                               value="{{ old('check_date') }}">
                        @error('check_date')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Bank Fields -->
            <div id="bankFields" style="display: none;">
                <div class="form-row">
                    <div class="form-group">
                        <label for="bank_name" class="form-label">اسم البنك</label>
                        <input type="text" name="bank_name" id="bank_name" class="form-control"
                               value="{{ old('bank_name') }}">
                        @error('bank_name')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="bank_account" class="form-label">رقم الحساب</label>
                        <input type="text" name="bank_account" id="bank_account" class="form-control"
                               value="{{ old('bank_account') }}">
                        @error('bank_account')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="notes" class="form-label">ملاحظات</label>
                <textarea name="notes" id="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                @error('notes')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">تأكيد الدفع</button>
                <a href="{{ route('suppliers.show', $supplier) }}" class="btn">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
    .mb-4 { margin-bottom: 20px; }

    .purchase-row {
        transition: all 0.2s;
    }
    .purchase-row:hover {
        background-color: #f0f9ff;
    }
    .purchase-row.selected {
        background-color: #dbeafe !important;
        border-right: 4px solid var(--primary);
    }
    .overdue-row {
        background-color: #fef2f2;
    }
    .overdue-row:hover {
        background-color: #fee2e2;
    }
    .alert-info {
        background: #e0f2fe;
        color: #0369a1;
        border: 1px solid #7dd3fc;
        padding: 12px 16px;
        border-radius: 8px;
    }
</style>
@endpush

@push('scripts')
<script>
function toggleCheckFields() {
    const method = document.getElementById('method').value;
    const checkFields = document.getElementById('checkFields');
    const bankFields = document.getElementById('bankFields');

    checkFields.style.display = method === 'check' ? 'block' : 'none';
    bankFields.style.display = (method === 'check' || method === 'bank_transfer' || method === 'instapay') ? 'block' : 'none';
}

// تحديد فاتورة شراء
function selectPurchase(row) {
    // إزالة التحديد السابق
    document.querySelectorAll('.purchase-row').forEach(r => r.classList.remove('selected'));

    // تحديد الصف الجديد
    row.classList.add('selected');

    // تحديث الحقول
    const purchaseId = row.dataset.id;
    const remaining = parseFloat(row.dataset.remaining);
    const invoiceNumber = row.querySelector('code').textContent;

    document.getElementById('purchase_id').value = purchaseId;
    document.getElementById('amount').value = remaining.toFixed(2);
    document.getElementById('selectedPurchaseNumber').textContent = invoiceNumber;
    document.getElementById('selectedPurchaseInfo').style.display = 'block';
}

// إلغاء تحديد الفاتورة
function clearSelectedPurchase() {
    document.querySelectorAll('.purchase-row').forEach(r => r.classList.remove('selected'));
    document.getElementById('purchase_id').value = '';
    document.getElementById('amount').value = '{{ $supplier->current_balance }}';
    document.getElementById('selectedPurchaseInfo').style.display = 'none';
}

document.addEventListener('DOMContentLoaded', function() {
    toggleCheckFields();

    // إضافة حدث النقر لصفوف الفواتير
    document.querySelectorAll('.purchase-row').forEach(row => {
        row.addEventListener('click', function() {
            selectPurchase(this);
        });
    });
});
</script>
@endpush
