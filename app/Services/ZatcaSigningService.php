<?php

namespace App\Services;

use DOMDocument;
use DOMXPath;

class ZatcaSigningService
{
    private string $privateKeyPem;
    private string $certificateBase64;

    public function __construct(string $privateKeyPath, string $certificateBase64)
    {
        $this->privateKeyPem = file_get_contents($privateKeyPath);
        $this->certificateBase64 = $certificateBase64;
    }

    /**
     * Sign an invoice XML and return the signed XML with QR.
     */
    public function sign(string $xml, string $invoiceHash): array
    {
        // 1. Get certificate info
        $certDer = base64_decode($this->certificateBase64);
        $certInfo = $this->extractCertificateInfo($certDer);

        // 2. Sign the invoice hash with private key
        $hashBytes = base64_decode($invoiceHash);
        $signature = '';
        $privateKey = openssl_pkey_get_private($this->privateKeyPem);
        openssl_sign($hashBytes, $signatureRaw, $privateKey, OPENSSL_ALGO_SHA256);
        $digitalSignature = base64_encode($signatureRaw);

        // 3. Build signed properties XML and hash it
        $signingTime = gmdate('Y-m-d\TH:i:s\Z');
        $certHash = $this->computeCertificateHash($this->certificateBase64);
        $signedPropsForSigning = $this->buildSignedPropertiesForSigning(
            $signingTime,
            $certHash,
            $certInfo['issuer'],
            $certInfo['serialNumber']
        );
        $signedPropsHash = $this->hashSignedProperties($signedPropsForSigning);

        // 4. Build the signed properties for embedding (without inline xmlns)
        $signedPropsEmbed = $this->buildSignedPropertiesForEmbedding(
            $signingTime,
            $certHash,
            $certInfo['issuer'],
            $certInfo['serialNumber']
        );

        // 5. Build the full ds:Signature XML
        $signatureXml = $this->buildSignatureXml(
            $invoiceHash,
            $signedPropsHash,
            $digitalSignature,
            $this->certificateBase64,
            $signedPropsEmbed
        );

        // 6. Inject signature into UBLExtensions
        $signedXml = $this->injectSignature($xml, $signatureXml);

        // 7. Build QR TLV with 9 tags
        $qrTlv = $this->buildQrTlv9Tags(
            $xml,
            $invoiceHash,
            $digitalSignature,
            $certInfo['publicKey'],
            $certInfo['certSignature']
        );

        // 8. Inject QR into XML
        $finalXml = $this->injectQrCode($signedXml, $qrTlv);

        return [
            'signed_xml' => $finalXml,
            'digital_signature' => $digitalSignature,
            'qr_tlv' => $qrTlv,
            'invoice_hash' => $invoiceHash,
        ];
    }

    private function extractCertificateInfo(string $certDer): array
    {
        // The certificateBase64 is already base64 of the DER cert
        // but certDer here is base64_decode of binarySecurityToken which is base64(base64(DER))
        // So certDer might actually be the PEM body text, not raw DER
        $certPem = "-----BEGIN CERTIFICATE-----\n"
            . chunk_split(base64_encode($certDer), 64, "\n")
            . "-----END CERTIFICATE-----";

        $certResource = openssl_x509_read($certPem);

        // If that fails, the token is base64(PEM_body), so decode once more
        if ($certResource === false) {
            $decoded = base64_decode($this->certificateBase64);
            if ($decoded !== false) {
                $certPem = "-----BEGIN CERTIFICATE-----\n"
                    . chunk_split($this->certificateBase64, 64, "\n")
                    . "-----END CERTIFICATE-----";
                $certResource = openssl_x509_read($certPem);
                $certDer = base64_decode($this->certificateBase64);
            }
        }

        // If still fails, try the binarySecurityToken as base64(base64(DER))
        if ($certResource === false) {
            $innerBase64 = base64_decode($this->certificateBase64);
            $certPem = "-----BEGIN CERTIFICATE-----\n"
                . chunk_split($innerBase64, 64, "\n")
                . "-----END CERTIFICATE-----";
            $certResource = openssl_x509_read($certPem);
            $certDer = base64_decode($innerBase64);
        }

        $certData = openssl_x509_parse($certResource);

        // Get issuer string (reversed, comma-separated)
        $issuerParts = [];
        foreach (array_reverse($certData['issuer']) as $key => $value) {
            $issuerParts[] = "{$key}={$value}";
        }
        $issuer = implode(', ', $issuerParts);

        // Serial number in decimal
        $serialNumber = $certData['serialNumber'] ?? '0';

        // Extract public key DER
        $pubKeyDetails = openssl_pkey_get_details(openssl_pkey_get_public($certResource));
        $publicKeyDer = $pubKeyDetails['key'] ?? '';
        // Convert PEM public key to DER
        $publicKeyDer = $this->pemToDer($pubKeyDetails['key']);

        // Extract certificate signature (last part of DER)
        $certSignature = $this->extractCertSignature($certDer);

        return [
            'issuer' => $issuer,
            'serialNumber' => $serialNumber,
            'publicKey' => $publicKeyDer,
            'certSignature' => $certSignature,
        ];
    }

    private function pemToDer(string $pem): string
    {
        $lines = explode("\n", $pem);
        $body = '';
        foreach ($lines as $line) {
            if (str_starts_with($line, '-----')) continue;
            $body .= trim($line);
        }
        return base64_decode($body);
    }

    private function extractCertSignature(string $certDer): string
    {
        // Parse ASN.1 to get the signature value from the certificate
        // Certificate = SEQUENCE { tbsCertificate, signatureAlgorithm, signatureValue }
        // signatureValue is a BIT STRING at the end

        $hex = bin2hex($certDer);
        // Find the last BIT STRING (tag 03) which is the signature
        $lastBitString = strrpos($hex, '03');

        if ($lastBitString !== false) {
            $pos = $lastBitString;
            $tag = substr($hex, $pos, 2);
            $pos += 2;

            // Read length
            $lenByte = hexdec(substr($hex, $pos, 2));
            $pos += 2;

            if ($lenByte > 0x80) {
                $numLenBytes = $lenByte - 0x80;
                $len = hexdec(substr($hex, $pos, $numLenBytes * 2));
                $pos += $numLenBytes * 2;
            } else {
                $len = $lenByte;
            }

            // Skip the unused bits byte (00)
            $pos += 2;
            $len -= 1;

            $sigHex = substr($hex, $pos, $len * 2);
            return hex2bin($sigHex);
        }

        return '';
    }

    private function computeCertificateHash(string $certBase64): string
    {
        // hash = base64(hex(sha256(cert_base64_string)))
        $hashBytes = hash('sha256', $certBase64, true);
        $hashHex = bin2hex($hashBytes);
        return base64_encode($hashHex);
    }

    private function hashSignedProperties(string $signedPropsXml): string
    {
        // hash = base64(hex(sha256(xml_bytes)))
        $hashBytes = hash('sha256', $signedPropsXml, true);
        $hashHex = bin2hex($hashBytes);
        return base64_encode($hashHex);
    }

    private function buildSignedPropertiesForSigning(
        string $signingTime,
        string $certHash,
        string $issuer,
        string $serialNumber
    ): string {
        return '<xades:SignedProperties xmlns:xades="http://uri.etsi.org/01903/v1.3.2#" Id="xadesSignedProperties">'
            . '<xades:SignedSignatureProperties>'
            . '<xades:SigningTime>' . $signingTime . '</xades:SigningTime>'
            . '<xades:SigningCertificate>'
            . '<xades:Cert>'
            . '<xades:CertDigest>'
            . '<ds:DigestMethod xmlns:ds="http://www.w3.org/2000/09/xmldsig#" Algorithm="http://www.w3.org/2001/04/xmlenc#sha256"></ds:DigestMethod>'
            . '<ds:DigestValue xmlns:ds="http://www.w3.org/2000/09/xmldsig#">' . $certHash . '</ds:DigestValue>'
            . '</xades:CertDigest>'
            . '<xades:IssuerSerial>'
            . '<ds:X509IssuerName xmlns:ds="http://www.w3.org/2000/09/xmldsig#">' . $issuer . '</ds:X509IssuerName>'
            . '<ds:X509SerialNumber xmlns:ds="http://www.w3.org/2000/09/xmldsig#">' . $serialNumber . '</ds:X509SerialNumber>'
            . '</xades:IssuerSerial>'
            . '</xades:Cert>'
            . '</xades:SigningCertificate>'
            . '</xades:SignedSignatureProperties>'
            . '</xades:SignedProperties>';
    }

    private function buildSignedPropertiesForEmbedding(
        string $signingTime,
        string $certHash,
        string $issuer,
        string $serialNumber
    ): string {
        return '<xades:SignedProperties xmlns:xades="http://uri.etsi.org/01903/v1.3.2#" Id="xadesSignedProperties">'
            . '<xades:SignedSignatureProperties>'
            . '<xades:SigningTime>' . $signingTime . '</xades:SigningTime>'
            . '<xades:SigningCertificate>'
            . '<xades:Cert>'
            . '<xades:CertDigest>'
            . '<ds:DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256"></ds:DigestMethod>'
            . '<ds:DigestValue>' . $certHash . '</ds:DigestValue>'
            . '</xades:CertDigest>'
            . '<xades:IssuerSerial>'
            . '<ds:X509IssuerName>' . $issuer . '</ds:X509IssuerName>'
            . '<ds:X509SerialNumber>' . $serialNumber . '</ds:X509SerialNumber>'
            . '</xades:IssuerSerial>'
            . '</xades:Cert>'
            . '</xades:SigningCertificate>'
            . '</xades:SignedSignatureProperties>'
            . '</xades:SignedProperties>';
    }

    private function buildSignatureXml(
        string $invoiceHash,
        string $signedPropsHash,
        string $digitalSignature,
        string $certificateBase64,
        string $signedPropsEmbed
    ): string {
        return '<ds:Signature xmlns:ds="http://www.w3.org/2000/09/xmldsig#" Id="signature">'
            . '<ds:SignedInfo>'
            . '<ds:CanonicalizationMethod Algorithm="http://www.w3.org/2006/12/xml-c14n11"/>'
            . '<ds:SignatureMethod Algorithm="http://www.w3.org/2001/04/xmldsig-more#ecdsa-sha256"/>'
            . '<ds:Reference Id="invoiceSignedData" URI="">'
            . '<ds:Transforms>'
            . '<ds:Transform Algorithm="http://www.w3.org/TR/1999/REC-xpath-19991116">'
            . '<ds:XPath>not(//ancestor-or-self::ext:UBLExtensions)</ds:XPath>'
            . '</ds:Transform>'
            . '<ds:Transform Algorithm="http://www.w3.org/TR/1999/REC-xpath-19991116">'
            . '<ds:XPath>not(//ancestor-or-self::cac:Signature)</ds:XPath>'
            . '</ds:Transform>'
            . '<ds:Transform Algorithm="http://www.w3.org/TR/1999/REC-xpath-19991116">'
            . '<ds:XPath>not(//ancestor-or-self::cac:AdditionalDocumentReference[cbc:ID=\'QR\'])</ds:XPath>'
            . '</ds:Transform>'
            . '<ds:Transform Algorithm="http://www.w3.org/2006/12/xml-c14n11"/>'
            . '</ds:Transforms>'
            . '<ds:DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256"/>'
            . '<ds:DigestValue>' . $invoiceHash . '</ds:DigestValue>'
            . '</ds:Reference>'
            . '<ds:Reference Type="http://www.w3.org/2000/09/xmldsig#SignatureProperties" URI="#xadesSignedProperties">'
            . '<ds:DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256"/>'
            . '<ds:DigestValue>' . $signedPropsHash . '</ds:DigestValue>'
            . '</ds:Reference>'
            . '</ds:SignedInfo>'
            . '<ds:SignatureValue>' . $digitalSignature . '</ds:SignatureValue>'
            . '<ds:KeyInfo>'
            . '<ds:X509Data>'
            . '<ds:X509Certificate>' . $certificateBase64 . '</ds:X509Certificate>'
            . '</ds:X509Data>'
            . '</ds:KeyInfo>'
            . '<ds:Object>'
            . '<xades:QualifyingProperties xmlns:xades="http://uri.etsi.org/01903/v1.3.2#" Target="signature">'
            . $signedPropsEmbed
            . '</xades:QualifyingProperties>'
            . '</ds:Object>'
            . '</ds:Signature>';
    }

    private function injectSignature(string $xml, string $signatureXml): string
    {
        $doc = new DOMDocument();
        $doc->loadXML($xml);
        $xpath = new DOMXPath($doc);
        $xpath->registerNamespace('ext', 'urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2');
        $xpath->registerNamespace('sig', 'urn:oasis:names:specification:ubl:schema:xsd:CommonSignatureComponents-2');
        $xpath->registerNamespace('sac', 'urn:oasis:names:specification:ubl:schema:xsd:SignatureAggregateComponents-2');
        $xpath->registerNamespace('sbc', 'urn:oasis:names:specification:ubl:schema:xsd:SignatureBasicComponents-2');

        // Find the sac:SignatureInformation element
        $sigInfoNodes = $xpath->query('//sac:SignatureInformation');

        if ($sigInfoNodes->length > 0) {
            $sigInfo = $sigInfoNodes->item(0);

            // Parse the signature XML fragment
            $sigDoc = new DOMDocument();
            $sigDoc->loadXML($signatureXml);
            $importedSig = $doc->importNode($sigDoc->documentElement, true);

            $sigInfo->appendChild($importedSig);
        }

        return $doc->saveXML();
    }

    private function injectQrCode(string $xml, string $qrTlvBase64): string
    {
        $doc = new DOMDocument();
        $doc->loadXML($xml);
        $xpath = new DOMXPath($doc);
        $xpath->registerNamespace('cac', 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
        $xpath->registerNamespace('cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');

        // Check if QR AdditionalDocumentReference exists, if not create it
        $qrNodes = $xpath->query("//cac:AdditionalDocumentReference[cbc:ID='QR']");

        $nsCAC = 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2';
        $nsCBC = 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2';

        if ($qrNodes->length > 0) {
            // Update existing
            $qrNode = $qrNodes->item(0);
            $embedNodes = $xpath->query('.//cbc:EmbeddedDocumentBinaryObject', $qrNode);
            if ($embedNodes->length > 0) {
                $embedNodes->item(0)->textContent = $qrTlvBase64;
            }
        } else {
            // Create QR reference before cac:Signature
            $sigNodes = $xpath->query('//cac:Signature');
            $qrRef = $doc->createElementNS($nsCAC, 'cac:AdditionalDocumentReference');
            $qrId = $doc->createElementNS($nsCBC, 'cbc:ID', 'QR');
            $qrRef->appendChild($qrId);

            $attachment = $doc->createElementNS($nsCAC, 'cac:Attachment');
            $embedded = $doc->createElementNS($nsCBC, 'cbc:EmbeddedDocumentBinaryObject', $qrTlvBase64);
            $embedded->setAttribute('mimeCode', 'text/plain');
            $attachment->appendChild($embedded);
            $qrRef->appendChild($attachment);

            if ($sigNodes->length > 0) {
                $sigNodes->item(0)->parentNode->insertBefore($qrRef, $sigNodes->item(0));
            } else {
                $doc->documentElement->appendChild($qrRef);
            }
        }

        return $doc->saveXML();
    }

    private function buildQrTlv9Tags(
        string $xml,
        string $invoiceHash,
        string $digitalSignature,
        string $publicKeyDer,
        string $certSignatureDer
    ): string {
        $doc = new DOMDocument();
        $doc->loadXML($xml);
        $xpath = new DOMXPath($doc);
        $xpath->registerNamespace('cac', 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
        $xpath->registerNamespace('cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');

        // Extract values from XML
        $sellerName = $this->xpathValue($xpath, '//cac:AccountingSupplierParty//cac:PartyLegalEntity/cbc:RegistrationName');
        $vatNumber = $this->xpathValue($xpath, '//cac:AccountingSupplierParty//cac:PartyTaxScheme/cbc:CompanyID');
        $issueDate = $this->xpathValue($xpath, '//cbc:IssueDate');
        $issueTime = $this->xpathValue($xpath, '//cbc:IssueTime');
        $dateTime = $issueDate . 'T' . $issueTime . 'Z';

        $totalNodes = $xpath->query('//cac:LegalMonetaryTotal/cbc:TaxInclusiveAmount');
        $total = $totalNodes->length > 0 ? $totalNodes->item(0)->textContent : '0.00';

        $taxNodes = $xpath->query('//cac:TaxTotal/cbc:TaxAmount');
        $tax = $taxNodes->length > 0 ? $taxNodes->item(0)->textContent : '0.00';

        $tlv = '';
        // Tags 1-6: UTF-8 strings
        $tlv .= $this->encodeTlv(1, $sellerName);
        $tlv .= $this->encodeTlv(2, $vatNumber);
        $tlv .= $this->encodeTlv(3, $dateTime);
        $tlv .= $this->encodeTlv(4, $total);
        $tlv .= $this->encodeTlv(5, $tax);
        $tlv .= $this->encodeTlv(6, $invoiceHash);
        // Tags 7-9: raw binary
        $tlv .= $this->encodeTlvBinary(7, base64_decode($digitalSignature));
        $tlv .= $this->encodeTlvBinary(8, $publicKeyDer);
        $tlv .= $this->encodeTlvBinary(9, $certSignatureDer);

        return base64_encode($tlv);
    }

    private function xpathValue(DOMXPath $xpath, string $query): string
    {
        $nodes = $xpath->query($query);
        return $nodes->length > 0 ? $nodes->item(0)->textContent : '';
    }

    private function encodeTlv(int $tag, string $value): string
    {
        $len = strlen($value);
        if ($len > 255) {
            // Use 2-byte length for values > 255
            return chr($tag) . chr(0x82) . pack('n', $len) . $value;
        }
        return chr($tag) . chr($len) . $value;
    }

    private function encodeTlvBinary(int $tag, string $value): string
    {
        $len = strlen($value);
        if ($len > 255) {
            return chr($tag) . chr(0x82) . pack('n', $len) . $value;
        }
        return chr($tag) . chr($len) . $value;
    }
}
