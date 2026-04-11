<?php

namespace Tests\Feature;

use App\Services\ZatcaApiService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ZatcaApiIntegrationTest extends TestCase
{
    public function test_clearance_sends_correct_request(): void
    {
        Http::fake([
            '*/invoices/clearance/single' => Http::response([
                'clearanceStatus' => 'CLEARED',
                'clearedInvoice' => base64_encode('<Invoice>cleared</Invoice>'),
                'validationResults' => [
                    'status' => 'PASS',
                    'infoMessages' => [],
                    'warningMessages' => [],
                    'errorMessages' => [],
                ],
            ], 200),
        ]);

        $service = new ZatcaApiService();
        $result = $service->clearInvoice(
            xml: '<Invoice>test</Invoice>',
            uuid: 'test-uuid-123',
            hash: 'test-hash-123',
            environment: 'sandbox',
            certificate: 'test-cert',
            secret: 'test-secret',
        );

        $this->assertSame('CLEARED', $result['clearanceStatus']);
        $this->assertSame('PASS', $result['validationResults']['status']);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'clearance/single')
                && $request->hasHeader('Authorization')
                && $request->hasHeader('Clearance-Status', '1');
        });
    }

    public function test_reporting_sends_correct_request(): void
    {
        Http::fake([
            '*/invoices/reporting/single' => Http::response([
                'reportingStatus' => 'REPORTED',
                'validationResults' => [
                    'status' => 'PASS',
                    'infoMessages' => [],
                    'warningMessages' => [],
                    'errorMessages' => [],
                ],
            ], 200),
        ]);

        $service = new ZatcaApiService();
        $result = $service->reportInvoice(
            xml: '<Invoice>test</Invoice>',
            uuid: 'test-uuid-123',
            hash: 'test-hash-123',
            environment: 'sandbox',
            certificate: 'test-cert',
            secret: 'test-secret',
        );

        $this->assertSame('REPORTED', $result['reportingStatus']);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'reporting/single')
                && $request->hasHeader('Authorization');
        });
    }

    public function test_handles_api_failure_gracefully(): void
    {
        Http::fake([
            '*' => Http::response(['message' => 'Server error'], 500),
        ]);

        $service = new ZatcaApiService();
        $result = $service->clearInvoice(
            xml: '<Invoice>test</Invoice>',
            uuid: 'test-uuid',
            hash: 'test-hash',
            environment: 'sandbox',
            certificate: 'test-cert',
            secret: 'test-secret',
        );

        $this->assertTrue($result['error']);
        $this->assertSame(500, $result['status_code']);
    }
}
