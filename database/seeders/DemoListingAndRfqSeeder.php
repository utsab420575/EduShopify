<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Award;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Listing;
use App\Models\ProductDetail;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Rfq;
use App\Models\RfqItem;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoListingAndRfqSeeder extends Seeder
{
    public function run(): void
    {
        $supplierAccount = Account::where('account_number', 'ACC-SUP-001')->first();
        $supplierUser    = User::where('email', 'supplier@edtech.com')->first();

        $supplier2Account = Account::where('account_number', 'ACC-SUP-002')->first();
        $supplier2User    = User::where('email', 'supplier2@furniture.com')->first();

        $buyerAccount = Account::where('account_number', 'ACC-BUY-001')->first();
        $buyerUser    = User::where('email', 'buyer@school.edu')->first();

        $categoryEdTech    = Category::where('slug', 'edtech-hardware')->first();
        $categoryFurniture = Category::where('slug', 'classroom-furniture')->first();
        $unitPc            = Unit::where('symbol', 'pc')->first();
        $brandDell         = Brand::where('slug', 'dell-education')->first();

        if (!$supplierAccount || !$buyerAccount) {
            return;
        }

        // ── 1. Create Demo Listing 1 (Apex EdTech Solutions) ──
        $listing1 = Listing::firstOrCreate(
            ['listing_number' => 'LST-2026-001'],
            [
                'supplier_account_id' => $supplierAccount->id,
                'created_by_user_id'  => $supplierUser->id,
                'listing_type'        => 'product',
                'main_category_id'    => $categoryEdTech?->id,
                'brand_id'            => $brandDell?->id,
                'name'                => 'Dell Latitude 3420 Education Laptop',
                'slug'                => 'dell-latitude-3420-education-laptop',
                'sku'                 => 'DELL-LAT-3420',
                'short_description'   => '14-inch FHD display, Intel i5 11th Gen, 16GB RAM, 512GB SSD. Built for classrooms.',
                'description'         => 'High-performance durable laptop designed for educational institutions, school districts, and universities.',
                'pricing_type'        => 'fixed',
                'sales_mode'          => 'both',
                'base_price'          => 650.00,
                'currency_code'       => 'USD',
                'min_order_quantity'  => 5.000,
                'unit_id'             => $unitPc?->id,
                'approval_status'     => 'approved',
                'approved_by_user_id' => User::where('email', 'admin@edushopify.com')->first()?->id,
                'approved_at'         => now(),
                'is_active'           => true,
                'is_featured'         => true,
                'published_at'        => now(),
            ]
        );

        ProductDetail::firstOrCreate(
            ['listing_id' => $listing1->id],
            [
                'product_type'   => 'simple',
                'stock_status'   => 'in_stock',
                'stock_quantity' => 250.000,
                'lead_time_days' => 5,
                'warranty_terms' => '3-Year On-Site Educational Warranty',
            ]
        );

        // ── 2. Create Demo Listing 2 (Global School Furniture) ──
        $listing2 = Listing::firstOrCreate(
            ['listing_number' => 'LST-2026-002'],
            [
                'supplier_account_id' => $supplier2Account->id,
                'created_by_user_id'  => $supplier2User->id,
                'listing_type'        => 'product',
                'main_category_id'    => $categoryFurniture?->id,
                'name'                => 'Ergonomic Student Desk & Chair Set',
                'slug'                => 'ergonomic-student-desk-chair-set',
                'sku'                 => 'FUR-STU-001',
                'short_description'   => 'Adjustable height student desk with steel frame and polypropylene ergonomic chair.',
                'description'         => 'Durable, easy-to-clean classroom desk and chair set suitable for K-12 schools.',
                'pricing_type'        => 'fixed',
                'sales_mode'          => 'both',
                'base_price'          => 120.00,
                'currency_code'       => 'USD',
                'min_order_quantity'  => 20.000,
                'unit_id'             => $unitPc?->id,
                'approval_status'     => 'approved',
                'approved_by_user_id' => User::where('email', 'admin@edushopify.com')->first()?->id,
                'approved_at'         => now(),
                'is_active'           => true,
                'is_featured'         => true,
                'published_at'        => now(),
            ]
        );

        ProductDetail::firstOrCreate(
            ['listing_id' => $listing2->id],
            [
                'product_type'   => 'simple',
                'stock_status'   => 'in_stock',
                'stock_quantity' => 500.000,
                'lead_time_days' => 10,
                'warranty_terms' => '5-Year Structural Frame Warranty',
            ]
        );


        // ── 3. Create Demo RFQ (Greenwood Academy) ──
        $rfq = Rfq::firstOrCreate(
            ['rfq_number' => 'RFQ-2026-001'],
            [
                'buyer_account_id'           => $buyerAccount->id,
                'created_by_user_id'         => $buyerUser->id,
                'visibility_type'            => 'global',
                'title'                      => 'Procurement of 50 Education Laptops for Computer Lab',
                'description'                => 'Greenwood Academy is requesting quotations for 50 high-durability laptops for our primary computer lab.',
                'currency_code'              => 'USD',
                'budget_min'                 => 25000.00,
                'budget_max'                 => 35000.00,
                'allow_partial_quotation'    => true,
                'allow_alternative_products' => true,
                'quotation_deadline'         => now()->addDays(14),
                'qna_deadline'               => now()->addDays(7),
                'expected_delivery_date'     => now()->addDays(30),
                'status'                     => 'open',
                'published_at'               => now(),
                'items_count'                => 1,
                'quotations_count'           => 1,
            ]
        );

        $rfqItem = RfqItem::firstOrCreate(
            ['rfq_id' => $rfq->id, 'item_name' => 'Education Laptops'],
            [
                'item_type'            => 'product',
                'category_id'          => $categoryEdTech?->id,
                'listing_id'           => $listing1->id,
                'description'          => '14-inch laptops, min 16GB RAM, 512GB SSD, Windows 11 Pro Edu',
                'quantity'             => 50.000,
                'unit_id'              => $unitPc?->id,
                'estimated_unit_price' => 600.00,
            ]
        );


        // ── 4. Create Demo Quotation (Apex EdTech Solutions) ──
        $quotation = Quotation::firstOrCreate(
            ['quotation_number' => 'QTN-2026-001'],
            [
                'rfq_id'               => $rfq->id,
                'supplier_account_id'  => $supplierAccount->id,
                'submitted_by_user_id' => $supplierUser->id,
                'rfq_version_no'       => 1,
                'subtotal'             => 31000.00,
                'grand_total'          => 31000.00,
                'currency_code'        => 'USD',
                'valid_until'          => now()->addDays(30),
                'payment_terms'        => 'Net 30 after delivery',
                'lead_time_days'       => 7,
                'warranty_terms'       => '3-Year On-Site Warranty Included',
                'proposal'             => 'Includes free white-glove setup and laser engraving of school logo.',
                'status'               => 'submitted',
                'submitted_at'         => now(),
            ]
        );

        QuotationItem::firstOrCreate(
            ['quotation_id' => $quotation->id, 'rfq_item_id' => $rfqItem->id],
            [
                'offered_listing_id' => $listing1->id,
                'is_alternative'     => false,
                'item_name'          => 'Dell Latitude 3420 Education Laptop',
                'quantity'           => 50.000,
                'unit_id'            => $unitPc?->id,
                'unit_price'         => 620.00,
                'line_total'         => 31000.00,
            ]
        );


        // ── 5. Create Award & Purchase Order ──
        $award = Award::firstOrCreate(
            ['award_number' => 'AWD-2026-001'],
            [
                'rfq_id'                     => $rfq->id,
                'quotation_id'               => $quotation->id,
                'buyer_account_id'          => $buyerAccount->id,
                'supplier_account_id'       => $supplierAccount->id,
                'awarded_by_user_id'         => $buyerUser->id,
                'award_attempt_no'           => 1,
                'status'                     => 'accepted',
                'response_deadline'          => now()->addDays(3),
                'awarded_at'                 => now(),
                'responded_at'               => now(),
                'accepted_at'                => now(),
            ]
        );

        $po = PurchaseOrder::firstOrCreate(
            ['po_number' => 'PO-2026-001'],
            [
                'award_id'            => $award->id,
                'rfq_id'              => $rfq->id,
                'quotation_id'        => $quotation->id,
                'buyer_account_id'    => $buyerAccount->id,
                'supplier_account_id' => $supplierAccount->id,
                'created_by_user_id'  => $buyerUser->id,
                'subtotal'            => 31000.00,
                'grand_total'         => 31000.00,
                'currency_code'       => 'USD',
                'status'              => 'issued',
                'issued_at'           => now(),
            ]
        );

        PurchaseOrderItem::firstOrCreate(
            ['purchase_order_id' => $po->id, 'item_name' => 'Dell Latitude 3420 Education Laptop'],
            [
                'quantity'   => 50.000,
                'unit_id'    => $unitPc?->id,
                'unit_price' => 620.00,
                'line_total' => 31000.00,
            ]
        );
    }
}
