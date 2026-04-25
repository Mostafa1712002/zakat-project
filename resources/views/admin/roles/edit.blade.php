@extends('layouts.app')

@section('title', 'تعديل الدور: ' . $role->name)

@section('content')
@php
    $domainLabels = [
        'customers' => 'العملاء',
        'service-types' => 'أنواع الخدمات',
        'services' => 'الخدمات',
        'quotes' => 'عروض الأسعار',
        'invoices' => 'الفواتير',
        'payments' => 'المدفوعات',
        'reports' => 'التقارير',
        'settings' => 'الإعدادات',
        'users' => 'المستخدمون',
        'roles' => 'الأدوار',
    ];
@endphp

<div class="page-header">
    <div>
        <h1>🛡️ تعديل الدور: {{ $role->name }}</h1>
        <p>تحديد الصلاحيات الممنوحة لهذا الدور</p>
    </div>
</div>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul style="margin:0;padding-right:1rem">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('admin.roles.update', $role) }}" class="card" style="padding:1.5rem">
    @csrf
    @method('PUT')

    @foreach ($permissions as $domain => $domainPermissions)
        <fieldset style="border:1px solid #e5e7eb;padding:1rem;margin-bottom:1rem;border-radius:6px">
            <legend style="padding:0 .5rem;font-weight:bold">
                {{ $domainLabels[$domain] ?? $domain }}
            </legend>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:.5rem">
                @foreach ($domainPermissions as $permission)
                    <label style="display:flex;align-items:center;gap:.5rem;padding:.25rem .5rem;background:#f9fafb;border-radius:4px">
                        <input type="checkbox"
                               name="permissions[]"
                               value="{{ $permission->name }}"
                               @checked(in_array($permission->name, old('permissions', $assigned), true))>
                        <span style="font-family:monospace;font-size:.9rem">{{ $permission->name }}</span>
                    </label>
                @endforeach
            </div>
        </fieldset>
    @endforeach

    <div style="margin-top:1rem;display:flex;gap:.5rem">
        <button type="submit" class="btn btn-primary">حفظ الصلاحيات</button>
        <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">إلغاء</a>
    </div>
</form>
@endsection
