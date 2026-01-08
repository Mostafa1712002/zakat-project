@extends('layouts.app')

@section('title', 'تعديل مورد')

@section('content')
<div class="page-header">
    <div>
        <h1>✏️ تعديل مورد</h1>
        <p>تعديل بيانات {{ $supplier->name }}</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('suppliers.index') }}" class="btn">← رجوع</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('suppliers.update', $supplier) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-row">
                <div class="form-group">
                    <label for="name" class="form-label">اسم المورد *</label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $supplier->name) }}" required>
                    @error('name')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="code" class="form-label">الكود</label>
                    <input type="text" name="code" id="code" class="form-control" value="{{ old('code', $supplier->code) }}">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="phone" class="form-label">الهاتف</label>
                    <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone', $supplier->phone) }}">
                </div>

                <div class="form-group">
                    <label for="mobile" class="form-label">الموبايل</label>
                    <input type="text" name="mobile" id="mobile" class="form-control" value="{{ old('mobile', $supplier->mobile) }}">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="email" class="form-label">البريد الإلكتروني</label>
                    <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $supplier->email) }}">
                </div>

                <div class="form-group">
                    <label for="contact_person" class="form-label">جهة الاتصال</label>
                    <input type="text" name="contact_person" id="contact_person" class="form-control" value="{{ old('contact_person', $supplier->contact_person) }}">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="address" class="form-label">العنوان</label>
                    <input type="text" name="address" id="address" class="form-control" value="{{ old('address', $supplier->address) }}">
                </div>

                <div class="form-group">
                    <label for="city" class="form-label">المدينة</label>
                    <input type="text" name="city" id="city" class="form-control" value="{{ old('city', $supplier->city) }}">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="tax_number" class="form-label">الرقم الضريبي</label>
                    <input type="text" name="tax_number" id="tax_number" class="form-control" value="{{ old('tax_number', $supplier->tax_number) }}">
                </div>

                <div class="form-group">
                    <label for="payment_terms_days" class="form-label">شروط الدفع (أيام)</label>
                    <input type="number" name="payment_terms_days" id="payment_terms_days" class="form-control" value="{{ old('payment_terms_days', $supplier->payment_terms_days) }}" min="0">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="bank_name" class="form-label">اسم البنك</label>
                    <input type="text" name="bank_name" id="bank_name" class="form-control" value="{{ old('bank_name', $supplier->bank_name) }}">
                </div>

                <div class="form-group">
                    <label for="bank_account" class="form-label">رقم الحساب</label>
                    <input type="text" name="bank_account" id="bank_account" class="form-control" value="{{ old('bank_account', $supplier->bank_account) }}">
                </div>
            </div>

            <div class="form-group">
                <label for="is_active" class="form-label">الحالة</label>
                <select name="is_active" id="is_active" class="form-control">
                    <option value="1" {{ old('is_active', $supplier->is_active) == 1 ? 'selected' : '' }}>نشط</option>
                    <option value="0" {{ old('is_active', $supplier->is_active) == 0 ? 'selected' : '' }}>غير نشط</option>
                </select>
            </div>

            <div class="form-group">
                <label for="notes" class="form-label">ملاحظات</label>
                <textarea name="notes" id="notes" class="form-control" rows="3">{{ old('notes', $supplier->notes) }}</textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">💾 حفظ التعديلات</button>
                <a href="{{ route('suppliers.index') }}" class="btn">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection
