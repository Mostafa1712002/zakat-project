@extends('layouts.app')

@section('title', 'تعديل عميل')

@section('content')
<div class="page-header">
    <div>
        <h1>✏️ تعديل عميل: {{ $customer->name }}</h1>
        <p>تعديل بيانات العميل</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('customers.index') }}" class="btn">← رجوع للعملاء</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('customers.update', $customer) }}" method="POST">
            @csrf
            @method('PUT')

            <h3 style="margin-bottom: 16px;">👤 البيانات الأساسية</h3>

            <div class="form-row">
                <div class="form-group">
                    <label for="name" class="form-label">اسم العميل *</label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $customer->name) }}" required>
                    @error('name')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="code" class="form-label">كود العميل</label>
                    <input type="text" name="code" id="code" class="form-control" value="{{ old('code', $customer->code) }}">
                    @error('code')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="item_type" class="form-label">صنف العميل</label>
                <select name="item_type" id="item_type" class="form-control">
                    <option value="">اختر صنف العميل</option>
                    <option value="fridge" {{ old('item_type', $customer->item_type) == 'fridge' ? 'selected' : '' }}>تلاجة</option>
                    <option value="special" {{ old('item_type', $customer->item_type) == 'special' ? 'selected' : '' }}>خاص</option>
                </select>
                @error('item_type')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <hr style="margin: 24px 0; border-color: var(--border-color);">
            <h3 style="margin-bottom: 16px;">📞 معلومات الاتصال</h3>

            <div class="form-row">
                <div class="form-group">
                    <label for="phone" class="form-label">الهاتف</label>
                    <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone', $customer->phone) }}">
                    @error('phone')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="mobile" class="form-label">الموبايل</label>
                    <input type="text" name="mobile" id="mobile" class="form-control" value="{{ old('mobile', $customer->mobile) }}">
                    @error('mobile')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="city" class="form-label">المدينة</label>
                <input type="text" name="city" id="city" class="form-control" value="{{ old('city', $customer->city) }}">
                @error('city')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="address" class="form-label">العنوان</label>
                <textarea name="address" id="address" class="form-control" rows="2">{{ old('address', $customer->address) }}</textarea>
                @error('address')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <hr style="margin: 24px 0; border-color: var(--border-color);">
            <h3 style="margin-bottom: 16px;">🎯 التارجت والخصم</h3>

            <div class="form-row">
                <div class="form-group">
                    <label for="target_amount" class="form-label">التارجت السنوي (ج.م)</label>
                    <input type="number" step="0.01" name="target_amount" id="target_amount" class="form-control" value="{{ old('target_amount', $customer->target_amount) }}" min="0" placeholder="المبلغ المطلوب للحصول على الخصم">
                    <small style="color: #64748b; font-size: 12px;">💡 عند وصول مشتريات العميل لهذا المبلغ يحصل على الخصم</small>
                    @error('target_amount')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="target_discount_percentage" class="form-label">نسبة خصم التارجت (%)</label>
                    <input type="number" step="0.01" name="target_discount_percentage" id="target_discount_percentage" class="form-control" value="{{ old('target_discount_percentage', $customer->target_discount_percentage) }}" min="0" max="100" placeholder="نسبة الخصم عند تحقيق التارجت">
                    <small style="color: #64748b; font-size: 12px;">📊 نسبة الخصم من إجمالي المشتريات عند تحقيق التارجت</small>
                    @error('target_discount_percentage')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            @if($customer->target_amount > 0)
            <div class="target-status-card" style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border-radius: 12px; padding: 20px; margin-bottom: 20px; border: 1px solid #bae6fd;">
                <h4 style="margin: 0 0 15px; color: #0369a1;">📊 حالة التارجت</h4>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px;">
                    <div>
                        <div style="color: #64748b; font-size: 12px;">إجمالي المشتريات</div>
                        <div style="font-size: 18px; font-weight: bold; color: #0369a1;">{{ number_format($customer->total_purchases, 2) }} ج.م</div>
                    </div>
                    <div>
                        <div style="color: #64748b; font-size: 12px;">نسبة التحقيق</div>
                        <div style="font-size: 18px; font-weight: bold; color: {{ $customer->hasAchievedTarget() ? '#16a34a' : '#ea580c' }};">{{ number_format($customer->target_achievement_percentage, 1) }}%</div>
                    </div>
                    <div>
                        <div style="color: #64748b; font-size: 12px;">قيمة الخصم المستحقة</div>
                        <div style="font-size: 18px; font-weight: bold; color: #16a34a;">{{ number_format($customer->target_discount_amount, 2) }} ج.م</div>
                    </div>
                    <div>
                        <div style="color: #64748b; font-size: 12px;">تم صرفه</div>
                        <div style="font-size: 18px; font-weight: bold; color: #dc2626;">{{ number_format($customer->target_paid_amount, 2) }} ج.م</div>
                    </div>
                </div>
                @if($customer->hasAchievedTarget())
                    <div style="margin-top: 15px; padding: 10px; background: #dcfce7; border-radius: 8px; color: #166534;">
                        ✅ العميل حقق التارجت! المتاح للسحب: <strong>{{ number_format($customer->withdrawable_target_amount, 2) }} ج.م</strong>
                    </div>
                @else
                    <div style="margin-top: 15px; padding: 10px; background: #fef3c7; border-radius: 8px; color: #92400e;">
                        ⏳ المتبقي لتحقيق التارجت: <strong>{{ number_format($customer->remaining_to_target, 2) }} ج.م</strong>
                    </div>
                @endif
            </div>
            @endif

            <hr style="margin: 24px 0; border-color: var(--border-color);">
            <h3 style="margin-bottom: 16px;">🏢 التخصيص</h3>

            <div class="form-row">
                <div class="form-group">
                    <label for="branch_id" class="form-label">الفرع</label>
                    <select name="branch_id" id="branch_id" class="form-control">
                        <option value="">بدون تخصيص لفرع</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ old('branch_id', $customer->branch_id) == $branch->id ? 'selected' : '' }}>
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
                            <option value="{{ $rep->id }}" {{ old('sales_rep_id', $customer->sales_rep_id) == $rep->id ? 'selected' : '' }}>
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
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $customer->is_active) ? 'checked' : '' }}>
                    <span>عميل نشط</span>
                </label>
            </div>

            <div class="form-group">
                <label for="notes" class="form-label">ملاحظات</label>
                <textarea name="notes" id="notes" class="form-control" rows="2">{{ old('notes', $customer->notes) }}</textarea>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary">💾 حفظ التعديلات</button>
                <a href="{{ route('customers.index') }}" class="btn">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection
