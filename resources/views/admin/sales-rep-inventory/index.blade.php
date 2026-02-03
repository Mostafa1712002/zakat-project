@extends('layouts.app')

@section('title', 'مخازن المندوبين')

@section('content')
<div class="page-header">
    <div>
        <h1>📦 مخازن المندوبين</h1>
        <p>إدارة وتخصيص الأصناف للمندوبين</p>
    </div>
</div>

<!-- قائمة المندوبين -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">📋 المندوبين</h3>
    </div>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>المندوب</th>
                    <th>الفرع</th>
                    <th>عدد الأصناف</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($salesReps as $salesRep)
                <tr>
                    <td>
                        <strong>{{ $salesRep->name }}</strong>
                        <br><small class="text-muted">{{ $salesRep->code }}</small>
                    </td>
                    <td>{{ $salesRep->branch?->name ?? '-' }}</td>
                    <td>
                        <span class="badge badge-primary">{{ $salesRep->inventory_count }} صنف</span>
                    </td>
                    <td>
                        <a href="{{ route('admin.sales-rep-inventory.show', $salesRep) }}" class="btn btn-sm btn-primary">
                            📦 إدارة المخزون
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center text-muted">لا يوجد مندوبين نشطين</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
