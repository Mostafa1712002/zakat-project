@extends('layouts.app')

@section('title', 'سحب تارجت عميل')

@section('content')
<div class="page-header">
    <div>
        <h1>🎯 سحب تارجت عميل</h1>
        <p>صرف خصم التارجت للعميل: {{ $customer->name }}</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('customers.index') }}" class="btn">← رجوع للعملاء</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <!-- معلومات التارجت -->
        <div class="target-info" style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border-radius: 12px; padding: 24px; margin-bottom: 24px; border: 1px solid #bae6fd;">
            <h3 style="margin: 0 0 20px; color: #0369a1;">📊 معلومات التارجت</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 20px;">
                <div class="stat-item">
                    <div style="color: #64748b; font-size: 13px; margin-bottom: 4px;">اسم العميل</div>
                    <div style="font-size: 18px; font-weight: bold; color: #1e293b;">{{ $customer->name }}</div>
                </div>
                <div class="stat-item">
                    <div style="color: #64748b; font-size: 13px; margin-bottom: 4px;">مبلغ التارجت</div>
                    <div style="font-size: 18px; font-weight: bold; color: #0369a1;">{{ number_format($customer->target_amount, 2) }} ج.م</div>
                </div>
                <div class="stat-item">
                    <div style="color: #64748b; font-size: 13px; margin-bottom: 4px;">إجمالي المشتريات</div>
                    <div style="font-size: 18px; font-weight: bold; color: #16a34a;">{{ number_format($customer->total_purchases, 2) }} ج.م</div>
                </div>
                <div class="stat-item">
                    <div style="color: #64748b; font-size: 13px; margin-bottom: 4px;">نسبة الخصم</div>
                    <div style="font-size: 18px; font-weight: bold; color: #7c3aed;">{{ $customer->target_discount_percentage }}%</div>
                </div>
                <div class="stat-item">
                    <div style="color: #64748b; font-size: 13px; margin-bottom: 4px;">قيمة الخصم الكلية</div>
                    <div style="font-size: 18px; font-weight: bold; color: #0891b2;">{{ number_format($customer->target_discount_amount, 2) }} ج.م</div>
                </div>
                <div class="stat-item">
                    <div style="color: #64748b; font-size: 13px; margin-bottom: 4px;">تم صرفه سابقاً</div>
                    <div style="font-size: 18px; font-weight: bold; color: #dc2626;">{{ number_format($customer->target_paid_amount, 2) }} ج.م</div>
                </div>
            </div>
            <div style="margin-top: 20px; padding: 16px; background: #dcfce7; border-radius: 8px; text-align: center;">
                <div style="color: #166534; font-size: 14px;">المتاح للسحب</div>
                <div style="font-size: 28px; font-weight: bold; color: #16a34a;">{{ number_format($customer->withdrawable_target_amount, 2) }} ج.م</div>
            </div>
        </div>

        <!-- نموذج السحب -->
        <form action="{{ route('customers.withdraw-target.store', $customer) }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="amount" class="form-label">المبلغ المراد صرفه *</label>
                <input type="number" step="0.01" name="amount" id="amount" class="form-control"
                       value="{{ old('amount', $customer->withdrawable_target_amount) }}"
                       min="0.01"
                       max="{{ $customer->withdrawable_target_amount }}"
                       required>
                <small style="color: #64748b; font-size: 12px;">الحد الأقصى: {{ number_format($customer->withdrawable_target_amount, 2) }} ج.م</small>
                @error('amount')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="notes" class="form-label">ملاحظات</label>
                <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="ملاحظات إضافية...">{{ old('notes') }}</textarea>
                @error('notes')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group" style="margin-top: 24px;">
                <button type="submit" class="btn btn-primary" onclick="return confirm('هل أنت متأكد من صرف هذا المبلغ من الخزنة؟')">
                    💸 صرف التارجت
                </button>
                <a href="{{ route('customers.index') }}" class="btn">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection
