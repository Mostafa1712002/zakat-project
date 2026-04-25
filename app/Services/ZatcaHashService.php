<?php

namespace App\Services;

use App\Domain\Sales\Models\Invoice as Sale;

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
        return $doc->documentElement->C14N(false, false);
    }

    public function getNextCounter(?int $lastCounter): int
    {
        return ($lastCounter ?? 0) + 1;
    }

    public function getLastIssuedInvoiceData(): array
    {
        // Phase 5b: read the new Invoice model (legacy column names mapped via
        // accessors: invoice_hash + icv).
        $last = Sale::whereNotNull('invoice_hash')
            ->orderByDesc('icv')
            ->first(['invoice_hash', 'icv']);

        return [
            'hash' => $last?->invoice_hash,
            'counter' => $last?->icv !== null ? (int) $last->icv : null,
        ];
    }
}
