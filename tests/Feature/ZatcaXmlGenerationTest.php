<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\InventoryLevel;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\ZatcaXmlService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ZatcaXmlGenerationTest extends TestCase
{
    use RefreshDatabase;

    private static int $saleCounter = 0;

    protected function setUp(): void
    {
        parent::setUp();
        self::$saleCounter = 0;
    }

    private function createConfirmedSale(?string $customerTaxNumber = null, ?string $noteType = null): Sale
    {
        self::$saleCounter++;
        $suffix = self::$saleCounter;

        $branch = Branch::create([
            'name' => "Main Branch {$suffix}", 'code' => "MAIN{$suffix}",
            'is_active' => true, 'is_main' => true,
        ]);
        $warehouse = Warehouse::create([
            'name' => "Main Warehouse {$suffix}", 'code' => "WH-{$suffix}",
            'branch_id' => $branch->id, 'is_active' => true,
        ]);
        $customer = Customer::create([
            'name' => 'Test Customer', 'code' => "CUST-{$suffix}",
            'tax_number' => $customerTaxNumber, 'type' => 'retail',
            'price_tier' => 'retail', 'branch_id' => $branch->id, 'is_active' => true,
        ]);
        $product = Product::create([
            'name' => 'Test Product', 'sku' => "SKU-{$suffix}",
            'cost_price' => 50, 'selling_price' => 100,
            'tax_rate' => 15, 'is_taxable' => true,
            'is_active' => true, 'track_inventory' => true,
        ]);
        InventoryLevel::create([
            'product_id' => $product->id, 'warehouse_id' => $warehouse->id,
            'quantity' => 100, 'reserved_quantity' => 0,
        ]);
        $user = User::factory()->create(['is_super_admin' => true]);

        $sale = Sale::create([
            'invoice_number' => "INV-{$suffix}",
            'customer_id' => $customer->id,
            'branch_id' => $branch->id,
            'warehouse_id' => $warehouse->id,
            'user_id' => $user->id,
            'invoice_date' => '2026-04-11',
            'payment_type' => 'cash',
            'status' => Sale::STATUS_CONFIRMED,
            'payment_status' => Sale::PAYMENT_STATUS_UNPAID,
            'discount_type' => 'fixed', 'discount_value' => 0,
            'shipping_amount' => 0, 'paid_amount' => 0, 'remaining_amount' => 0,
            'zatca_uuid' => sprintf('e1a2b3c4-d5e6-f7a8-b9c0-d1e2f3a4b%03d', $suffix),
            'zatca_invoice_type' => $noteType ? Sale::ZATCA_INVOICE_STANDARD : ($customerTaxNumber ? Sale::ZATCA_INVOICE_STANDARD : Sale::ZATCA_INVOICE_SIMPLIFIED),
            'zatca_issued_at' => '2026-04-11 10:00:00',
            'zatca_invoice_counter' => 1,
            'zatca_previous_invoice_hash' => base64_encode(hash('sha256', '0', true)),
            'zatca_note_type' => $noteType,
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'product_name' => 'Test Product',
            'product_sku' => 'SKU-1',
            'quantity' => 2, 'unit_price' => 100, 'cost_price' => 50,
            'discount_amount' => 0, 'tax_rate' => 15,
            'tax_amount' => 30, 'subtotal' => 200, 'total' => 230,
        ]);

        $sale->load('items', 'customer');
        $sale->calculateTotals();
        $sale->save();

        return $sale;
    }

    private function getSellerData(): array
    {
        return [
            'company_name' => 'Test Company',
            'tax_number' => '300000000000003',
            'address' => 'Riyadh Front',
            'city' => 'Riyadh',
            'postal_code' => '13311',
            'country' => 'SA',
            'zatca_building_number' => '1234',
            'zatca_additional_number' => '4321',
            'zatca_district' => 'Al Olaya',
            'zatca_country_code' => 'SA',
        ];
    }

    public function test_generates_valid_xml_for_simplified_invoice(): void
    {
        $sale = $this->createConfirmedSale(customerTaxNumber: null);
        $service = new ZatcaXmlService();
        $xml = $service->generate($sale, $this->getSellerData());

        $this->assertNotEmpty($xml);
        $doc = new \DOMDocument();
        $this->assertTrue($doc->loadXML($xml));
        $this->assertSame('Invoice', $doc->documentElement->localName);
        $this->assertStringContainsString('urn:oasis:names:specification:ubl:schema:xsd:Invoice-2', $xml);
        $this->assertStringContainsString('<cbc:InvoiceTypeCode', $xml);
        $this->assertStringContainsString('e1a2b3c4-d5e6-f7a8-b9c0-d1e2f3a4b', $xml);
        $this->assertStringContainsString('300000000000003', $xml);
    }

    public function test_generates_valid_xml_for_standard_invoice(): void
    {
        $sale = $this->createConfirmedSale(customerTaxNumber: '310000000000003');
        $service = new ZatcaXmlService();
        $xml = $service->generate($sale, $this->getSellerData());

        $this->assertNotEmpty($xml);
        $doc = new \DOMDocument();
        $this->assertTrue($doc->loadXML($xml));
        $this->assertStringContainsString('310000000000003', $xml);
    }

    public function test_generates_valid_xml_for_credit_note(): void
    {
        $originalSale = $this->createConfirmedSale(customerTaxNumber: '310000000000003');
        $creditNote = $this->createConfirmedSale(customerTaxNumber: '310000000000003', noteType: Sale::ZATCA_NOTE_CREDIT);
        $creditNote->update([
            'zatca_original_sale_id' => $originalSale->id,
            'zatca_note_reason' => 'Return of goods',
        ]);
        $creditNote->refresh();
        $creditNote->load('originalSale');

        $service = new ZatcaXmlService();
        $xml = $service->generate($creditNote, $this->getSellerData());

        $this->assertStringContainsString('381', $xml);
        $this->assertStringContainsString($originalSale->invoice_number, $xml);
    }

    public function test_xml_contains_line_items(): void
    {
        $sale = $this->createConfirmedSale();
        $service = new ZatcaXmlService();
        $xml = $service->generate($sale, $this->getSellerData());

        $this->assertStringContainsString('InvoiceLine', $xml);
        $this->assertStringContainsString('Test Product', $xml);
    }

    public function test_xml_contains_tax_totals(): void
    {
        $sale = $this->createConfirmedSale();
        $service = new ZatcaXmlService();
        $xml = $service->generate($sale, $this->getSellerData());

        $this->assertStringContainsString('TaxTotal', $xml);
        $this->assertStringContainsString('TaxSubtotal', $xml);
    }
}
