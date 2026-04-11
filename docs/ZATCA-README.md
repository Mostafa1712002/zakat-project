# دليل نظام فاتورة ZATCA — مؤسسة آمرك الرائدة

> **الفرع:** `feat/zatca-einvoicing` / `zaca`
> **آخر تحديث:** 2026-04-11

---

## معلومات النظام

| الحقل | القيمة |
|-------|--------|
| الموقع | https://ammrk.newaves-systems.com |
| بريد الدخول | admin@ammrk.com |
| كلمة المرور | Ammrk@2026 |
| السيرفر | root@178.104.122.98 |
| مسار التطبيق | /var/www/zaca.newaves-systems.com |
| الفرع النشط | `feat/zatca-einvoicing` (deploy alias: `zaca`) |
| قاعدة البيانات | MySQL — ammrk_crm |

---

## بيانات الشركة (Seller Info — المسجّلة في الهيئة)

| الحقل | القيمة |
|-------|--------|
| اسم المنشأة | مؤسسة آمرك الرائدة |
| الرقم الضريبي (VAT) | 311037737100003 |
| الرقم الضريبي (TIN) | 3110377371 |
| الفرع | جدة |
| السجل التجاري (CR) | 4030573692 |
| اسم الشارع | عبدالله بن سعيد ابن الحر |
| رقم المبنى | 7005 |
| الرقم الإضافي | 4027 |
| الحي | النخيل |
| المدينة | جدة |
| الرمز البريدي | 23241 |

هذه البيانات مخزّنة في جدول `settings` ويمكن تعديلها من إعدادات النظام.

---

## الملفات الرئيسية

### Services (الخدمات — طبقة الأعمال)

| الملف | المهمة |
|-------|--------|
| `app/Services/ZatcaComplianceService.php` | الخدمة المحورية — تنسيق دورة الفاتورة الكاملة، قراءة إعدادات الشركة من جدول `settings`، تحديد نوع الفاتورة (standard/simplified)، توليد QR ذي 5 tags عند التأكيد، استدعاء XML وهاش والتوقيع |
| `app/Services/ZatcaXmlService.php` | توليد XML بصيغة UBL 2.1 — يبني كامل شجرة عناصر الفاتورة (UBLExtensions, ProfileID, InvoiceTypeCode, BillingReference, AdditionalDocumentReferences, Parties, Delivery, PaymentMeans, TaxTotal, LegalMonetaryTotal, InvoiceLines) |
| `app/Services/ZatcaHashService.php` | حساب سلسلة الهاش — حذف UBLExtensions وcac:Signature وQR من الـ XML ثم C14N(false,false) ثم SHA-256 ثم base64، إدارة PIH، تتبع عدّاد ICV |
| `app/Services/ZatcaSigningService.php` | التوقيع الرقمي XAdES-BES — يستخدم phpseclib3 لتوقيع ECDSA-SHA256 على raw bytes الهاش، يبني SignedProperties بـ whitespace دقيق، يحقن ds:Signature، يُوّلد QR ذا 9 tags، يحقن الكل في الـ XML |
| `app/Services/ZatcaApiService.php` | الاتصال بـ ZATCA API — clearance للفواتير الضريبية (header: Clearance-Status: 1)، reporting للمبسطة، Basic Auth بالشهادة والـ secret، معالجة الأخطاء والـ logging |
| `app/Services/ZatcaQrService.php` | تحويل TLV base64 إلى صورة QR PNG باستخدام `chillerlan/php-qrcode` v6 (`QRGdImagePNG`) |

### Controllers (المتحكمات)

| الملف | المهمة |
|-------|--------|
| `app/Http/Controllers/ZatcaController.php` | لوحة متابعة ZATCA — يعرض إحصائيات الفواتير مقسّمة حسب الحالة (pending_clearance, pending_reporting, cleared, reported, failed) مع قوائم الفواتير المعلّقة والفاشلة والحديثة |
| `app/Http/Controllers/SaleController.php` | إصدار الفواتير — دالة `confirm()` تستدعي `ZatcaComplianceService` لتوليد بيانات ZATCA وحفظها، دالة `storeCreditNote()` لإنشاء إشعارات الدائن/المدين مع ربطها بالفاتورة الأصلية |

### Views (الواجهات)

| الملف | المهمة |
|-------|--------|
| `resources/views/zatca/dashboard.blade.php` | واجهة لوحة متابعة ZATCA على مسار `/zatca/dashboard` |
| `resources/views/sales/credit-note-create.blade.php` | نموذج إنشاء إشعار دائن (Credit Note) أو مدين (Debit Note) |

### Configuration (الإعدادات)

| الملف | المهمة |
|-------|--------|
| `config/zatca.php` | نقاط نهاية API للبيئات الثلاث (sandbox, simulation, production) ومهلة الطلب (timeout: 30s) وإعدادات إعادة المحاولة |

### Migrations (ترحيلات قاعدة البيانات)

| الملف | الحقول المضافة |
|-------|---------------|
| `database/migrations/2026_04_09_160000_add_zatca_fields_to_sales_table.php` | `zatca_uuid`, `zatca_invoice_type`, `zatca_status`, `zatca_issued_at`, `zatca_qr_tlv`, `zatca_xml`, `zatca_xml_generated_at`, `zatca_cleared_at`, `zatca_reported_at`, `zatca_response_reference`, `zatca_last_error` |
| `database/migrations/2026_04_11_100000_add_zatca_phase2_fields_to_sales_table.php` | `zatca_invoice_hash`, `zatca_previous_invoice_hash`, `zatca_invoice_counter`, `zatca_note_type`, `zatca_original_sale_id` (FK), `zatca_note_reason`, `zatca_retry_count`, `zatca_next_retry_at` |

### Storage (التخزين)

| المسار | التفاصيل |
|--------|---------|
| `storage/zatca/zatca_key.pem` | المفتاح الخاص ECDSA (secp256k1) — **لا يُرفع على git أبداً — مضاف في .gitignore** |

---

## حقول جدول sales المضافة لـ ZATCA

### من الترحيل الأول (2026-04-09)

| العمود | النوع | الوصف |
|--------|-------|--------|
| `zatca_uuid` | string, unique | معرّف الفاتورة الفريد (UUIDv4) — يُستخدم في XML وفي طلبات الـ API |
| `zatca_invoice_type` | string | `standard` = فاتورة ضريبية B2B / `simplified` = فاتورة مبسطة B2C |
| `zatca_status` | string | حالة الامتثال (انظر جدول الحالات أدناه) |
| `zatca_issued_at` | timestamp | توقيت إصدار الفاتورة بالـ UTC |
| `zatca_qr_tlv` | longText | TLV مُرمَّز بـ base64 (5 tags عند التأكيد، 9 tags بعد التوقيع) |
| `zatca_xml` | longText | الـ XML الكامل بعد التوقيع وحقن QR |
| `zatca_xml_generated_at` | timestamp | وقت توليد الـ XML |
| `zatca_cleared_at` | timestamp | وقت إتمام التخليص من الهيئة |
| `zatca_reported_at` | timestamp | وقت إتمام الرفع |
| `zatca_response_reference` | string | مرجع الاستجابة من الهيئة |
| `zatca_last_error` | text | آخر رسالة خطأ |

### من الترحيل الثاني (2026-04-11)

| العمود | النوع | الوصف |
|--------|-------|--------|
| `zatca_invoice_hash` | string | هاش الفاتورة الحالية (base64 SHA-256 بعد C14N) |
| `zatca_previous_invoice_hash` | string | هاش الفاتورة السابقة PIH — لبناء سلسلة التجزئة |
| `zatca_invoice_counter` | bigInteger | رقم تسلسلي ICV يزداد بمقدار 1 مع كل فاتورة |
| `zatca_note_type` | string | `credit` = إشعار دائن / `debit` = إشعار مدين |
| `zatca_original_sale_id` | bigInteger, FK | مرجع الفاتورة الأصلية عند إنشاء إشعار |
| `zatca_note_reason` | string | سبب إصدار الإشعار |
| `zatca_retry_count` | integer | عدد محاولات إعادة الإرسال |
| `zatca_next_retry_at` | timestamp | موعد المحاولة التالية |

---

## حالات الفاتورة (ZATCA Status)

| الثابت (Sale Model) | القيمة | المعنى |
|--------------------|--------|--------|
| `ZATCA_STATUS_DRAFT` | `draft` | مسودة — لم تُؤكَّد بعد |
| `ZATCA_STATUS_PENDING_CLEARANCE` | `pending_clearance` | بانتظار التخليص (فاتورة ضريبية standard) |
| `ZATCA_STATUS_PENDING_REPORTING` | `pending_reporting` | بانتظار الرفع (فاتورة مبسطة simplified) |
| `ZATCA_STATUS_CLEARED` | `cleared` | تم التخليص بنجاح |
| `ZATCA_STATUS_REPORTED` | `reported` | تم الرفع بنجاح |
| `ZATCA_STATUS_FAILED` | `failed` | فشل الإرسال — يظهر في لوحة التحكم |

---

## أنواع الفواتير والإشعارات

| النوع | الثابت | InvoiceTypeCode | name attribute |
|-------|--------|-----------------|----------------|
| فاتورة ضريبية B2B | `ZATCA_INVOICE_STANDARD` | 388 | 0100000 |
| فاتورة مبسطة B2C | `ZATCA_INVOICE_SIMPLIFIED` | 388 | 0200000 |
| إشعار دائن | `ZATCA_NOTE_CREDIT` | 381 | حسب نوع الأصلية |
| إشعار مدين | `ZATCA_NOTE_DEBIT` | 383 | حسب نوع الأصلية |

**قاعدة التحديد التلقائي:** إذا كان للعميل `tax_number` → standard، وإلا → simplified

---

## كيفية عمل النظام — الدورة الكاملة

```
1. إنشاء فاتورة بيع (status = draft)
        |
2. تأكيد الفاتورة → SaleController::confirm()
        |
        ├─ ZatcaComplianceService::prepareIssuedInvoiceData()
        │       ├─ توليد UUID فريد (Str::uuid())
        │       ├─ تحديد النوع: tax_number موجود → standard / غير موجود → simplified
        │       ├─ توليد QR ذي 5 tags (seller, VAT, date, total, tax) للعرض الفوري
        │       └─ ضبط الحالة: standard → pending_clearance / simplified → pending_reporting
        |
3. ZatcaComplianceService::generateInvoiceXmlAndHash()
        |
        ├─ ZatcaHashService::getLastIssuedInvoiceData()   ← آخر hash وعدّاد من DB
        ├─ ZatcaHashService::getPreviousInvoiceHash()     ← PIH (أو base64(sha256("0")) للأولى)
        ├─ ZatcaHashService::getNextCounter()             ← ICV = lastCounter + 1
        ├─ ZatcaXmlService::generate()                    ← بناء XML كامل (UBL 2.1)
        └─ ZatcaHashService::generateInvoiceHash()        ← C14N → SHA-256 → base64
        |
4. التوقيع الرقمي (إذا توفّر storage/zatca/zatca_key.pem والشهادة)
        |
        └─ ZatcaSigningService::sign()
                ├─ extractCertificateInfo():
                │       ├─ serial hex → decimal (bcmath)
                │       ├─ issuer (array DC → reversed string)
                │       ├─ public key DER (phpseclib3 PKCS8 → strip headers → base64_decode)
                │       └─ cert signature DER (phpseclib3 getCurrentCert → strip first byte)
                ├─ توقيع hashBytes بـ ECDSA-SHA256 عبر phpseclib3 EC::loadPrivateKey()->sign()
                ├─ certHash = base64(hex(sha256(full_PEM_string)))
                ├─ بناء SignedProperties XML (بـ 32-space indentation للهاش، بدونها للتضمين)
                ├─ signedPropsHash = base64(hex(sha256(signedPropsXml)))
                ├─ بناء ds:Signature XML (XAdES-BES)
                ├─ حقن ds:Signature داخل sac:SignatureInformation في UBLExtensions
                ├─ بناء QR ذي 9 tags (TLV)
                └─ حقن QR في AdditionalDocumentReference[ID=QR]
        |
5. حفظ النتائج في جدول sales
        |
6. إرسال للهيئة (ZatcaApiService)
        ├─ Standard → POST /invoices/clearance/single  (+ Clearance-Status: 1)
        └─ Simplified → POST /invoices/reporting/single
        |
7. تحديث الحالة في قاعدة البيانات (cleared/reported/failed)
```

---

## البيئات

| البيئة | Base URL | الحالة |
|--------|----------|--------|
| `sandbox` (developer-portal) | `https://gw-fatoora.zatca.gov.sa/e-invoicing/developer-portal` | شغّال — للتطوير |
| `simulation` | `https://gw-fatoora.zatca.gov.sa/e-invoicing/simulation` | يحتاج تفعيل من الهيئة |
| `production` (core) | `https://gw-fatoora.zatca.gov.sa/e-invoicing/core` | الإنتاج الفعلي |

الإعداد عبر متغير البيئة في `.env`:

```env
ZATCA_ENVIRONMENT=sandbox
```

أو عبر عمود `zatca_environment` في جدول `settings`.

---

## التحويل للإنتاج — الخطوات بالترتيب

1. التواصل مع الهيئة لتفعيل بيئة `simulation` على الـ VAT الحقيقي
2. الحصول على OTP من بوابة فاتورة (https://fatoora.zatca.gov.sa)
3. استدعاء `POST /compliance` بالـ OTP للحصول على compliance CSID
4. استدعاء `POST /production/csids` بالـ `compliance_request_id`
5. حفظ `zatca_certificate` (binarySecurityToken) وَ `zatca_secret` في جدول `settings`
6. رفع المفتاح الخاص إلى `storage/zatca/zatca_key.pem` على السيرفر
7. تغيير `zatca_environment` إلى `production`
8. **الكود جاهز — لا تعديل مطلوب في الـ services**

---

## أوامر النشر (Deploy)

```bash
# من الجهاز المحلي
cd /home/mostafa/www/crm

# رفع الكود على الفرعين
git push origin feat/zatca-einvoicing zaca

# تحديث السيرفر وتشغيل الترحيلات
ssh root@178.104.122.98 "
  cd /var/www/zaca.newaves-systems.com && \
  git fetch origin && \
  git reset --hard origin/zaca && \
  php artisan migrate --force && \
  php artisan config:clear && \
  php artisan cache:clear
"
```

الفرع `zaca` على السيرفر يعكس `feat/zatca-einvoicing` دائماً.

---

## الاختبارات

```bash
# تشغيل جميع اختبارات ZATCA
php artisan test --filter=Zatca

# أو كل ملف على حدة
php artisan test tests/Feature/ZatcaSaleComplianceTest.php
php artisan test tests/Feature/ZatcaXmlGenerationTest.php
php artisan test tests/Feature/ZatcaHashChainTest.php
php artisan test tests/Feature/ZatcaApiIntegrationTest.php
php artisan test tests/Feature/ZatcaCreditNoteTest.php
```

| ملف الاختبار | الاختبارات | ما يغطيه |
|-------------|-----------|---------|
| `ZatcaSaleComplianceTest` | 4 | تحديد النوع B2B/B2C، توليد UUID وQR وهاش وXML عند التأكيد، منع التعديل بعد الإصدار |
| `ZatcaXmlGenerationTest` | 5 | XML للمبسطة، الضريبية، إشعار الدائن مع BillingReference، بنود الفاتورة، TaxTotal |
| `ZatcaHashChainTest` | 4 | PIH للفاتورة الأولى (hash("0")), SHA-256، عدّاد ICV |
| `ZatcaApiIntegrationTest` | 3 | clearance مع Clearance-Status header، reporting، معالجة أخطاء API |
| `ZatcaCreditNoteTest` | 2 | إنشاء إشعار دائن على فاتورة cleared، منع الإشعار على فاتورة draft |

---

## الروابط المهمة

| الوصف | الرابط |
|-------|--------|
| لوحة ZATCA في النظام | `/zatca/dashboard` |
| بوابة فاتورة (ZATCA) | https://fatoora.zatca.gov.sa |
| ZATCA Sandbox | https://sandbox.zatca.gov.sa |
| ZATCA API Gateway | https://gw-fatoora.zatca.gov.sa/e-invoicing/ |
| التوثيق الفني للتوثيق التقني | انظر `docs/ZATCA-TECHNICAL.md` |

---

## ملاحظات تشغيلية مهمة

- المفتاح الخاص `storage/zatca/zatca_key.pem` لا يُرفع على git — يُرفع يدوياً على السيرفر
- `zatca_certificate` و`zatca_secret` مخزّنان في جدول `settings` وليس في `.env`
- النظام يعمل بدون توقيع إذا غاب المفتاح أو الشهادة (للاختبار فقط)
- إشعارات الدائن/المدين مرتبطة بالفاتورة الأصلية عبر `zatca_original_sale_id`
- لا يمكن تعديل فاتورة بعد إصدارها (تحقق في SaleController)
- للتفاصيل التقنية الدقيقة (XML، هاش، توقيع، QR، API) انظر `docs/ZATCA-TECHNICAL.md`
