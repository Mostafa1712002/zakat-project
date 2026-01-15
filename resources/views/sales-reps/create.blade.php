@extends('layouts.app')

@section('title', 'إضافة مندوب')

@section('content')
<div class="page-header">
    <div>
        <h1>إضافة مندوب جديد</h1>
        <p>إضافة مندوب مبيعات جديد مع حساب تسجيل الدخول</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('sales-reps.index') }}" class="btn">رجوع</a>
    </div>
</div>

<form action="{{ route('sales-reps.store') }}" method="POST">
    @csrf

    <!-- User Account Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h3 class="card-title">بيانات حساب تسجيل الدخول</h3>
        </div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group">
                    <label for="name" class="form-label">اسم المندوب *</label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">البريد الإلكتروني (لتسجيل الدخول) *</label>
                    <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" required>
                    @error('email')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="password" class="form-label">كلمة المرور *</label>
                    <input type="password" name="password" id="password" class="form-control" required minlength="8">
                    @error('password')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation" class="form-label">تأكيد كلمة المرور *</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
                </div>
            </div>
        </div>
    </div>

    <!-- Sales Rep Info Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h3 class="card-title">بيانات المندوب</h3>
        </div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group">
                    <label for="code" class="form-label">كود المندوب</label>
                    <input type="text" name="code" id="code" class="form-control" value="{{ old('code') }}" placeholder="سيتم توليده تلقائياً">
                    <small class="text-muted">يتم توليده تلقائياً إذا تُرك فارغاً</small>
                    @error('code')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="phone" class="form-label">الهاتف</label>
                    <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone') }}">
                    @error('phone')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="branch_id" class="form-label">الفرع *</label>
                    <select name="branch_id" id="branch_id" class="form-control" required>
                        <option value="">اختر الفرع</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('branch_id')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="is_active" class="form-label">الحالة</label>
                    <select name="is_active" id="is_active" class="form-control">
                        <option value="1" {{ old('is_active', 1) == 1 ? 'selected' : '' }}>نشط</option>
                        <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>غير نشط</option>
                    </select>
                    @error('is_active')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <!-- Warehouse Assignment Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h3 class="card-title">المخازن المتاحة</h3>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label class="form-label">اختر المخازن التي يمكن للمندوب البيع منها</label>
                <div class="checkbox-group">
                    @foreach($warehouses as $warehouse)
                        <label class="checkbox-label">
                            <input type="checkbox" name="warehouse_ids[]" value="{{ $warehouse->id }}"
                                   {{ in_array($warehouse->id, old('warehouse_ids', [])) ? 'checked' : '' }}>
                            {{ $warehouse->name }}
                            @if($warehouse->branch)
                                <small class="text-muted">({{ $warehouse->branch->name }})</small>
                            @endif
                        </label>
                    @endforeach
                </div>
                @error('warehouse_ids')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="default_warehouse_id" class="form-label">المخزن الافتراضي</label>
                <select name="default_warehouse_id" id="default_warehouse_id" class="form-control">
                    <option value="">اختر المخزن الافتراضي</option>
                    @foreach($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}" {{ old('default_warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                            {{ $warehouse->name }}
                        </option>
                    @endforeach
                </select>
                <small class="text-muted">سيتم استخدامه كمخزن افتراضي عند إنشاء الفواتير</small>
                @error('default_warehouse_id')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <!-- Commission Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h3 class="card-title">العمولات والأهداف</h3>
        </div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group">
                    <label for="commission_rate" class="form-label">نسبة/قيمة العمولة</label>
                    <input type="number" step="0.01" name="commission_rate" id="commission_rate" class="form-control"
                           value="{{ old('commission_rate', 0) }}" min="0" max="100">
                    @error('commission_rate')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="commission_type" class="form-label">نوع العمولة</label>
                    <select name="commission_type" id="commission_type" class="form-control">
                        <option value="percentage" {{ old('commission_type') == 'percentage' ? 'selected' : '' }}>نسبة مئوية (%)</option>
                        <option value="fixed" {{ old('commission_type') == 'fixed' ? 'selected' : '' }}>مبلغ ثابت (ج.م)</option>
                    </select>
                    @error('commission_type')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="sales_target" class="form-label">الهدف البيعي الشهري (ج.م)</label>
                <input type="number" step="0.01" name="sales_target" id="sales_target" class="form-control"
                       value="{{ old('sales_target', 0) }}" min="0">
                @error('sales_target')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <!-- Notes Section -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="form-group">
                <label for="notes" class="form-label">ملاحظات</label>
                <textarea name="notes" id="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                @error('notes')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">حفظ</button>
        <a href="{{ route('sales-reps.index') }}" class="btn">إلغاء</a>
    </div>
</form>
@endsection

@push('styles')
<style>
    .mb-4 { margin-bottom: 20px; }
    .checkbox-group {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 12px;
        padding: 12px;
        background: var(--bg-secondary);
        border-radius: 8px;
    }
    .checkbox-label {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        background: var(--bg-primary);
        border-radius: 6px;
        cursor: pointer;
        transition: background 0.2s;
    }
    .checkbox-label:hover {
        background: var(--bg-hover);
    }
    .checkbox-label input[type="checkbox"] {
        width: 18px;
        height: 18px;
    }
    .text-muted {
        color: var(--text-muted);
        font-size: 12px;
    }
</style>
@endpush
