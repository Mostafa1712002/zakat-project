# Design: Invoice Contact Persons Footer

## Architecture Overview

```
Settings Page (company.blade.php)
    ↓ form submit (contacts[][name], contacts[][phone])
SettingController::updateCompany()
    ↓ validate + json_encode
settings table (key: invoice_contacts, value: JSON)
    ↓ read on invoice load
SaleController / PurchaseController (show + pdf)
    ↓ json_decode → $invoiceContacts
Invoice Templates (show + pdf × sales + purchases)
```

## Data Storage

Uses existing `settings` key-value table — no migration needed.

```
Key: invoice_contacts
Value: [{"name":"الشيخ رمزي","phone":"01234567890"},{"name":"أحمد","phone":"01111111111"}]
```

## Modified Files

| File | Change |
|------|--------|
| `deploy.sh` | Add `git pull --rebase` before push |
| `settings/company.blade.php` | Contact management UI with dynamic JS |
| `SettingController.php` | Validate + save/load `invoice_contacts` JSON |
| `SaleController.php` | Load `invoice_contacts` in `show()` and `pdf()` |
| `PurchaseController.php` | Load `invoice_contacts` in `show()` and `pdf()` |
| `sales/show.blade.php` | Contacts section below signatures (screen + print) |
| `purchases/show.blade.php` | Contacts section below signatures (screen + print) |
| `pdf/sale.blade.php` | Contacts section with table-cell layout |
| `pdf/purchase.blade.php` | Contacts section with table-cell layout |

## UI Design

### Settings Page Section
```
--- hr ---
📇 جهات اتصال الفاتورة
[اسم جهة الاتصال] [رقم الهاتف] [✕]
[اسم جهة الاتصال] [رقم الهاتف] [✕]
[+ إضافة جهة اتصال]
```
- Dynamic add/remove via vanilla JS
- Form fields: `contacts[index][name]`, `contacts[index][phone]`

### Invoice Display (Screen/Print)
```
──────────────────────────
          جهات الاتصال
  ┌──────────┐  ┌──────────┐
  │  الاسم   │  │  الاسم   │
  │  الهاتف  │  │  الهاتف  │
  └──────────┘  └──────────┘
```
- Flex grid, centered, card-style items
- Appears below signatures section
- Hidden when no contacts configured

### PDF Layout
- `display: table` / `table-cell` for wkhtmltopdf compatibility
- Centered grid with name + phone stacked per contact

## Validation Rules

```php
'contacts' => 'nullable|array',
'contacts.*.name' => 'required|string|max:255',
'contacts.*.phone' => 'required|string|max:20',
```

Empty rows filtered out before saving.
