# التوثيق التقني — ZATCA e-Invoicing (Phase 2)

> للنظرة العامة وبيانات النظام انظر `docs/ZATCA-README.md`

---

## هيكل الـ XML (UBL 2.1)

### Namespaces (مساحات الأسماء)

```xml
<Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2"
         xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2"
         xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2"
         xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2"
         xmlns:sig="urn:oasis:names:specification:ubl:schema:xsd:CommonSignatureComponents-2"
         xmlns:sbc="urn:oasis:names:specification:ubl:schema:xsd:SignatureBasicComponents-2"
         xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
```

### ترتيب العناصر (Order of Elements)

الترتيب في الـ XML ثابت ومطلوب بالضبط من الهيئة:

```
Invoice
├── ext:UBLExtensions                          ← placeholder للتوقيع
│   └── ext:UBLExtension
│       └── ext:ExtensionContent
│           └── sig:UBLDocumentSignatures
│               └── sac:SignatureInformation
│                   ├── cbc:ID
│                   └── sbc:ReferencedSignatureID
├── cbc:ProfileID                              ← "reporting:1.0"
├── cbc:ID                                     ← رقم الفاتورة
├── cbc:UUID                                   ← UUIDv4
├── cbc:IssueDate                              ← YYYY-MM-DD
├── cbc:IssueTime                              ← HH:MM:SS
├── cbc:InvoiceTypeCode name="..."             ← 388/381/383 مع subtype
├── cbc:DocumentCurrencyCode                   ← "SAR"
├── cbc:TaxCurrencyCode                        ← "SAR"
├── cac:BillingReference                       ← للإشعارات فقط
│   └── cac:InvoiceDocumentReference
│       └── cbc:ID                             ← رقم الفاتورة الأصلية
├── cbc:Note                                   ← سبب الإشعار (اختياري)
├── cac:AdditionalDocumentReference [ID=ICV]   ← UUID = رقم ICV التسلسلي
├── cac:AdditionalDocumentReference [ID=PIH]   ← هاش الفاتورة السابقة
│   └── cac:Attachment
│       └── cbc:EmbeddedDocumentBinaryObject mimeCode="text/plain"
├── cac:AdditionalDocumentReference [ID=QR]    ← يُضاف بعد التوقيع
│   └── cac:Attachment
│       └── cbc:EmbeddedDocumentBinaryObject mimeCode="text/plain"
├── cac:Signature                              ← مرجع فقط (قبل Parties)
│   ├── cbc:ID
│   └── cbc:SignatureMethod
├── cac:AccountingSupplierParty               ← بيانات البائع
│   └── cac:Party
│       ├── cac:PartyIdentification [schemeID=CRN]
│       ├── cac:PostalAddress
│       │   ├── cbc:StreetName
│       │   ├── cbc:BuildingNumber
│       │   ├── cbc:PlotIdentification
│       │   ├── cbc:CitySubdivisionName
│       │   ├── cbc:CityName
│       │   ├── cbc:PostalZone
│       │   └── cac:Country/cbc:IdentificationCode
│       ├── cac:PartyTaxScheme
│       │   ├── cbc:CompanyID                  ← رقم VAT
│       │   └── cac:TaxScheme/cbc:ID "VAT"
│       └── cac:PartyLegalEntity/cbc:RegistrationName
├── cac:AccountingCustomerParty               ← بيانات المشتري (B2B كاملة، B2C فارغة)
├── cac:Delivery
│   └── cbc:ActualDeliveryDate                ← تاريخ الفاتورة (KSA-5)
├── cac:PaymentMeans
│   └── cbc:PaymentMeansCode                  ← 10=cash, 30=credit
├── cac:TaxTotal                              ← الأول: مع TaxSubtotals
│   ├── cbc:TaxAmount currencyID="SAR"
│   └── cac:TaxSubtotal (واحد لكل tax rate)
│       ├── cbc:TaxableAmount
│       ├── cbc:TaxAmount
│       └── cac:TaxCategory
│           ├── cbc:ID                        ← "S" (خاضع) أو "Z" (صفري)
│           ├── cbc:Percent
│           └── cac:TaxScheme/cbc:ID "VAT"
├── cac:TaxTotal                              ← الثاني: مجموع فقط (مطلوب من الهيئة)
│   └── cbc:TaxAmount currencyID="SAR"
├── cac:LegalMonetaryTotal
│   ├── cbc:LineExtensionAmount               ← مجموع subtotals
│   ├── cbc:TaxExclusiveAmount                ← بعد خصم الحسم الكلي
│   ├── cbc:TaxInclusiveAmount                ← شامل الضريبة
│   ├── cbc:AllowanceTotalAmount              ← مجموع الحسومات
│   └── cbc:PayableAmount                     ← المبلغ المستحق
└── cac:InvoiceLine (واحد لكل بند)
    ├── cbc:ID
    ├── cbc:InvoicedQuantity unitCode="PCE"
    ├── cbc:LineExtensionAmount               ← subtotal - discount
    ├── cac:TaxTotal
    │   ├── cbc:TaxAmount
    │   └── cbc:RoundingAmount                ← lineAmount + taxAmount
    ├── cac:Item
    │   ├── cbc:Name
    │   └── cac:ClassifiedTaxCategory
    │       ├── cbc:ID
    │       ├── cbc:Percent
    │       └── cac:TaxScheme/cbc:ID "VAT"
    └── cac:Price/cbc:PriceAmount             ← سعر الوحدة
```

### InvoiceTypeCode — القيم

| InvoiceTypeCode | name | النوع |
|-----------------|------|-------|
| 388 | 0100000 | فاتورة ضريبية (standard B2B) |
| 388 | 0200000 | فاتورة مبسطة (simplified B2C) |
| 381 | 0100000 | إشعار دائن على فاتورة ضريبية |
| 381 | 0200000 | إشعار دائن على فاتورة مبسطة |
| 383 | 0100000 | إشعار مدين على فاتورة ضريبية |

---

## حساب الهاش (Invoice Hash)

**الخدمة:** `ZatcaHashService`

### خوارزمية الحساب

```php
// 1. تحميل XML في DOMDocument
$doc = new DOMDocument();
$doc->loadXML($xml);

// 2. حذف ثلاثة عناصر قبل الـ C14N
//    - ext:UBLExtensions  (يحتوي على التوقيع)
//    - cac:Signature      (مرجع التوقيع)
//    - cac:AdditionalDocumentReference[cbc:ID='QR']  (رمز QR)

// 3. Canonical XML
$canonical = $doc->documentElement->C14N(false, false);
//    الوسيط الأول false = inclusive (not exclusive)
//    الوسيط الثاني false = no comments

// 4. الهاش
$hash = base64_encode(hash('sha256', $canonical, true));
```

### سلسلة التجزئة (Hash Chain)

- **PIH للفاتورة الأولى:** `base64_encode(hash('sha256', '0', true))`
- **PIH لكل فاتورة لاحقة:** هاش الفاتورة السابقة مخزوناً في `zatca_invoice_hash`
- **ICV (Invoice Counter Value):** يزداد بمقدار 1 مع كل فاتورة — يُخزَّن في `zatca_invoice_counter`
- PIH يُضَمَّن في XML كـ `cbc:EmbeddedDocumentBinaryObject` داخل `AdditionalDocumentReference[ID=PIH]`

### البحث عن آخر فاتورة

```php
// ZatcaHashService::getLastIssuedInvoiceData()
$lastSale = Sale::whereNotNull('zatca_invoice_hash')
    ->orderByDesc('zatca_invoice_counter')
    ->first(['zatca_invoice_hash', 'zatca_invoice_counter']);
```

---

## التوقيع الرقمي (XAdES-BES Signature)

**الخدمة:** `ZatcaSigningService`

### المتطلبات

- المفتاح الخاص: ECDSA secp256k1 بصيغة PEM في `storage/zatca/zatca_key.pem`
- الشهادة: `binarySecurityToken` من الهيئة مخزّنة في جدول `settings`
- المكتبة: `phpseclib3` (لا openssl — انظر مشكلة double-hashing في قسم المشاكل المعروفة)

### فك تشفير الشهادة (binarySecurityToken)

الشهادة من الهيئة بصيغة `base64(base64(DER))`:

```php
// binarySecurityToken = base64(base64(DER))
$certBody = base64_decode($binarySecurityToken);   // الناتج: base64(DER) — يُستخدم في ds:X509Certificate
$certDer   = base64_decode($certBody);              // الناتج: raw DER bytes
$certPem   = "-----BEGIN CERTIFICATE-----\n"
           . chunk_split($certBody, 64, "\n")
           . "-----END CERTIFICATE-----";
```

### استخراج معلومات الشهادة

```php
// Serial Number: hex → decimal (bcmath للأرقام الكبيرة)
$serialRaw = $certData['serialNumber']; // قد يكون "0x..." أو decimal
if (str_starts_with($serialRaw, '0x')) {
    $hex = substr($serialRaw, 2);
    $serialNumber = '0';
    for ($i = 0; $i < strlen($hex); $i++) {
        $serialNumber = bcmul($serialNumber, '16');
        $serialNumber = bcadd($serialNumber, (string) hexdec($hex[$i]));
    }
}

// Issuer: قد يحتوي DC على قيم متعددة (array)
foreach ($certData['issuer'] as $key => $value) {
    if (is_array($value)) {
        foreach ($value as $v) { $issuerParts[] = "{$key}={$v}"; }
    } else {
        $issuerParts[] = "{$key}={$value}";
    }
}
$issuer = implode(', ', array_reverse($issuerParts));

// Public Key: phpseclib3 → PKCS8 → strip headers → base64_decode → DER
$plainPubKeyB64 = str_replace(
    ["-----BEGIN PUBLIC KEY-----\n", "\n-----END PUBLIC KEY-----", "\n"],
    '', $x509->getPublicKey()->toString('PKCS8')
);
$publicKeyDer = base64_decode($plainPubKeyB64);

// Cert Signature: phpseclib3 X509 → getCurrentCert()['signature'] → strip first byte
$certSigRaw   = $x509->getCurrentCert()['signature'];
$certSignature = substr($certSigRaw, 1); // الـ byte الأول هو unused bits indicator
```

### حساب هاش الشهادة

```php
// certHash = base64(hex(sha256(full PEM string with headers)))
$certHash = base64_encode(hash('sha256', $certPem));
//                                         ^^^^^ string hex (not binary)
//          ^^^^^^^^^^^ ثم base64 على الـ hex string
```

ملاحظة: الهاش يكون على كامل PEM string بما فيه `-----BEGIN CERTIFICATE-----`.

### التوقيع الرقمي

```php
// توقيع raw bytes الهاش (32 bytes) — ليس الـ base64 string
$hashBytes      = base64_decode($invoiceHash);
$ecPrivateKey   = \phpseclib3\Crypt\EC::loadPrivateKey($privateKeyPem);
$signatureRaw   = $ecPrivateKey->sign($hashBytes);
// phpseclib3 default = sha256 → sign(hashBytes) = ECDSA(SHA256(hashBytes))
$digitalSignature = base64_encode($signatureRaw);
```

### SignedProperties XML

هناك نسختان من SignedProperties:

**نسخة الهاش (للحساب):** بـ whitespace دقيق (32-space indentation) وـ `xmlns:ds` مُضمَّن في كل عنصر

```xml
<xades:SignedProperties xmlns:xades="http://uri.etsi.org/01903/v1.3.2#" Id="xadesSignedProperties">
                                <xades:SignedSignatureProperties>
                                    <xades:SigningTime>2026-04-11T10:00:00Z</xades:SigningTime>
                                    <xades:SigningCertificate>
                                        <xades:Cert>
                                            <xades:CertDigest>
                                                <ds:DigestMethod xmlns:ds="http://www.w3.org/2000/09/xmldsig#" Algorithm="http://www.w3.org/2001/04/xmlenc#sha256"/>
                                                <ds:DigestValue xmlns:ds="http://www.w3.org/2000/09/xmldsig#">[certHash]</ds:DigestValue>
                                            </xades:CertDigest>
                                            <xades:IssuerSerial>
                                                <ds:X509IssuerName xmlns:ds="http://www.w3.org/2000/09/xmldsig#">[issuer]</ds:X509IssuerName>
                                                <ds:X509SerialNumber xmlns:ds="http://www.w3.org/2000/09/xmldsig#">[serialNumber]</ds:X509SerialNumber>
                                            </xades:IssuerSerial>
                                        </xades:Cert>
                                    </xades:SigningCertificate>
                                </xades:SignedSignatureProperties>
                            </xades:SignedProperties>
```

```php
$signedPropsHash = base64_encode(hash('sha256', $signedPropsForSigning));
//                                              ^^^^ أيضاً string hex
```

**نسخة التضمين (للـ XML النهائي):** بدون whitespace، بدون `xmlns:ds` المكرر

### هيكل ds:Signature الكامل

```xml
<ds:Signature xmlns:ds="http://www.w3.org/2000/09/xmldsig#" Id="signature">
  <ds:SignedInfo>
    <ds:CanonicalizationMethod Algorithm="http://www.w3.org/2006/12/xml-c14n11"/>
    <ds:SignatureMethod Algorithm="http://www.w3.org/2001/04/xmldsig-more#ecdsa-sha256"/>
    <ds:Reference Id="invoiceSignedData" URI="">
      <ds:Transforms>
        <ds:Transform Algorithm="http://www.w3.org/TR/1999/REC-xpath-19991116">
          <ds:XPath>not(//ancestor-or-self::ext:UBLExtensions)</ds:XPath>
        </ds:Transform>
        <ds:Transform Algorithm="http://www.w3.org/TR/1999/REC-xpath-19991116">
          <ds:XPath>not(//ancestor-or-self::cac:Signature)</ds:XPath>
        </ds:Transform>
        <ds:Transform Algorithm="http://www.w3.org/TR/1999/REC-xpath-19991116">
          <ds:XPath>not(//ancestor-or-self::cac:AdditionalDocumentReference[cbc:ID='QR'])</ds:XPath>
        </ds:Transform>
        <ds:Transform Algorithm="http://www.w3.org/2006/12/xml-c14n11"/>
      </ds:Transforms>
      <ds:DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256"/>
      <ds:DigestValue>[invoiceHash]</ds:DigestValue>
    </ds:Reference>
    <ds:Reference Type="http://www.w3.org/2000/09/xmldsig#SignatureProperties" URI="#xadesSignedProperties">
      <ds:DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256"/>
      <ds:DigestValue>[signedPropsHash]</ds:DigestValue>
    </ds:Reference>
  </ds:SignedInfo>
  <ds:SignatureValue>[digitalSignature base64]</ds:SignatureValue>
  <ds:KeyInfo>
    <ds:X509Data>
      <ds:X509Certificate>[certBody — inner base64, not full PEM]</ds:X509Certificate>
    </ds:X509Data>
  </ds:KeyInfo>
  <ds:Object>
    <xades:QualifyingProperties xmlns:xades="http://uri.etsi.org/01903/v1.3.2#" Target="signature">
      [SignedProperties — نسخة التضمين بدون whitespace]
    </xades:QualifyingProperties>
  </ds:Object>
</ds:Signature>
```

يُحقَن الـ `ds:Signature` داخل `sac:SignatureInformation` الموجود في `ext:UBLExtensions`.

---

## رمز QR — تشفير TLV (9 Tags)

**الخدمة:** `ZatcaSigningService::buildQrTlv9Tags()`

### جدول الـ Tags

| Tag | الحقل | نوع القيمة | المصدر |
|-----|-------|-----------|--------|
| 1 | اسم البائع (Seller Name) | UTF-8 string | `cac:PartyLegalEntity/cbc:RegistrationName` من XML |
| 2 | الرقم الضريبي (VAT Number) | UTF-8 string | `cac:PartyTaxScheme/cbc:CompanyID` من XML |
| 3 | تاريخ ووقت الإصدار | UTF-8 string | `IssueDate + "T" + IssueTime + "Z"` بصيغة ISO 8601 |
| 4 | المبلغ الإجمالي (Tax Inclusive) | UTF-8 string | `cac:LegalMonetaryTotal/cbc:TaxInclusiveAmount` |
| 5 | مبلغ الضريبة | UTF-8 string | أول `cac:TaxTotal/cbc:TaxAmount` |
| 6 | هاش الفاتورة | UTF-8 string | base64 string (ليس raw bytes) |
| 7 | التوقيع الرقمي | UTF-8 string | base64 string (ليس raw bytes) |
| 8 | المفتاح العام | binary bytes | DER SubjectPublicKeyInfo (phpseclib3 PKCS8 → base64_decode) |
| 9 | توقيع الشهادة | binary bytes | DER signature bytes (strip first byte من phpseclib3 getCurrentCert) |

### تشفير TLV

```php
// للقيم النصية (tags 1-7):
function encodeTlv(int $tag, string $value): string {
    $len = strlen($value);
    if ($len > 255) {
        return chr($tag) . chr(0x82) . pack('n', $len) . $value;
    }
    return chr($tag) . chr($len) . $value;
}

// للقيم الثنائية (tags 8-9): نفس الطريقة — القيمة raw binary bytes
```

البنية: `tag (1 byte) + length (1 byte) + value`
للقيم الأطول من 255 بايت: `tag (1 byte) + 0x82 (1 byte) + length (2 bytes big-endian) + value`

### التجميع النهائي

```php
$tlv = encodeTlv(1, $sellerName)
     . encodeTlv(2, $vatNumber)
     . encodeTlv(3, $dateTime)          // "2026-04-11T10:00:00Z"
     . encodeTlv(4, $totalAmount)
     . encodeTlv(5, $taxAmount)
     . encodeTlv(6, $invoiceHash)       // base64 STRING
     . encodeTlv(7, $digitalSignature)  // base64 STRING
     . encodeTlvBinary(8, $publicKeyDer)    // raw DER bytes
     . encodeTlvBinary(9, $certSignatureDer); // raw DER bytes

$qrTlvBase64 = base64_encode($tlv);    // هذا يُحفظ في zatca_qr_tlv ويُضمَّن في XML
```

### QR الأولي (5 Tags) مقابل QR الكامل (9 Tags)

- **عند تأكيد الفاتورة:** يُولَّد QR بـ 5 tags فقط (tags 1-5) في `ZatcaComplianceService::buildQrPayload()` للعرض الفوري
- **بعد التوقيع:** يُستبدَل بـ QR كامل بـ 9 tags في `ZatcaSigningService::buildQrTlv9Tags()`

### توليد صورة QR

```php
// ZatcaQrService — chillerlan/php-qrcode v6
$options = new QROptions();
$options->outputInterface = QRGdImagePNG::class;
$options->scale = 5;
$qr = new QRCode($options);
$base64Image = $qr->render($tlvBase64);
```

---

## ZATCA API — نقاط النهاية والطلبات

**الخدمة:** `ZatcaApiService`

### Base URLs

| البيئة | Base URL |
|--------|----------|
| developer-portal (sandbox) | `https://gw-fatoora.zatca.gov.sa/e-invoicing/developer-portal` |
| simulation | `https://gw-fatoora.zatca.gov.sa/e-invoicing/simulation` |
| production (core) | `https://gw-fatoora.zatca.gov.sa/e-invoicing/core` |

### Endpoints

| الـ Endpoint | الطريقة | الغرض | Headers إضافية |
|-------------|---------|-------|----------------|
| `/compliance` | POST | الحصول على CSID الأولي | `OTP: [رمز من بوابة فاتورة]`, `Accept-Version: V2` |
| `/compliance/invoices` | POST | فحص compliance الفاتورة | `Authorization: Basic [cert:secret]`, `Accept-Version: V2` |
| `/production/csids` | POST | الحصول على CSID الإنتاج | body: `{compliance_request_id}` |
| `/invoices/clearance/single` | POST | تخليص فاتورة ضريبية | `Clearance-Status: 1` |
| `/invoices/reporting/single` | POST | رفع فاتورة مبسطة | — |

### هيكل الطلب (Request Body)

```json
{
  "invoiceHash": "[base64 SHA-256 hash]",
  "uuid": "[UUIDv4]",
  "invoice": "[base64_encode(xml_string)]"
}
```

### Headers الأساسية

```
Authorization: Basic [base64(certificate:secret)]
Accept-Version: V2
Accept-Language: en
Content-Type: application/json
Accept: application/json
```

### استجابة التخليص الناجحة

```json
{
  "clearanceStatus": "CLEARED",
  "clearedInvoice": "[base64 encoded cleared XML]",
  "validationResults": {
    "status": "PASS",
    "infoMessages": [],
    "warningMessages": [],
    "errorMessages": []
  }
}
```

### استجابة الرفع الناجحة

```json
{
  "reportingStatus": "REPORTED",
  "validationResults": {
    "status": "PASS",
    "infoMessages": [],
    "warningMessages": [],
    "errorMessages": []
  }
}
```

### معالجة الأخطاء في ZatcaApiService

```php
// عند status غير 2xx:
return [
    'error'       => true,
    'status_code' => $response->status(),
    'message'     => $response->json('message', 'Unknown error'),
    'body'        => $response->body(),
];

// عند exception (timeout, network):
return [
    'error'       => true,
    'status_code' => 0,
    'message'     => $e->getMessage(),
];
```

---

## إعدادات الشركة المطلوبة في جدول settings

| المفتاح | الوصف | مثال |
|---------|-------|------|
| `company_name` | الاسم التجاري كما في السجل | مؤسسة آمرك الرائدة |
| `tax_number` | الرقم الضريبي (15 رقم) | 311037737100003 |
| `address` | اسم الشارع | عبدالله بن سعيد ابن الحر |
| `city` | المدينة | جدة |
| `postal_code` | الرمز البريدي | 23241 |
| `zatca_enabled` | تفعيل النظام `0` أو `1` | 1 |
| `zatca_environment` | البيئة | sandbox / simulation / production |
| `zatca_building_number` | رقم المبنى | 7005 |
| `zatca_additional_number` | الرقم الإضافي | 4027 |
| `zatca_district` | الحي | النخيل |
| `zatca_country_code` | رمز الدولة | SA |
| `zatca_egs_serial` | الرقم التسلسلي لوحدة الفوترة | EGS-1-xxx |
| `zatca_solution_name` | اسم الحل | CRM |
| `zatca_certificate` | binarySecurityToken من الهيئة | base64(base64(DER)) |
| `zatca_secret` | الـ secret المصاحب للشهادة | من الهيئة |

---

## المشاكل المعروفة والحلول

### 1. Double-Hashing عند استخدام openssl_sign

**المشكلة:** `openssl_sign($data, $sig, $key, OPENSSL_ALGO_SHA256)` تعمل على الـ data كـ string وتُطبّق SHA-256 داخلياً. إذا مررنا hash bytes (32 bytes) فإنها تُهشّ مرة ثانية → نتيجة خاطئة.

**الحل:** استخدام `phpseclib3\Crypt\EC::loadPrivateKey()->sign($hashBytes)` التي تعمل على raw bytes مباشرة:
```php
// phpseclib3 default hash = sha256
// sign(hashBytes) = ECDSA(SHA256(hashBytes))
// مما يطابق المطلوب: ECDSA sign of the 32 raw bytes
$ecPrivateKey = \phpseclib3\Crypt\EC::loadPrivateKey($privateKeyPem);
$signatureRaw = $ecPrivateKey->sign($hashBytes);
```

### 2. QR Tag 7 — base64 String وليس raw bytes

**المشكلة:** تضمين raw bytes التوقيع مباشرة في Tag 7 يُنتج QR غير صالح.

**الحل:** تمرير `$digitalSignature` كـ base64 STRING:
```php
$tlv .= $this->encodeTlv(7, $digitalSignature); // $digitalSignature = base64_encode($signatureRaw)
```

### 3. هاش الشهادة — sha256 على PEM كاملاً

**المشكلة:** أخذ هاش DER bytes بدلاً من PEM string.

**الحل:**
```php
// صح: على PEM كاملاً بما فيه الـ headers
$certHash = base64_encode(hash('sha256', $certPem));

// خطأ: على DER أو certBody فقط
// $certHash = base64_encode(hash('sha256', $certDer)); // WRONG
```

### 4. xmlns:sbc — namespace مكرر

**المشكلة:** إضافة `xmlns:sbc` في عنصر `sbc:ReferencedSignatureID` رغم تعريفه في `sig:UBLDocumentSignatures` يسبب رفض من بعض المُحقّقين.

**الحل:** استخدام `createElementNS()` بصحيح بدلاً من setAttribute يدوي — PHP DOM يُدير التوريث تلقائياً.

### 5. ترتيب العناصر — AdditionalDocumentReference قبل Signature

**المشكلة:** وضع `cac:Signature` قبل `cac:AdditionalDocumentReference` يُخالف schema الهيئة.

**الحل:** الترتيب الصحيح: ICV → PIH → (QR يُضاف لاحقاً) → Signature → Parties

### 6. تحويل Serial Number من hex إلى decimal

**المشكلة:** `openssl_x509_parse` قد يُرجع serial number بصيغة `0x...` وهو كبير جداً لـ PHP int.

**الحل:**
```php
$hex = substr($serialRaw, 2); // إزالة "0x"
$decimal = '0';
for ($i = 0; $i < strlen($hex); $i++) {
    $decimal = bcmul($decimal, '16');
    $decimal = bcadd($decimal, (string) hexdec($hex[$i]));
}
```

### 7. chillerlan/php-qrcode v6 — تغيير الـ API

**المشكلة:** v4/v5 كانت تستخدم `QRCode::getMatrix()->toString()` أو methods مختلفة.

**الحل (v6):**
```php
$options = new QROptions();
$options->outputInterface = QRGdImagePNG::class;
$options->scale = 5;
$qr = new QRCode($options);
$base64Image = $qr->render($data); // يُرجع data URI مباشرة
```

### 8. binarySecurityToken — double base64

**المشكلة:** محاولة استخدام الـ token مباشرة كـ PEM أو كـ DER.

**الحل:**
```php
$binarySecurityToken = 'MIIB...'; // من الهيئة
$certBody = base64_decode($binarySecurityToken); // = inner base64(DER)
$certDer  = base64_decode($certBody);            // = raw DER bytes
$certPem  = "-----BEGIN CERTIFICATE-----\n"
          . chunk_split($certBody, 64, "\n")
          . "-----END CERTIFICATE-----";
// certBody يُستخدم في ds:X509Certificate
// certPem يُستخدم لـ openssl_x509_read وphpseclib3
```

### 9. C14N Whitespace — لا تعديل مطلوب

**ملاحظة:** تجربة تعديل المسافات في الـ XML قبل C14N لم تكن ضرورية. `DOMDocument::C14N(false, false)` تعمل بشكل صحيح على الـ XML المُولَّد بـ `formatOutput = true`.

---

## تدفق البيانات — ملخص

```
sale.total_amount, sale.tax_amount, sale.items
        ↓
ZatcaXmlService::generate()
        ↓ XML string (UBL 2.1, unsigned)
ZatcaHashService::generateInvoiceHash()
        ↓ strip UBLExtensions + cac:Signature + QR → C14N → SHA-256 → base64
        ↓ invoiceHash
ZatcaSigningService::sign(xml, invoiceHash)
        ↓ hashBytes = base64_decode(invoiceHash)
        ↓ signatureRaw = EC::sign(hashBytes)           [phpseclib3]
        ↓ digitalSignature = base64_encode(signatureRaw)
        ↓ certHash = base64_encode(hex(sha256(certPem)))
        ↓ signedPropsHash = base64_encode(hex(sha256(signedPropsXml)))
        ↓ signatureXml = buildSignatureXml(...)
        ↓ signedXml = injectSignature(xml, signatureXml)
        ↓ qrTlv = buildQrTlv9Tags(...)  [base64 encoded TLV]
        ↓ finalXml = injectQrCode(signedXml, qrTlv)
        ↓
        { signed_xml, digital_signature, qr_tlv, invoice_hash }
        ↓
ZatcaApiService::clearInvoice() or reportInvoice()
        ↓ POST body: { invoiceHash, uuid, invoice: base64(xml) }
        ↓
        استجابة الهيئة → تحديث حالة الفاتورة في DB
```
