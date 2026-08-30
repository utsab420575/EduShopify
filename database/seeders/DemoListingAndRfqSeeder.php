<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Award;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Listing;
use App\Models\ListingAttributeValue;
use App\Models\ListingCategory;
use App\Models\ListingTierPrice;
use App\Models\ListingType;
use App\Models\ListingVariant;
use App\Models\ListingVariantAttribute;
use App\Models\PricingType;
use App\Models\ProductDetail;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Rfq;
use App\Models\RfqItem;
use App\Models\SalesMode;
use App\Models\Unit;
use App\Models\User;
use App\Models\VisibilityType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoListingAndRfqSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // ── Lookup Accounts & Users ───────────────────────────────────────
            $supEdTech     = Account::where('account_number', 'ACC-SUP-001')->first();
            $userEdTech    = User::where('email', 'supplier@edtech.com')->first();

            $supFurniture  = Account::where('account_number', 'ACC-SUP-002')->first();
            $userFurniture = User::where('email', 'supplier2@furniture.com')->first();

            $supScience    = Account::where('account_number', 'ACC-SUP-003')->first();
            $userScience   = User::where('email', 'supplier3@bioscience.com')->first();

            $supBooks      = Account::where('account_number', 'ACC-SUP-004')->first();
            $userBooks     = User::where('email', 'supplier4@horizonbooks.com')->first();

            $supSports     = Account::where('account_number', 'ACC-SUP-005')->first();
            $userSports    = User::where('email', 'supplier5@championsports.com')->first();

            $supApparel    = Account::where('account_number', 'ACC-SUP-006')->first();
            $userApparel   = User::where('email', 'supplier6@artisanapparel.com')->first();

            $buyer1Account = Account::where('account_number', 'ACC-BUY-001')->first();
            $buyer1User    = User::where('email', 'buyer@school.edu')->first();

            $buyer2Account = Account::where('account_number', 'ACC-BUY-002')->first();
            $buyer2User    = User::where('email', 'buyer2@university.edu')->first();

            $buyer3Account = Account::where('account_number', 'ACC-BUY-003')->first();
            $buyer3User    = User::where('email', 'buyer3@oakridge.edu')->first();

            $adminUser     = User::where('email', 'admin@edushopify.com')->first() ?? $userEdTech;

            if (!$supEdTech || !$buyer1Account) {
                return;
            }

            // ── Lookup Taxonomy & Units ───────────────────────────────────────
            $listingTypeProduct = ListingType::firstOrCreate(['code' => 'product'], ['name' => 'Product', 'is_active' => true]);
            $pricingTypeFixed   = PricingType::firstOrCreate(['code' => 'fixed'], ['name' => 'Fixed Catalog Price', 'is_active' => true]);
            $pricingTypeRfq     = PricingType::firstOrCreate(['code' => 'rfq_enabled'], ['name' => 'RFQ Enabled Pricing', 'is_active' => true]);
            $salesModeBoth      = SalesMode::firstOrCreate(['code' => 'both'], ['name' => 'Direct Purchase & RFQ', 'is_active' => true]);
            $salesModeRfqOnly   = SalesMode::firstOrCreate(['code' => 'rfq_only'], ['name' => 'RFQ Only', 'is_active' => true]);

            $unitPc     = Unit::where('symbol', 'pc')->first() ?? Unit::first();
            $unitSet    = Unit::where('symbol', 'set')->first() ?? $unitPc;
            $unitBox    = Unit::where('symbol', 'box')->first() ?? $unitPc;
            $unitKit    = Unit::where('symbol', 'kit')->first() ?? $unitPc;
            $unitBundle = Unit::where('symbol', 'bundle')->first() ?? $unitPc;

            // Map Attributes & Values for Easy Linking
            $attrMap = Attribute::pluck('id', 'slug')->toArray();
            $valMap  = AttributeValue::pluck('id', 'slug')->toArray();
            $catMap  = Category::pluck('id', 'slug')->toArray();
            $brandMap= Brand::pluck('id', 'slug')->toArray();

            // ══════════════════════════════════════════════════════════════════
            // 1. PRODUCT CATALOG SEEDING (18 Rich Educational Products)
            // ══════════════════════════════════════════════════════════════════

            $productsData = [
                // ── [EdTech] Product 1: Dell Latitude Education Laptop ──────────
                [
                    'account'      => $supEdTech,
                    'user'         => $userEdTech,
                    'number'       => 'LST-2026-001',
                    'brand_slug'   => 'dell-education',
                    'cat_slug'     => 'student-laptops-tablets',
                    'parent_slug'  => 'edtech-hardware',
                    'name'         => 'Dell Latitude 3420 Education Laptop',
                    'slug'         => 'dell-latitude-3420-education-laptop',
                    'sku'          => 'DELL-LAT-3420-EDU',
                    'short_desc'   => '14-inch FHD, Intel i5-1135G7, 16GB RAM, 512GB NVMe SSD, rugged drop-tested chassis.',
                    'description'  => 'Engineered specifically for schools and colleges. Features spill-resistant keyboard, 12-hour all-day battery life, and rubberized edges for enhanced durability in active classrooms.',
                    'pricing_type' => $pricingTypeFixed,
                    'sales_mode'   => $salesModeBoth,
                    'base_price'   => 650.00,
                    'compare_price'=> 720.00,
                    'moq'          => 5.0,
                    'unit'         => $unitPc,
                    'featured'     => true,
                    'stock_qty'    => 350.0,
                    'lead_days'    => 5,
                    'warranty'     => '3-Year On-Site Educational Hardware Warranty with Accidental Damage Protection',
                    'support'      => 'Dedicated 24/7 Education Tech Support Line & Next-Day Part Replacement',
                    'specs'        => [
                        'brand'               => 'dell-education',
                        'screen-size'         => '140-standard',
                        'ram-capacity'        => '16gb-ddr5',
                        'storage-capacity'    => '512gb-nvme-ssd',
                        'processor-type'      => 'intel-core-i5',
                        'operating-system'    => 'windows-11-pro-education',
                        'touchscreen-enabled' => false,
                        'color'               => 'charcoal-grey',
                        'warranty-duration'   => '3-year-on-site-educational',
                    ],
                    'tiers'        => [
                        ['min' => 1, 'max' => 9, 'price' => 650.00],
                        ['min' => 10, 'max' => 49, 'price' => 590.00],
                        ['min' => 50, 'max' => null, 'price' => 520.00],
                    ],
                    'variants'     => [
                        ['sku' => 'DELL-LAT-3420-8GB', 'name' => '8GB RAM / 256GB SSD', 'price' => 550.00, 'stock' => 150, 'specs' => ['ram-capacity' => '8gb-ddr4', 'storage-capacity' => '256gb-nvme-ssd']],
                        ['sku' => 'DELL-LAT-3420-16GB', 'name' => '16GB RAM / 512GB SSD', 'price' => 650.00, 'stock' => 200, 'specs' => ['ram-capacity' => '16gb-ddr5', 'storage-capacity' => '512gb-nvme-ssd']],
                    ],
                ],

                // ── [EdTech] Product 2: Promethean ActivPanel 9 Premium 75" ───
                [
                    'account'      => $supEdTech,
                    'user'         => $userEdTech,
                    'number'       => 'LST-2026-002',
                    'brand_slug'   => 'promethean',
                    'cat_slug'     => 'interactive-displays',
                    'parent_slug'  => 'edtech-hardware',
                    'name'         => 'Promethean ActivPanel 9 Premium 75" 4K Interactive Display',
                    'slug'         => 'promethean-activpanel-9-premium-75',
                    'sku'          => 'PROM-AP9-75-PREM',
                    'short_desc'   => '75-inch 4K UHD interactive display with Vellum writing tech, 20 touch points, and anti-glare glass.',
                    'description'  => 'The ultimate interactive teaching panel. Integrates seamless cloud connectivity, multi-window palm rejection, built-in front-facing stereo speakers, and educator software suite.',
                    'pricing_type' => $pricingTypeRfq,
                    'sales_mode'   => $salesModeBoth,
                    'base_price'   => 2850.00,
                    'compare_price'=> 3200.00,
                    'moq'          => 1.0,
                    'unit'         => $unitPc,
                    'featured'     => true,
                    'stock_qty'    => 45.0,
                    'lead_days'    => 10,
                    'warranty'     => '5-Year Standard Advanced Replacement Warranty',
                    'support'      => 'White-glove installation support and teacher training webinars included',
                    'specs'        => [
                        'brand'               => 'promethean',
                        'screen-size'         => '75-interactive-4k',
                        'ram-capacity'        => '8gb-ddr4',
                        'storage-capacity'    => '64gb-emmc',
                        'operating-system'    => 'android-13-enterprise',
                        'touchscreen-enabled' => true,
                        'warranty-duration'   => '5-year-structural-frame',
                    ],
                    'tiers'        => [
                        ['min' => 1, 'max' => 4, 'price' => 2850.00],
                        ['min' => 5, 'max' => null, 'price' => 2600.00],
                    ],
                ],

                // ── [EdTech] Product 3: LEGO SPIKE Prime Robotics Set ─────────
                [
                    'account'      => $supEdTech,
                    'user'         => $userEdTech,
                    'number'       => 'LST-2026-003',
                    'brand_slug'   => 'lego-education',
                    'cat_slug'     => 'stem-robotics',
                    'parent_slug'  => 'edtech-hardware',
                    'name'         => 'LEGO Education SPIKE Prime STEAM Robotics Core Set',
                    'slug'         => 'lego-education-spike-prime-core-set',
                    'sku'          => 'LEGO-SPIKE-45678',
                    'short_desc'   => '528-piece hands-on STEAM learning tool for Grades 6-8 with smart hub, motors, and sensors.',
                    'description'  => 'Combines colorful LEGO building elements, easy-to-use hardware, and an intuitive drag-and-drop coding language based on Scratch to engage students in creative problem-solving.',
                    'pricing_type' => $pricingTypeFixed,
                    'sales_mode'   => $salesModeBoth,
                    'base_price'   => 399.95,
                    'compare_price'=> 430.00,
                    'moq'          => 2.0,
                    'unit'         => $unitSet,
                    'featured'     => true,
                    'stock_qty'    => 180.0,
                    'lead_days'    => 3,
                    'warranty'     => '2-Year Official LEGO Education Replacement Warranty',
                    'support'      => 'Full curriculum unit plans and student assessment rubrics included',
                    'specs'        => [
                        'brand'               => 'lego-education',
                        'target-grade-level'  => 'middle-school-grades-6-8',
                        'subject-area'        => 'computer-science-coding',
                        'warranty-duration'   => '2-year-advanced-replacement',
                    ],
                    'tiers'        => [
                        ['min' => 1, 'max' => 9, 'price' => 399.95],
                        ['min' => 10, 'max' => null, 'price' => 365.00],
                    ],
                ],

                // ── [Science] Product 4: AmScope LED Binocular Compound Microscope ──
                [
                    'account'      => $supScience,
                    'user'         => $userScience,
                    'number'       => 'LST-2026-004',
                    'brand_slug'   => 'amscope-optics',
                    'cat_slug'     => 'microscopes-optics',
                    'parent_slug'  => 'science-lab-equipment',
                    'name'         => 'AmScope B120C 40X-2500X LED Binocular Compound Microscope',
                    'slug'         => 'amscope-b120c-binocular-compound-microscope',
                    'sku'          => 'AMS-B120C-BINOC',
                    'short_desc'   => 'High-power 3D double-layer mechanical stage microscope with 6 magnification levels up to 2500X.',
                    'description'  => 'Designed for university teaching labs, high school advanced biology, and clinical demonstrations. Equipped with professional Siedentopf binocular head and adjustable intensity LED illumination.',
                    'pricing_type' => $pricingTypeFixed,
                    'sales_mode'   => $salesModeBoth,
                    'base_price'   => 260.00,
                    'compare_price'=> 310.00,
                    'moq'          => 2.0,
                    'unit'         => $unitPc,
                    'featured'     => true,
                    'stock_qty'    => 120.0,
                    'lead_days'    => 4,
                    'warranty'     => '5-Year Manufacturer Optical & Mechanical Warranty',
                    'support'      => 'Replacement bulbs and calibration slides available on demand',
                    'specs'        => [
                        'brand'                 => 'amscope-optics',
                        'optical-magnification' => '40x-2500x-high-power-compound',
                        'optics-head-type'      => 'binocular-45-inclined',
                        'target-grade-level'    => 'high-school-grades-9-12',
                        'subject-area'          => 'biology-life-science',
                        'warranty-duration'     => '5-year-structural-frame',
                    ],
                    'tiers'        => [
                        ['min' => 1, 'max' => 9, 'price' => 260.00],
                        ['min' => 10, 'max' => null, 'price' => 225.00],
                    ],
                    'variants'     => [
                        ['sku' => 'AMS-B120C-BIN', 'name' => 'Binocular Standard Head', 'price' => 260.00, 'stock' => 80, 'specs' => ['optics-head-type' => 'binocular-45-inclined']],
                        ['sku' => 'AMS-T120C-TRI', 'name' => 'Trinocular Digital + 5MP Camera', 'price' => 380.00, 'stock' => 40, 'specs' => ['optics-head-type' => 'trinocular-with-5mp-usbhdmi-camera']],
                    ],
                ],

                // ── [Science] Product 5: Fisher Scientific Borosilicate Glassware Set ──
                [
                    'account'      => $supScience,
                    'user'         => $userScience,
                    'number'       => 'LST-2026-005',
                    'brand_slug'   => 'fisher-scientific-ed',
                    'cat_slug'     => 'lab-glassware',
                    'parent_slug'  => 'science-lab-equipment',
                    'name'         => 'Fisher Scientific Class Lab Borosilicate 3.3 Glassware Starter Kit (24 Pcs)',
                    'slug'         => 'fisher-scientific-borosilicate-glassware-starter-kit',
                    'sku'          => 'FSH-GLS-KIT-24',
                    'short_desc'   => 'Heavy-duty ASTM E438 Type 1 Class A thermal shock resistant lab glassware set.',
                    'description'  => 'Includes Griffin beakers (50ml-1000ml), Erlenmeyer flasks, graduated cylinders, glass stirring rods, and reagent bottles. Ideal for high school chemistry and undergraduate labs.',
                    'pricing_type' => $pricingTypeFixed,
                    'sales_mode'   => $salesModeBoth,
                    'base_price'   => 145.00,
                    'compare_price'=> 175.00,
                    'moq'          => 4.0,
                    'unit'         => $unitKit,
                    'featured'     => false,
                    'stock_qty'    => 200.0,
                    'lead_days'    => 3,
                    'warranty'     => '1-Year Quality Assurance Guarantee',
                    'support'      => 'Individual replacement pieces in stock year-round',
                    'specs'        => [
                        'brand'                            => 'fisher-scientific-ed',
                        'material'                         => 'borosilicate-33-glass',
                        'autoclavable-chemical-resistant'  => true,
                        'target-grade-level'               => 'high-school-grades-9-12',
                        'subject-area'                     => 'chemistry-earth-science',
                    ],
                    'tiers'        => [
                        ['min' => 1, 'max' => 9, 'price' => 145.00],
                        ['min' => 10, 'max' => null, 'price' => 125.00],
                    ],
                ],

                // ── [Science] Product 6: Vernier Go Direct Sensor Package ─────
                [
                    'account'      => $supScience,
                    'user'         => $userScience,
                    'number'       => 'LST-2026-006',
                    'brand_slug'   => 'vernier-science',
                    'cat_slug'     => 'physics-mechanics-kits',
                    'parent_slug'  => 'science-lab-equipment',
                    'name'         => 'Vernier Go Direct Wireless STEM Sensor Starter Package',
                    'slug'         => 'vernier-go-direct-wireless-stem-sensor-package',
                    'sku'          => 'VRN-GD-STEM-01',
                    'short_desc'   => 'Includes Wireless Temperature, Gas Pressure, Motion, and pH Sensors with Graphical Analysis app.',
                    'description'  => 'Connects directly via Bluetooth or USB to Chromebooks, computers, and tablets. Enables real-time data streaming, graphing, and scientific analysis in physics and chemistry.',
                    'pricing_type' => $pricingTypeFixed,
                    'sales_mode'   => $salesModeBoth,
                    'base_price'   => 480.00,
                    'compare_price'=> 540.00,
                    'moq'          => 2.0,
                    'unit'         => $unitKit,
                    'featured'     => true,
                    'stock_qty'    => 90.0,
                    'lead_days'    => 5,
                    'warranty'     => '5-Year Educational Limited Warranty',
                    'support'      => 'Over 40 downloadable experiments and teacher lesson guides',
                    'specs'        => [
                        'brand'               => 'vernier-science',
                        'target-grade-level'  => 'high-school-grades-9-12',
                        'subject-area'        => 'physics-engineering',
                        'warranty-duration'   => '5-year-structural-frame',
                    ],
                    'tiers'        => [
                        ['min' => 1, 'max' => 5, 'price' => 480.00],
                        ['min' => 6, 'max' => null, 'price' => 440.00],
                    ],
                ],

                // ── [Furniture] Product 7: Ergonomic Student Desk & Chair Set ─
                [
                    'account'      => $supFurniture,
                    'user'         => $userFurniture,
                    'number'       => 'LST-2026-007',
                    'brand_slug'   => 'smith-system',
                    'cat_slug'     => 'student-desks',
                    'parent_slug'  => 'classroom-furniture',
                    'name'         => 'Smith System Silhouette Adjustable Student Desk & Chair Set',
                    'slug'         => 'smith-system-silhouette-student-desk-chair-set',
                    'sku'          => 'SMS-SIL-SET-01',
                    'short_desc'   => 'Height-adjustable steel frame desk with high-pressure laminate top and ergonomic cantilever chair.',
                    'description'  => 'Built to withstand decades of heavy classroom use. Resists scratches, marker stains, and impacts. Features integrated book box backpack hook and silent nylon floor glides.',
                    'pricing_type' => $pricingTypeFixed,
                    'sales_mode'   => $salesModeBoth,
                    'base_price'   => 185.00,
                    'compare_price'=> 215.00,
                    'moq'          => 10.0,
                    'unit'         => $unitSet,
                    'featured'     => true,
                    'stock_qty'    => 500.0,
                    'lead_days'    => 10,
                    'warranty'     => '10-Year Lifetime Structural Frame Warranty',
                    'support'      => 'Assembly guide, spare hardware packs, and bulk freight options',
                    'specs'        => [
                        'brand'               => 'smith-system',
                        'material'            => 'high-pressure-laminate-steel',
                        'height-adjustable'   => true,
                        'color'               => 'navy-blue',
                        'target-grade-level'  => 'all-educational-levels',
                        'warranty-duration'   => '10-year-lifetime-structural',
                    ],
                    'tiers'        => [
                        ['min' => 10, 'max' => 29, 'price' => 185.00],
                        ['min' => 30, 'max' => 99, 'price' => 165.00],
                        ['min' => 100, 'max' => null, 'price' => 145.00],
                    ],
                    'variants'     => [
                        ['sku' => 'SMS-SIL-NAVY', 'name' => 'Navy Blue Shell / Natural Oak Top', 'price' => 185.00, 'stock' => 250, 'specs' => ['color' => 'navy-blue']],
                        ['sku' => 'SMS-SIL-GREY', 'name' => 'Charcoal Grey Shell / Natural Oak Top', 'price' => 185.00, 'stock' => 250, 'specs' => ['color' => 'charcoal-grey']],
                    ],
                ],

                // ── [Furniture] Product 8: Mobile 30-Bay Chromebook Charging Cart ──
                [
                    'account'      => $supFurniture,
                    'user'         => $userFurniture,
                    'number'       => 'LST-2026-008',
                    'brand_slug'   => 'smith-system',
                    'cat_slug'     => 'storage-cabinets',
                    'parent_slug'  => 'classroom-furniture',
                    'name'         => 'Heavy-Duty 30-Bay Intelligent Mobile Device Charging Cart',
                    'slug'         => 'heavy-duty-30-bay-mobile-charging-cart',
                    'sku'          => 'SMS-CHG-CART-30',
                    'short_desc'   => 'Lockable steel cart with smart cable routing, surge protection, and smooth locking swivel casters.',
                    'description'  => 'Safely charges, stores, and transports up to 30 laptops, Chromebooks, or tablets up to 15.6 inches. Features dual front and rear doors with 3-point key locking mechanism.',
                    'pricing_type' => $pricingTypeFixed,
                    'sales_mode'   => $salesModeBoth,
                    'base_price'   => 890.00,
                    'compare_price'=> 1050.00,
                    'moq'          => 1.0,
                    'unit'         => $unitPc,
                    'featured'     => false,
                    'stock_qty'    => 60.0,
                    'lead_days'    => 7,
                    'warranty'     => '5-Year Mechanical / 2-Year Electrical Component Warranty',
                    'support'      => 'Pre-wired power strips included',
                    'specs'        => [
                        'brand'               => 'smith-system',
                        'material'            => 'heavy-duty-steel-sheet',
                        'color'               => 'charcoal-grey',
                        'warranty-duration'   => '5-year-structural-frame',
                    ],
                    'tiers'        => [
                        ['min' => 1, 'max' => 4, 'price' => 890.00],
                        ['min' => 5, 'max' => null, 'price' => 810.00],
                    ],
                ],

                // ── [Furniture] Product 9: Mobile Magnetic Double-Sided Whiteboard ──
                [
                    'account'      => $supFurniture,
                    'user'         => $userFurniture,
                    'number'       => 'LST-2026-009',
                    'brand_slug'   => 'smith-system',
                    'cat_slug'     => 'whiteboards-pinboards',
                    'parent_slug'  => 'classroom-furniture',
                    'name'         => 'Reversible Mobile Magnetic Porcelain Whiteboard (72" x 40")',
                    'slug'         => 'reversible-mobile-magnetic-whiteboard-72x40',
                    'sku'          => 'SMS-WBD-MOB-72',
                    'short_desc'   => 'Double-sided 360-degree pivoting magnetic dry-erase board with aluminum frame and full marker tray.',
                    'description'  => 'Commercial porcelain surface resists ghosting, staining, and scratches. Locking pivot clip holds the board firmly in place, while 4 locking heavy-duty casters provide effortless mobility.',
                    'pricing_type' => $pricingTypeFixed,
                    'sales_mode'   => $salesModeBoth,
                    'base_price'   => 295.00,
                    'compare_price'=> 350.00,
                    'moq'          => 1.0,
                    'unit'         => $unitPc,
                    'featured'     => false,
                    'stock_qty'    => 110.0,
                    'lead_days'    => 5,
                    'warranty'     => '10-Year Surface Warranty',
                    'support'      => 'Includes magnetic erasers and starter marker set',
                    'specs'        => [
                        'brand'               => 'smith-system',
                        'material'            => 'reinforced-aluminum',
                        'height-adjustable'   => false,
                        'warranty-duration'   => '10-year-lifetime-structural',
                    ],
                    'tiers'        => [
                        ['min' => 1, 'max' => 4, 'price' => 295.00],
                        ['min' => 5, 'max' => null, 'price' => 260.00],
                    ],
                ],

                // ── [Curriculum] Product 10: Cambridge AP Computer Science Coursebook ──
                [
                    'account'      => $supBooks,
                    'user'         => $userBooks,
                    'number'       => 'LST-2026-010',
                    'brand_slug'   => 'cambridge-learning',
                    'cat_slug'     => 'stem-textbooks',
                    'parent_slug'  => 'curriculum-learning-materials',
                    'name'         => 'Cambridge AP Computer Science A & Python Principles Class Pack (30 Books)',
                    'slug'         => 'cambridge-ap-computer-science-class-pack-30',
                    'sku'          => 'CAM-CS-AP-CP30',
                    'short_desc'   => 'Complete 30-student coursebook bundle with digital coding lab sandbox and teacher solution key.',
                    'description'  => 'Fully aligned with the latest College Board and IGCSE standards. Features hands-on algorithmic coding projects, pseudocode breakdowns, and comprehensive practice exams.',
                    'pricing_type' => $pricingTypeFixed,
                    'sales_mode'   => $salesModeBoth,
                    'base_price'   => 1250.00,
                    'compare_price'=> 1400.00,
                    'moq'          => 1.0,
                    'unit'         => $unitBundle,
                    'featured'     => true,
                    'stock_qty'    => 80.0,
                    'lead_days'    => 4,
                    'warranty'     => '1-Year Digital Platform Access Included',
                    'support'      => 'Complimentary teacher portal access with slide decks and test banks',
                    'specs'        => [
                        'brand'               => 'cambridge-learning',
                        'target-grade-level'  => 'high-school-grades-9-12',
                        'subject-area'        => 'computer-science-coding',
                        'language'            => 'english',
                        'country-of-origin'   => 'united-kingdom',
                    ],
                    'tiers'        => [
                        ['min' => 1, 'max' => 4, 'price' => 1250.00],
                        ['min' => 5, 'max' => null, 'price' => 1100.00],
                    ],
                ],

                // ── [Curriculum] Product 11: Scholastic Leveled Guided Reading Library ──
                [
                    'account'      => $supBooks,
                    'user'         => $userBooks,
                    'number'       => 'LST-2026-011',
                    'brand_slug'   => 'scholastic-education',
                    'cat_slug'     => 'early-reader-sets',
                    'parent_slug'  => 'curriculum-learning-materials',
                    'name'         => 'Scholastic Guided Reading Levels A-Z Classroom Book Library (120 Titles)',
                    'slug'         => 'scholastic-guided-reading-library-120-titles',
                    'sku'          => 'SCH-GRL-AZ-120',
                    'short_desc'   => 'Curated collection of 120 fiction and non-fiction leveled readers with teacher running records.',
                    'description'  => 'Covers diverse genres, multicultural stories, STEM topics, and phonics foundations. Packed in labeled plastic book bins ready for instant classroom deployment.',
                    'pricing_type' => $pricingTypeFixed,
                    'sales_mode'   => $salesModeBoth,
                    'base_price'   => 820.00,
                    'compare_price'=> 950.00,
                    'moq'          => 1.0,
                    'unit'         => $unitSet,
                    'featured'     => true,
                    'stock_qty'    => 65.0,
                    'lead_days'    => 3,
                    'warranty'     => 'Publisher Satisfaction Guarantee',
                    'support'      => 'Printable comprehension checks and lesson cards for every book',
                    'specs'        => [
                        'brand'               => 'scholastic-education',
                        'target-grade-level'  => 'elementary-school-grades-1-5',
                        'subject-area'        => 'language-arts-literacy',
                        'language'            => 'english',
                    ],
                    'tiers'        => [
                        ['min' => 1, 'max' => 3, 'price' => 820.00],
                        ['min' => 4, 'max' => null, 'price' => 740.00],
                    ],
                ],

                // ── [Sports] Product 12: Spalding Heavy-Duty Basketball & Cart Package ──
                [
                    'account'      => $supSports,
                    'user'         => $userSports,
                    'number'       => 'LST-2026-012',
                    'brand_slug'   => 'spalding-sports',
                    'cat_slug'     => 'team-sports-equipment',
                    'parent_slug'  => 'sports-physical-education',
                    'name'         => 'Spalding Institutional TF-1000 Indoor/Outdoor Basketball Class Pack (12 Balls + Cart)',
                    'slug'         => 'spalding-tf-1000-basketball-classpack-12',
                    'sku'          => 'SPL-TF1000-CP12',
                    'short_desc'   => '12 composite leather official size/weight basketballs with heavy-duty lockable steel ball cart.',
                    'description'  => 'Engineered for institutional physical education programs and school varsity teams. Exceptional grip, deep channels, and durable microfiber composite casing.',
                    'pricing_type' => $pricingTypeFixed,
                    'sales_mode'   => $salesModeBoth,
                    'base_price'   => 460.00,
                    'compare_price'=> 530.00,
                    'moq'          => 1.0,
                    'unit'         => $unitSet,
                    'featured'     => false,
                    'stock_qty'    => 75.0,
                    'lead_days'    => 4,
                    'warranty'     => '2-Year Institutional Ball & Cart Warranty',
                    'support'      => 'Inflation pump and heavy-duty needle kit included',
                    'specs'        => [
                        'brand'               => 'spalding-sports',
                        'target-grade-level'  => 'all-educational-levels',
                        'warranty-duration'   => '2-year-advanced-replacement',
                    ],
                    'tiers'        => [
                        ['min' => 1, 'max' => 4, 'price' => 460.00],
                        ['min' => 5, 'max' => null, 'price' => 415.00],
                    ],
                ],

                // ── [Sports] Product 13: Gopher 2" High-Density Gymnasium Folding Mat ──
                [
                    'account'      => $supSports,
                    'user'         => $userSports,
                    'number'       => 'LST-2026-013',
                    'brand_slug'   => 'gopher-sport',
                    'cat_slug'     => 'gymnasium-mats',
                    'parent_slug'  => 'sports-physical-education',
                    'name'         => 'Gopher 4ft x 8ft High-Density Cross-Linked Polyethylene Folding Gym Mat',
                    'slug'         => 'gopher-4x8-folding-gymnasium-mat',
                    'sku'          => 'GPH-MAT-4X8-BLU',
                    'short_desc'   => '2-inch thick crosslink foam mat with 18oz vinyl reinforced cover and 4-sided hook-and-loop fasteners.',
                    'description'  => 'Meets all ASTM institutional safety standards for gymnastics, wrestling, and physical education. Antibacterial, fungal-resistant, and easily wiped clean.',
                    'pricing_type' => $pricingTypeFixed,
                    'sales_mode'   => $salesModeBoth,
                    'base_price'   => 210.00,
                    'compare_price'=> 245.00,
                    'moq'          => 2.0,
                    'unit'         => $unitPc,
                    'featured'     => false,
                    'stock_qty'    => 140.0,
                    'lead_days'    => 5,
                    'warranty'     => '3-Year Commercial Seam & Foam Warranty',
                    'support'      => 'Custom school color and logo stamping available on bulk orders',
                    'specs'        => [
                        'brand'               => 'gopher-sport',
                        'color'               => 'navy-blue',
                        'warranty-duration'   => '3-year-on-site-educational',
                    ],
                    'tiers'        => [
                        ['min' => 2, 'max' => 9, 'price' => 210.00],
                        ['min' => 10, 'max' => null, 'price' => 185.00],
                    ],
                ],

                // ── [Arts] Product 14: Crayola Ultimate Classroom Art Classpack ──
                [
                    'account'      => $supApparel,
                    'user'         => $userApparel,
                    'number'       => 'LST-2026-014',
                    'brand_slug'   => 'crayola-classpack',
                    'cat_slug'     => 'painting-drawing-supplies',
                    'parent_slug'  => 'arts-crafts-music',
                    'name'         => 'Crayola Ultimate Classroom Art & Drawing Classpack (800+ Pieces)',
                    'slug'         => 'crayola-ultimate-classroom-art-classpack-800',
                    'sku'          => 'CRY-CP-800-ALL',
                    'short_desc'   => 'Comprehensive bulk art kit: 256 broad line markers, 400 crayons, and 144 colored pencils in partitioned box.',
                    'description'  => 'Non-toxic, ultra-washable pigments formulated specifically for schools. Organized in heavy-duty classroom storage boxes for easy distribution and clean-up.',
                    'pricing_type' => $pricingTypeFixed,
                    'sales_mode'   => $salesModeBoth,
                    'base_price'   => 165.00,
                    'compare_price'=> 195.00,
                    'moq'          => 2.0,
                    'unit'         => $unitBox,
                    'featured'     => false,
                    'stock_qty'    => 220.0,
                    'lead_days'    => 3,
                    'warranty'     => 'ACMI Non-Toxic Certified',
                    'support'      => 'Individual color refill packs available',
                    'specs'        => [
                        'brand'               => 'crayola-classpack',
                        'target-grade-level'  => 'elementary-school-grades-1-5',
                    ],
                    'tiers'        => [
                        ['min' => 2, 'max' => 9, 'price' => 165.00],
                        ['min' => 10, 'max' => null, 'price' => 140.00],
                    ],
                ],

                // ── [Apparel] Product 15: French Toast Cotton Blend Student Polo ──
                [
                    'account'      => $supApparel,
                    'user'         => $userApparel,
                    'number'       => 'LST-2026-015',
                    'brand_slug'   => 'french-toast-uniforms',
                    'cat_slug'     => 'student-polos-blazers',
                    'parent_slug'  => 'school-uniforms-apparel',
                    'name'         => 'French Toast Short Sleeve Pique School Uniform Polo (Pack of 10)',
                    'slug'         => 'french-toast-short-sleeve-pique-polo-pack-10',
                    'sku'          => 'FT-POLO-PK10',
                    'short_desc'   => '60% cotton / 40% polyester pique knit with 3-button placket and reinforced collar.',
                    'description'  => 'Fade-resistant, shrink-resistant, and tagless for maximum student comfort. Designed for high-frequency industrial and home machine washing without pilling.',
                    'pricing_type' => $pricingTypeFixed,
                    'sales_mode'   => $salesModeBoth,
                    'base_price'   => 130.00,
                    'compare_price'=> 150.00,
                    'moq'          => 5.0,
                    'unit'         => $unitBox,
                    'featured'     => false,
                    'stock_qty'    => 300.0,
                    'lead_days'    => 5,
                    'warranty'     => '50-Wash Durability Tested Guarantee',
                    'support'      => 'Custom school crest embroidery available on bulk orders',
                    'specs'        => [
                        'brand'               => 'french-toast-uniforms',
                        'material'            => '100-organic-cotton',
                        'color'               => 'navy-blue',
                        'apparel-size'        => 'youth-medium-8-10',
                        'country-of-origin'   => 'vietnam',
                    ],
                    'tiers'        => [
                        ['min' => 5, 'max' => 19, 'price' => 130.00],
                        ['min' => 20, 'max' => null, 'price' => 110.00],
                    ],
                    'variants'     => [
                        ['sku' => 'FT-POLO-NAVY-YM', 'name' => 'Navy Blue / Youth Medium', 'price' => 130.00, 'stock' => 100, 'specs' => ['color' => 'navy-blue', 'apparel-size' => 'youth-medium-8-10']],
                        ['sku' => 'FT-POLO-WHT-YM',  'name' => 'Arctic White / Youth Medium', 'price' => 130.00, 'stock' => 100, 'specs' => ['color' => 'arctic-white', 'apparel-size' => 'youth-medium-8-10']],
                        ['sku' => 'FT-POLO-NAVY-YL', 'name' => 'Navy Blue / Youth Large', 'price' => 130.00, 'stock' => 100, 'specs' => ['color' => 'navy-blue', 'apparel-size' => 'youth-large-12-14']],
                    ],
                ],

                // ── [Apparel] Product 16: Cotton Science Lab Coat ─────────────
                [
                    'account'      => $supApparel,
                    'user'         => $userApparel,
                    'number'       => 'LST-2026-016',
                    'brand_slug'   => 'french-toast-uniforms',
                    'cat_slug'     => 'lab-coats-aprons',
                    'parent_slug'  => 'school-uniforms-apparel',
                    'name'         => 'Unisex Student Knee-Length 100% Cotton Lab Safety Coat (Pack of 5)',
                    'slug'         => 'unisex-student-cotton-lab-safety-coat-pack-5',
                    'sku'          => 'APP-LAB-COAT-PK5',
                    'short_desc'   => '100% heavy cotton twill lab coat with snap front closures, side pocket access, and knit cuffs.',
                    'description'  => 'Essential protective apparel for chemistry and biology laboratories. Flame-resistant natural fibers safeguard against chemical splatters and minor spills.',
                    'pricing_type' => $pricingTypeFixed,
                    'sales_mode'   => $salesModeBoth,
                    'base_price'   => 95.00,
                    'compare_price'=> 115.00,
                    'moq'          => 2.0,
                    'unit'         => $unitBox,
                    'featured'     => false,
                    'stock_qty'    => 160.0,
                    'lead_days'    => 4,
                    'warranty'     => '1-Year Seam Quality Guarantee',
                    'support'      => 'Individual sizes available from Youth XS to Adult 2XL',
                    'specs'        => [
                        'brand'               => 'french-toast-uniforms',
                        'material'            => '100-organic-cotton',
                        'color'               => 'arctic-white',
                        'apparel-size'        => 'adult-small',
                    ],
                    'tiers'        => [
                        ['min' => 2, 'max' => 9, 'price' => 95.00],
                        ['min' => 10, 'max' => null, 'price' => 82.00],
                    ],
                ],

                // ── [EdTech] Product 17: Epson PowerLite Laser Projector ──────
                [
                    'account'      => $supEdTech,
                    'user'         => $userEdTech,
                    'number'       => 'LST-2026-017',
                    'brand_slug'   => 'epson-education',
                    'cat_slug'     => 'projectors-audio',
                    'parent_slug'  => 'edtech-hardware',
                    'name'         => 'Epson PowerLite L210W Classroom Wireless Laser Projector',
                    'slug'         => 'epson-powerlite-l210w-laser-projector',
                    'sku'          => 'EPS-L210W-PROJ',
                    'short_desc'   => '4500 lumens WXGA laser display with 20,000-hour solid-state laser light source and built-in Wi-Fi.',
                    'description'  => 'Delivers vivid, easy-to-read widescreen images up to 300 inches even in bright classrooms. Zero lamp replacement downtime and instant on/off convenience.',
                    'pricing_type' => $pricingTypeFixed,
                    'sales_mode'   => $salesModeBoth,
                    'base_price'   => 1050.00,
                    'compare_price'=> 1200.00,
                    'moq'          => 1.0,
                    'unit'         => $unitPc,
                    'featured'     => false,
                    'stock_qty'    => 55.0,
                    'lead_days'    => 5,
                    'warranty'     => '3-Year Limited Projector / 20,000-Hour Laser Warranty',
                    'support'      => 'Next-business-day road service replacement',
                    'specs'        => [
                        'brand'               => 'epson-education',
                        'warranty-duration'   => '3-year-on-site-educational',
                    ],
                    'tiers'        => [
                        ['min' => 1, 'max' => 3, 'price' => 1050.00],
                        ['min' => 4, 'max' => null, 'price' => 960.00],
                    ],
                ],

                // ── [EdTech] Product 18: Lenovo 300w Gen 4 Yoga 2-in-1 ────────
                [
                    'account'      => $supEdTech,
                    'user'         => $userEdTech,
                    'number'       => 'LST-2026-018',
                    'brand_slug'   => 'lenovo-campus',
                    'cat_slug'     => 'student-laptops-tablets',
                    'parent_slug'  => 'edtech-hardware',
                    'name'         => 'Lenovo 300w Gen 4 Yoga 11.6" Touchscreen 2-in-1 Convertible',
                    'slug'         => 'lenovo-300w-gen-4-yoga-convertible',
                    'sku'          => 'LNV-300W-G4-YOGA',
                    'short_desc'   => '11.6" HD IPS 10-point Multi-Touch, Intel N100, 8GB RAM, 128GB SSD, 360-degree hinge.',
                    'description'  => 'Flexible 2-in-1 design transitions effortlessly from laptop to tent, stand, or tablet mode. Built with MIL-SPEC 810H durability, mechanically anchored keys, and pencil-touch screen.',
                    'pricing_type' => $pricingTypeFixed,
                    'sales_mode'   => $salesModeBoth,
                    'base_price'   => 410.00,
                    'compare_price'=> 465.00,
                    'moq'          => 5.0,
                    'unit'         => $unitPc,
                    'featured'     => true,
                    'stock_qty'    => 280.0,
                    'lead_days'    => 4,
                    'warranty'     => '3-Year Mail-in / Premier Support Educational Warranty',
                    'support'      => 'Bulk provisioning and Google Zero-Touch Enrollment ready',
                    'specs'        => [
                        'brand'               => 'lenovo-campus',
                        'screen-size'         => '116-convertible',
                        'ram-capacity'        => '8gb-ddr4',
                        'storage-capacity'    => '128gb-ssd',
                        'processor-type'      => 'intel-core-i3',
                        'operating-system'    => 'windows-11-pro-education',
                        'touchscreen-enabled' => true,
                        'color'               => 'charcoal-grey',
                        'warranty-duration'   => '3-year-on-site-educational',
                    ],
                    'tiers'        => [
                        ['min' => 5, 'max' => 24, 'price' => 410.00],
                        ['min' => 25, 'max' => null, 'price' => 370.00],
                    ],
                ],
            ];

            $createdListings = [];

            foreach ($productsData as $p) {
                $mainCatId = $catMap[$p['cat_slug']] ?? null;
                $parentCatId = $catMap[$p['parent_slug']] ?? null;
                $brandId   = $brandMap[$p['brand_slug']] ?? null;

                $listing = Listing::firstOrCreate(
                    ['listing_number' => $p['number']],
                    [
                        'supplier_account_id' => $p['account']->id,
                        'created_by_user_id'  => $p['user']->id,
                        'listing_type_id'     => $listingTypeProduct->id,
                        'main_category_id'    => $mainCatId,
                        'brand_id'            => $brandId,
                        'name'                => $p['name'],
                        'slug'                => $p['slug'],
                        'sku'                 => $p['sku'],
                        'short_description'   => $p['short_desc'],
                        'description'         => $p['description'],
                        'pricing_type_id'     => $p['pricing_type']->id,
                        'sales_mode_id'       => $p['sales_mode']->id,
                        'base_price'          => $p['base_price'],
                        'compare_at_price'    => $p['compare_price'] ?? null,
                        'currency_code'       => 'USD',
                        'min_order_quantity'  => $p['moq'] ?? 1.0,
                        'unit_id'             => $p['unit']->id,
                        'approval_status'     => 'approved',
                        'approved_by_user_id' => $adminUser->id,
                        'approved_at'         => now(),
                        'is_active'           => true,
                        'is_featured'         => $p['featured'] ?? false,
                        'published_at'        => now(),
                        'setup_step'          => 5,
                        'setup_completed_at'  => now(),
                    ]
                );

                $createdListings[$p['number']] = $listing;

                // Product Details
                ProductDetail::updateOrCreate(
                    ['listing_id' => $listing->id],
                    [
                        'product_type'   => !empty($p['variants']) ? 'variable' : 'simple',
                        'stock_status'   => 'in_stock',
                        'stock_quantity' => $p['stock_qty'] ?? 100.0,
                        'lead_time_days' => $p['lead_days'] ?? 5,
                        'warranty_terms' => $p['warranty'] ?? '1-Year Standard Educational Warranty',
                        'support_terms'  => $p['support'] ?? 'Standard Institutional Support',
                    ]
                );

                // Listing Categories
                if ($mainCatId) {
                    ListingCategory::firstOrCreate(
                        ['listing_id' => $listing->id, 'category_id' => $mainCatId],
                        ['is_primary' => true]
                    );
                }
                if ($parentCatId && $parentCatId !== $mainCatId) {
                    ListingCategory::firstOrCreate(
                        ['listing_id' => $listing->id, 'category_id' => $parentCatId],
                        ['is_primary' => false]
                    );
                }

                // Listing Attribute Values
                if (!empty($p['specs'])) {
                    foreach ($p['specs'] as $aSlug => $specVal) {
                        $attrId = $attrMap[$aSlug] ?? null;
                        if (!$attrId) continue;

                        $valId = is_string($specVal) ? ($valMap[$specVal] ?? null) : null;
                        $isBool = is_bool($specVal);

                        ListingAttributeValue::updateOrCreate(
                            ['listing_id' => $listing->id, 'attribute_id' => $attrId],
                            [
                                'attribute_value_id' => $valId,
                                'value_text'         => is_string($specVal) && !$valId ? $specVal : null,
                                'value_boolean'      => $isBool ? $specVal : null,
                            ]
                        );
                    }
                }

                // Volume Bulk Tier Prices
                if (!empty($p['tiers'])) {
                    foreach ($p['tiers'] as $t) {
                        ListingTierPrice::firstOrCreate(
                            [
                                'listing_id'   => $listing->id,
                                'min_quantity' => $t['min'],
                            ],
                            [
                                'max_quantity'  => $t['max'],
                                'unit_price'    => $t['price'],
                                'currency_code' => 'USD',
                            ]
                        );
                    }
                }

                // Variants
                if (!empty($p['variants'])) {
                    foreach ($p['variants'] as $idx => $v) {
                        $variant = ListingVariant::firstOrCreate(
                            ['listing_id' => $listing->id, 'sku' => $v['sku']],
                            [
                                'name'               => $v['name'],
                                'price'              => $v['price'],
                                'currency_code'      => 'USD',
                                'stock_status'       => 'in_stock',
                                'stock_quantity'     => $v['stock'] ?? 50.0,
                                'min_order_quantity' => 1.0,
                                'unit_id'            => $p['unit']->id,
                                'is_active'          => true,
                                'sort_order'         => $idx + 1,
                            ]
                        );

                        if (!empty($v['specs'])) {
                            foreach ($v['specs'] as $aSlug => $vSpecVal) {
                                $attrId = $attrMap[$aSlug] ?? null;
                                $valId = is_string($vSpecVal) ? ($valMap[$vSpecVal] ?? null) : null;
                                if (!$attrId) continue;

                                ListingVariantAttribute::firstOrCreate(
                                    [
                                        'listing_variant_id' => $variant->id,
                                        'attribute_id'       => $attrId,
                                    ],
                                    [
                                        'attribute_value_id' => $valId,
                                        'custom_value'       => !$valId ? (string)$vSpecVal : null,
                                    ]
                                );
                            }
                        }
                    }
                }
            }

            // ══════════════════════════════════════════════════════════════════
            // 2. DEMO RFQS, QUOTATIONS, AWARDS, AND PURCHASE ORDERS
            // ══════════════════════════════════════════════════════════════════

            $visibilityOpen    = VisibilityType::where('code', 'open_matching')->first() ?? VisibilityType::first();
            $visibilityInvited = VisibilityType::where('code', 'invited')->first() ?? $visibilityOpen;
            $visibilityDirect  = VisibilityType::where('code', 'direct')->first() ?? $visibilityOpen;

            $laptopListing   = $createdListings['LST-2026-001'] ?? null;
            $microListing    = $createdListings['LST-2026-004'] ?? null;
            $deskListing     = $createdListings['LST-2026-007'] ?? null;
            $spikeListing    = $createdListings['LST-2026-003'] ?? null;

            // ── RFQ 1: Greenwood Academy $\to$ Awarded & PO Issued (Laptops) ──
            if ($laptopListing) {
                $rfq1 = Rfq::firstOrCreate(
                    ['rfq_number' => 'RFQ-2026-001'],
                    [
                        'buyer_account_id'           => $buyer1Account->id,
                        'created_by_user_id'         => $buyer1User->id,
                        'visibility_type_id'         => $visibilityOpen->id,
                        'title'                      => 'Procurement of 50 Education Laptops for Computer Lab',
                        'description'                => 'Greenwood Academy is requesting quotations for 50 high-durability laptops for our primary computer lab. Must include educational warranty.',
                        'currency_code'              => 'USD',
                        'budget_min'                 => 25000.00,
                        'budget_max'                 => 35000.00,
                        'allow_partial_quotation'    => true,
                        'allow_alternative_products' => true,
                        'quotation_deadline'         => now()->addDays(14),
                        'qna_deadline'               => now()->addDays(7),
                        'expected_delivery_date'     => now()->addDays(30),
                        'status'                     => 'awarded',
                        'published_at'               => now()->subDays(5),
                        'items_count'                => 1,
                        'quotations_count'           => 1,
                    ]
                );

                $rfq1Item = RfqItem::firstOrCreate(
                    ['rfq_id' => $rfq1->id, 'item_name' => 'Education Laptops (14" i5 16GB)'],
                    [
                        'item_type'            => 'product',
                        'category_id'          => $catMap['student-laptops-tablets'] ?? null,
                        'listing_id'           => $laptopListing->id,
                        'description'          => '14-inch laptops, min 16GB RAM, 512GB SSD, Windows 11 Pro Edu',
                        'quantity'             => 50.000,
                        'unit_id'              => $unitPc->id,
                        'estimated_unit_price' => 600.00,
                    ]
                );

                $qtn1 = Quotation::firstOrCreate(
                    ['quotation_number' => 'QTN-2026-001'],
                    [
                        'rfq_id'               => $rfq1->id,
                        'supplier_account_id'  => $supEdTech->id,
                        'submitted_by_user_id' => $userEdTech->id,
                        'rfq_version_no'       => 1,
                        'subtotal'             => 26000.00,
                        'grand_total'          => 26000.00,
                        'currency_code'        => 'USD',
                        'valid_until'          => now()->addDays(30),
                        'payment_terms'        => 'Net 30 after delivery',
                        'lead_time_days'       => 5,
                        'warranty_terms'       => '3-Year On-Site Educational Warranty Included',
                        'proposal'             => 'Special institutional tier pricing @ $520/unit. Free laser asset tagging and BIOS custom configuration included.',
                        'status'               => 'awarded',
                        'submitted_at'         => now()->subDays(4),
                    ]
                );

                QuotationItem::firstOrCreate(
                    ['quotation_id' => $qtn1->id, 'rfq_item_id' => $rfq1Item->id],
                    [
                        'offered_listing_id' => $laptopListing->id,
                        'is_alternative'     => false,
                        'item_name'          => 'Dell Latitude 3420 Education Laptop',
                        'quantity'           => 50.000,
                        'unit_id'            => $unitPc->id,
                        'unit_price'         => 520.00,
                        'line_total'         => 26000.00,
                    ]
                );

                $award1 = Award::firstOrCreate(
                    ['award_number' => 'AWD-2026-001'],
                    [
                        'rfq_id'               => $rfq1->id,
                        'quotation_id'         => $qtn1->id,
                        'buyer_account_id'    => $buyer1Account->id,
                        'supplier_account_id' => $supEdTech->id,
                        'awarded_by_user_id'   => $buyer1User->id,
                        'award_attempt_no'     => 1,
                        'status'               => 'accepted',
                        'response_deadline'    => now()->addDays(3),
                        'awarded_at'           => now()->subDays(3),
                        'responded_at'         => now()->subDays(2),
                        'accepted_at'          => now()->subDays(2),
                    ]
                );

                $po1 = PurchaseOrder::firstOrCreate(
                    ['po_number' => 'PO-2026-001'],
                    [
                        'award_id'            => $award1->id,
                        'rfq_id'              => $rfq1->id,
                        'quotation_id'        => $qtn1->id,
                        'buyer_account_id'    => $buyer1Account->id,
                        'supplier_account_id' => $supEdTech->id,
                        'created_by_user_id'  => $buyer1User->id,
                        'subtotal'            => 26000.00,
                        'grand_total'         => 26000.00,
                        'currency_code'       => 'USD',
                        'status'              => 'issued',
                        'issued_at'           => now()->subDays(1),
                    ]
                );

                PurchaseOrderItem::firstOrCreate(
                    ['purchase_order_id' => $po1->id, 'item_name' => 'Dell Latitude 3420 Education Laptop'],
                    [
                        'quantity'   => 50.000,
                        'unit_id'    => $unitPc->id,
                        'unit_price' => 520.00,
                        'line_total' => 26000.00,
                    ]
                );
            }

            // ── RFQ 2: Metro State University (Microscopes & Science Lab) ────
            if ($microListing) {
                $rfq2 = Rfq::firstOrCreate(
                    ['rfq_number' => 'RFQ-2026-002'],
                    [
                        'buyer_account_id'           => $buyer2Account->id,
                        'created_by_user_id'         => $buyer2User->id,
                        'visibility_type_id'         => $visibilityOpen->id,
                        'title'                      => 'Procurement of 25 High-Power Compound Microscopes for Biology Department',
                        'description'                => 'Metro State University Department of Life Sciences is procuring 25 binocular/trinocular microscopes for our histology and microbiology laboratory.',
                        'currency_code'              => 'USD',
                        'budget_min'                 => 5000.00,
                        'budget_max'                 => 8000.00,
                        'allow_partial_quotation'    => true,
                        'allow_alternative_products' => true,
                        'quotation_deadline'         => now()->addDays(20),
                        'qna_deadline'               => now()->addDays(10),
                        'expected_delivery_date'     => now()->addDays(40),
                        'status'                     => 'open',
                        'published_at'               => now()->subDays(2),
                        'items_count'                => 1,
                        'quotations_count'           => 1,
                    ]
                );

                $rfq2Item = RfqItem::firstOrCreate(
                    ['rfq_id' => $rfq2->id, 'item_name' => 'Binocular Compound Microscopes (40x-2500x)'],
                    [
                        'item_type'            => 'product',
                        'category_id'          => $catMap['microscopes-optics'] ?? null,
                        'listing_id'           => $microListing->id,
                        'description'          => 'LED illumination, Siedentopf 45-degree binocular head, 40X-2500X magnification range',
                        'quantity'             => 25.000,
                        'unit_id'              => $unitPc->id,
                        'estimated_unit_price' => 240.00,
                    ]
                );

                // Submitted Quotation by BioScience LLC
                $qtn2 = Quotation::firstOrCreate(
                    ['quotation_number' => 'QTN-2026-002'],
                    [
                        'rfq_id'               => $rfq2->id,
                        'supplier_account_id'  => $supScience->id,
                        'submitted_by_user_id' => $userScience->id,
                        'rfq_version_no'       => 1,
                        'subtotal'             => 5625.00,
                        'grand_total'          => 5625.00,
                        'currency_code'        => 'USD',
                        'valid_until'          => now()->addDays(30),
                        'payment_terms'        => 'Net 30',
                        'lead_time_days'       => 4,
                        'warranty_terms'       => '5-Year Manufacturer Optical Warranty Included',
                        'proposal'             => 'Discounted university price @ $225/unit. Includes free dust covers, immersion oil, and replacement LED bulbs.',
                        'status'               => 'submitted',
                        'submitted_at'         => now()->subDay(),
                    ]
                );

                QuotationItem::firstOrCreate(
                    ['quotation_id' => $qtn2->id, 'rfq_item_id' => $rfq2Item->id],
                    [
                        'offered_listing_id' => $microListing->id,
                        'is_alternative'     => false,
                        'item_name'          => 'AmScope B120C 40X-2500X LED Binocular Microscope',
                        'quantity'           => 25.000,
                        'unit_id'            => $unitPc->id,
                        'unit_price'         => 225.00,
                        'line_total'         => 5625.00,
                    ]
                );
            }

            // ── RFQ 3: Oakridge STEM District $\to$ Invited Furniture RFQ ────
            if ($deskListing) {
                $rfq3 = Rfq::firstOrCreate(
                    ['rfq_number' => 'RFQ-2026-003'],
                    [
                        'buyer_account_id'           => $buyer3Account->id,
                        'created_by_user_id'         => $buyer3User->id,
                        'visibility_type_id'         => $visibilityInvited->id,
                        'title'                      => 'Procurement of 120 Ergonomic Student Desk & Chair Sets for High School Wing',
                        'description'                => 'Seeking durable, adjustable-height student desks with high-pressure laminate tops and matching ergonomic cantilever chairs for 4 newly renovated classrooms.',
                        'currency_code'              => 'USD',
                        'budget_min'                 => 16000.00,
                        'budget_max'                 => 22000.00,
                        'allow_partial_quotation'    => false,
                        'allow_alternative_products' => true,
                        'quotation_deadline'         => now()->addDays(15),
                        'qna_deadline'               => now()->addDays(7),
                        'expected_delivery_date'     => now()->addDays(28),
                        'status'                     => 'open',
                        'published_at'               => now()->subDays(1),
                        'items_count'                => 1,
                        'quotations_count'           => 1,
                    ]
                );

                $rfq3Item = RfqItem::firstOrCreate(
                    ['rfq_id' => $rfq3->id, 'item_name' => 'Adjustable Student Desk & Chair Sets'],
                    [
                        'item_type'            => 'product',
                        'category_id'          => $catMap['student-desks'] ?? null,
                        'listing_id'           => $deskListing->id,
                        'description'          => 'Navy blue chairs with laminate natural oak tops',
                        'quantity'             => 120.000,
                        'unit_id'              => $unitSet->id,
                        'estimated_unit_price' => 150.00,
                    ]
                );

                // Quotation by Global School Furniture
                $qtn3 = Quotation::firstOrCreate(
                    ['quotation_number' => 'QTN-2026-003'],
                    [
                        'rfq_id'               => $rfq3->id,
                        'supplier_account_id'  => $supFurniture->id,
                        'submitted_by_user_id' => $userFurniture->id,
                        'rfq_version_no'       => 1,
                        'subtotal'             => 17400.00,
                        'grand_total'          => 17400.00,
                        'currency_code'        => 'USD',
                        'valid_until'          => now()->addDays(30),
                        'payment_terms'        => 'Net 45 upon completion of delivery and inspection',
                        'lead_time_days'       => 10,
                        'warranty_terms'       => '10-Year Lifetime Structural Frame Warranty',
                        'proposal'             => 'Volume discount applied @ $145/set. Includes white-glove unboxing and inside classroom placement.',
                        'status'               => 'submitted',
                        'submitted_at'         => now(),
                    ]
                );

                QuotationItem::firstOrCreate(
                    ['quotation_id' => $qtn3->id, 'rfq_item_id' => $rfq3Item->id],
                    [
                        'offered_listing_id' => $deskListing->id,
                        'is_alternative'     => false,
                        'item_name'          => 'Smith System Silhouette Student Desk & Chair Set',
                        'quantity'           => 120.000,
                        'unit_id'            => $unitSet->id,
                        'unit_price'         => 145.00,
                        'line_total'         => 17400.00,
                    ]
                );
            }

            // ── RFQ 4: Greenwood Academy $\to$ Open Marketplace RFQ (Robotics) ──
            if ($spikeListing) {
                $rfq4 = Rfq::firstOrCreate(
                    ['rfq_number' => 'RFQ-2026-004'],
                    [
                        'buyer_account_id'           => $buyer1Account->id,
                        'created_by_user_id'         => $buyer1User->id,
                        'visibility_type_id'         => $visibilityOpen->id,
                        'title'                      => 'Procurement of 15 LEGO SPIKE Prime STEAM Robotics Kits',
                        'description'                => 'Seeking 15 LEGO Education SPIKE Prime Core Sets for our after-school middle school robotics club competition team.',
                        'currency_code'              => 'USD',
                        'budget_min'                 => 5000.00,
                        'budget_max'                 => 6500.00,
                        'allow_partial_quotation'    => false,
                        'allow_alternative_products' => false,
                        'quotation_deadline'         => now()->addDays(18),
                        'qna_deadline'               => now()->addDays(9),
                        'expected_delivery_date'     => now()->addDays(25),
                        'status'                     => 'open',
                        'published_at'               => now(),
                        'items_count'                => 1,
                        'quotations_count'           => 0,
                    ]
                );

                RfqItem::firstOrCreate(
                    ['rfq_id' => $rfq4->id, 'item_name' => 'LEGO SPIKE Prime Core Set (45678)'],
                    [
                        'item_type'            => 'product',
                        'category_id'          => $catMap['stem-robotics'] ?? null,
                        'listing_id'           => $spikeListing->id,
                        'description'          => 'Authentic LEGO Education SPIKE Prime Core Sets with smart hubs and sensors',
                        'quantity'             => 15.000,
                        'unit_id'              => $unitSet->id,
                        'estimated_unit_price' => 380.00,
                    ]
                );
            }
        });
    }
}
