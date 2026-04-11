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
        $doc = new \DOMDocument();
        $doc->loadXML($xml);
        $xpath = new \DOMXPath($doc);

        $xpath->registerNamespace('ext', 'urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2');
        $xpath->registerNamespace('cac', 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
        $xpath->registerNamespace('cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');

        // Remove UBLExtensions
        foreach ($xpath->query('//ext:UBLExtensions') as $node) {
            $node->parentNode->removeChild($node);
        }

        // Remove cac:Signature
        foreach ($xpath->query('//cac:Signature') as $node) {
            $node->parentNode->removeChild($node);
        }

        // Remove QR AdditionalDocumentReference
        foreach ($xpath->query("//cac:AdditionalDocumentReference[cbc:ID='QR']") as $node) {
            $node->parentNode->removeChild($node);
        }

        // C14N canonicalize
        $canonicalized = $doc->documentElement->C14N(false, false);

        // ZATCA whitespace fixups
        $canonicalized = str_replace(
            '<cbc:ProfileID>',
            "\n    <cbc:ProfileID>",
            $canonicalized
        );
        $canonicalized = str_replace(
            '<cac:AccountingSupplierParty>',
            "\n    \n    <cac:AccountingSupplierParty>",
            $canonicalized
        );

        return $canonicalized;
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
