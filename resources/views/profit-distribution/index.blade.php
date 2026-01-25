@extends('layouts.app')

@section('title', 'توزيعات الأرباح')

@section('content')
<div class="page-header">
    <div>
        <h1>📊 توزيعات الأرباح</h1>
        <p>سجل توزيعات أرباح الشركاء</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('profit-distribution.create') }}" class="btn btn-primary">+ توزيع أرباح جديد</a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
<div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card">
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>الفترة</th>
                    <th>إجمالي التوزيع</th>
                    <th>عدد الشركاء</th>
                    <th>تاريخ التوزيع</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($distributions as $distribution)
                <tr>
                    <td><strong>{{ $distribution->period }}</strong></td>
                    <td class="text-success">{{ number_format($distribution->total_amount, 2) }} ج.م</td>
                    <td>{{ $distribution->partners_count }} شريك</td>
                    <td>{{ \Carbon\Carbon::parse($distribution->distribution_date)->format('Y/m/d') }}</td>
                    <td>
                        <a href="{{ route('profit-distribution.show', $distribution->period) }}" class="btn btn-sm">👁️ عرض التفاصيل</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5">
                        <div class="empty-state">
                            <div class="empty-state-icon">📊</div>
                            <h3>لا توجد توزيعات</h3>
                            <p>ابدأ بتوزيع أرباح على الشركاء</p>
                            <a href="{{ route('profit-distribution.create') }}" class="btn btn-primary">+ توزيع أرباح جديد</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($distributions->hasPages())
    <div class="card-footer">
        {{ $distributions->links() }}
    </div>
    @endif
</div>

<style>
.text-success { color: #10b981; font-weight: 600; }
.alert { padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
.alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
.alert-danger { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
</style>
@endsection
