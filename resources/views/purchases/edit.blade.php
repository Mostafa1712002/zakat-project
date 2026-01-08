@extends('layouts.app')

@section('title', 'تعديل فاتورة الشراء')

@section('content')
<div class="page-header">
    <div>
        <h1>✏️ تعديل فاتورة الشراء</h1>
        <p>{{ $purchase->invoice_number }}</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('purchases.index') }}" class="btn">← رجوع</a>
    </div>
</div>

<form action="{{ route('purchases.update', $purchase) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="grid-2">
        <div class="card">
            <div class="card-body">
                <h3>📋 بيانات الفاتورة</h3>

                <div class="form-group">
                    <label class="form-label">المورد *</label>
                    <select name="supplier_id" class="form-control" required>
                        @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ $purchase->supplier_id == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">تاريخ الفاتورة *</label>
                        <input type="date" name="invoice_date" class="form-control" value="{{ $purchase->invoice_date?->format('Y-m-d') }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">الحالة *</label>
                        <select name="status" class="form-control" required>
                            <option value="draft" {{ $purchase->status == 'draft' ? 'selected' : '' }}>مسودة</option>
                            <option value="ordered" {{ $purchase->status == 'ordered' ? 'selected' : '' }}>تم الطلب</option>
                            <option value="received" {{ $purchase->status == 'received' ? 'selected' : '' }}>مستلم</option>
                            <option value="cancelled" {{ $purchase->status == 'cancelled' ? 'selected' : '' }}>ملغي</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">ملاحظات</label>
                    <textarea name="notes" class="form-control" rows="3">{{ $purchase->notes }}</textarea>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h3>💰 ملخص الفاتورة</h3>
                <table class="table">
                    <tr><td>الإجمالي الفرعي</td><td>{{ number_format($purchase->subtotal, 2) }} ج.م</td></tr>
                    <tr><td>الخصم</td><td>{{ number_format($purchase->discount_amount, 2) }} ج.م</td></tr>
                    <tr><td>الشحن</td><td>{{ number_format($purchase->shipping_amount, 2) }} ج.م</td></tr>
                    <tr><td><strong>الإجمالي</strong></td><td><strong>{{ number_format($purchase->total_amount, 2) }} ج.م</strong></td></tr>
                    <tr><td>المدفوع</td><td>{{ number_format($purchase->paid_amount, 2) }} ج.م</td></tr>
                    <tr><td>المتبقي</td><td>{{ number_format($purchase->remaining_amount, 2) }} ج.م</td></tr>
                </table>
            </div>
        </div>
    </div>

    <div class="card" style="margin-top: 1.5rem;">
        <div class="card-body">
            <button type="submit" class="btn btn-primary btn-lg">💾 حفظ التعديلات</button>
            <a href="{{ route('purchases.index') }}" class="btn btn-lg">إلغاء</a>
        </div>
    </div>
</form>

<style>
.grid-2 { display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 1.5rem; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.card-body h3 { margin-bottom: 1rem; font-size: 1rem; }
.btn-lg { padding: 14px 32px; font-size: 1rem; }
</style>
@endsection
