@extends('layouts.app')

@section('title', 'سحب عمولة مندوب')

@section('content')
<div class="page-header">
    <div>
        <h1>💰 سحب عمولة مندوب</h1>
        <p>صرف عمولة المندوب: {{ $salesRep->name }}</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('sales-reps.index') }}" class="btn">← رجوع للمندوبين</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <!-- معلومات العمولة -->
        <div class="commission-info" style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border-radius: 12px; padding: 24px; margin-bottom: 24px; border: 1px solid #fbbf24;">
            <h3 style="margin: 0 0 20px; color: #92400e;">📊 معلومات العمولة - {{ \Carbon\Carbon::create($year, $month, 1)->translatedFormat('F Y') }}</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 20px;">
                <div class="stat-item">
                    <div style="color: #78350f; font-size: 13px; margin-bottom: 4px;">اسم المندوب</div>
                    <div style="font-size: 18px; font-weight: bold; color: #1e293b;">{{ $salesRep->name }}</div>
                </div>
                <div class="stat-item">
                    <div style="color: #78350f; font-size: 13px; margin-bottom: 4px;">مبيعات الشهر</div>
                    <div style="font-size: 18px; font-weight: bold; color: #0369a1;">{{ number_format($commissionDetails['monthly_sales'], 2) }} ج.م</div>
                </div>
                <div class="stat-item">
                    <div style="color: #78350f; font-size: 13px; margin-bottom: 4px;">التارجت المطلوب</div>
                    <div style="font-size: 18px; font-weight: bold; color: #7c3aed;">{{ number_format($commissionDetails['sales_target'], 2) }} ج.م</div>
                </div>
                <div class="stat-item">
                    <div style="color: #78350f; font-size: 13px; margin-bottom: 4px;">نسبة التحقيق</div>
                    <div style="font-size: 18px; font-weight: bold; color: {{ $commissionDetails['target_achievement'] >= 100 ? '#16a34a' : '#ea580c' }};">{{ number_format($commissionDetails['target_achievement'], 1) }}%</div>
                </div>
                <div class="stat-item">
                    <div style="color: #78350f; font-size: 13px; margin-bottom: 4px;">نسبة العمولة</div>
                    <div style="font-size: 18px; font-weight: bold; color: #0891b2;">
                        {{ $salesRep->commission_rate }}{{ $salesRep->commission_type === 'percentage' ? '%' : ' ج.م (ثابت)' }}
                    </div>
                </div>
            </div>
            <div style="margin-top: 20px; padding: 16px; background: #dcfce7; border-radius: 8px; text-align: center;">
                <div style="color: #166534; font-size: 14px;">العمولة المستحقة</div>
                <div style="font-size: 28px; font-weight: bold; color: #16a34a;">{{ number_format($commissionDetails['commission_earned'], 2) }} ج.م</div>
            </div>
        </div>

        <!-- نموذج السحب -->
        <form action="{{ route('sales-reps.withdraw-commission.store', $salesRep) }}" method="POST">
            @csrf
            <input type="hidden" name="year" value="{{ $year }}">
            <input type="hidden" name="month" value="{{ $month }}">

            <div class="form-group">
                <label for="amount" class="form-label">المبلغ المراد صرفه *</label>
                <input type="number" step="0.01" name="amount" id="amount" class="form-control"
                       value="{{ old('amount', $commissionDetails['commission_earned']) }}"
                       min="0.01"
                       max="{{ $commissionDetails['commission_earned'] }}"
                       required>
                <small style="color: #64748b; font-size: 12px;">الحد الأقصى: {{ number_format($commissionDetails['commission_earned'], 2) }} ج.م</small>
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
                    💸 صرف العمولة
                </button>
                <a href="{{ route('sales-reps.index') }}" class="btn">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection
