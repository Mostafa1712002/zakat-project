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

<div class="card" style="margin-bottom: 20px;">
    <div class="card-body">
        <form action="{{ route('sales-reps.index') }}" method="GET" class="filter-form">
            <div class="filter-row">
                <input type="text" name="search" class="form-control" placeholder="بحث بالاسم أو الكود..." value="{{ request('search') }}">
                <select name="is_active" class="form-control">
                    <option value="">كل الحالات</option>
                    <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>نشط</option>
                    <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>غير نشط</option>
                </select>
                <button type="submit" class="btn btn-primary">بحث</button>
                <a href="{{ route('sales-reps.index') }}" class="btn">إعادة تعيين</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-container overflow-auto">
        <table class="table text-nowrap">
            <thead>
                <tr>
                    <th>الاسم</th>
                    <th>الكود</th>
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
                    $alreadyWithdrawn = \App\Models\CommissionWithdrawal::isAlreadyWithdrawn($rep->id, now()->year, now()->month);
                @endphp
                <tr>
                    <td><strong>{{ $rep->name }}</strong></td>
                    <td>{{ $rep->code ?? '-' }}</td>
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
                                @if($alreadyWithdrawn)
                                    <span class="btn btn-sm btn-secondary" style="cursor: not-allowed;" title="تم سحب العمولة">✅ تم السحب</span>
                                @else
                                    <a href="{{ route('sales-reps.withdraw-commission.form', $rep) }}" class="btn btn-sm btn-success" title="سحب العمولة">💰 سحب</a>
                                @endif
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
                    <td colspan="6" class="text-center">لا يوجد مندوبين</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($salesReps->hasPages())
    <div class="card-footer">
        {{ $salesReps->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
