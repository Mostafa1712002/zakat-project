@extends('layouts.app')

@section('title', 'الأدوار والصلاحيات')

@section('content')
<div class="page-header">
    <div>
        <h1>🛡️ الأدوار والصلاحيات</h1>
        <p>إدارة صلاحيات كل دور</p>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card" style="padding:1rem">
    <table class="table" style="width:100%">
        <thead>
            <tr>
                <th>الدور</th>
                <th>عدد الصلاحيات</th>
                <th>الحالة</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($roles as $role)
                @php
                    $locked = $role->name === 'Super Admin' || (bool) ($role->is_locked ?? false);
                @endphp
                <tr>
                    <td><strong>{{ $role->name }}</strong></td>
                    <td>{{ $role->permissions_count }}</td>
                    <td>
                        @if ($locked)
                            <span class="badge badge-secondary">مقفل</span>
                        @else
                            <span class="badge badge-success">قابل للتعديل</span>
                        @endif
                    </td>
                    <td>
                        @if ($locked)
                            <button type="button" class="btn btn-sm btn-secondary" disabled>تعديل</button>
                        @else
                            <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-sm btn-primary">تعديل</a>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
