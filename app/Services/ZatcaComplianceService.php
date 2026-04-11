<?php

namespace App\Services;

use App\Models\Sale;
use App\Services\ZatcaHashService;
use App\Services\ZatcaXmlService;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ZatcaComplianceService
{
    public function isEnabled(): bool
    {
        return $this->getSetting('zatca_enabled', '0') === '1';
    }

    public function getCompanySettings(): array
    {
        return [
            'company_name' => (string) $this->getSetting('company_name', ''),
            'tax_number' => (string) $this->getSetting('tax_number', ''),
            'address' => (string) $this->getSetting('address', ''),
            'city' => (string) $this->getSetting('city', ''),
            'postal_code' => (string) $this->getSetting('postal_code', ''),
            'country' => (string) $this->getSetting('country', 'المملكة العربية السعودية'),
            'zatca_enabled' => (string) $this->getSetting('zatca_enabled', '0'),
            'zatca_environment' => (string) $this->getSetting('zatca_environment', 'simulation'),
            'zatca_business_category' => (string) $this->getSetting('zatca_business_category', ''),
            'zatca_building_number' => (string) $this->getSetting('zatca_building_number', ''),
            'zatca_additional_number' => (string) $this->getSetting('zatca_additional_number', ''),
            'zatca_district' => (string) $this->getSetting('zatca_district', ''),
            'zatca_country_code' => (string) $this->getSetting('zatca_country_code', 'SA'),
            'zatca_egs_serial' => (string) $this->getSetting('zatca_egs_serial', ''),
            'zatca_solution_name' => (string) $this->getSetting('zatca_solution_name', ''),
            'zatca_certificate' => (string) $this->getSetting('zatca_certificate', ''),
            'zatca_secret' => (string) $this->getSetting('zatca_secret', ''),
        ];
    }

    public function missingRequiredSettings(?array $settings = null): array
    {
        $settings ??= $this->getCompanySettings();

        $required = [
            'company_name' => 'اسم الشركة',
            'tax_number' => 'الرقم الضريبي',
            'address' => 'العنوان',
            'city' => 'المدينة',
            'postal_code' => 'الرمز البريدي',
            'zatca_building_number' => 'رقم المبنى',
            'zatca_district' => 'الحي',
            'zatca_egs_serial' => 'الرقم التسلسلي لوحدة الفوترة',
        ];

        $missing = [];

        foreach ($required as $key => $label) {
            if (blank($settings[$key] ?? null)) {
                $missing[] = $label;
            }
        }

        return $missing;
    }

    public function prepareIssuedInvoiceData(Sale $sale, ?array $settings = null): array
    {
        $settings ??= $this->getCompanySettings();
        $issuedAt = now();
        $invoiceType = $this->determineInvoiceType($sale);

        return [
            'zatca_uuid' => (string) Str::uuid(),
            'zatca_invoice_type' => $invoiceType,
            'zatca_status' => $invoiceType === Sale::ZATCA_INVOICE_STANDARD
                ? Sale::ZATCA_STATUS_PENDING_CLEARANCE
                : Sale::ZATCA_STATUS_PENDING_REPORTING,
            'zatca_issued_at' => $issuedAt,
            'zatca_qr_tlv' => $this->buildQrPayload(
                sellerName: $settings['company_name'],
                vatNumber: $settings['tax_number'],
                issuedAt: $issuedAt,
                invoiceTotal: (float) $sale->total_amount,
                vatTotal: (float) $sale->tax_amount,
            ),
        ];
    }

    public function determineInvoiceType(Sale $sale): string
    {
        return filled($sale->customer?->tax_number)
            ? Sale::ZATCA_INVOICE_STANDARD
            : Sale::ZATCA_INVOICE_SIMPLIFIED;
    }

    public function buildQrPayload(
        string $sellerName,
        string $vatNumber,
        CarbonInterface $issuedAt,
        float $invoiceTotal,
        float $vatTotal
    ): string {
        $payload = '';

        $payload .= $this->encodeTlv(1, $sellerName);
        $payload .= $this->encodeTlv(2, $vatNumber);
        $payload .= $this->encodeTlv(3, $issuedAt->toIso8601String());
        $payload .= $this->encodeTlv(4, number_format($invoiceTotal, 2, '.', ''));
        $payload .= $this->encodeTlv(5, number_format($vatTotal, 2, '.', ''));

        return base64_encode($payload);
    }

    public function generateInvoiceXmlAndHash(Sale $sale, array $settings): array
    {
        $hashService = new ZatcaHashService();
        $xmlService = new ZatcaXmlService();

        $lastIssued = $hashService->getLastIssuedInvoiceData();
        $pih = $hashService->getPreviousInvoiceHash($lastIssued['hash']);
        $counter = $hashService->getNextCounter($lastIssued['counter']);

        $sale->zatca_previous_invoice_hash = $pih;
        $sale->zatca_invoice_counter = $counter;

        $xml = $xmlService->generate($sale, $settings);
        $invoiceHash = $hashService->generateInvoiceHash($xml);

        return [
            'zatca_xml' => $xml,
            'zatca_invoice_hash' => $invoiceHash,
            'zatca_previous_invoice_hash' => $pih,
            'zatca_invoice_counter' => $counter,
            'zatca_xml_generated_at' => now(),
        ];
    }

    protected function encodeTlv(int $tag, string $value): string
    {
        $length = strlen($value);

        return chr($tag) . chr($length) . $value;
    }

    protected function getSetting(string $key, $default = null)
    {
        try {
            $value = DB::table('settings')->where('key', $key)->value('value');

            return $value ?? $default;
        } catch (\Throwable) {
            return $default;
        }
    }
}
