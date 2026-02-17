@extends('layouts.app')

@section('title', 'تحصيل من عميل')

@section('content')
<div class="page-header">
    <div>
        <h1>تحصيل من عميل</h1>
        <p>تسجيل تحصيل من العميل: {{ $customer->name }}</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('customers.show', $customer) }}" class="btn">← رجوع</a>
    </div>
</div>

<!-- Customer Info -->
<div class="card mb-4">
    <div class="card-header">
        <h3 class="card-title">بيانات العميل</h3>
    </div>
    <div class="card-body">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
            <div>
                <span class="text-muted">اسم العميل:</span>
                <strong>{{ $customer->name }}</strong>
            </div>
            <div>
                <span class="text-muted">كود العميل:</span>
                <strong>{{ $customer->code }}</strong>
            </div>
            @php
                $calculatedBalance = $unpaidSales->sum('remaining_amount');
            @endphp
            <div>
                <span class="text-muted">الرصيد المستحق:</span>
                <strong class="text-danger">{{ number_format($calculatedBalance) }} ج.م</strong>
            </div>
            <div>
                <span class="text-muted">الهاتف:</span>
                <strong>{{ $customer->phone ?? $customer->mobile ?? '-' }}</strong>
            </div>
        </div>
    </div>
</div>

<!-- Unpaid Invoices -->
@if(isset($unpaidSales) && $unpaidSales->count() > 0)
<div class="card mb-4">
    <div class="card-header">
        <h3 class="card-title">🧾 الفواتير المستحقة</h3>
    </div>
    <div class="table-container overflow-auto">
        <table class="table text-nowrap">
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
                @foreach($unpaidSales as $sale)
                <tr class="invoice-row {{ $sale->isOverdue() ? 'overdue-row' : '' }}"
                    data-id="{{ $sale->id }}"
                    data-remaining="{{ $sale->remaining_amount }}"
                    style="cursor: pointer;">
                    <td><code>{{ $sale->invoice_number }}</code></td>
                    <td>{{ $sale->invoice_date?->format('Y-m-d') }}</td>
                    <td>
                        {{ $sale->due_date?->format('Y-m-d') ?? '-' }}
                        @if($sale->isOverdue())
                            <span class="badge badge-danger">متأخرة</span>
                        @endif
                    </td>
                    <td>{{ number_format($sale->total_amount, 2) }} ج.م</td>
                    <td>{{ number_format($sale->paid_amount, 2) }} ج.م</td>
                    <td><strong class="text-danger">{{ number_format($sale->remaining_amount, 2) }} ج.م</strong></td>
                    <td>
                        @if($sale->payment_status === 'unpaid')
                            <span class="badge badge-danger">غير مدفوعة</span>
                        @elseif($sale->payment_status === 'partial')
                            <span class="badge badge-warning">جزئي</span>
                        @elseif($sale->payment_status === 'overdue')
                            <span class="badge badge-danger">متأخرة</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-body" style="background: #f8f9fa; border-top: 1px solid var(--border);">
        <small class="text-muted">💡 اضغط على فاتورة لتحديدها للتحصيل، أو اترك بدون تحديد للتحصيل على الحساب العام</small>
    </div>
</div>
@endif

<!-- Payment Form -->
<div class="card">
    <div class="card-body">
        <form action="{{ route('customers.collect', $customer) }}" method="POST">
            @csrf
            <input type="hidden" name="sale_id" id="sale_id" value="{{ old('sale_id') }}">

            <!-- Selected Invoice Info -->
            <div id="selectedInvoiceInfo" class="alert alert-info mb-4" style="display: none;">
                <strong>📋 الفاتورة المحددة:</strong>
                <span id="selectedInvoiceNumber"></span>
                <button type="button" class="btn btn-sm" onclick="clearSelectedInvoice()" style="float: left;">✕ إلغاء التحديد</button>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="amount" class="form-label">المبلغ *</label>
                    <input type="number" step="0.01" name="amount" id="amount" class="form-control"
                           value="{{ old('amount', $calculatedBalance) }}" min="0.01" required>
                    @error('amount')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="payment_date" class="form-label">تاريخ التحصيل *</label>
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
                <button type="submit" class="btn btn-primary">تأكيد التحصيل</button>
                <a href="{{ route('customers.show', $customer) }}" class="btn">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
    .mb-4 { margin-bottom: 20px; }

    .invoice-row {
        transition: all 0.2s;
    }
    .invoice-row:hover {
        background-color: #f0f9ff;
    }
    .invoice-row.selected {
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

// تحديد فاتورة
function selectInvoice(row) {
    // إزالة التحديد السابق
    document.querySelectorAll('.invoice-row').forEach(r => r.classList.remove('selected'));

    // تحديد الصف الجديد
    row.classList.add('selected');

    // تحديث الحقول
    const saleId = row.dataset.id;
    const remaining = parseFloat(row.dataset.remaining);
    const invoiceNumber = row.querySelector('code').textContent;

    document.getElementById('sale_id').value = saleId;
    document.getElementById('amount').value = remaining.toFixed(2);
    document.getElementById('selectedInvoiceNumber').textContent = invoiceNumber;
    document.getElementById('selectedInvoiceInfo').style.display = 'block';
}

// إلغاء تحديد الفاتورة
function clearSelectedInvoice() {
    document.querySelectorAll('.invoice-row').forEach(r => r.classList.remove('selected'));
    document.getElementById('sale_id').value = '';
    document.getElementById('amount').value = '{{ $calculatedBalance }}';
    document.getElementById('selectedInvoiceInfo').style.display = 'none';
}

document.addEventListener('DOMContentLoaded', function() {
    toggleCheckFields();

    // إضافة حدث النقر لصفوف الفواتير
    document.querySelectorAll('.invoice-row').forEach(row => {
        row.addEventListener('click', function() {
            selectInvoice(this);
        });
    });
});
</script>
@endpush
