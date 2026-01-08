#!/usr/bin/env node

const { exec } = require('child_process');
const { promisify } = require('util');
const fs = require('fs');
const path = require('path');

const execAsync = promisify(exec);
const OUTPUT_DIR = path.join(__dirname, '../gallery/ux-analysis');

// Create output directory
if (!fs.existsSync(OUTPUT_DIR)) {
    fs.mkdirSync(OUTPUT_DIR, { recursive: true });
}

// Simplified modules for text-based analysis
const modules = [
    {
        name: 'Dashboard',
        nameAr: 'لوحة التحكم',
        operations: ['Read'],
        description: 'عرض الإحصائيات والمبيعات الشهرية والسنوية، ملخص الفواتير والمشتريات، أحدث العمليات',
        elements: 'إحصائيات رقمية، رسوم بيانية، جداول الأنشطة الأخيرة، بطاقات ملونة للمؤشرات'
    },
    {
        name: 'Invoices',
        nameAr: 'الفواتير',
        operations: ['Create', 'Read', 'Update', 'Delete', 'Print'],
        description: 'إدارة فواتير البيع مع طباعة الفواتير، البحث والفلترة، تتبع حالة الدفع',
        elements: 'جدول الفواتير، نموذج إنشاء فاتورة، حقول: العميل، المنتجات، الأسعار، المجاميع، حالة الدفع'
    },
    {
        name: 'Purchases',
        nameAr: 'المشتريات',
        operations: ['Create', 'Read', 'Update', 'Delete'],
        description: 'إدارة فواتير الشراء من الموردين، تتبع المخزون، حساب التكاليف',
        elements: 'قائمة المشتريات، نموذج فاتورة شراء، اختيار المورد، إضافة منتجات، حساب الإجمالي'
    },
    {
        name: 'Employees',
        nameAr: 'الموظفين',
        operations: ['Create', 'Read', 'Update', 'Delete'],
        description: 'إدارة بيانات الموظفين والرواتب والمعلومات الشخصية',
        elements: 'جدول الموظفين، نموذج إضافة موظف، حقول: الاسم، الوظيفة، الراتب، رقم الهاتف، العنوان'
    },
    {
        name: 'Customers',
        nameAr: 'العملاء',
        operations: ['Create', 'Read', 'Update', 'Delete'],
        description: 'إدارة قاعدة بيانات العملاء، معلومات الاتصال، سجل المعاملات',
        elements: 'قائمة العملاء، نموذج إضافة عميل، حقول: الاسم، الهاتف، العنوان، البريد الإلكتروني'
    },
    {
        name: 'Products',
        nameAr: 'المنتجات',
        operations: ['Create', 'Read', 'Update', 'Delete'],
        description: 'إدارة كتالوج المنتجات، الأسعار، الكميات، الأقسام',
        elements: 'جدول المنتجات، نموذج منتج جديد، حقول: الاسم، القسم، السعر، الكمية، الباركود'
    },
    {
        name: 'Suppliers',
        nameAr: 'الموردين',
        operations: ['Create', 'Read', 'Update', 'Delete'],
        description: 'إدارة بيانات الموردين، معلومات الاتصال، سجل المشتريات',
        elements: 'قائمة الموردين، معلومات المورد، تاريخ التعاملات'
    },
    {
        name: 'Warehouses',
        nameAr: 'المخازن',
        operations: ['Create', 'Read', 'Update', 'Delete', 'Transfer'],
        description: 'إدارة المخازن المتعددة، نقل المخزون بين المخازن، تتبع المستويات',
        elements: 'قائمة المخازن، حركات المخزون، نموذج نقل بين المخازن'
    },
    {
        name: 'Sales',
        nameAr: 'المبيعات',
        operations: ['Create', 'Read', 'Update', 'Delete'],
        description: 'تتبع عمليات البيع اليومية، الإيرادات، والأرباح',
        elements: 'سجل المبيعات، تفاصيل البيع، المندوب، التاريخ، المبلغ'
    },
    {
        name: 'Expenses',
        nameAr: 'المصروفات',
        operations: ['Create', 'Read', 'Update', 'Delete'],
        description: 'إدارة المصروفات والتكاليف التشغيلية، تصنيف المصروفات',
        elements: 'قائمة المصروفات، نموذج إضافة مصروف، حقول: النوع، المبلغ، التاريخ، الوصف'
    },
    {
        name: 'Reports',
        nameAr: 'التقارير',
        operations: ['Read', 'Generate', 'Export'],
        description: 'تقارير مالية وإحصائية: المبيعات، المشتريات، الأرباح والخسائر، المصروفات',
        elements: 'أقسام التقارير، فلاتر التاريخ (من-إلى), زر عرض التقرير، خيارات التصدير'
    }
];

async function analyzeWithGemini(prompt) {
    try {
        // Create a temporary file for the prompt
        const tmpFile = `/tmp/gemini-prompt-${Date.now()}.txt`;
        fs.writeFileSync(tmpFile, prompt);

        const command = `cat "${tmpFile}" | gemini`;
        const { stdout } = await execAsync(command, { maxBuffer: 1024 * 1024 * 10 });

        // Clean up
        fs.unlinkSync(tmpFile);

        // Remove "Loaded cached credentials." from output
        return stdout.replace(/Loaded cached credentials\./g, '').trim();
    } catch (error) {
        console.error(`خطأ في التحليل:`, error.message);
        return `خطأ في التحليل: ${error.message}`;
    }
}

async function analyzeModule(module) {
    console.log(`\n🔍 تحليل: ${module.nameAr} (${module.name})`);

    const prompt = `أنت خبير في تجربة المستخدم (UX) وتحليل الأنظمة. قم بتحليل هذه الوحدة من نظام CRM:

**اسم الوحدة:** ${module.nameAr} (${module.name})
**الوصف:** ${module.description}
**العمليات المتاحة:** ${module.operations.join(', ')}
**العناصر الرئيسية:** ${module.elements}

قم بتقديم التالي بشكل تفصيلي ومنظم:

## 1. تحليل واجهة المستخدم (UI Analysis)
اشرح العناصر الموجودة:
- الجداول والقوائم
- النماذج وحقول الإدخال
- الأزرار وعناصر التحكم
- التصميم والتخطيط

## 2. تدفق تجربة المستخدم (User Flow)
اشرح خطوات المستخدم لكل عملية:
- كيف يبدأ؟
- ماذا يفعل؟
- أين ينقر؟
- ماذا يرى؟
- كيف ينتهي؟

## 3. عمليات CRUD بالتفصيل
لكل عملية من (${module.operations.join(', ')}):

### CREATE (إنشاء):
- كيف يصل المستخدم لنموذج الإنشاء؟
- ما هي الحقول المطلوبة والاختيارية؟
- كيف يتم التحقق من البيانات؟
- ما هي رسالة النجاح؟
- ماذا يحدث بعد الحفظ؟

### READ (قراءة/عرض):
- كيف تعرض القائمة؟
- ما هي خيارات البحث والفلترة؟
- كيف يعرض التفاصيل؟
- ما هي المعلومات المعروضة؟

### UPDATE (تعديل):
- كيف يصل للتعديل؟
- كيف تحمل البيانات الحالية؟
- ما الذي يمكن تعديله؟
- كيف يحفظ التغييرات؟

### DELETE (حذف):
- كيف يحذف سجل؟
- هل هناك تأكيد؟
- ماذا يحدث للبيانات المرتبطة؟
- ما هي رسالة النجاح/الخطأ؟

## 4. نقاط القوة
اذكر 5 نقاط قوة في التصميم

## 5. نقاط التحسين المقترحة
اقترح 5 تحسينات ممكنة

## 6. مخطط تدفق نصي (Text Flow Diagram)
قدم وصف تدفق العمليات بشكل نقاط متسلسلة من البداية للنهاية

استخدم اللغة العربية الواضحة وأمثلة عملية واقعية.`;

    const analysis = await analyzeWithGemini(prompt);

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
اشرح 4 شخصيات:
- المدير المالي: أهدافه، مهامه اليومية، التحديات
- محاسب المبيعات: أهدافه، مهامه اليومية، التحديات
- أمين المخزن: أهدافه، مهامه اليومية، التحديات
- مندوب المبيعات: أهدافه، مهامه اليومية، التحديات

## 2. السيناريوهات الرئيسية
لكل شخصية، اشرح سيناريو يوم عمل كامل:
- ماذا يفعل في البداية؟
- ما هي المهام الرئيسية؟
- كيف يستخدم النظام؟
- ما هي التحديات؟
- كيف ينهي يومه؟

## 3. مخطط التدفق الكامل
اشرح رحلة المستخدم من تسجيل الدخول حتى إنهاء العمل

## 4. نقاط الاحتكاك (Pain Points)
حدد 10 مشاكل محتملة مع الحلول المقترحة

## 5. مقاييس النجاح (Success Metrics)
كيف نقيس نجاح تجربة المستخدم؟ حدد 10 مؤشرات رئيسية

استخدم اللغة العربية الواضحة.`;

    const journeyMap = await analyzeWithGemini(prompt);

    const outputFile = path.join(OUTPUT_DIR, 'complete-user-journey.md');
    fs.writeFileSync(outputFile, `# خريطة رحلة المستخدم الكاملة\n\n${journeyMap}\n`);

    console.log('✅ تم إنشاء خريطة رحلة المستخدم');

    return journeyMap;
}

async function generateCRUDGuide() {
    console.log('\n📊 إنشاء دليل عمليات CRUD...');

    const prompt = `أنت خبير في توثيق الأنظمة. قم بإنشاء دليل شامل وتفصيلي لعمليات CRUD في نظام CRM:

## ما هو CRUD؟
اشرح المفهوم بالعربية بطريقة بسيطة

## عمليات CRUD الأربعة

### 1. CREATE (إنشاء - إضافة)
**التعريف:** ...
**متى نستخدمها:** ...
**الخطوات التفصيلية:**
- الخطوة 1: ...
- الخطوة 2: ...
- الخطوة 3: ...
**مثال من نظام CRM:** إنشاء فاتورة جديدة
**أفضل الممارسات:** ...
**الأخطاء الشائعة:** ...

### 2. READ (قراءة - عرض)
**التعريف:** ...
**متى نستخدمها:** ...
**أنواع القراءة:**
- عرض القوائم (List View)
- عرض التفاصيل (Detail View)
- البحث والفلترة
**مثال من نظام CRM:** عرض قائمة المنتجات
**أفضل الممارسات:** ...

### 3. UPDATE (تحديث - تعديل)
**التعريف:** ...
**متى نستخدمها:** ...
**الخطوات:** ...
**مثال من نظام CRM:** تعديل بيانات عميل
**أفضل الممارسات:** ...
**التحديات:** ...

### 4. DELETE (حذف)
**التعريف:** ...
**متى نستخدمها:** ...
**أنواع الحذف:**
- الحذف الفعلي (Hard Delete)
- الحذف المنطقي (Soft Delete)
**مثال من نظام CRM:** حذف مصروف
**أفضل الممارسات:** ...
**تحذيرات السلامة:** ...

## أمثلة تطبيقية من كل وحدة
قدم مثال CRUD كامل لـ:
- الفواتير
- المنتجات
- العملاء
- المصروفات

## مخططات تدفق نصية
ارسم مخططات تدفق بالنص توضح العمليات

استخدم اللغة العربية البسيطة مع أمثلة واقعية.`;

    const crudGuide = await analyzeWithGemini(prompt);

    const outputFile = path.join(OUTPUT_DIR, 'crud-operations-guide.md');
    fs.writeFileSync(outputFile, `# دليل عمليات CRUD الشامل\n\n${crudGuide}\n`);

    console.log('✅ تم إنشاء دليل عمليات CRUD');

    return crudGuide;
}

async function generateSystemArchitecture() {
    console.log('\n🏗️  إنشاء وثيقة بنية النظام...');

    const prompt = `أنت مهندس أنظمة. قم بإنشاء وصف تفصيلي لبنية نظام CRM:

**التقنيات:**
- Backend: Laravel 12 (PHP 8.2)
- Database: SQLite
- Frontend: Vite, Alpine.js, Tailwind CSS
- Arabic RTL Interface

**الوحدات:** ${modules.length} وحدة رئيسية

قم بإنشاء:

## 1. معمارية النظام (System Architecture)

### الطبقات (Layers):
- **طبقة العرض (Presentation):** Blade Templates, Alpine.js, Tailwind CSS
- **طبقة المنطق (Business Logic):** Laravel Controllers, Models
- **طبقة البيانات (Data):** SQLite Database

اشرح كيف تتفاعل هذه الطبقات

### 2. قاعدة البيانات (Database Schema)

اشرح الجداول الرئيسية:
- invoices (الفواتير)
- products (المنتجات)
- customers (العملاء)
- warehouses (المخازن)
- employees (الموظفين)
- purchases (المشتريات)
- expenses (المصروفات)

**العلاقات:**
- One-to-Many: اذكر أمثلة
- Many-to-Many: اذكر أمثلة

### 3. تدفق الطلبات (Request Lifecycle)

اشرح خطوة بخطوة من المتصفح للاستجابة:
1. المستخدم يطلب صفحة
2. Routing (Laravel Routes)
3. Controller يستقبل الطلب
4. Model يتعامل مع البيانات
5. Database Query
6. معالجة النتائج
7. إرجاع View
8. عرض الصفحة

### 4. الأمان (Security)

- المصادقة (Authentication): Laravel Breeze
- CSRF Protection
- SQL Injection Prevention
- Input Validation
- XSS Protection

### 5. الأداء (Performance)

- Database Indexing
- Query Optimization
- Caching Strategy
- Asset Optimization

استخدم العربية مع مصطلحات تقنية واضحة.`;

    const architecture = await analyzeWithGemini(prompt);

    const outputFile = path.join(OUTPUT_DIR, 'system-architecture.md');
    fs.writeFileSync(outputFile, `# وثيقة بنية النظام\n\n${architecture}\n`);

    console.log('✅ تم إنشاء وثيقة بنية النظام');

    return architecture;
}

async function generateMasterIndex(analyses) {
    console.log('\n📚 إنشاء الفهرس الرئيسي...');

    const indexContent = `# 📊 تحليل تجربة المستخدم الشامل - نظام CRM

> تم إنشاء هذا التوثيق باستخدام **Google Gemini AI**
> التاريخ: ${new Date().toLocaleDateString('ar-EG')}

## 📋 جدول المحتويات

### 📱 تحليل الوحدات الفردية (${analyses.length} وحدة)
${analyses.map((a, i) => `${i + 1}. [${a.nameAr} (${a.module})](${a.module.toLowerCase()}-analysis.md) - ${modules.find(m => m.name === a.module)?.description}`).join('\n')}

### 📚 التوثيق الشامل

- [🗺️ خريطة رحلة المستخدم الكاملة](complete-user-journey.md)
- [📊 دليل عمليات CRUD الشامل](crud-operations-guide.md)
- [🏗️ وثيقة بنية النظام](system-architecture.md)

## 🎯 نظرة عامة على النظام

نظام CRM متكامل يحتوي على **${modules.length} وحدة رئيسية** مع دعم كامل لعمليات CRUD.

### 🔹 الوحدات الرئيسية:

${modules.map((m, i) => `**${i + 1}. ${m.nameAr}** (${m.name})
   - الوصف: ${m.description}
   - العمليات: ${m.operations.join(', ')}`).join('\n\n')}

## 📊 إحصائيات النظام

| المؤشر | القيمة |
|--------|--------|
| 📄 إجمالي الصفحات | 19 صفحة |
| 🔧 عدد الوحدات | ${modules.length} وحدة |
| ⚡ عمليات CRUD | Create, Read, Update, Delete |
| 🎨 عمليات خاصة | Print, Transfer, Generate Reports |
| 🌍 اللغة | العربية (RTL) |
| 📱 التصميم | Responsive (4 أحجام) |
| 📸 لقطات الشاشة | 76 صورة |
| 🎬 فيديو توضيحي | 70 ثانية |

## 🎨 مميزات تجربة المستخدم

### ✅ نقاط القوة
- واجهة عربية كاملة مع دعم RTL
- تصميم متجاوب على جميع الأجهزة
- تنظيم واضح ومنطقي للمحتوى
- عمليات CRUD سهلة ومباشرة
- بحث وفلترة متقدمة
- إحصائيات وتقارير شاملة

### 🚀 التقنيات المستخدمة
- **Backend:** Laravel 12 + PHP 8.2
- **Frontend:** Vite + Alpine.js + Tailwind CSS
- **Database:** SQLite
- **Authentication:** Laravel Breeze
- **UI Framework:** Tailwind CSS + RTL

## 📖 كيفية استخدام هذا التوثيق

### 👨‍💻 للمطورين
1. ابدأ بـ [وثيقة بنية النظام](system-architecture.md)
2. راجع تحليل كل وحدة لفهم المتطلبات
3. استخدم [دليل CRUD](crud-operations-guide.md) للتطبيق

### 🎨 لمصممي UX
1. اقرأ [خريطة رحلة المستخدم](complete-user-journey.md)
2. راجع تحليل كل وحدة للتفاصيل
3. استفد من نقاط القوة والتحسينات المقترحة

### 👥 للمستخدمين النهائيين
1. ابدأ بـ [دليل عمليات CRUD](crud-operations-guide.md)
2. راجع تحليل الوحدات التي تستخدمها
3. تابع خريطة رحلة المستخدم لفهم التدفق الكامل

### 🎓 للمتعلمين
1. افهم مفهوم CRUD من [الدليل الشامل](crud-operations-guide.md)
2. تعلم بنية الأنظمة من [وثيقة البنية](system-architecture.md)
3. ادرس أمثلة عملية من تحليل الوحدات

## 🚀 البدء السريع

### 🔐 تسجيل الدخول
\`\`\`
URL: http://crm.test/login
Email: admin@crm.test
Password: password
\`\`\`

### 📝 الوحدات الأساسية للبدء
1. **لوحة التحكم** - نظرة عامة على جميع البيانات
2. **الفواتير** - إنشاء أول فاتورة بيع
3. **المنتجات** - إضافة منتجات للمخزون
4. **العملاء** - إضافة عملاء جدد
5. **التقارير** - عرض التقارير المالية

## 🎯 حالات الاستخدام

### 💼 للشركات الصغيرة والمتوسطة
- إدارة المخزون والمبيعات
- تتبع العملاء والفواتير
- تقارير مالية دورية

### 🏪 للمتاجر
- إدارة نقاط البيع
- تتبع المخزون عبر فروع متعددة
- إدارة الموردين والمشتريات

### 📊 للإدارة المالية
- تقارير الأرباح والخسائر
- تتبع المصروفات
- تحليل الأداء المالي

---

**المحلل:** Google Gemini AI
**النظام:** Laravel CRM System v12
**التاريخ:** ${new Date().toLocaleDateString('ar-EG')}
**الترخيص:** MIT License
`;

    const indexFile = path.join(OUTPUT_DIR, 'README.md');
    fs.writeFileSync(indexFile, indexContent);

    console.log('✅ تم إنشاء الفهرس الرئيسي');
}

async function main() {
    console.log('🤖 بدء تحليل تجربة المستخدم باستخدام Gemini AI...\n');
    console.log('📂 المخرجات ستحفظ في: ' + OUTPUT_DIR + '\n');
    console.log('⏱️  الوقت المتوقع: ~10-15 دقيقة\n');

    // Analyze each module
    const analyses = [];
    for (const module of modules) {
        const analysis = await analyzeModule(module);
        analyses.push(analysis);

        // Wait between requests
        await new Promise(resolve => setTimeout(resolve, 3000));
    }

    // Generate comprehensive documentation
    await generateUserJourneyMap();
    await new Promise(resolve => setTimeout(resolve, 3000));

    await generateCRUDGuide();
    await new Promise(resolve => setTimeout(resolve, 3000));

    await generateSystemArchitecture();
    await new Promise(resolve => setTimeout(resolve, 3000));

    // Generate master index
    await generateMasterIndex(analyses);

    console.log('\n\n✨ اكتمل التحليل بنجاح!');
    console.log(`📁 تم إنشاء ${analyses.length + 4} ملف توثيق`);
    console.log(`📍 الموقع: ${OUTPUT_DIR}`);
    console.log(`📖 افتح: ${path.join(OUTPUT_DIR, 'README.md')} للبدء`);
    console.log('\n🎉 يمكنك الآن استعراض التحليل الكامل لتجربة المستخدم!');
}

main().catch(console.error);
