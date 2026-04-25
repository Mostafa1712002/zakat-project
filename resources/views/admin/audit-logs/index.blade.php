@extends('layouts.app')

@section('title', 'سجل التعديلات')

@section('content')
<div class="page-header">
    <div>
        <h1>📋 سجل التعديلات</h1>
        <p>سجل العمليات الحساسة على الفواتير، عروض الأسعار والمدفوعات</p>
    </div>
</div>

<form method="GET" action="{{ route('admin.audit-logs.index') }}" class="card" style="padding:1rem;margin-bottom:1rem">
    <div style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:end">
        <div style="flex:1;min-width:200px">
            <label class="form-label">نوع الحدث</label>
            <select name="event" class="form-control">
                <option value="">— كل الأحداث —</option>
                @foreach ($events as $eventName)
                    <option value="{{ $eventName }}" @selected($filters['event'] === $eventName)>{{ $eventName }}</option>
                @endforeach
            </select>
        </div>
        <div style="flex:1;min-width:160px">
            <label class="form-label">معرف المستخدم</label>
            <input type="number" name="user_id" value="{{ $filters['user_id'] }}" class="form-control" placeholder="ID">
        </div>
        <div>
            <button type="submit" class="btn btn-primary">تصفية</button>
            <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-secondary">مسح</a>
        </div>
    </div>
</form>

<div class="card">
    <div class="table-responsive">
        <table class="table table-striped" style="margin:0">
            <thead>
                <tr>
                    <th>التاريخ</th>
                    <th>المستخدم</th>
                    <th>الحدث</th>
                    <th>النوع</th>
                    <th>المعرف</th>
                    <th>البيانات</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($logs as $log)
                    <tr>
                        <td style="white-space:nowrap">{{ $log->created_at?->format('Y-m-d H:i:s') }}</td>
                        <td>
                            @if ($log->user)
                                {{ $log->user->name }}
                                <br><small class="text-muted">{{ $log->user->email }}</small>
                            @else
                                <span class="text-muted">— نظام —</span>
                            @endif
                        </td>
                        <td><code>{{ $log->event }}</code></td>
                        <td>
                            @php
                                $shortType = class_basename($log->auditable_type);
                            @endphp
                            <span title="{{ $log->auditable_type }}">{{ $shortType }}</span>
                        </td>
                        <td>#{{ $log->auditable_id }}</td>
                        <td>
                            @if ($log->new_values)
                                <details>
                                    <summary style="cursor:pointer;color:#0d6efd">عرض</summary>
                                    <pre style="margin:.5rem 0 0;padding:.5rem;background:#f7f7f9;border-radius:4px;font-size:.85rem;direction:ltr;text-align:left;white-space:pre-wrap">{{ json_encode($log->new_values, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) }}</pre>
                                </details>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted" style="padding:2rem">لا توجد سجلات.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div style="margin-top:1rem">
    {{ $logs->links() }}
</div>
@endsection
