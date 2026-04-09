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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ZatcaSaleComplianceTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirming_b2c_sale_generates_simplified_zatca_metadata(): void
    {
        $user = User::factory()->create(['is_super_admin' => true]);
        $sale = $this->createSale($user, customerTaxNumber: null);
        $this->enableZatca();

        $response = $this->actingAs($user)->post(route('sales.confirm', $sale));

        $response->assertRedirect(route('sales.show', $sale));

        $sale->refresh();

        $this->assertSame(Sale::STATUS_CONFIRMED, $sale->status);
        $this->assertSame(Sale::ZATCA_INVOICE_SIMPLIFIED, $sale->zatca_invoice_type);
        $this->assertSame(Sale::ZATCA_STATUS_PENDING_REPORTING, $sale->zatca_status);
        $this->assertNotNull($sale->zatca_uuid);
        $this->assertNotNull($sale->zatca_issued_at);
        $this->assertNotEmpty($sale->zatca_qr_tlv);
    }

    public function test_confirming_b2b_sale_generates_standard_zatca_metadata(): void
    {
        $user = User::factory()->create(['is_super_admin' => true]);
        $sale = $this->createSale($user, customerTaxNumber: '300000000000003');
        $this->enableZatca();

        $response = $this->actingAs($user)->post(route('sales.confirm', $sale));

        $response->assertRedirect(route('sales.show', $sale));

        $sale->refresh();

        $this->assertSame(Sale::ZATCA_INVOICE_STANDARD, $sale->zatca_invoice_type);
        $this->assertSame(Sale::ZATCA_STATUS_PENDING_CLEARANCE, $sale->zatca_status);
        $this->assertNotEmpty($sale->zatca_qr_tlv);
    }

    public function test_zatca_issued_sale_cannot_be_edited(): void
    {
        $user = User::factory()->create(['is_super_admin' => true]);
        $sale = $this->createSale($user, customerTaxNumber: null);
        $this->enableZatca();

        $this->actingAs($user)->post(route('sales.confirm', $sale));

        $response = $this->actingAs($user)
            ->from(route('sales.show', $sale))
            ->get(route('sales.edit', $sale));

        $response->assertRedirect(route('sales.show', $sale));
        $response->assertSessionHas('error');
    }

    protected function createSale(User $user, ?string $customerTaxNumber): Sale
    {
        $branch = Branch::create([
            'name' => 'Main Branch',
            'code' => 'MAIN',
            'is_active' => true,
            'is_main' => true,
        ]);

        $warehouse = Warehouse::create([
            'name' => 'Main Warehouse',
            'code' => 'WH-1',
            'branch_id' => $branch->id,
            'is_active' => true,
        ]);

        $customer = Customer::create([
            'name' => 'Customer One',
            'code' => 'CUST-1',
            'tax_number' => $customerTaxNumber,
            'type' => 'retail',
            'price_tier' => 'retail',
            'branch_id' => $branch->id,
            'is_active' => true,
        ]);

        $product = Product::create([
            'name' => 'Product One',
            'sku' => 'SKU-1',
            'cost_price' => 50,
            'selling_price' => 100,
            'tax_rate' => 15,
            'is_taxable' => true,
            'is_active' => true,
            'track_inventory' => true,
        ]);

        InventoryLevel::create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 10,
            'reserved_quantity' => 0,
        ]);

        $sale = Sale::create([
            'invoice_number' => 'INV-1001',
            'customer_id' => $customer->id,
            'branch_id' => $branch->id,
            'warehouse_id' => $warehouse->id,
            'user_id' => $user->id,
            'invoice_date' => now()->toDateString(),
            'payment_type' => 'cash',
            'status' => Sale::STATUS_DRAFT,
            'payment_status' => Sale::PAYMENT_STATUS_UNPAID,
            'discount_type' => 'fixed',
            'discount_value' => 0,
            'shipping_amount' => 0,
            'paid_amount' => 0,
            'remaining_amount' => 0,
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_sku' => $product->sku,
            'quantity' => 2,
            'unit_price' => 100,
            'cost_price' => 50,
            'discount_amount' => 0,
            'tax_rate' => 15,
            'tax_amount' => 30,
            'subtotal' => 200,
            'total' => 230,
        ]);

        $sale->load('items');
        $sale->calculateTotals();
        $sale->save();

        return $sale;
    }

    protected function enableZatca(): void
    {
        $now = now();
        $settings = [
            'company_name' => 'Test Company',
            'tax_number' => '300000000000003',
            'address' => 'Riyadh Front',
            'city' => 'Riyadh',
            'postal_code' => '13311',
            'zatca_enabled' => '1',
            'zatca_building_number' => '1234',
            'zatca_district' => 'Al Olaya',
            'zatca_egs_serial' => 'EGS-UNIT-001',
            'zatca_environment' => 'simulation',
        ];

        foreach ($settings as $key => $value) {
            DB::table('settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $value, 'created_at' => $now, 'updated_at' => $now]
            );
        }
    }
}
