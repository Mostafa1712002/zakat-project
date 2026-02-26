@extends('layouts.app')

@section('title', 'العملاء')

@section('content')
<div class="page-header">
    <div>
        <h1>👥 العملاء</h1>
        <p>إدارة بيانات العملاء</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('customers.create') }}" class="btn btn-primary">+ إضافة عميل</a>
    </div>
</div>

<div class="card" style="margin-bottom: 20px;">
    <div class="card-body">
        <form action="{{ route('customers.index') }}" method="GET" class="filter-form">
            <div class="filter-row">
                <input type="text" name="search" class="form-control" placeholder="بحث بالاسم أو الهاتف..." value="{{ request('search') }}">
                <select name="item_type" class="form-control">
                    <option value="">كل الأصناف</option>
                    @foreach(customer_item_types() as $type)
                        <option value="{{ $type['value'] }}" {{ request('item_type') == $type['value'] ? 'selected' : '' }}>{{ $type['label'] }}</option>
                    @endforeach
                </select>
                <select name="balance" class="form-control">
                    <option value="">كل الأرصدة</option>
                    <option value="has_balance" {{ request('balance') == 'has_balance' ? 'selected' : '' }}>عليه رصيد</option>
                    <option value="no_balance" {{ request('balance') == 'no_balance' ? 'selected' : '' }}>بدون رصيد</option>
                </select>
                <button type="submit" class="btn btn-primary">بحث</button>
                <a href="{{ route('customers.index') }}" class="btn">إعادة تعيين</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-container overflow-auto">
        <table class="table text-nowrap">
            <thead>
                <tr>
                    <th>#</th>
                    <th>الاسم</th>
                    <th>الهاتف</th>
                    <th>الصنف</th>
                    <th>الرصيد المستحق</th>
                    @if(feature_enabled('customer_target'))
                    <th>التارجت</th>
                    @endif
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $customer)
                <tr>
                    <td>{{ $customer->id }}</td>
                    <td><strong>{{ $customer->name }}</strong></td>
                    <td>{{ $customer->phone ?? '-' }}</td>
                    <td>
                        @php
                            $itemTypes = collect(customer_item_types())->keyBy('value');
                            $itemTypeLabel = $itemTypes[$customer->item_type]['label'] ?? '-';
                            $itemTypeColors = ['fridge' => 'badge-primary', 'special' => 'badge-warning', 'trader' => 'badge-primary', 'regular' => 'badge-warning'];
                            $itemTypeClass = $itemTypeColors[$customer->item_type] ?? '';
                        @endphp
                        @if($itemTypeClass)
                            <span class="badge {{ $itemTypeClass }}">{{ $itemTypeLabel }}</span>
                        @else
                            <span class="text-muted">{{ $itemTypeLabel }}</span>
                        @endif
                    </td>
                    <td>
                        @if(($customer->total_remaining ?? 0) > 0)
                            <strong class="text-danger">{{ number_format($customer->total_remaining, 2) }} ج.م</strong>
                        @else
                            <span class="text-muted">0.00 ج.م</span>
                        @endif
                    </td>
                    @if(feature_enabled('customer_target'))
                    <td>
                        @if($customer->target_amount > 0)
                            @php
                                $percentage = $customer->target_achievement_percentage;
                                $progressColor = $percentage >= 100 ? '#16a34a' : ($percentage >= 50 ? '#f59e0b' : '#ef4444');
                            @endphp
                            <div style="min-width: 120px;">
                                <div style="display: flex; justify-content: space-between; font-size: 11px; margin-bottom: 2px;">
                                    <span>{{ number_format($percentage, 0) }}%</span>
                                    <span>{{ number_format($customer->target_amount, 0) }} ج.م</span>
                                </div>
                                <div style="background: #e5e7eb; border-radius: 4px; height: 6px; overflow: hidden;">
                                    <div style="background: {{ $progressColor }}; width: {{ min(100, $percentage) }}%; height: 100%;"></div>
                                </div>
                                @if($customer->hasAchievedTarget() && $customer->withdrawable_target_amount > 0)
                                    <div style="font-size: 10px; color: #16a34a; margin-top: 2px;">✅ متاح: {{ number_format($customer->withdrawable_target_amount, 0) }} ج.م</div>
                                @endif
                            </div>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    @endif
                    <td>
                        <div class="table-actions">
                            <a href="{{ route('customers.show', $customer) }}" class="btn btn-sm">عرض</a>
                            @if(($customer->total_remaining ?? 0) > 0)
                            <a href="{{ route('customers.collect.form', $customer) }}" class="btn btn-sm btn-success">💰 تحصيل</a>
                            @endif
                            <a href="{{ route('customers.edit', $customer) }}" class="btn btn-sm">تعديل</a>
                            @if(feature_enabled('customer_target') && $customer->hasAchievedTarget() && $customer->withdrawable_target_amount > 0)
                                <a href="{{ route('customers.withdraw-target.form', $customer) }}" class="btn btn-sm btn-success" title="سحب التارجت">🎯 سحب</a>
                            @endif
                            <form action="{{ route('customers.destroy', $customer) }}" method="POST" style="display: inline;" onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">حذف</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ feature_enabled('customer_target') ? 7 : 6 }}">
                        <div class="empty-state">
                            <div class="empty-state-icon">👥</div>
                            <h3>لا يوجد عملاء</h3>
                            <p>ابدأ بإضافة عميل جديد</p>
                            <a href="{{ route('customers.create') }}" class="btn btn-primary">+ إضافة عميل</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($customers->hasPages())
    <div class="card-footer">
        {{ $customers->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
