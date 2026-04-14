<?php

namespace App\Services;

use DOMDocument;
use DOMXPath;

class ZatcaSigningService
{
    private string $privateKeyPem;
    private string $certificateBase64;
    private string $certPem;
    private string $certDer;
    private string $certBody;

    public function __construct(string $privateKeyPath, string $certificateBase64)
    {
        $this->privateKeyPem = file_get_contents($privateKeyPath);
        $this->certificateBase64 = $certificateBase64;

        // binarySecurityToken = base64(base64(DER))
        $this->certBody = base64_decode($certificateBase64);
        $this->certDer = base64_decode($this->certBody);

        // Use phpseclib PEM format (CRLF line endings) to match ZATCA's expected hash
        $x509 = new \phpseclib3\File\X509();
        $tempPem = "-----BEGIN CERTIFICATE-----\n" . chunk_split($this->certBody, 64, "\n") . "-----END CERTIFICATE-----";
        $x509->loadX509($tempPem);
        $this->certPem = $x509->saveX509($x509->getCurrentCert());
    }

    public function sign(string $xml, string $invoiceHash): array
    {
        $certInfo = $this->extractCertificateInfo();

        // Sign the raw binary hash bytes (32 bytes from SHA256 of canonical XML)
        // phpseclib3 default hash is sha256, so sign(hashBytes) = ECDSA(SHA256(hashBytes))
        // This matches SallaApp: $privateKey->sign($invoiceHashBinary)
        $hashBytes = base64_decode($invoiceHash);
        $ecPrivateKey = \phpseclib3\Crypt\EC::loadPrivateKey($this->privateKeyPem);
        $signatureRaw = $ecPrivateKey->sign($hashBytes);
        $digitalSignature = base64_encode($signatureRaw);

        // Certificate hash: base64(hex(sha256(full_pem_string))) — matches SallaApp getHash()
        $signingTime = gmdate('Y-m-d\TH:i:s\Z');
        $certHash = base64_encode(hash('sha256', $this->certPem));

        $signedPropsForSigning = $this->buildSignedPropertiesForSigning(
            $signingTime, $certHash, $certInfo['issuer'], $certInfo['serialNumber']
        );
        $signedPropsHash = base64_encode(hash('sha256', $signedPropsForSigning));

        $signedPropsEmbed = $this->buildSignedPropertiesForEmbedding(
            $signingTime, $certHash, $certInfo['issuer'], $certInfo['serialNumber']
        );

        $signatureXml = $this->buildSignatureXml(
            $invoiceHash, $signedPropsHash, $digitalSignature,
            $this->certBody, $signedPropsEmbed
        );

        $signedXml = $this->injectSignature($xml, $signatureXml);

        $qrTlv = $this->buildQrTlv9Tags(
            $xml, $invoiceHash, $digitalSignature,
            $certInfo['publicKey'], $certInfo['certSignature']
        );

        $finalXml = $this->injectQrCode($signedXml, $qrTlv);

        return [
            'signed_xml' => $finalXml,
            'digital_signature' => $digitalSignature,
            'qr_tlv' => $qrTlv,
            'invoice_hash' => $invoiceHash,
        ];
    }

    private function extractCertificateInfo(): array
    {
        $certResource = openssl_x509_read($this->certPem);
        $certData = openssl_x509_parse($certResource);

        // Serial number must be decimal for X509SerialNumber
        $serialRaw = $certData['serialNumber'] ?? '0';
        if (str_starts_with($serialRaw, '0x') || str_starts_with($serialRaw, '0X')) {
            // Convert hex to decimal using BC math for large numbers
            $hex = substr($serialRaw, 2);
            $serialNumber = '0';
            for ($i = 0; $i < strlen($hex); $i++) {
                $serialNumber = bcmul($serialNumber, '16');
                $serialNumber = bcadd($serialNumber, (string) hexdec($hex[$i]));
            }
        } else {
            $serialNumber = $serialRaw;
        }

        // Issuer may have array values (DC can appear multiple times)
        $issuerParts2 = [];
        foreach ($certData['issuer'] as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $v) {
                    $issuerParts2[] = "{$key}={$v}";
                }
            } else {
                $issuerParts2[] = "{$key}={$value}";
            }
        }
        $issuer = implode(', ', array_reverse($issuerParts2));

        $pubKey = openssl_pkey_get_public($certResource);
        $pubKeyDetails = openssl_pkey_get_details($pubKey);
        $publicKeyDer = $this->pemToDer($pubKeyDetails['key']);

        // Extract cert signature using phpseclib3 (matches SallaApp)
        $x509 = new \phpseclib3\File\X509();
        $x509->loadX509($this->certPem);
        $certSigRaw = $x509->getCurrentCert()['signature'];
        $certSignature = substr($certSigRaw, 1); // strip unused bits byte

        // Public key via phpseclib3 (matches SallaApp getPlainPublicKey)
        $plainPubKeyB64 = str_replace(
            ["-----BEGIN PUBLIC KEY-----\r\n", "\r\n-----END PUBLIC KEY-----", "\r\n",
             "-----BEGIN PUBLIC KEY-----\n", "\n-----END PUBLIC KEY-----", "\n"],
            '', $x509->getPublicKey()->toString('PKCS8')
        );
        $publicKeyDer = base64_decode($plainPubKeyB64);

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
        $offset = 0;
        $offset = $this->skipAsn1TagAndLength($certDer, $offset);
        $offset = $this->skipAsn1Element($certDer, $offset);
        $offset = $this->skipAsn1Element($certDer, $offset);

        // BIT STRING tag
        $offset++; // skip tag byte
        $length = $this->readAsn1Length($certDer, $offset);
        $offset = $length['offset'];
        $len = $length['length'];

        // Skip unused bits byte (0x00)
        $offset++;
        $len--;

        return substr($certDer, $offset, $len);
    }

    private function skipAsn1TagAndLength(string $data, int $offset): int
    {
        $offset++;
        $lenByte = ord($data[$offset]);
        $offset++;
        if ($lenByte > 0x80) {
            $offset += ($lenByte - 0x80);
        }
        return $offset;
    }

    private function skipAsn1Element(string $data, int $offset): int
    {
        $offset++;
        $length = $this->readAsn1Length($data, $offset);
        return $length['offset'] + $length['length'];
    }

    private function readAsn1Length(string $data, int $offset): array
    {
        $lenByte = ord($data[$offset]);
        $offset++;

        if ($lenByte <= 0x7F) {
            return ['offset' => $offset, 'length' => $lenByte];
        }

        $numBytes = $lenByte - 0x80;
        $length = 0;
        for ($i = 0; $i < $numBytes; $i++) {
            $length = ($length << 8) | ord($data[$offset]);
            $offset++;
        }

        return ['offset' => $offset, 'length' => $length];
    }

    // BUG FIX #2: Signed properties template must match SallaApp format exactly
    // Self-closing DigestMethod tag, proper indentation with newlines
    private function buildSignedPropertiesForSigning(
        string $signingTime, string $certHash, string $issuer, string $serialNumber
    ): string {
        return '<xades:SignedProperties xmlns:xades="http://uri.etsi.org/01903/v1.3.2#" Id="xadesSignedProperties">' . "\n"
            . '                                <xades:SignedSignatureProperties>' . "\n"
            . '                                    <xades:SigningTime>' . $signingTime . '</xades:SigningTime>' . "\n"
            . '                                    <xades:SigningCertificate>' . "\n"
            . '                                        <xades:Cert>' . "\n"
            . '                                            <xades:CertDigest>' . "\n"
            . '                                                <ds:DigestMethod xmlns:ds="http://www.w3.org/2000/09/xmldsig#" Algorithm="http://www.w3.org/2001/04/xmlenc#sha256"/>' . "\n"
            . '                                                <ds:DigestValue xmlns:ds="http://www.w3.org/2000/09/xmldsig#">' . $certHash . '</ds:DigestValue>' . "\n"
            . '                                            </xades:CertDigest>' . "\n"
            . '                                            <xades:IssuerSerial>' . "\n"
            . '                                                <ds:X509IssuerName xmlns:ds="http://www.w3.org/2000/09/xmldsig#">' . $issuer . '</ds:X509IssuerName>' . "\n"
            . '                                                <ds:X509SerialNumber xmlns:ds="http://www.w3.org/2000/09/xmldsig#">' . $serialNumber . '</ds:X509SerialNumber>' . "\n"
            . '                                            </xades:IssuerSerial>' . "\n"
            . '                                        </xades:Cert>' . "\n"
            . '                                    </xades:SigningCertificate>' . "\n"
            . '                                </xades:SignedSignatureProperties>' . "\n"
            . '                            </xades:SignedProperties>';
    }

    private function buildSignedPropertiesForEmbedding(
        string $signingTime, string $certHash, string $issuer, string $serialNumber
    ): string {
        return '<xades:SignedProperties xmlns:xades="http://uri.etsi.org/01903/v1.3.2#" Id="xadesSignedProperties">'
            . '<xades:SignedSignatureProperties>'
            . '<xades:SigningTime>' . $signingTime . '</xades:SigningTime>'
            . '<xades:SigningCertificate>'
            . '<xades:Cert>'
            . '<xades:CertDigest>'
            . '<ds:DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256"/>'
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
        string $invoiceHash, string $signedPropsHash, string $digitalSignature,
        string $certificateBody, string $signedPropsEmbed
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
            . '<ds:X509Certificate>' . $certificateBody . '</ds:X509Certificate>'
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
        $xpath->registerNamespace('sac', 'urn:oasis:names:specification:ubl:schema:xsd:SignatureAggregateComponents-2');

        $sigInfoNodes = $xpath->query('//sac:SignatureInformation');
        if ($sigInfoNodes->length > 0) {
            $sigDoc = new DOMDocument();
            $sigDoc->loadXML($signatureXml);
            $imported = $doc->importNode($sigDoc->documentElement, true);
            $sigInfoNodes->item(0)->appendChild($imported);
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

        $qrNodes = $xpath->query("//cac:AdditionalDocumentReference[cbc:ID='QR']");
        $nsCAC = 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2';
        $nsCBC = 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2';

        if ($qrNodes->length > 0) {
            $embedNodes = $xpath->query('.//cbc:EmbeddedDocumentBinaryObject', $qrNodes->item(0));
            if ($embedNodes->length > 0) {
                $embedNodes->item(0)->textContent = $qrTlvBase64;
            }
        } else {
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
        string $xml, string $invoiceHash, string $digitalSignature,
        string $publicKeyDer, string $certSignatureDer
    ): string {
        $doc = new DOMDocument();
        $doc->loadXML($xml);
        $xpath = new DOMXPath($doc);
        $xpath->registerNamespace('cac', 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
        $xpath->registerNamespace('cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');

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
        $tlv .= $this->encodeTlv(1, $sellerName);
        $tlv .= $this->encodeTlv(2, $vatNumber);
        $tlv .= $this->encodeTlv(3, $dateTime);
        $tlv .= $this->encodeTlv(4, $total);
        $tlv .= $this->encodeTlv(5, $tax);
        $tlv .= $this->encodeTlv(6, $invoiceHash);
        // Tag 7: SallaApp passes the base64 STRING, not raw bytes
        $tlv .= $this->encodeTlv(7, $digitalSignature);
        // Tags 8-9: raw binary DER bytes
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
