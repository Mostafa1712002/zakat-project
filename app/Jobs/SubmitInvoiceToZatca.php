<?php

namespace App\Jobs;

use App\Domain\Sales\Models\Invoice;
use App\Models\Setting;
use App\Services\ZatcaApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * SubmitInvoiceToZatca — async clearance/reporting submission.
 *
 * Runs on the `zatca` queue. Constructed with the invoice ID (not the model)
 * so we always read fresh state on retry.
 *
 * Retries: 3 attempts with exponential backoff (60s, 5min, 15min).
 *
 * Reference: .kiro/specs/ammrk-platform/tasks.md (Task 5.6)
 */
class SubmitInvoiceToZatca implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public function __construct(public int $invoiceId)
    {
        $this->onQueue('zatca');
    }

    /** @return array<int, int> */
    public function backoff(): array
    {
        return [60, 300, 900];
    }

    public function handle(ZatcaApiService $api): void
    {
        $invoice = Invoice::find($this->invoiceId);
        if (! $invoice) {
            Log::warning("ZATCA submit: invoice {$this->invoiceId} not found.");
            return;
        }

        if (! $invoice->signed_xml || ! $invoice->invoice_hash || ! $invoice->uuid) {
            Log::warning("ZATCA submit: invoice {$invoice->invoice_number} missing signed payload — skipping.");
            $invoice->update([
                'zatca_status' => Invoice::ZATCA_FAILED,
                'zatca_warnings' => [['code' => 'NO_PAYLOAD', 'message' => 'Signed XML, hash or UUID missing.']],
            ]);
            return;
        }

        $environment = (string) Setting::get('zatca_environment', 'production');
        $certBase64 = (string) (env('ZATCA_CERT_BASE64') ?: Setting::get('zatca_cert_base64', ''));
        $secret = (string) (env('ZATCA_SECRET') ?: Setting::get('zatca_secret', ''));

        try {
            $response = $api->clearInvoice(
                $invoice->signed_xml,
                $invoice->uuid,
                $invoice->invoice_hash,
                $environment,
                $certBase64,
                $secret,
            );

            // ZatcaApiService returns ['error' => true, ...] on non-2xx
            if (isset($response['error']) && $response['error'] === true) {
                $invoice->update([
                    'zatca_status' => Invoice::ZATCA_FAILED,
                    'zatca_warnings' => [[
                        'code' => 'API_' . ($response['status_code'] ?? 'ERROR'),
                        'message' => $response['message'] ?? ($response['body'] ?? 'ZATCA rejected the invoice'),
                    ]],
                    'zatca_submitted_at' => now(),
                ]);

                Log::error("ZATCA rejected invoice {$invoice->invoice_number}", [
                    'status' => $response['status_code'] ?? null,
                    'body' => $response['body'] ?? null,
                ]);

                throw new \RuntimeException("ZATCA returned non-success status {$response['status_code']}: {$response['body']}");
            }

            $invoice->update([
                'zatca_status' => Invoice::ZATCA_CLEARED,
                'zatca_uuid' => $response['uuid'] ?? $invoice->uuid,
                'zatca_warnings' => $response['warnings'] ?? null,
                'zatca_submitted_at' => now(),
            ]);

            Log::info("ZATCA cleared invoice {$invoice->invoice_number}");
        } catch (Throwable $e) {
            $invoice->update([
                'zatca_status' => Invoice::ZATCA_FAILED,
                'zatca_warnings' => [['code' => 'API_ERROR', 'message' => $e->getMessage()]],
                'zatca_submitted_at' => now(),
            ]);

            Log::error("ZATCA submission failed for invoice {$invoice->invoice_number}", [
                'error' => $e->getMessage(),
            ]);

            // Re-throw so the queue worker honours the retry policy.
            throw $e;
        }
    }
}
