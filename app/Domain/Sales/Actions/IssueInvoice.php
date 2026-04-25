<?php

namespace App\Domain\Sales\Actions;

use App\Domain\Customer\Validators\ZatcaCustomerValidator;
use App\Domain\Sales\Events\InvoiceIssued;
use App\Domain\Sales\Models\Invoice;
use App\Jobs\SubmitInvoiceToZatca;
use App\Models\Setting;
use App\Services\ZatcaHashService;
use App\Services\ZatcaQrService;
use App\Services\ZatcaSigningService;
use App\Services\ZatcaXmlService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use LogicException;
use RuntimeException;
use Throwable;

/**
 * IssueInvoice — finalises a draft invoice and produces ZATCA artefacts.
 *
 * Pipeline (inside DB transaction):
 *   1) Validate the customer is ZATCA-ready (re-check post-conversion).
 *   2) Allocate UUID + ICV (sequential count of issued invoices + 1).
 *   3) Resolve PIH from previous issued invoice (or base64(sha256('0'))).
 *   4) Build UBL 2.1 XML via ZatcaXmlService (legacy service, adapted to
 *      the new Invoice surface via accessor compat layer).
 *   5) Hash the canonical XML, sign it, embed signature + QR.
 *   6) Persist signed_xml, qr_code, invoice_hash, uuid, icv, pih.
 *   7) Flip status → issued, set issued_at = now.
 *   8) Dispatch the queued submission job.
 *
 * Failure policy: in dev, ZATCA cert/private key are not provisioned. To
 * avoid blocking the rest of the pipeline (and to let smoke tests run), the
 * XML/sign block is wrapped in try/catch; if it throws, the invoice is still
 * issued (status='issued'), zatca_status='failed', and the warnings field
 * captures the error for later retry by the operator.
 *
 * Reference: .kiro/specs/ammrk-platform/tasks.md (Task 5.5)
 */
class IssueInvoice
{
    public function __construct(
        protected ZatcaXmlService $xmlService,
        protected ZatcaHashService $hashService,
        protected ZatcaQrService $qrService,
    ) {
    }

    public function execute(Invoice $invoice): Invoice
    {
        if ($invoice->status !== Invoice::STATUS_DRAFT) {
            throw new LogicException(
                "Invoice {$invoice->invoice_number} is not in draft status (status={$invoice->status})."
            );
        }

        $invoice->loadMissing('customer', 'items.service');

        $check = ZatcaCustomerValidator::isReadyForInvoicing($invoice->customer);
        if (! $check['ok']) {
            throw new RuntimeException(
                'Customer is not ZATCA-ready: ' . implode(' ', $check['errors'])
            );
        }

        return DB::transaction(function () use ($invoice) {
            $uuid = (string) Str::uuid();
            $icv = (int) Invoice::whereNotNull('issued_at')->count() + 1;

            // Previous Invoice Hash: chain to the last issued invoice's hash,
            // or fall back to base64(sha256('0')) for the first one.
            $previousHash = Invoice::whereNotNull('invoice_hash')
                ->orderByDesc('icv')
                ->value('invoice_hash');
            $pih = $this->hashService->getPreviousInvoiceHash($previousHash);

            $invoice->forceFill([
                'uuid' => $uuid,
                // Legacy XML service reads `$sale->zatca_uuid`; mirror our
                // local UUID until ZATCA returns its own one in the clear
                // response (which then overwrites this value).
                'zatca_uuid' => $uuid,
                'icv' => $icv,
                'pih' => $pih,
                'status' => Invoice::STATUS_ISSUED,
                'issued_at' => now(),
            ])->save();

            // Reload so accessors (zatca_*) see the freshly-persisted values.
            $invoice->refresh()->loadMissing('customer', 'items.service');

            $signedXml = null;
            $invoiceHash = null;
            $qrBase64 = null;
            $zatcaStatus = Invoice::ZATCA_PENDING;
            $warnings = null;

            try {
                $seller = $this->resolveSellerInfo();
                $xml = $this->xmlService->generate($invoice, $seller);
                $invoiceHash = $this->hashService->generateInvoiceHash($xml);

                $signing = $this->makeSigningService();
                $signed = $signing->sign($xml, $invoiceHash);

                $signedXml = $signed['signed_xml'];
                $qrTlv = $signed['qr_tlv'] ?? null;
                if ($qrTlv) {
                    $qrBase64 = $this->qrService->generateBase64Image($qrTlv);
                }
            } catch (Throwable $e) {
                Log::warning('ZATCA signing failed for invoice ' . $invoice->invoice_number, [
                    'error' => $e->getMessage(),
                ]);
                $zatcaStatus = Invoice::ZATCA_FAILED;
                $warnings = [['code' => 'SIGNING_FAILED', 'message' => $e->getMessage()]];
            }

            $invoice->forceFill([
                'signed_xml' => $signedXml,
                'invoice_hash' => $invoiceHash,
                'qr_code' => $qrBase64,
                'zatca_status' => $zatcaStatus,
                'zatca_warnings' => $warnings,
            ])->save();

            // Only queue submission when we actually have a signed payload.
            if ($zatcaStatus === Invoice::ZATCA_PENDING && $signedXml !== null) {
                SubmitInvoiceToZatca::dispatch($invoice->id);
            }

            $fresh = $invoice->refresh();
            InvoiceIssued::dispatch($fresh);

            return $fresh;
        });
    }

    /**
     * Build the seller info array expected by ZatcaXmlService.
     * Pulled from settings with sensible-but-empty defaults so dev still
     * runs even without real ZATCA credentials.
     *
     * @return array<string, string>
     */
    protected function resolveSellerInfo(): array
    {
        return [
            'company_name' => (string) Setting::get('zatca_company_name', config('app.name', 'AMMRK')),
            'tax_number' => (string) Setting::get('zatca_tax_number', ''),
            'address' => (string) Setting::get('zatca_address', ''),
            'city' => (string) Setting::get('zatca_city', ''),
            'postal_code' => (string) Setting::get('zatca_postal_code', '00000'),
            'zatca_building_number' => (string) Setting::get('zatca_building_number', ''),
            'zatca_additional_number' => (string) Setting::get('zatca_additional_number', ''),
            'zatca_district' => (string) Setting::get('zatca_district', ''),
            'zatca_country_code' => (string) Setting::get('zatca_country_code', 'SA'),
        ];
    }

    /**
     * Construct the signing service from the configured key/cert. Throws if
     * the cert files are missing — caller's try/catch flips the invoice to
     * zatca_status=failed in that case.
     */
    protected function makeSigningService(): ZatcaSigningService
    {
        $keyPath = (string) (env('ZATCA_PRIVATE_KEY_PATH') ?: storage_path('zatca/private.pem'));
        $certBase64 = (string) (env('ZATCA_CERT_BASE64') ?: Setting::get('zatca_cert_base64', ''));

        if ($certBase64 === '' || ! is_file($keyPath)) {
            throw new RuntimeException('ZATCA signing credentials are not configured.');
        }

        return new ZatcaSigningService($keyPath, $certBase64);
    }
}
