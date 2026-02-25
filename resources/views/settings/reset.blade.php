@extends('layouts.app')

@section('title', 'التصفير - حذف البيانات')

@section('content')
<div class="page-header">
    <div>
        <h1>🔄 التصفير</h1>
        <p>مسح جميع البيانات والبدء من جديد</p>
    </div>
    <a href="{{ route('settings.index') }}" class="btn btn-secondary">رجوع للإعدادات</a>
</div>

<div class="reset-container">
    <div class="card reset-warning-card">
        <div class="warning-icon">⚠️</div>
        <h2>تحذير: هذه العملية لا يمكن التراجع عنها!</h2>
        <p>سيتم حذف جميع البيانات التالية نهائياً:</p>

        <div class="reset-details">
            <div class="reset-delete-list">
                <h3>سيتم حذفه:</h3>
                <ul>
                    <li>جميع فواتير المبيعات وبنودها</li>
                    <li>جميع فواتير المشتريات وبنودها</li>
                    <li>جميع التسعيرات (مبيعات ومشتريات)</li>
                    <li>جميع المرتجعات</li>
                    <li>جميع المدفوعات والتحصيلات</li>
                    <li>جميع المصروفات</li>
                    <li>جميع حركات المخزون</li>
                    <li>أرصدة العملاء والموردين</li>
                    <li>معاملات الموظفين والشركاء</li>
                </ul>
            </div>
            <div class="reset-keep-list">
                <h3>سيبقى كما هو:</h3>
                <ul>
                    <li>حسابات المستخدمين وتسجيل الدخول</li>
                    <li>إعدادات الشركة والألوان والشعار</li>
                    <li>المنتجات والأقسام</li>
                    <li>العملاء والموردين (بدون أرصدة)</li>
                    <li>المخازن</li>
                    <li>الأدوار والصلاحيات</li>
                </ul>
            </div>
        </div>

        @if($errors->any())
        <div class="alert alert-danger" style="margin-top: 1.5rem;">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
        @endif

        <form method="POST" action="{{ route('settings.reset.confirm') }}" class="reset-form" onsubmit="return confirmReset()">
            @csrf

            <div class="form-group">
                <label for="password" class="form-label">كلمة المرور الحالية</label>
                <input type="password" id="password" name="password" class="form-control" required placeholder="أدخل كلمة المرور للتأكيد">
            </div>

            <div class="form-group">
                <label for="confirmation" class="form-label">اكتب كلمة <strong style="color: #dc2626;">"تصفير"</strong> للتأكيد</label>
                <input type="text" id="confirmation" name="confirmation" class="form-control" required placeholder="تصفير" autocomplete="off">
            </div>

            <button type="submit" class="btn btn-danger btn-lg btn-block" id="resetBtn" disabled>
                🔄 تنفيذ التصفير
            </button>
        </form>
    </div>
</div>

<style>
.reset-container {
    max-width: 700px;
    margin: 0 auto;
}

.reset-warning-card {
    padding: 2rem;
    text-align: center;
    border: 3px solid #fca5a5;
    background: #fff5f5;
}

.warning-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
}

.reset-warning-card h2 {
    color: #dc2626;
    margin-bottom: 0.5rem;
}

.reset-warning-card > p {
    color: #991b1b;
    margin-bottom: 1.5rem;
}

.reset-details {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
    text-align: right;
    margin-bottom: 2rem;
}

.reset-delete-list {
    background: #fee2e2;
    padding: 1rem;
    border-radius: 8px;
}

.reset-delete-list h3 {
    color: #dc2626;
    margin-bottom: 0.5rem;
    font-size: 1rem;
}

.reset-delete-list li {
    color: #991b1b;
    font-size: 0.85rem;
    margin-bottom: 4px;
}

.reset-keep-list {
    background: #d1fae5;
    padding: 1rem;
    border-radius: 8px;
}

.reset-keep-list h3 {
    color: #065f46;
    margin-bottom: 0.5rem;
    font-size: 1rem;
}

.reset-keep-list li {
    color: #065f46;
    font-size: 0.85rem;
    margin-bottom: 4px;
}

.reset-form {
    text-align: right;
    max-width: 400px;
    margin: 0 auto;
}

.reset-form .form-group {
    margin-bottom: 1rem;
}

.reset-form .form-label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
}

.reset-form .form-control {
    width: 100%;
    padding: 10px 14px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-family: inherit;
    font-size: 15px;
}

.btn-block {
    width: 100%;
    margin-top: 1.5rem;
    padding: 14px;
    font-size: 16px;
    font-weight: 700;
}

.btn-lg {
    padding: 14px 24px;
}

.alert-danger {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fca5a5;
    padding: 12px 16px;
    border-radius: 8px;
}

@media (max-width: 600px) {
    .reset-details {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
document.getElementById('confirmation').addEventListener('input', function() {
    const btn = document.getElementById('resetBtn');
    btn.disabled = this.value !== 'تصفير';
});

function confirmReset() {
    return confirm('هل أنت متأكد تماماً؟ سيتم حذف جميع البيانات نهائياً ولا يمكن استرجاعها!');
}
</script>
@endsection
