@extends('layouts.app')

@section('title', 'الشركاء')

@section('content')
<div class="page-header">
    <div>
        <h1>🤝 الشركاء</h1>
        <p>إدارة بيانات الشركاء ورأس المال</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('partner-transactions.index') }}" class="btn">💰 سجل المعاملات</a>
        <a href="{{ route('partners.create') }}" class="btn btn-primary">+ إضافة شريك</a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

<!-- Summary Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background: #3b82f6;">👥</div>
        <div class="stat-content">
            <div class="stat-value">{{ $totals['total_partners'] }}</div>
            <div class="stat-label">عدد الشركاء النشطين</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: #10b981;">💵</div>
        <div class="stat-content">
            <div class="stat-value">{{ number_format($totals['total_investment'], 2) }}</div>
            <div class="stat-label">إجمالي رأس المال المبدئي</div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card" style="margin-bottom: 20px;">
    <div class="card-body">
        <form action="{{ route('partners.index') }}" method="GET" class="filter-form">
            <div class="filter-row">
                <input type="text" name="search" class="form-control" placeholder="بحث بالاسم أو الكود أو الهاتف..." value="{{ request('search') }}">
                <select name="is_active" class="form-control">
                    <option value="">الكل</option>
                    <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>نشط</option>
                    <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>غير نشط</option>
                </select>
                <button type="submit" class="btn btn-primary">🔍 بحث</button>
                <a href="{{ route('partners.index') }}" class="btn">إعادة تعيين</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>الكود</th>
                    <th>الاسم</th>
                    <th>الهاتف</th>
                    <th>نسبة الملكية</th>
                    <th>رأس المال</th>
                    <th>تاريخ الانضمام</th>
                    <th>الحالة</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($partners as $partner)
                <tr>
                    <td><code>{{ $partner->partner_code }}</code></td>
                    <td>
                        <a href="{{ route('partner-transactions.partner-history', $partner) }}">
                            <strong>{{ $partner->name }}</strong>
                        </a>
                    </td>
                    <td>{{ $partner->phone ?? '-' }}</td>
                    <td>{{ $partner->ownership_percentage }}%</td>
                    <td>{{ number_format($partner->initial_investment, 2) }} ج.م</td>
                    <td>{{ $partner->join_date?->format('Y/m/d') ?? '-' }}</td>
                    <td>
                        @if($partner->is_active)
                            <span class="badge badge-success">نشط</span>
                        @else
                            <span class="badge badge-danger">غير نشط</span>
                        @endif
                    </td>
                    <td>
                        <div class="table-actions">
                            <a href="{{ route('partners.show', $partner) }}" class="btn btn-sm" title="عرض">👁️</a>
                            <a href="{{ route('partner-transactions.partner-history', $partner) }}" class="btn btn-sm" title="سجل المعاملات">💰</a>
                            <a href="{{ route('partners.edit', $partner) }}" class="btn btn-sm" title="تعديل">✏️</a>
                            <form action="{{ route('partners.destroy', $partner) }}" method="POST" style="display: inline;" onsubmit="return confirm('هل أنت متأكد من حذف هذا الشريك؟')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="حذف">🗑️</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8">
                        <div class="empty-state">
                            <div class="empty-state-icon">🤝</div>
                            <h3>لا يوجد شركاء</h3>
                            <p>ابدأ بإضافة شريك جديد</p>
                            <a href="{{ route('partners.create') }}" class="btn btn-primary">+ إضافة شريك</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($partners->hasPages())
    <div class="card-footer">
        {{ $partners->withQueryString()->links() }}
    </div>
    @endif
</div>

<style>
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.stat-card {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 1.25rem;
    display: flex;
    align-items: center;
    gap: 1rem;
}
.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}
.stat-content { flex: 1; }
.stat-value { font-size: 1.5rem; font-weight: 700; }
.stat-label { color: var(--text-muted); font-size: 0.875rem; }
.filter-form { margin-bottom: 0; }
.filter-row { display: flex; gap: 1rem; flex-wrap: wrap; align-items: center; }
.filter-row .form-control { flex: 1; min-width: 150px; }
.filter-row .btn { white-space: nowrap; }
.table-actions { display: flex; gap: 0.25rem; }
.badge { padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; }
.badge-success { background: #10b981; color: white; }
.badge-danger { background: #ef4444; color: white; }
.alert { padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
.alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
</style>
@endsection
