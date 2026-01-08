#!/usr/bin/env node

const { exec } = require('child_process');
const { promisify } = require('util');
const fs = require('fs');
const path = require('path');

const execAsync = promisify(exec);
const GALLERY_DIR = path.join(__dirname, '../gallery');
const OUTPUT_DIR = path.join(__dirname, '../gallery/ux-analysis');

// Create output directory
if (!fs.existsSync(OUTPUT_DIR)) {
    fs.mkdirSync(OUTPUT_DIR, { recursive: true });
}

// CRM Modules with CRUD operations
const modules = [
    {
        name: 'Dashboard',
        nameAr: 'لوحة التحكم',
        screenshot: '02-dashboard-1920x1080.png',
        operations: ['Read'],
        description: 'عرض الإحصائيات والمبيعات والملخصات'
    },
    {
        name: 'Invoices',
        nameAr: 'الفواتير',
        screenshots: ['03-invoices-list-1920x1080.png', '04-invoice-create-1920x1080.png'],
        operations: ['Create', 'Read', 'Update', 'Delete', 'Print'],
        description: 'إدارة فواتير البيع مع طباعة الفواتير'
    },
    {
        name: 'Purchases',
        nameAr: 'المشتريات',
        screenshots: ['05-purchases-list-1920x1080.png', '06-purchase-create-1920x1080.png'],
        operations: ['Create', 'Read', 'Update', 'Delete'],
        description: 'إدارة فواتير الشراء من الموردين'
    },
    {
        name: 'Employees',
        nameAr: 'الموظفين',
        screenshots: ['07-employees-list-1920x1080.png', '08-employee-create-1920x1080.png'],
        operations: ['Create', 'Read', 'Update', 'Delete'],
        description: 'إدارة بيانات الموظفين والرواتب'
    },
    {
        name: 'Customers',
        nameAr: 'العملاء',
        screenshots: ['09-customers-list-1920x1080.png', '10-customer-create-1920x1080.png'],
        operations: ['Create', 'Read', 'Update', 'Delete'],
        description: 'إدارة قاعدة بيانات العملاء'
    },
    {
        name: 'Products',
        nameAr: 'المنتجات',
        screenshots: ['11-products-list-1920x1080.png', '12-product-create-1920x1080.png'],
        operations: ['Create', 'Read', 'Update', 'Delete'],
        description: 'إدارة كتالوج المنتجات والمخزون'
    },
    {
        name: 'Suppliers',
        nameAr: 'الموردين',
        screenshot: '13-suppliers-list-1920x1080.png',
        operations: ['Create', 'Read', 'Update', 'Delete'],
        description: 'إدارة بيانات الموردين والعلاقات'
    },
    {
        name: 'Warehouses',
        nameAr: 'المخازن',
        screenshot: '14-warehouses-list-1920x1080.png',
        operations: ['Create', 'Read', 'Update', 'Delete', 'Transfer'],
        description: 'إدارة المخازن ونقل المخزون'
    },
    {
        name: 'Sales',
        nameAr: 'المبيعات',
        screenshot: '16-sales-list-1920x1080.png',
        operations: ['Create', 'Read', 'Update', 'Delete'],
        description: 'تتبع عمليات البيع والإيرادات'
    },
    {
        name: 'Expenses',
        nameAr: 'المصروفات',
        screenshot: '17-expenses-list-1920x1080.png',
        operations: ['Create', 'Read', 'Update', 'Delete'],
        description: 'إدارة المصروفات والتكاليف'
    },
    {
        name: 'Reports',
        nameAr: 'التقارير',
        screenshot: '18-reports-1920x1080.png',
        operations: ['Read', 'Generate', 'Export'],
        description: 'تقارير مالية وإحصائية شاملة'
    }
];

async function analyzeWithGemini(prompt, imagePath = null) {
    try {
        let command;
        if (imagePath && fs.existsSync(imagePath)) {
            command = `gemini -p "${prompt}" < "${imagePath}"`;
        } else {
            command = `gemini -p "${prompt}"`;
        }

        const { stdout } = await execAsync(command, { maxBuffer: 1024 * 1024 * 10 });
        return stdout.trim();
    } catch (error) {
        console.error(`Error with Gemini:`, error.message);
        return `خطأ في التحليل: ${error.message}`;
    }
}

async function analyzeModule(module) {
    console.log(`\n🔍 تحليل: ${module.nameAr} (${module.name})`);

    const screenshots = module.screenshots || [module.screenshot];
    const firstScreenshot = path.join(GALLERY_DIR, screenshots[0]);

    const prompt = `أنت خبير في تجربة المستخدم (UX) وتحليل الأنظمة. قم بتحليل هذه الصفحة من نظام CRM:

**اسم الوحدة:** ${module.nameAr} (${module.name})
**الوصف:** ${module.description}
**العمليات المتاحة:** ${module.operations.join(', ')}

قم بتقديم التالي بشكل تفصيلي ومنظم:

## 1. تحليل واجهة المستخدم (UI Analysis)
- العناصر الرئيسية المرئية
- تنظيم المحتوى والتخطيط
- عناصر التنقل والأزرار
- الألوان والتصميم المرئي

## 2. تدفق تجربة المستخدم (User Flow)
- خطوات المستخدم لإكمال كل عملية CRUD
- النقاط المتوقعة للتفاعل
- التسلسل المنطقي للإجراءات

## 3. عمليات CRUD بالتفصيل
لكل عملية من (${module.operations.join(', ')}):
- كيف يقوم المستخدم بتنفيذها؟
- ما هي الحقول/البيانات المطلوبة؟
- ما هي رسائل النجاح/الخطأ المتوقعة؟
- ما هي الخطوة التالية بعد إكمال العملية؟

## 4. نقاط القوة والضعف
- ما الذي يعمل بشكل جيد في التصميم؟
- أين يمكن تحسين تجربة المستخدم؟
- اقتراحات للتحسين

## 5. مخطط تدفق العمليات (Flow Diagram)
قدم وصف نصي لمخطط تدفق يوضح:
- نقطة البداية
- الخطوات المتتالية
- نقاط اتخاذ القرار
- نقاط النهاية والنتائج

استخدم نص واضح ومنظم باللغة العربية مع أمثلة عملية.`;

    const analysis = await analyzeWithGemini(prompt, firstScreenshot);

    // Save analysis
    const outputFile = path.join(OUTPUT_DIR, `${module.name.toLowerCase()}-analysis.md`);
    fs.writeFileSync(outputFile, `# تحليل تجربة المستخدم: ${module.nameAr}\n\n${analysis}\n`);

    console.log(`✅ تم حفظ التحليل: ${module.name}`);

    return {
        module: module.name,
        nameAr: module.nameAr,
        analysis: analysis
    };
}

async function generateUserJourneyMap() {
    console.log('\n🗺️  إنشاء خريطة رحلة المستخدم الكاملة...');

    const prompt = `أنت خبير في تصميم تجربة المستخدم. قم بإنشاء خريطة رحلة مستخدم كاملة (User Journey Map) لنظام CRM يحتوي على الوحدات التالية:

${modules.map(m => `- **${m.nameAr}** (${m.name}): ${m.description} - العمليات: ${m.operations.join(', ')}`).join('\n')}

قم بإنشاء:

## 1. شخصيات المستخدمين (User Personas)
- المدير المالي
- محاسب المبيعات
- أمين المخزن
- مندوب المبيعات

## 2. السيناريوهات الرئيسية (Key Scenarios)
لكل شخصية، اشرح:
- الأهداف الرئيسية
- الخطوات المتبعة لتحقيق الأهداف
- التحديات المحتملة
- النتائج المتوقعة

## 3. مخطط التدفق الكامل (Complete Flow Chart)
صف بالنص مخطط تدفق يبدأ من:
- تسجيل الدخول
- اختيار الوحدة
- تنفيذ العمليات
- التحقق من النتائج
- الخروج من النظام

## 4. نقاط الاحتكاك (Pain Points)
- المشاكل المحتملة في كل مرحلة
- الحلول المقترحة

## 5. مقاييس النجاح (Success Metrics)
- كيف نقيس نجاح تجربة المستخدم؟
- ما هي المؤشرات الرئيسية (KPIs)؟

استخدم اللغة العربية وقدم أمثلة عملية واقعية.`;

    const journeyMap = await analyzeWithGemini(prompt);

    const outputFile = path.join(OUTPUT_DIR, 'complete-user-journey.md');
    fs.writeFileSync(outputFile, `# خريطة رحلة المستخدم الكاملة\n\n${journeyMap}\n`);

    console.log('✅ تم إنشاء خريطة رحلة المستخدم');

    return journeyMap;
}

async function generateCRUDDiagrams() {
    console.log('\n📊 إنشاء مخططات عمليات CRUD...');

    const prompt = `أنت خبير في توثيق الأنظمة. قم بإنشاء دليل شامل لعمليات CRUD في نظام CRM للوحدات التالية:

${modules.map(m => `**${m.nameAr}**: ${m.operations.join(', ')}`).join('\n')}

لكل وحدة، اشرح بالتفصيل:

## عملية CREATE (إنشاء)
1. نقطة الدخول (أين يبدأ المستخدم؟)
2. الحقول المطلوبة والاختيارية
3. التحقق من صحة البيانات (Validation)
4. رسالة النجاح والخطوة التالية
5. معالجة الأخطاء

## عملية READ (قراءة/عرض)
1. طريقة عرض القائمة (List View)
2. البحث والفلترة
3. الترتيب والترقيم (Pagination)
4. عرض التفاصيل (Detail View)
5. التنقل بين السجلات

## عملية UPDATE (تعديل)
1. الوصول إلى نموذج التعديل
2. تحميل البيانات الحالية
3. التعديل والحفظ
4. التحقق من الصلاحيات
5. تأكيد التعديل

## عملية DELETE (حذف)
1. تحديد السجل المراد حذفه
2. رسالة التأكيد
3. التحقق من الارتباطات (Relations)
4. الحذف الآمن
5. رسالة النجاح/الفشل

## مخططات تدفق البيانات
قدم وصف نصي لمخطط يوضح:
- مسار البيانات من الإدخال إلى الحفظ
- نقاط التحقق والمعالجة
- التفاعل مع قاعدة البيانات

استخدم أمثلة من وحدة الفواتير ووحدة المنتجات.`;

    const crudGuide = await analyzeWithGemini(prompt);

    const outputFile = path.join(OUTPUT_DIR, 'crud-operations-guide.md');
    fs.writeFileSync(outputFile, `# دليل عمليات CRUD الشامل\n\n${crudGuide}\n`);

    console.log('✅ تم إنشاء دليل عمليات CRUD');

    return crudGuide;
}

async function generateSystemArchitectureDiagram() {
    console.log('\n🏗️  إنشاء مخطط بنية النظام...');

    const prompt = `أنت مهندس أنظمة خبير. قم بإنشاء وصف تفصيلي لمخطط بنية نظام CRM هذا:

**التقنيات المستخدمة:**
- Backend: Laravel 12 (PHP 8.2)
- Database: SQLite
- Frontend: Vite, Alpine.js, Tailwind CSS
- Authentication: Laravel Breeze
- RTL Support: Arabic Interface

**الوحدات الرئيسية:**
${modules.map(m => `- ${m.nameAr}`).join('\n')}

قم بإنشاء:

## 1. مخطط البنية الكلية (System Architecture)
اشرح بالنص:
- طبقة العرض (Presentation Layer)
- طبقة المنطق (Business Logic Layer)
- طبقة البيانات (Data Layer)
- التفاعلات بينها

## 2. مخطط قاعدة البيانات (Database Schema)
وضح العلاقات بين:
- جدول الفواتير (invoices)
- جدول المنتجات (products)
- جدول العملاء (customers)
- جدول المخازن (warehouses)
- الجداول الأخرى
- العلاقات (Relations): One-to-Many, Many-to-Many

## 3. مخطط تدفق الطلبات (Request Flow)
من متصفح المستخدم حتى قاعدة البيانات:
1. HTTP Request
2. Routing (routes/web.php)
3. Controller
4. Model
5. Database
6. Response
7. View (Blade Template)

## 4. معمارية CRUD
كيف تم تصميم كل وحدة:
- Controller Methods
- Validation Rules
- Database Transactions
- Response Handling

## 5. الأمان والصلاحيات
- Authentication Flow
- CSRF Protection
- Input Validation
- SQL Injection Prevention

قدم المخططات كوصف نصي تفصيلي يمكن تحويله إلى رسم بياني.`;

    const architecture = await analyzeWithGemini(prompt);

    const outputFile = path.join(OUTPUT_DIR, 'system-architecture.md');
    fs.writeFileSync(outputFile, `# مخطط بنية النظام\n\n${architecture}\n`);

    console.log('✅ تم إنشاء مخطط بنية النظام');

    return architecture;
}

async function generateMasterIndex(analyses) {
    console.log('\n📚 إنشاء الفهرس الرئيسي...');

    const indexContent = `# تحليل تجربة المستخدم الشامل - نظام CRM

تم إنشاء هذا التوثيق باستخدام Google Gemini AI

## 📋 المحتويات

### 1. تحليل الوحدات الفردية
${analyses.map((a, i) => `${i + 1}. [${a.nameAr} (${a.module})](${a.module.toLowerCase()}-analysis.md)`).join('\n')}

### 2. التوثيق الشامل

- [خريطة رحلة المستخدم الكاملة](complete-user-journey.md)
- [دليل عمليات CRUD الشامل](crud-operations-guide.md)
- [مخطط بنية النظام](system-architecture.md)

## 🎯 نظرة عامة

نظام CRM متكامل يحتوي على **${modules.length} وحدة رئيسية** مع دعم كامل لعمليات CRUD.

### الوحدات الرئيسية:

${modules.map(m => `- **${m.nameAr}** (${m.name}): ${m.description}
  - العمليات: ${m.operations.join(', ')}`).join('\n\n')}

## 📊 إحصائيات النظام

- **إجمالي الصفحات:** 19 صفحة
- **الوحدات:** ${modules.length} وحدة
- **عمليات CRUD:** Create, Read, Update, Delete
- **عمليات خاصة:** Print, Transfer, Generate Reports
- **اللغة:** العربية (RTL)
- **التصميم:** Responsive (Desktop, Laptop, Tablet, Mobile)

## 🎨 تجربة المستخدم

### نقاط القوة
- واجهة عربية كاملة مع دعم RTL
- تصميم متجاوب على جميع الأجهزة
- تنظيم واضح للمحتوى
- عمليات CRUD سهلة ومباشرة

### التقنيات
- **Backend:** Laravel 12 + PHP 8.2
- **Frontend:** Vite + Alpine.js + Tailwind CSS
- **Database:** SQLite
- **Authentication:** Laravel Breeze

## 📖 كيفية استخدام هذا التوثيق

1. **للمطورين:** راجع [مخطط بنية النظام](system-architecture.md)
2. **لمصممي UX:** راجع [خريطة رحلة المستخدم](complete-user-journey.md)
3. **للمستخدمين:** راجع [دليل عمليات CRUD](crud-operations-guide.md)
4. **للتحليل التفصيلي:** راجع تحليل كل وحدة على حدة

## 🚀 البدء السريع

### تسجيل الدخول
- **Email:** admin@crm.test
- **Password:** password

### الوحدات الأساسية للبدء
1. لوحة التحكم - نظرة عامة على النظام
2. الفواتير - إنشاء أول فاتورة
3. المنتجات - إضافة منتجات للمخزون
4. العملاء - إضافة عملاء جدد

---

تاريخ الإنشاء: ${new Date().toLocaleDateString('ar-EG')}
المحلل: Google Gemini AI
النظام: Laravel CRM System
`;

    const indexFile = path.join(OUTPUT_DIR, 'README.md');
    fs.writeFileSync(indexFile, indexContent);

    console.log('✅ تم إنشاء الفهرس الرئيسي');
}

async function main() {
    console.log('🤖 بدء تحليل تجربة المستخدم باستخدام Gemini AI...\n');
    console.log('📂 المخرجات ستحفظ في: ' + OUTPUT_DIR + '\n');

    // Analyze each module
    const analyses = [];
    for (const module of modules) {
        const analysis = await analyzeModule(module);
        analyses.push(analysis);

        // Wait a bit between requests
        await new Promise(resolve => setTimeout(resolve, 2000));
    }

    // Generate comprehensive documentation
    await generateUserJourneyMap();
    await new Promise(resolve => setTimeout(resolve, 2000));

    await generateCRUDDiagrams();
    await new Promise(resolve => setTimeout(resolve, 2000));

    await generateSystemArchitectureDiagram();
    await new Promise(resolve => setTimeout(resolve, 2000));

    // Generate master index
    await generateMasterIndex(analyses);

    console.log('\n\n✨ اكتمل التحليل بنجاح!');
    console.log(`📁 تم إنشاء ${analyses.length + 4} ملف توثيق`);
    console.log(`📍 الموقع: ${OUTPUT_DIR}`);
    console.log(`📖 افتح: ${path.join(OUTPUT_DIR, 'README.md')} للبدء`);
}

main().catch(console.error);
