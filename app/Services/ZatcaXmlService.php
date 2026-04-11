<?php

namespace App\Services;

use App\Models\Sale;
use DOMDocument;
use DOMElement;

class ZatcaXmlService
{
    private const NS_INVOICE = 'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2';
    private const NS_CAC = 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2';
    private const NS_CBC = 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2';
    private const NS_EXT = 'urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2';

    public function generate(Sale $sale, array $seller): string
    {
        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->formatOutput = true;

        $invoice = $this->createRootElement($doc);
        $doc->appendChild($invoice);

        $this->addExtensions($doc, $invoice);
        $this->addProfileIds($doc, $invoice);
        $this->addInvoiceMetadata($doc, $invoice, $sale);
        $this->addInvoiceTypeCode($doc, $invoice, $sale);
        $this->addElement($doc, $invoice, 'cbc', 'DocumentCurrencyCode', 'SAR');
        $this->addElement($doc, $invoice, 'cbc', 'TaxCurrencyCode', 'SAR');

        if ($sale->zatca_note_type && $sale->originalSale) {
            $this->addBillingReference($doc, $invoice, $sale);
        }

        $this->addAdditionalDocumentReferences($doc, $invoice, $sale);
        $this->addSupplierParty($doc, $invoice, $seller);
        $this->addCustomerParty($doc, $invoice, $sale);
        $this->addPaymentMeans($doc, $invoice, $sale);
        $this->addTaxTotals($doc, $invoice, $sale);
        $this->addLegalMonetaryTotal($doc, $invoice, $sale);
        $this->addInvoiceLines($doc, $invoice, $sale);

        return $doc->saveXML();
    }

    private function createRootElement(DOMDocument $doc): DOMElement
    {
        $invoice = $doc->createElementNS(self::NS_INVOICE, 'Invoice');
        $invoice->setAttribute('xmlns:cac', self::NS_CAC);
        $invoice->setAttribute('xmlns:cbc', self::NS_CBC);
        $invoice->setAttribute('xmlns:ext', self::NS_EXT);
        return $invoice;
    }

    private function addExtensions(DOMDocument $doc, DOMElement $parent): void
    {
        $extensions = $doc->createElement('ext:UBLExtensions');
        $extension = $doc->createElement('ext:UBLExtension');
        $content = $doc->createElement('ext:ExtensionContent');
        $extension->appendChild($content);
        $extensions->appendChild($extension);
        $parent->appendChild($extensions);
    }

    private function addProfileIds(DOMDocument $doc, DOMElement $parent): void
    {
        $this->addElement($doc, $parent, 'cbc', 'ProfileID', 'reporting:1.0');
    }

    private function addInvoiceMetadata(DOMDocument $doc, DOMElement $parent, Sale $sale): void
    {
        $this->addElement($doc, $parent, 'cbc', 'ID', $sale->invoice_number);
        $this->addElement($doc, $parent, 'cbc', 'UUID', $sale->zatca_uuid);
        $issuedAt = $sale->zatca_issued_at;
        $this->addElement($doc, $parent, 'cbc', 'IssueDate', $issuedAt->format('Y-m-d'));
        $this->addElement($doc, $parent, 'cbc', 'IssueTime', $issuedAt->format('H:i:s'));
    }

    private function addInvoiceTypeCode(DOMDocument $doc, DOMElement $parent, Sale $sale): void
    {
        $typeCode = match ($sale->zatca_note_type) {
            Sale::ZATCA_NOTE_CREDIT => '381',
            Sale::ZATCA_NOTE_DEBIT => '383',
            default => '388',
        };
        $element = $this->addElement($doc, $parent, 'cbc', 'InvoiceTypeCode', $typeCode);
        $subType = $sale->zatca_invoice_type === Sale::ZATCA_INVOICE_STANDARD ? '0100000' : '0200000';
        $element->setAttribute('name', $subType);
    }

    private function addBillingReference(DOMDocument $doc, DOMElement $parent, Sale $sale): void
    {
        $billingRef = $doc->createElement('cac:BillingReference');
        $invoiceDocRef = $doc->createElement('cac:InvoiceDocumentReference');
        $this->addElement($doc, $invoiceDocRef, 'cbc', 'ID', $sale->originalSale->invoice_number);
        $billingRef->appendChild($invoiceDocRef);
        $parent->appendChild($billingRef);

        if ($sale->zatca_note_reason) {
            $this->addElement($doc, $parent, 'cbc', 'Note', $sale->zatca_note_reason);
        }
    }

    private function addAdditionalDocumentReferences(DOMDocument $doc, DOMElement $parent, Sale $sale): void
    {
        $icvRef = $doc->createElement('cac:AdditionalDocumentReference');
        $this->addElement($doc, $icvRef, 'cbc', 'ID', 'ICV');
        $this->addElement($doc, $icvRef, 'cbc', 'UUID', (string) ($sale->zatca_invoice_counter ?? 1));
        $parent->appendChild($icvRef);

        $pihRef = $doc->createElement('cac:AdditionalDocumentReference');
        $this->addElement($doc, $pihRef, 'cbc', 'ID', 'PIH');
        $attachment = $doc->createElement('cac:Attachment');
        $embedded = $doc->createElement('cbc:EmbeddedDocumentBinaryObject', $sale->zatca_previous_invoice_hash ?? '');
        $embedded->setAttribute('mimeCode', 'text/plain');
        $attachment->appendChild($embedded);
        $pihRef->appendChild($attachment);
        $parent->appendChild($pihRef);
    }

    private function addSupplierParty(DOMDocument $doc, DOMElement $parent, array $seller): void
    {
        $supplierParty = $doc->createElement('cac:AccountingSupplierParty');
        $party = $doc->createElement('cac:Party');

        $partyId = $doc->createElement('cac:PartyIdentification');
        $idEl = $this->addElement($doc, $partyId, 'cbc', 'ID', $seller['tax_number']);
        $idEl->setAttribute('schemeID', 'CRN');
        $party->appendChild($partyId);

        $address = $doc->createElement('cac:PostalAddress');
        $this->addElement($doc, $address, 'cbc', 'StreetName', $seller['address']);
        $this->addElement($doc, $address, 'cbc', 'BuildingNumber', $seller['zatca_building_number'] ?? '');
        $this->addElement($doc, $address, 'cbc', 'PlotIdentification', $seller['zatca_additional_number'] ?? '');
        $this->addElement($doc, $address, 'cbc', 'CitySubdivisionName', $seller['zatca_district'] ?? '');
        $this->addElement($doc, $address, 'cbc', 'CityName', $seller['city']);
        $this->addElement($doc, $address, 'cbc', 'PostalZone', $seller['postal_code']);
        $country = $doc->createElement('cac:Country');
        $this->addElement($doc, $country, 'cbc', 'IdentificationCode', $seller['zatca_country_code'] ?? 'SA');
        $address->appendChild($country);
        $party->appendChild($address);

        $partyTaxScheme = $doc->createElement('cac:PartyTaxScheme');
        $this->addElement($doc, $partyTaxScheme, 'cbc', 'CompanyID', $seller['tax_number']);
        $taxScheme = $doc->createElement('cac:TaxScheme');
        $this->addElement($doc, $taxScheme, 'cbc', 'ID', 'VAT');
        $partyTaxScheme->appendChild($taxScheme);
        $party->appendChild($partyTaxScheme);

        $legalEntity = $doc->createElement('cac:PartyLegalEntity');
        $this->addElement($doc, $legalEntity, 'cbc', 'RegistrationName', $seller['company_name']);
        $party->appendChild($legalEntity);

        $supplierParty->appendChild($party);
        $parent->appendChild($supplierParty);
    }

    private function addCustomerParty(DOMDocument $doc, DOMElement $parent, Sale $sale): void
    {
        $customerParty = $doc->createElement('cac:AccountingCustomerParty');
        $party = $doc->createElement('cac:Party');
        $customer = $sale->customer;

        if ($customer && filled($customer->tax_number)) {
            $partyId = $doc->createElement('cac:PartyIdentification');
            $idEl = $this->addElement($doc, $partyId, 'cbc', 'ID', $customer->tax_number);
            $idEl->setAttribute('schemeID', 'CRN');
            $party->appendChild($partyId);

            $partyTaxScheme = $doc->createElement('cac:PartyTaxScheme');
            $this->addElement($doc, $partyTaxScheme, 'cbc', 'CompanyID', $customer->tax_number);
            $taxScheme = $doc->createElement('cac:TaxScheme');
            $this->addElement($doc, $taxScheme, 'cbc', 'ID', 'VAT');
            $partyTaxScheme->appendChild($taxScheme);
            $party->appendChild($partyTaxScheme);

            $legalEntity = $doc->createElement('cac:PartyLegalEntity');
            $this->addElement($doc, $legalEntity, 'cbc', 'RegistrationName', $customer->name);
            $party->appendChild($legalEntity);
        }

        $customerParty->appendChild($party);
        $parent->appendChild($customerParty);
    }

    private function addPaymentMeans(DOMDocument $doc, DOMElement $parent, Sale $sale): void
    {
        $paymentMeans = $doc->createElement('cac:PaymentMeans');
        $code = match ($sale->payment_type) {
            'cash' => '10',
            'credit' => '30',
            default => '10',
        };
        $this->addElement($doc, $paymentMeans, 'cbc', 'PaymentMeansCode', $code);
        $parent->appendChild($paymentMeans);
    }

    private function addTaxTotals(DOMDocument $doc, DOMElement $parent, Sale $sale): void
    {
        $taxTotal = $doc->createElement('cac:TaxTotal');
        $taxAmountEl = $this->addElement($doc, $taxTotal, 'cbc', 'TaxAmount', $this->formatAmount($sale->tax_amount));
        $taxAmountEl->setAttribute('currencyID', 'SAR');

        $taxGroups = $sale->items->groupBy('tax_rate');
        foreach ($taxGroups as $rate => $items) {
            $taxSubtotal = $doc->createElement('cac:TaxSubtotal');
            $taxableAmount = $items->sum('subtotal') - $items->sum('discount_amount');
            $taxAmount = $items->sum('tax_amount');

            $el1 = $this->addElement($doc, $taxSubtotal, 'cbc', 'TaxableAmount', $this->formatAmount($taxableAmount));
            $el1->setAttribute('currencyID', 'SAR');
            $el2 = $this->addElement($doc, $taxSubtotal, 'cbc', 'TaxAmount', $this->formatAmount($taxAmount));
            $el2->setAttribute('currencyID', 'SAR');

            $taxCategory = $doc->createElement('cac:TaxCategory');
            $this->addElement($doc, $taxCategory, 'cbc', 'ID', $rate > 0 ? 'S' : 'Z');
            $this->addElement($doc, $taxCategory, 'cbc', 'Percent', $this->formatAmount($rate));
            $taxScheme = $doc->createElement('cac:TaxScheme');
            $this->addElement($doc, $taxScheme, 'cbc', 'ID', 'VAT');
            $taxCategory->appendChild($taxScheme);
            $taxSubtotal->appendChild($taxCategory);
            $taxTotal->appendChild($taxSubtotal);
        }
        $parent->appendChild($taxTotal);

        $taxTotal2 = $doc->createElement('cac:TaxTotal');
        $el3 = $this->addElement($doc, $taxTotal2, 'cbc', 'TaxAmount', $this->formatAmount($sale->tax_amount));
        $el3->setAttribute('currencyID', 'SAR');
        $parent->appendChild($taxTotal2);
    }

    private function addLegalMonetaryTotal(DOMDocument $doc, DOMElement $parent, Sale $sale): void
    {
        $total = $doc->createElement('cac:LegalMonetaryTotal');
        $lineExtension = $sale->items->sum(fn ($item) => $item->subtotal - $item->discount_amount);

        $el1 = $this->addElement($doc, $total, 'cbc', 'LineExtensionAmount', $this->formatAmount($lineExtension));
        $el1->setAttribute('currencyID', 'SAR');
        $taxExclusive = $lineExtension - ($sale->discount_amount ?? 0);
        $el2 = $this->addElement($doc, $total, 'cbc', 'TaxExclusiveAmount', $this->formatAmount($taxExclusive));
        $el2->setAttribute('currencyID', 'SAR');
        $taxInclusive = $taxExclusive + $sale->tax_amount;
        $el3 = $this->addElement($doc, $total, 'cbc', 'TaxInclusiveAmount', $this->formatAmount($taxInclusive));
        $el3->setAttribute('currencyID', 'SAR');
        $el4 = $this->addElement($doc, $total, 'cbc', 'AllowanceTotalAmount', $this->formatAmount($sale->discount_amount ?? 0));
        $el4->setAttribute('currencyID', 'SAR');
        $el5 = $this->addElement($doc, $total, 'cbc', 'PayableAmount', $this->formatAmount($sale->total_amount));
        $el5->setAttribute('currencyID', 'SAR');
        $parent->appendChild($total);
    }

    private function addInvoiceLines(DOMDocument $doc, DOMElement $parent, Sale $sale): void
    {
        foreach ($sale->items as $index => $item) {
            $line = $doc->createElement('cac:InvoiceLine');
            $this->addElement($doc, $line, 'cbc', 'ID', (string) ($index + 1));

            $qty = $this->addElement($doc, $line, 'cbc', 'InvoicedQuantity', $this->formatAmount($item->quantity));
            $qty->setAttribute('unitCode', 'PCE');

            $lineAmount = $item->subtotal - $item->discount_amount;
            $ext = $this->addElement($doc, $line, 'cbc', 'LineExtensionAmount', $this->formatAmount($lineAmount));
            $ext->setAttribute('currencyID', 'SAR');

            $lineTax = $doc->createElement('cac:TaxTotal');
            $lineTaxAmt = $this->addElement($doc, $lineTax, 'cbc', 'TaxAmount', $this->formatAmount($item->tax_amount));
            $lineTaxAmt->setAttribute('currencyID', 'SAR');
            $roundingAmt = $this->addElement($doc, $lineTax, 'cbc', 'RoundingAmount', $this->formatAmount($lineAmount + $item->tax_amount));
            $roundingAmt->setAttribute('currencyID', 'SAR');
            $line->appendChild($lineTax);

            $itemEl = $doc->createElement('cac:Item');
            $this->addElement($doc, $itemEl, 'cbc', 'Name', $item->product_name);
            $classifiedTax = $doc->createElement('cac:ClassifiedTaxCategory');
            $this->addElement($doc, $classifiedTax, 'cbc', 'ID', $item->tax_rate > 0 ? 'S' : 'Z');
            $this->addElement($doc, $classifiedTax, 'cbc', 'Percent', $this->formatAmount($item->tax_rate));
            $taxScheme = $doc->createElement('cac:TaxScheme');
            $this->addElement($doc, $taxScheme, 'cbc', 'ID', 'VAT');
            $classifiedTax->appendChild($taxScheme);
            $itemEl->appendChild($classifiedTax);
            $line->appendChild($itemEl);

            $price = $doc->createElement('cac:Price');
            $priceAmt = $this->addElement($doc, $price, 'cbc', 'PriceAmount', $this->formatAmount($item->unit_price));
            $priceAmt->setAttribute('currencyID', 'SAR');
            $line->appendChild($price);

            $parent->appendChild($line);
        }
    }

    private function addElement(DOMDocument $doc, DOMElement $parent, string $prefix, string $name, string $value): DOMElement
    {
        $ns = match ($prefix) {
            'cbc' => self::NS_CBC,
            'cac' => self::NS_CAC,
            'ext' => self::NS_EXT,
            default => self::NS_INVOICE,
        };
        $element = $doc->createElementNS($ns, "{$prefix}:{$name}");
        $element->appendChild($doc->createTextNode($value));
        $parent->appendChild($element);
        return $element;
    }

    private function formatAmount(float|string $amount): string
    {
        return number_format((float) $amount, 2, '.', '');
    }
}
