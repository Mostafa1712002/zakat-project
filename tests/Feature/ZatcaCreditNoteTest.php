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
use Tests\TestCase;

class ZatcaCreditNoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_credit_note_for_confirmed_zatca_sale(): void
    {
        $user = User::factory()->create(['is_super_admin' => true]);
        $sale = $this->createConfirmedSaleWithZatca($user);

        $response = $this->actingAs($user)->post(route('sales.credit-note.store', $sale), [
            'reason' => 'Return of goods',
            'items' => [
                [
                    'sale_item_id' => $sale->items->first()->id,
                    'quantity' => 1,
                ],
            ],
        ]);

        $response->assertRedirect();

        $creditNote = Sale::where('zatca_note_type', Sale::ZATCA_NOTE_CREDIT)->first();
        $this->assertNotNull($creditNote);
        $this->assertSame($sale->id, (int) $creditNote->zatca_original_sale_id);
        $this->assertSame('Return of goods', $creditNote->zatca_note_reason);
        $this->assertSame(Sale::ZATCA_NOTE_CREDIT, $creditNote->zatca_note_type);
    }

    public function test_cannot_create_credit_note_for_non_zatca_sale(): void
    {
        $user = User::factory()->create(['is_super_admin' => true]);
        $sale = $this->createDraftSale($user);

        $response = $this->actingAs($user)->get(route('sales.credit-note.create', $sale));

        $response->assertRedirect();
    }

    protected function createConfirmedSaleWithZatca(User $user): Sale
    {
        $branch = Branch::create([
            'name' => 'Main Branch', 'code' => 'MAIN',
            'is_active' => true, 'is_main' => true,
        ]);
        $warehouse = Warehouse::create([
            'name' => 'Main Warehouse', 'code' => 'WH-1',
            'branch_id' => $branch->id, 'is_active' => true,
        ]);
        $customer = Customer::create([
            'name' => 'Customer One', 'code' => 'CUST-1',
            'tax_number' => '300000000000003', 'type' => 'retail',
            'price_tier' => 'retail', 'branch_id' => $branch->id, 'is_active' => true,
        ]);
        $product = Product::create([
            'name' => 'Product One', 'sku' => 'SKU-1',
            'cost_price' => 50, 'selling_price' => 100,
            'tax_rate' => 15, 'is_taxable' => true,
            'is_active' => true, 'track_inventory' => true,
        ]);
        InventoryLevel::create([
            'product_id' => $product->id, 'warehouse_id' => $warehouse->id,
            'quantity' => 100, 'reserved_quantity' => 0,
        ]);

        $sale = Sale::create([
            'invoice_number' => 'INV-001',
            'customer_id' => $customer->id,
            'branch_id' => $branch->id,
            'warehouse_id' => $warehouse->id,
            'user_id' => $user->id,
            'invoice_date' => now()->toDateString(),
            'payment_type' => 'cash',
            'status' => Sale::STATUS_CONFIRMED,
            'payment_status' => Sale::PAYMENT_STATUS_UNPAID,
            'discount_type' => 'fixed', 'discount_value' => 0,
            'shipping_amount' => 0, 'paid_amount' => 0, 'remaining_amount' => 0,
            'zatca_uuid' => 'test-uuid-original',
            'zatca_invoice_type' => Sale::ZATCA_INVOICE_STANDARD,
            'zatca_status' => Sale::ZATCA_STATUS_CLEARED,
            'zatca_issued_at' => now(),
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_sku' => $product->sku,
            'quantity' => 5, 'unit_price' => 100, 'cost_price' => 50,
            'discount_amount' => 0, 'tax_rate' => 15,
            'tax_amount' => 75, 'subtotal' => 500, 'total' => 575,
        ]);

        $sale->load('items');
        $sale->calculateTotals();
        $sale->save();

        return $sale;
    }

    protected function createDraftSale(User $user): Sale
    {
        $branch = Branch::create([
            'name' => 'Main Branch', 'code' => 'BR-2',
            'is_active' => true, 'is_main' => true,
        ]);
        $warehouse = Warehouse::create([
            'name' => 'Main Warehouse', 'code' => 'WH-2',
            'branch_id' => $branch->id, 'is_active' => true,
        ]);

        return Sale::create([
            'invoice_number' => 'INV-002',
            'branch_id' => $branch->id,
            'warehouse_id' => $warehouse->id,
            'user_id' => $user->id,
            'invoice_date' => now()->toDateString(),
            'payment_type' => 'cash',
            'status' => Sale::STATUS_DRAFT,
            'payment_status' => Sale::PAYMENT_STATUS_UNPAID,
            'discount_type' => 'fixed', 'discount_value' => 0,
            'shipping_amount' => 0, 'paid_amount' => 0, 'remaining_amount' => 0,
        ]);
    }
}
