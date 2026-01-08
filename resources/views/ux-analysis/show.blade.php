@extends('layouts.app')

@section('title', $title . ' - تحليل تجربة المستخدم')

@section('content')
<div class="page-header">
    <div>
        <h1>🎨 {{ $title }}</h1>
        <p>تحليل تجربة المستخدم الشامل</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('ux-analysis.index') }}" class="btn btn-secondary">
            ← العودة للفهرس
        </a>
    </div>
</div>

<style>
.analysis-container {
    background: white;
    border-radius: 16px;
    padding: 40px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    max-width: 100%;
    overflow-x: auto;
}

.analysis-content {
    font-size: 16px;
    line-height: 1.8;
    color: #1e293b;
}

.analysis-content h1 {
    font-size: 32px;
    font-weight: 700;
    color: #1e293b;
    margin: 32px 0 16px 0;
    padding-bottom: 12px;
    border-bottom: 3px solid #667eea;
}

.analysis-content h2 {
    font-size: 26px;
    font-weight: 700;
    color: #1e293b;
    margin: 28px 0 14px 0;
    padding-right: 16px;
    border-right: 4px solid #667eea;
}

.analysis-content h3 {
    font-size: 22px;
    font-weight: 600;
    color: #334155;
    margin: 24px 0 12px 0;
}

.analysis-content h4 {
    font-size: 18px;
    font-weight: 600;
    color: #475569;
    margin: 20px 0 10px 0;
}

.analysis-content p {
    margin: 12px 0;
    text-align: justify;
}

.analysis-content ul, .analysis-content ol {
    margin: 16px 0;
    padding-right: 24px;
}

.analysis-content li {
    margin: 8px 0;
    line-height: 1.7;
}

.analysis-content code {
    background: #f1f5f9;
    padding: 2px 6px;
    border-radius: 4px;
    font-family: 'Courier New', monospace;
    font-size: 14px;
    color: #dc2626;
}

.analysis-content pre {
    background: #1e293b;
    color: #e2e8f0;
    padding: 20px;
    border-radius: 8px;
    overflow-x: auto;
    margin: 16px 0;
}

.analysis-content blockquote {
    border-right: 4px solid #667eea;
    padding: 16px 20px;
    margin: 16px 0;
    background: #f8fafc;
    border-radius: 8px;
}

.analysis-content strong {
    color: #667eea;
    font-weight: 700;
}

.analysis-content hr {
    border: none;
    border-top: 2px solid #e2e8f0;
    margin: 32px 0;
}

.analysis-content table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
}

.analysis-content table th {
    background: #667eea;
    color: white;
    padding: 12px;
    text-align: right;
    font-weight: 600;
}

.analysis-content table td {
    padding: 10px 12px;
    border: 1px solid #e2e8f0;
}

.analysis-content table tr:nth-child(even) {
    background: #f8fafc;
}

/* Mermaid diagrams styling */
.mermaid {
    background: white;
    padding: 24px;
    border-radius: 12px;
    margin: 24px 0;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    direction: rtl;
}

.diagram-section {
    background: linear-gradient(135deg, #667eea15 0%, #764ba215 100%);
    padding: 32px;
    border-radius: 16px;
    margin: 32px 0;
}

.diagram-title {
    font-size: 22px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.toc {
    background: #f8fafc;
    padding: 24px;
    border-radius: 12px;
    margin-bottom: 32px;
    border: 2px solid #e2e8f0;
}

.toc-title {
    font-size: 20px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 16px;
}

.toc ul {
    list-style: none;
    padding: 0;
}

.toc li {
    margin: 8px 0;
}

.toc a {
    color: #667eea;
    text-decoration: none;
    font-weight: 500;
}

.toc a:hover {
    text-decoration: underline;
}
</style>

<div class="analysis-container">
    <div class="toc">
        <div class="toc-title">📑 محتويات الصفحة</div>
        <ul>
            <li><a href="#ui-analysis">🎨 تحليل واجهة المستخدم</a></li>
            <li><a href="#user-flow">🔄 تدفق تجربة المستخدم</a></li>
            <li><a href="#crud-operations">⚙️ عمليات العمليات الأساسية</a></li>
            <li><a href="#strengths">💪 نقاط القوة</a></li>
            <li><a href="#improvements">🚀 نقاط التحسين</a></li>
            <li><a href="#diagrams">📊 المخططات التوضيحية</a></li>
        </ul>
    </div>

    <div class="analysis-content">
        {!! \Illuminate\Support\Str::markdown($content) !!}
    </div>

    <!-- العمليات الأساسية Flow Diagram -->
    <div class="diagram-section" id="diagrams">
        <div class="diagram-title">
            📊 مخطط تدفق عمليات العمليات الأساسية
        </div>
        <div class="mermaid">
graph TD
    A[البداية - المستخدم] --> B{نوع العملية}
    B -->|إنشاء| C[فتح نموذج الإنشاء]
    B -->|قراءة| D[عرض القائمة/التفاصيل]
    B -->|تعديل| E[فتح نموذج التعديل]
    B -->|حذف| F[طلب تأكيد الحذف]

    C --> C1[ملء الحقول المطلوبة]
    C1 --> C2{التحقق من البيانات}
    C2 -->|صحيح| C3[حفظ في قاعدة البيانات]
    C2 -->|خطأ| C4[عرض رسائل الخطأ]
    C4 --> C1
    C3 --> G[رسالة نجاح]

    D --> D1[عرض البيانات]
    D1 --> D2{بحث/فلترة؟}
    D2 -->|نعم| D3[تطبيق الفلاتر]
    D3 --> D1
    D2 -->|لا| D4[عرض كل البيانات]

    E --> E1[تحميل البيانات الحالية]
    E1 --> E2[تعديل الحقول]
    E2 --> E3{التحقق}
    E3 -->|صحيح| E4[تحديث قاعدة البيانات]
    E3 -->|خطأ| E5[عرض رسائل الخطأ]
    E5 --> E2
    E4 --> G

    F --> F1{تأكيد الحذف؟}
    F1 -->|نعم| F2[حذف من قاعدة البيانات]
    F1 -->|لا| F3[إلغاء العملية]
    F2 --> G

    G --> H[النهاية]
    F3 --> H
    D4 --> H

    style A fill:#667eea,stroke:#333,stroke-width:2px,color:#fff
    style H fill:#43e97b,stroke:#333,stroke-width:2px,color:#fff
    style G fill:#4facfe,stroke:#333,stroke-width:2px,color:#fff
    style C2 fill:#f093fb,stroke:#333,stroke-width:2px,color:#fff
    style E3 fill:#f093fb,stroke:#333,stroke-width:2px,color:#fff
    style F1 fill:#fa709a,stroke:#333,stroke-width:2px,color:#fff
        </div>
    </div>

    <!-- User Journey Diagram -->
    <div class="diagram-section">
        <div class="diagram-title">
            🗺️ خريطة رحلة المستخدم
        </div>
        <div class="mermaid">
journey
    title رحلة المستخدم في نظام إدارة علاقات العملاء
    section تسجيل الدخول
      فتح الصفحة: 5: المستخدم
      إدخال البيانات: 4: المستخدم
      التحقق: 5: النظام
      الدخول للنظام: 5: المستخدم
    section لوحة التحكم
      عرض الإحصائيات: 5: النظام
      مراجعة البيانات: 4: المستخدم
      اتخاذ القرار: 4: المستخدم
    section تنفيذ العملية
      اختيار الوحدة: 5: المستخدم
      فتح النموذج: 5: النظام
      إدخال البيانات: 3: المستخدم
      حفظ التغييرات: 5: المستخدم
    section المراجعة
      عرض النتيجة: 5: النظام
      التأكد من النجاح: 5: المستخدم
        </div>
    </div>

    <!-- System Architecture Diagram -->
    @if($module === 'architecture' || $module === 'crud-guide')
    <div class="diagram-section">
        <div class="diagram-title">
            🏗️ معمارية النظام
        </div>
        <div class="mermaid">
graph TB
    subgraph "طبقة العرض"
        A[المتصفح]
        B[قوالب العرض]
        C[الجافاسكربت]
        D[التنسيقات]
    end

    subgraph "طبقة المنطق"
        E[المسارات]
        F[المتحكمات]
        G[النماذج]
        H[التحقق]
    end

    subgraph "طبقة البيانات"
        I[قاعدة البيانات]
        J[الهجرات]
        K[البيانات الأولية]
    end

    A --> B
    B --> C
    B --> D
    B --> E
    E --> F
    F --> G
    F --> H
    G --> I
    J --> I
    K --> I

    style A fill:#667eea,stroke:#333,stroke-width:2px,color:#fff
    style I fill:#43e97b,stroke:#333,stroke-width:2px,color:#fff
        </div>
    </div>
    @endif

    <!-- Database Relationships -->
    @if($module === 'architecture')
    <div class="diagram-section">
        <div class="diagram-title">
            🗄️ علاقات قاعدة البيانات
        </div>
        <div class="mermaid">
erDiagram
    العملاء ||--o{ الفواتير : "لديه"
    العملاء {
        رقم المعرف
        نص الاسم
        نص الهاتف
        نص البريد_الإلكتروني
        نص العنوان
    }

    الفواتير ||--|{ بنود_الفاتورة : "يحتوي"
    الفواتير {
        رقم المعرف
        رقم معرف_العميل
        نص رقم_الفاتورة
        تاريخ التاريخ
        عشري الإجمالي
        نص حالة_الدفع
    }

    المنتجات ||--o{ بنود_الفاتورة : "في"
    المنتجات {
        رقم المعرف
        نص الاسم
        نص الباركود
        عشري السعر
        رقم الكمية
    }

    المنتجات }|--|| الأقسام : "ينتمي"
    الأقسام {
        رقم المعرف
        نص الاسم
    }

    المخازن ||--o{ المخزون : "يخزن"
    المخازن {
        رقم المعرف
        نص الاسم
        نص الموقع
    }

    المنتجات ||--o{ المخزون : "مخزن في"
    المخزون {
        رقم المعرف
        رقم معرف_المنتج
        رقم معرف_المخزن
        رقم الكمية
    }
        </div>
    </div>
    @endif
</div>

<!-- Include Mermaid.js -->
<script type="module">
    import mermaid from 'https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.esm.min.mjs';
    mermaid.initialize({
        startOnLoad: true,
        theme: 'default',
        flowchart: {
            useMaxWidth: true,
            htmlLabels: true,
            curve: 'basis'
        }
    });
</script>

@endsection
