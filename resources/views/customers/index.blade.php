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

<div class="card">
    <div class="table-container  overflow-auto">
        <table class="table text-nowrap">
            <thead>
                <tr>
                    <th>#</th>
                    <th>الاسم</th>
                    <th>الهاتف</th>
                    <th>النوع</th>
                    <th>التارجت</th>
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
                            $typeLabel = match ($customer->type) {
                                'wholesale' => 'جملة',
                                'corporate' => 'شركة',
                                default => 'قطاعي',
                            };
                            $typeClass = match ($customer->type) {
                                'corporate' => 'badge-primary',
                                'wholesale' => 'badge-warning',
                                default => 'badge-secondary',
                            };
                        @endphp
                        <span class="badge {{ $typeClass }}">{{ $typeLabel }}</span>
                    </td>
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
                    <td>
                        <div class="table-actions">
                            <a href="{{ route('customers.show', $customer) }}" class="btn btn-sm">عرض</a>
                            <a href="{{ route('customers.edit', $customer) }}" class="btn btn-sm">تعديل</a>
                            @if($customer->hasAchievedTarget() && $customer->withdrawable_target_amount > 0)
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
                    <td colspan="6">
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
    <div class="pagination">
        {{ $customers->links() }}
    </div>
    @endif
</div>
@endsection
