<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ZatcaApiService
{
    public function clearInvoice(string $xml, string $uuid, string $hash, string $environment, string $certificate, string $secret): array
    {
        $endpoint = $this->getEndpoint($environment, 'clearance');
        return $this->sendRequest($endpoint, $xml, $uuid, $hash, $certificate, $secret, [
            'Clearance-Status' => '1',
        ]);
    }

    public function reportInvoice(string $xml, string $uuid, string $hash, string $environment, string $certificate, string $secret): array
    {
        $endpoint = $this->getEndpoint($environment, 'reporting');
        return $this->sendRequest($endpoint, $xml, $uuid, $hash, $certificate, $secret);
    }

    public function complianceCheck(string $xml, string $uuid, string $hash, string $environment, string $certificate, string $secret): array
    {
        $endpoint = $this->getEndpoint($environment, 'compliance');
        return $this->sendRequest($endpoint, $xml, $uuid, $hash, $certificate, $secret);
    }

    private function sendRequest(string $endpoint, string $xml, string $uuid, string $hash, string $certificate, string $secret, array $extraHeaders = []): array
    {
        $body = [
            'invoiceHash' => $hash,
            'uuid' => $uuid,
            'invoice' => base64_encode($xml),
        ];

        $auth = base64_encode("{$certificate}:{$secret}");

        $headers = array_merge([
            'Authorization' => "Basic {$auth}",
            'Accept-Version' => 'V2',
            'Accept-Language' => 'en',
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ], $extraHeaders);

        try {
            $response = Http::withHeaders($headers)
                ->timeout(config('zatca.timeout', 30))
                ->post($endpoint, $body);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('ZATCA API non-success response', [
                'status' => $response->status(),
                'body' => $response->body(),
                'uuid' => $uuid,
            ]);

            return [
                'error' => true,
                'status_code' => $response->status(),
                'message' => $response->json('message', 'Unknown error'),
                'body' => $response->body(),
            ];
        } catch (\Throwable $e) {
            Log::error('ZATCA API request failed', [
                'uuid' => $uuid,
                'exception' => $e->getMessage(),
            ]);

            return [
                'error' => true,
                'status_code' => 0,
                'message' => $e->getMessage(),
            ];
        }
    }

    private function getEndpoint(string $environment, string $type): string
    {
        $config = config("zatca.endpoints.{$environment}", config('zatca.endpoints.sandbox'));
        return $config['base_url'] . $config[$type];
    }
}
