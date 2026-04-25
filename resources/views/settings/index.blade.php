@extends('layouts.app')

@section('title', 'الإعدادات')

@section('content')
<div class="page-header">
    <div>
        <h1>⚙️ الإعدادات</h1>
        <p>إدارة إعدادات النظام</p>
    </div>
</div>

<div class="settings-grid">
    <!-- Company Settings -->
    <div class="card settings-card">
        <div class="settings-card-icon">🏢</div>
        <div class="settings-card-content">
            <h3>إعدادات الشركة</h3>
            <p>تعديل بيانات الشركة والشعار ومعلومات الاتصال</p>
        </div>
        <div class="settings-card-action">
            <a href="{{ route('settings.company') }}" class="btn btn-primary">إدارة</a>
        </div>
    </div>

    <!-- Users Settings -->
    <div class="card settings-card">
        <div class="settings-card-icon">👥</div>
        <div class="settings-card-content">
            <h3>إدارة المستخدمين</h3>
            <p>إضافة وتعديل صلاحيات المستخدمين</p>
        </div>
        <div class="settings-card-action">
            <a href="{{ route('settings.users') }}" class="btn btn-primary">إدارة</a>
        </div>
    </div>

    <!-- Roles Settings -->
    <div class="card settings-card">
        <div class="settings-card-icon">🔐</div>
        <div class="settings-card-content">
            <h3>إدارة الأدوار</h3>
            <p>إنشاء الأدوار وتحديد الصلاحيات</p>
        </div>
        <div class="settings-card-action">
            <a href="{{ route('settings.roles') }}" class="btn btn-primary">إدارة</a>
        </div>
    </div>

    <!-- Invoice Settings -->
    <div class="card settings-card">
        <div class="settings-card-icon">🧾</div>
        <div class="settings-card-content">
            <h3>إعدادات الفواتير</h3>
            <p>تخصيص ترقيم الفواتير وخيارات العرض</p>
        </div>
        <div class="settings-card-action">
            <a href="{{ route('settings.invoices') }}" class="btn btn-primary">إدارة</a>
        </div>
    </div>

    {{-- NOTE: Phase 1 cleanup — Categories and Warehouses cards removed (deleted domains). --}}

    <!-- Data Reset -->
    <div class="card settings-card settings-card-danger">
        <div class="settings-card-icon">🔄</div>
        <div class="settings-card-content">
            <h3>التصفير</h3>
            <p>مسح جميع المبيعات والمشتريات والأرصدة والبدء من جديد</p>
        </div>
        <div class="settings-card-action">
            <a href="{{ route('settings.reset') }}" class="btn btn-danger">تصفير</a>
        </div>
    </div>

</div>

<style>
.settings-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
}

.settings-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: 2rem;
    transition: transform 0.2s, box-shadow 0.2s;
}

.settings-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.settings-card-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
}

.settings-card-content h3 {
    margin: 0 0 0.5rem;
    font-size: 1.25rem;
    color: var(--text-primary);
}

.settings-card-content p {
    margin: 0;
    color: var(--text-secondary);
    font-size: 0.875rem;
}

.settings-card-action {
    margin-top: 1.5rem;
}

.settings-card-danger {
    border: 2px solid #fee2e2;
    background: #fff5f5;
}
.settings-card-danger:hover {
    border-color: #fca5a5;
}
</style>
@endsection
