@extends('layouts.app')

@section('title', 'تعديل معاملة شريك')

@section('content')
<div class="page-header">
    <div>
        <h1>✏️ تعديل معاملة</h1>
        <p>{{ $partnerTransaction->transaction_number }}</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('partner-transactions.index') }}" class="btn">← رجوع</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('partner-transactions.update', $partnerTransaction) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-row">
                <div class="form-group">
                    <label for="partner_id" class="form-label">الشريك *</label>
                    <select name="partner_id" id="partner_id" class="form-control" required>
                        <option value="">-- اختر الشريك --</option>
                        @foreach($partners as $partner)
                            <option value="{{ $partner->id }}" {{ old('partner_id', $partnerTransaction->partner_id) == $partner->id ? 'selected' : '' }}>
                                {{ $partner->name }} ({{ $partner->ownership_percentage }}%)
                            </option>
                        @endforeach
                    </select>
                    @error('partner_id')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="type" class="form-label">نوع المعاملة *</label>
                    <select name="type" id="type" class="form-control" required>
                        <option value="withdrawal" {{ old('type', $partnerTransaction->type) == 'withdrawal' ? 'selected' : '' }}>💸 سحب أرباح</option>
                        <option value="profit_share" {{ old('type', $partnerTransaction->type) == 'profit_share' ? 'selected' : '' }}>📊 توزيع أرباح</option>
                        <option value="investment" {{ old('type', $partnerTransaction->type) == 'investment' ? 'selected' : '' }}>💵 إضافة رأس مال</option>
                        <option value="return" {{ old('type', $partnerTransaction->type) == 'return' ? 'selected' : '' }}>↩️ إرجاع رأس مال</option>
                    </select>
                    @error('type')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="amount" class="form-label">المبلغ (ج.م) *</label>
                    <input type="number" step="0.01" name="amount" id="amount" class="form-control" value="{{ old('amount', $partnerTransaction->amount) }}" required min="0.01">
                    @error('amount')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="transaction_date" class="form-label">تاريخ المعاملة *</label>
                    <input type="date" name="transaction_date" id="transaction_date" class="form-control" value="{{ old('transaction_date', $partnerTransaction->transaction_date->format('Y-m-d')) }}" required>
                    @error('transaction_date')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="period" class="form-label">الفترة</label>
                    <input type="text" name="period" id="period" class="form-control" value="{{ old('period', $partnerTransaction->period) }}" placeholder="مثال: 2026-Q1 أو 2026">
                    @error('period')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="payment_method" class="form-label">طريقة الدفع *</label>
                    <select name="payment_method" id="payment_method" class="form-control" required>
                        <option value="cash" {{ old('payment_method', $partnerTransaction->payment_method) == 'cash' ? 'selected' : '' }}>💵 نقدي</option>
                        <option value="bank_transfer" {{ old('payment_method', $partnerTransaction->payment_method) == 'bank_transfer' ? 'selected' : '' }}>🏦 تحويل بنكي</option>
                        <option value="check" {{ old('payment_method', $partnerTransaction->payment_method) == 'check' ? 'selected' : '' }}>📝 شيك</option>
                    </select>
                    @error('payment_method')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="reference_number" class="form-label">رقم المرجع</label>
                <input type="text" name="reference_number" id="reference_number" class="form-control" value="{{ old('reference_number', $partnerTransaction->reference_number) }}">
                @error('reference_number')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="description" class="form-label">الوصف</label>
                <textarea name="description" id="description" class="form-control" rows="2">{{ old('description', $partnerTransaction->description) }}</textarea>
            </div>

            <div class="form-group">
                <label for="notes" class="form-label">ملاحظات</label>
                <textarea name="notes" id="notes" class="form-control" rows="2">{{ old('notes', $partnerTransaction->notes) }}</textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-lg">💾 حفظ التعديلات</button>
                <a href="{{ route('partner-transactions.index') }}" class="btn btn-lg">إلغاء</a>
            </div>
        </form>
    </div>
</div>

<style>
.form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
}
.form-actions {
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid var(--border-color);
}
.btn-lg {
    padding: 12px 24px;
    font-size: 1rem;
}
</style>
@endsection
