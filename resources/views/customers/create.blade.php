@extends('layouts.app')

@section('title', 'إضافة عميل')

@section('content')
<div class="page-header">
    <div>
        <h1>➕ إضافة عميل جديد</h1>
        <p>أضف عميل جديد للنظام</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('customers.index') }}" class="btn">← رجوع للعملاء</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('customers.store') }}" method="POST">
            @csrf

            <h3 style="margin-bottom: 16px;">👤 البيانات الأساسية</h3>

            <div class="form-row">
                <div class="form-group">
                    <label for="name" class="form-label">اسم العميل *</label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required placeholder="اسم العميل">
                    @error('name')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="code" class="form-label">كود العميل</label>
                    <input type="text" name="code" id="code" class="form-control" value="{{ old('code') }}" placeholder="سيتم توليده تلقائياً" style="background: rgba(0,0,0,0.05);">
                    <small style="color: #64748b; font-size: 12px;">⚡ يتم توليده تلقائياً إذا تُرك فارغاً</small>
                    @error('code')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="type" class="form-label">نوع العميل *</label>
                <select name="type" id="type" class="form-control" required>
                    <option value="retail" {{ old('type', 'retail') == 'retail' ? 'selected' : '' }}>قطاعي</option>
                    <option value="wholesale" {{ old('type') == 'wholesale' ? 'selected' : '' }}>جملة</option>
                    <option value="corporate" {{ old('type') == 'corporate' ? 'selected' : '' }}>شركة</option>
                </select>
                @error('type')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <hr style="margin: 24px 0; border-color: var(--border-color);">
            <h3 style="margin-bottom: 16px;">📞 معلومات الاتصال</h3>

            <div class="form-row">
                <div class="form-group">
                    <label for="phone" class="form-label">الهاتف</label>
                    <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone') }}" placeholder="رقم الهاتف">
                    @error('phone')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="mobile" class="form-label">الموبايل</label>
                    <input type="text" name="mobile" id="mobile" class="form-control" value="{{ old('mobile') }}" placeholder="رقم الموبايل">
                    @error('mobile')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="city" class="form-label">المدينة</label>
                <input type="text" name="city" id="city" class="form-control" value="{{ old('city') }}" placeholder="المدينة">
                @error('city')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="address" class="form-label">العنوان</label>
                <textarea name="address" id="address" class="form-control" rows="2" placeholder="العنوان بالتفصيل">{{ old('address') }}</textarea>
                @error('address')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <hr style="margin: 24px 0; border-color: var(--border-color);">
            <h3 style="margin-bottom: 16px;">💰 الإعدادات المالية</h3>

            <div class="form-row">
                <div class="form-group">
                    <label for="price_tier" class="form-label">فئة السعر</label>
                    <select name="price_tier" id="price_tier" class="form-control">
                        <option value="">اختر فئة السعر</option>
                        <option value="retail" {{ old('price_tier') == 'retail' ? 'selected' : '' }}>قطاعي</option>
                        <option value="wholesale" {{ old('price_tier') == 'wholesale' ? 'selected' : '' }}>جملة</option>
                        <option value="special" {{ old('price_tier') == 'special' ? 'selected' : '' }}>خاص</option>
                    </select>
                    @error('price_tier')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="credit_limit" class="form-label">حد الائتمان (ج.م)</label>
                    <input type="number" step="0.01" name="credit_limit" id="credit_limit" class="form-control" value="{{ old('credit_limit', 0) }}" min="0">
                    @error('credit_limit')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="payment_terms_days" class="form-label">فترة السداد (بالأيام)</label>
                    <input type="number" name="payment_terms_days" id="payment_terms_days" class="form-control" value="{{ old('payment_terms_days', 0) }}" min="0">
                    @error('payment_terms_days')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <hr style="margin: 24px 0; border-color: var(--border-color);">
            <h3 style="margin-bottom: 16px;">🎯 التارجت والخصم</h3>

            <div class="form-row">
                <div class="form-group">
                    <label for="target_amount" class="form-label">مبلغ التارجت (ج.م)</label>
                    <input type="number" step="0.01" name="target_amount" id="target_amount" class="form-control" value="{{ old('target_amount', 0) }}" min="0" placeholder="المبلغ المطلوب للحصول على الخصم">
                    <small style="color: #64748b; font-size: 12px;">💡 عند وصول مشتريات العميل لهذا المبلغ يحصل على الخصم</small>
                    @error('target_amount')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="target_discount_percentage" class="form-label">نسبة خصم التارجت (%)</label>
                    <input type="number" step="0.01" name="target_discount_percentage" id="target_discount_percentage" class="form-control" value="{{ old('target_discount_percentage', 0) }}" min="0" max="100" placeholder="نسبة الخصم عند تحقيق التارجت">
                    <small style="color: #64748b; font-size: 12px;">📊 نسبة الخصم من إجمالي المشتريات عند تحقيق التارجت</small>
                    @error('target_discount_percentage')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <hr style="margin: 24px 0; border-color: var(--border-color);">
            <h3 style="margin-bottom: 16px;">🏢 التخصيص</h3>

            <div class="form-row">
                <div class="form-group">
                    <label for="branch_id" class="form-label">الفرع</label>
                    <select name="branch_id" id="branch_id" class="form-control">
                        <option value="">بدون تخصيص لفرع</option>
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
                    <label for="sales_rep_id" class="form-label">مندوب المبيعات</label>
                    <select name="sales_rep_id" id="sales_rep_id" class="form-control">
                        <option value="">بدون مندوب</option>
                        @foreach($salesReps as $rep)
                            <option value="{{ $rep->id }}" {{ old('sales_rep_id') == $rep->id ? 'selected' : '' }}>
                                {{ $rep->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('sales_rep_id')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                    <span>عميل نشط</span>
                </label>
            </div>

            <div class="form-group">
                <label for="notes" class="form-label">ملاحظات</label>
                <textarea name="notes" id="notes" class="form-control" rows="2" placeholder="ملاحظات إضافية عن العميل...">{{ old('notes') }}</textarea>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary">💾 حفظ العميل</button>
                <a href="{{ route('customers.index') }}" class="btn">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection
