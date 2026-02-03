@extends('layouts.app')

@section('title', 'المندوبين')

@section('content')
<div class="page-header">
    <div>
        <h1>👔 المندوبين</h1>
        <p>إدارة مندوبي المبيعات</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('sales-reps.create') }}" class="btn btn-primary">+ إضافة مندوب</a>
    </div>
</div>

<div class="card">
    <div class="table-container overflow-auto">
        <table class="table text-nowrap">
            <thead>
                <tr>
                    <th>الاسم</th>
                    <th>الكود</th>
                    <th>النوع</th>
                    <th>نسبة العمولة</th>
                    <th>التارجت الشهري</th>
                    <th>الحالة</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($salesReps as $rep)
                @php
                    $monthlyAchievement = $rep->getMonthlyTargetAchievement();
                    $hasMetTarget = $rep->hasMetMonthlyTarget();
                    $commission = $rep->calculateMonthlyCommission();
                    $progressColor = $monthlyAchievement >= 100 ? '#16a34a' : ($monthlyAchievement >= 50 ? '#f59e0b' : '#ef4444');
                @endphp
                <tr>
                    <td><strong>{{ $rep->name }}</strong></td>
                    <td>{{ $rep->code ?? '-' }}</td>
                    <td>
                        @if($rep->type === 'fridge')
                            <span class="badge badge-info">🧊 تلاجة</span>
                        @else
                            <span class="badge badge-warning">⭐ خاص</span>
                        @endif
                    </td>
                    <td>{{ $rep->commission_rate }}{{ $rep->commission_type === 'percentage' ? '%' : ' ج.م' }}</td>
                    <td>
                        @if($rep->sales_target > 0)
                            <div style="min-width: 140px;">
                                <div style="display: flex; justify-content: space-between; font-size: 11px; margin-bottom: 2px;">
                                    <span>{{ number_format($monthlyAchievement, 0) }}%</span>
                                    <span>{{ number_format($rep->sales_target, 0) }} ج.م</span>
                                </div>
                                <div style="background: #e5e7eb; border-radius: 4px; height: 6px; overflow: hidden;">
                                    <div style="background: {{ $progressColor }}; width: {{ min(100, $monthlyAchievement) }}%; height: 100%;"></div>
                                </div>
                                @if($hasMetTarget && $commission > 0)
                                    <div style="font-size: 10px; color: #16a34a; margin-top: 2px;">✅ عمولة: {{ number_format($commission, 0) }} ج.م</div>
                                @endif
                            </div>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        @if($rep->is_active)
                            <span class="badge badge-success">نشط</span>
                        @else
                            <span class="badge badge-danger">غير نشط</span>
                        @endif
                    </td>
                    <td>
                        <div class="btn-group">
                            <a href="{{ route('sales-reps.show', $rep) }}" class="btn btn-sm">عرض</a>
                            <a href="{{ route('sales-reps.edit', $rep) }}" class="btn btn-sm">تعديل</a>
                            @if($hasMetTarget && $commission > 0)
                                <a href="{{ route('sales-reps.withdraw-commission.form', $rep) }}" class="btn btn-sm btn-success" title="سحب العمولة">💰 سحب</a>
                            @endif
                            <form action="{{ route('sales-reps.destroy', $rep) }}" method="POST" style="display: inline;" onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">حذف</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center">لا يوجد مندوبين</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($salesReps->hasPages())
    <div class="pagination">
        {{ $salesReps->links() }}
    </div>
    @endif
</div>
@endsection
