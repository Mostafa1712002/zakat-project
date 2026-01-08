@extends('layouts.app')

@section('title', 'تحليل تجربة المستخدم')

@section('content')
<div class="page-header">
    <div>
        <h1>🎨 تحليل تجربة المستخدم (UX Analysis)</h1>
        <p>تحليل شامل لكل وحدات النظام باستخدام Google Gemini AI</p>
    </div>
</div>

<style>
.ux-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.ux-card {
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: all 0.3s;
    cursor: pointer;
    text-decoration: none;
    color: inherit;
    display: block;
}

.ux-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.15);
}

.ux-card-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 12px;
}

.ux-icon {
    font-size: 32px;
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 10px;
}

.ux-title {
    font-size: 18px;
    font-weight: 700;
    color: #1e293b;
}

.ux-description {
    color: #64748b;
    font-size: 14px;
    line-height: 1.6;
    margin-bottom: 12px;
}

.ux-meta {
    display: flex;
    gap: 16px;
    padding-top: 12px;
    border-top: 1px solid #e2e8f0;
    font-size: 13px;
    color: #64748b;
}

.section-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 40px 0 20px 0;
    padding-bottom: 12px;
    border-bottom: 2px solid #e2e8f0;
}

.section-icon {
    font-size: 24px;
}

.section-title {
    font-size: 22px;
    font-weight: 700;
    color: #1e293b;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 30px;
}

.stat-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 12px;
    text-align: center;
}

.stat-value {
    font-size: 32px;
    font-weight: 700;
    margin-bottom: 4px;
}

.stat-label {
    font-size: 14px;
    opacity: 0.9;
}
</style>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value">{{ count($analyses) }}</div>
        <div class="stat-label">تحليلات الوحدات</div>
    </div>
    <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
        <div class="stat-value">{{ count($documentation) }}</div>
        <div class="stat-label">التوثيق الشامل</div>
    </div>
    <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
        <div class="stat-value">2,388</div>
        <div class="stat-label">سطر من التحليل</div>
    </div>
    <div class="stat-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
        <div class="stat-value">244 KB</div>
        <div class="stat-label">حجم المحتوى</div>
    </div>
</div>

<div class="section-header">
    <span class="section-icon">📚</span>
    <h2 class="section-title">التوثيق الشامل</h2>
</div>

<div class="ux-grid">
    @if(isset($documentation['user-journey']))
    <a href="{{ route('ux-analysis.show', 'user-journey') }}" class="ux-card">
        <div class="ux-card-header">
            <div class="ux-icon">🗺️</div>
            <div class="ux-title">{{ $documentation['user-journey']['name'] }}</div>
        </div>
        <div class="ux-description">
            شخصيات المستخدمين، السيناريوهات، نقاط الاحتكاك، ومقاييس النجاح الكاملة
        </div>
        <div class="ux-meta">
            <span>📝 17KB</span>
            <span>🎯 4 شخصيات</span>
            <span>📊 تحليل شامل</span>
        </div>
    </a>
    @endif

    @if(isset($documentation['crud-guide']))
    <a href="{{ route('ux-analysis.show', 'crud-guide') }}" class="ux-card">
        <div class="ux-card-header">
            <div class="ux-icon">📊</div>
            <div class="ux-title">{{ $documentation['crud-guide']['name'] }}</div>
        </div>
        <div class="ux-description">
            شرح مفصل لعمليات Create, Read, Update, Delete مع أمثلة عملية ومخططات تدفق
        </div>
        <div class="ux-meta">
            <span>📝 13KB</span>
            <span>🔧 4 عمليات</span>
            <span>💡 أمثلة تطبيقية</span>
        </div>
    </a>
    @endif

    @if(isset($documentation['architecture']))
    <a href="{{ route('ux-analysis.show', 'architecture') }}" class="ux-card">
        <div class="ux-card-header">
            <div class="ux-icon">🏗️</div>
            <div class="ux-title">{{ $documentation['architecture']['name'] }}</div>
        </div>
        <div class="ux-description">
            معمارية النظام، قاعدة البيانات، تدفق الطلبات، الأمان والأداء
        </div>
        <div class="ux-meta">
            <span>📝 11KB</span>
            <span>🔐 أمان</span>
            <span>⚡ أداء</span>
        </div>
    </a>
    @endif
</div>

<div class="section-header">
    <span class="section-icon">🎯</span>
    <h2 class="section-title">تحليلات الوحدات ({{ count($analyses) }} وحدة)</h2>
</div>

<div class="ux-grid">
    @foreach($analyses as $key => $analysis)
    <a href="{{ route('ux-analysis.show', $key) }}" class="ux-card">
        <div class="ux-card-header">
            <div class="ux-icon">{{ ['dashboard' => '📊', 'invoices' => '📄', 'purchases' => '🛒', 'employees' => '👨‍💻', 'customers' => '👥', 'products' => '📦', 'suppliers' => '🏢', 'warehouses' => '🏭', 'sales' => '💰', 'expenses' => '💸', 'reports' => '📈'][$key] ?? '📱' }}</div>
            <div class="ux-title">{{ $analysis['name'] }}</div>
        </div>
        <div class="ux-description">
            تحليل شامل لواجهة المستخدم، تدفق التجربة، عمليات CRUD، نقاط القوة والتحسينات
        </div>
        <div class="ux-meta">
            <span>🎨 UI/UX</span>
            <span>🔄 CRUD</span>
            <span>📈 مخططات</span>
        </div>
    </a>
    @endforeach
</div>

<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 32px; border-radius: 16px; margin-top: 40px; text-align: center;">
    <h3 style="font-size: 24px; margin-bottom: 12px;">🤖 تم إنشاء هذا التحليل بواسطة</h3>
    <p style="font-size: 32px; font-weight: 700; margin: 0;">Google Gemini AI</p>
    <p style="font-size: 16px; opacity: 0.9; margin-top: 8px;">تحليل احترافي شامل باللغة العربية</p>
</div>

@endsection
