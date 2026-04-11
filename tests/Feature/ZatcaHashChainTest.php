<?php

namespace Tests\Feature;

use App\Services\ZatcaHashService;
use Tests\TestCase;

class ZatcaHashChainTest extends TestCase
{
    public function test_first_invoice_uses_base64_zero_hash_as_previous(): void
    {
        $service = new ZatcaHashService();
        $pih = $service->getPreviousInvoiceHash(null);
        $expected = base64_encode(hash('sha256', '0', true));
        $this->assertSame($expected, $pih);
    }

    public function test_hash_is_sha256_base64_of_xml(): void
    {
        $service = new ZatcaHashService();
        $xml = '<Invoice>test</Invoice>';
        $hash = $service->generateInvoiceHash($xml);
        $expected = base64_encode(hash('sha256', $xml, true));
        $this->assertSame($expected, $hash);
    }

    public function test_next_counter_starts_at_one(): void
    {
        $service = new ZatcaHashService();
        $counter = $service->getNextCounter(null);
        $this->assertSame(1, $counter);
    }

    public function test_next_counter_increments_from_last(): void
    {
        $service = new ZatcaHashService();
        $counter = $service->getNextCounter(5);
        $this->assertSame(6, $counter);
    }
}
