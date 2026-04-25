@extends('layouts.app')

@section('title', $customer->name)

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
        <h1>👥 {{ $customer->name }}</h1>
        <p>{{ $typeLabels[$customer->type] ?? $customer->type }}
           @if ($customer->is_tax_exempt) <span class="badge badge-warning">معفى من الضريبة</span> @endif
        </p>
    </div>
    <div class="header-actions" style="display:flex;gap:.5rem">
        @can('update', $customer)
            <a href="{{ route('admin.customers.edit', $customer) }}" class="btn btn-primary">✏️ تعديل</a>
        @endcan
        <a href="{{ route('admin.customers.index') }}" class="btn">← العملاء</a>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@unless ($zatcaCheck['ok'])
    <div class="alert alert-warning">
        <strong>⚠️ بيانات ZATCA غير مكتملة لإصدار الفواتير:</strong>
        <ul style="margin:.5rem 0 0;padding-right:1rem">
            @foreach ($zatcaCheck['errors'] as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endunless

<div x-data="{ tab: 'details' }">
    <div class="tabs" style="display:flex;gap:.5rem;border-bottom:1px solid #ddd;margin-bottom:1rem">
        <button type="button" class="btn" :class="tab === 'details' ? 'btn-primary' : ''" @click="tab = 'details'">📋 التفاصيل</button>
        <button type="button" class="btn" :class="tab === 'contacts' ? 'btn-primary' : ''" @click="tab = 'contacts'">👤 جهات الاتصال</button>
        <button type="button" class="btn" :class="tab === 'invoices' ? 'btn-primary' : ''" @click="tab = 'invoices'">🧾 الفواتير</button>
        <button type="button" class="btn" :class="tab === 'payments' ? 'btn-primary' : ''" @click="tab = 'payments'">💰 الدفعات</button>
    </div>

    <div x-show="tab === 'details'">
        <div class="card" style="padding:1rem;margin-bottom:1rem">
            <h3>📋 المعلومات الأساسية</h3>
            <table class="table" style="width:100%">
                <tbody>
                    <tr><th style="width:200px">النوع</th><td>{{ $typeLabels[$customer->type] ?? $customer->type }}</td></tr>
                    <tr><th>الرقم الضريبي</th><td>{{ $customer->vat_number ?: '—' }}</td></tr>
                    <tr><th>السجل التجاري</th><td>{{ $customer->cr_number ?: '—' }}</td></tr>
                    <tr><th>الجوال</th><td>{{ $customer->phone ?: '—' }}</td></tr>
                    <tr><th>البريد الإلكتروني</th><td>{{ $customer->email ?: '—' }}</td></tr>
                    <tr><th>مدير الحساب</th><td>{{ $customer->accountManager?->name ?? '—' }}</td></tr>
                    <tr><th>الفرع</th><td>{{ $customer->branch?->name ?? '—' }}</td></tr>
                    <tr><th>معفى ضريبياً</th><td>{{ $customer->is_tax_exempt ? 'نعم' : 'لا' }}</td></tr>
                    @if ($customer->notes)
                        <tr><th>ملاحظات</th><td>{{ $customer->notes }}</td></tr>
                    @endif
                </tbody>
            </table>
        </div>

        <div class="card" style="padding:1rem">
            <h3>📍 العنوان الوطني (REGA)</h3>
            <table class="table" style="width:100%">
                <tbody>
                    <tr><th style="width:200px">اسم الشارع</th><td>{{ $customer->street_name ?: '—' }}</td></tr>
                    <tr><th>رقم المبنى</th><td>{{ $customer->building_number ?: '—' }}</td></tr>
                    <tr><th>الرقم الفرعي</th><td>{{ $customer->secondary_number ?: '—' }}</td></tr>
                    <tr><th>الحي</th><td>{{ $customer->district ?: '—' }}</td></tr>
                    <tr><th>المدينة</th><td>{{ $customer->city ?: '—' }}</td></tr>
                    <tr><th>الرمز البريدي</th><td>{{ $customer->postal_code ?: '—' }}</td></tr>
                    <tr><th>الدولة</th><td>{{ $customer->country_code ?: '—' }}</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <div x-show="tab === 'contacts'" x-cloak>
        <div class="card" style="padding:1rem">
            <h3>👤 جهات الاتصال</h3>
            @if ($customer->contacts->isEmpty())
                <p class="text-muted">لا توجد جهات اتصال مضافة بعد.</p>
            @else
                <table class="table" style="width:100%">
                    <thead>
                        <tr>
                            <th>الاسم</th>
                            <th>الوظيفة</th>
                            <th>الجوال</th>
                            <th>البريد</th>
                            <th>أساسي</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($customer->contacts as $contact)
                            <tr>
                                <td>{{ $contact->name }}</td>
                                <td>{{ $contact->position ?: '—' }}</td>
                                <td>{{ $contact->phone ?: '—' }}</td>
                                <td>{{ $contact->email ?: '—' }}</td>
                                <td>@if ($contact->is_primary)<span class="badge badge-success">نعم</span>@endif</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    <div x-show="tab === 'invoices'" x-cloak>
        <div class="card" style="padding:1rem">
            <h3>🧾 الفواتير</h3>
            <p class="text-muted">سيتم تفعيل هذا القسم في المرحلة الخامسة (المبيعات).</p>
        </div>
    </div>

    <div x-show="tab === 'payments'" x-cloak>
        <div class="card" style="padding:1rem">
            <h3>💰 الدفعات</h3>
            <p class="text-muted">سيتم تفعيل هذا القسم في المرحلة السادسة (الخزنة).</p>
        </div>
    </div>
</div>
@endsection
