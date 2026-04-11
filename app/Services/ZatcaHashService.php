<?php

namespace App\Services;

use App\Models\Sale;

class ZatcaHashService
{
    public function getPreviousInvoiceHash(?string $lastHash): string
    {
        if ($lastHash !== null) {
            return $lastHash;
        }
        return base64_encode(hash('sha256', '0', true));
    }

    public function generateInvoiceHash(string $xml): string
    {
        $hashableXml = $this->prepareXmlForHashing($xml);
        return base64_encode(hash('sha256', $hashableXml, true));
    }

    public function prepareXmlForHashing(string $xml): string
    {
        // Remove XML declaration line
        $lines = explode("\n", $xml);
        if (count($lines) > 0 && str_starts_with(trim($lines[0]), '<' . '?xml')) {
            array_shift($lines);
        }
        $xml = implode("\n", $lines);

        // Remove UBLExtensions element
        $xml = preg_replace('#<ext:UBLExtensions>.*?</ext:UBLExtensions>\s*#s', '', $xml);

        // Remove cac:Signature element
        $xml = preg_replace('#<cac:Signature>.*?</cac:Signature>\s*#s', '', $xml);

        return trim($xml);
    }

    public function getNextCounter(?int $lastCounter): int
    {
        return ($lastCounter ?? 0) + 1;
    }

    public function getLastIssuedInvoiceData(): array
    {
        $lastSale = Sale::whereNotNull('zatca_invoice_hash')
            ->orderByDesc('zatca_invoice_counter')
            ->first(['zatca_invoice_hash', 'zatca_invoice_counter']);

        return [
            'hash' => $lastSale?->zatca_invoice_hash,
            'counter' => $lastSale?->zatca_invoice_counter,
        ];
    }
}
