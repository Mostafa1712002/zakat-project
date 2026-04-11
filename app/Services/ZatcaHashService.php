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
        return base64_encode(hash('sha256', $xml, true));
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
