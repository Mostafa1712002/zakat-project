@extends('layouts.app')

@section('title', 'العملاء')

@section('content')
@php
    $typeLabels = [
        'company'    => 'شركة',
        'government' => 'جهة حكومية',
        'individual' => 'فرد',
    ];
@endphp
<div class="page-header">
    <div>
        <h1>👥 العملاء</h1>
        <p>إدارة عملاء المنصة مع بيانات هيئة الزكاة (ZATCA) والعنوان الوطني</p>
    </div>
    @can('create', \App\Domain\Customer\Models\Customer::class)
        <div class="header-actions">
            <a href="{{ route('admin.customers.create') }}" class="btn btn-primary">
                ➕ إضافة عميل
            </a>
        </div>
    @endcan
</div>

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card" style="padding:1rem;margin-bottom:1rem">
    <form method="GET" action="{{ route('admin.customers.index') }}"
          class="filter-row" style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:flex-end">
        <div class="form-group" style="margin:0;flex:1;min-width:220px">
            <label for="search">بحث</label>
            <input type="text" id="search" name="search" value="{{ request('search') }}"
                   class="form-control" placeholder="الاسم أو الرقم الضريبي أو البريد">
        </div>
        <div class="form-group" style="margin:0;min-width:180px">
            <label for="type">نوع العميل</label>
            <select id="type" name="type" class="form-control">
                <option value="">الكل</option>
                @foreach ($typeLabels as $value => $label)
                    <option value="{{ $value }}" @selected(request('type') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group" style="margin:0;min-width:200px">
            <label for="branch_id">الفرع</label>
            <select id="branch_id" name="branch_id" class="form-control">
                <option value="">الكل</option>
                @foreach ($branches as $branch)
                    <option value="{{ $branch->id }}" @selected(request('branch_id') == $branch->id)>{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>
        <div style="display:flex;gap:.5rem">
            <button type="submit" class="btn btn-primary">بحث</button>
            <a href="{{ route('admin.customers.index') }}" class="btn">إعادة تعيين</a>
        </div>
    </form>
</div>

<div class="card" style="padding:1rem">
    <table class="table" style="width:100%">
        <thead>
            <tr>
                <th>#</th>
                <th>الاسم</th>
                <th>النوع</th>
                <th>الرقم الضريبي</th>
                <th>مدير الحساب</th>
                <th>الفرع</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($customers as $customer)
                <tr>
                    <td>{{ $customer->id }}</td>
                    <td>
                        <a href="{{ route('admin.customers.show', $customer) }}">
                            <strong>{{ $customer->name }}</strong>
                        </a>
                        @if ($customer->is_tax_exempt)
                            <span class="badge badge-warning">معفى</span>
                        @endif
                    </td>
                    <td>{{ $typeLabels[$customer->type] ?? $customer->type }}</td>
                    <td>
                        @if ($customer->vat_number)
                            <code>{{ $customer->vat_number }}</code>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>{{ $customer->accountManager?->name ?? '—' }}</td>
                    <td>{{ $customer->branch?->name ?? '—' }}</td>
                    <td class="table-actions" style="display:flex;gap:.25rem">
                        <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-sm">عرض</a>
                        @can('update', $customer)
                            <a href="{{ route('admin.customers.edit', $customer) }}" class="btn btn-sm btn-primary">تعديل</a>
                        @endcan
                        @can('delete', $customer)
                            <form method="POST" action="{{ route('admin.customers.destroy', $customer) }}"
                                  onsubmit="return confirm('هل أنت متأكد من حذف العميل؟');"
                                  style="display:inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">حذف</button>
                            </form>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center;color:#888">لا يوجد عملاء</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top:1rem">
        {{ $customers->links() }}
    </div>
</div>
@endsection
