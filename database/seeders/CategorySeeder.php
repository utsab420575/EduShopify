<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeGroup;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\InputType;
use App\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // ── 1. Units of Measure ───────────────────────────────────────────
            $units = [
                ['name' => 'Piece', 'symbol' => 'pc', 'unit_type' => 'count'],
                ['name' => 'Set', 'symbol' => 'set', 'unit_type' => 'pack'],
                ['name' => 'Pack of 5', 'symbol' => 'pk-5', 'unit_type' => 'pack'],
                ['name' => 'Pack of 10', 'symbol' => 'pk-10', 'unit_type' => 'pack'],
                ['name' => 'Box', 'symbol' => 'box', 'unit_type' => 'pack'],
                ['name' => 'Carton', 'symbol' => 'ctn', 'unit_type' => 'pack'],
                ['name' => 'Kit', 'symbol' => 'kit', 'unit_type' => 'pack'],
                ['name' => 'Bundle', 'symbol' => 'bundle', 'unit_type' => 'pack'],
                ['name' => 'Pair', 'symbol' => 'pr', 'unit_type' => 'count'],
                ['name' => 'Meter', 'symbol' => 'm', 'unit_type' => 'length'],
                ['name' => 'Kilogram', 'symbol' => 'kg', 'unit_type' => 'weight'],
                ['name' => 'Liter', 'symbol' => 'L', 'unit_type' => 'volume'],
                ['name' => 'Hour', 'symbol' => 'hr', 'unit_type' => 'time'],
                ['name' => 'License / Seat', 'symbol' => 'lic', 'unit_type' => 'count'],
            ];

            foreach ($units as $u) {
                Unit::firstOrCreate(
                    ['name' => $u['name']],
                    [
                        'symbol'          => $u['symbol'],
                        'unit_type'       => $u['unit_type'],
                        'scope'           => 'global',
                        'approval_status' => 'approved',
                        'is_active'       => true,
                    ]
                );
            }

            // ── 2. Global Educational Brands ───────────────────────────────────
            $brands = [
                // EdTech & Electronics
                ['name' => 'Dell Education', 'slug' => 'dell-education', 'website' => 'https://dell.com/education', 'description' => 'Laptops, chromebooks and monitors built for education.'],
                ['name' => 'Promethean', 'slug' => 'promethean', 'website' => 'https://prometheanworld.com', 'description' => 'World-leading interactive flat panel displays and educational software.'],
                ['name' => 'SMART Technologies', 'slug' => 'smart-technologies', 'website' => 'https://smarttech.com', 'description' => 'Pioneer of interactive whiteboards and collaborative classroom technology.'],
                ['name' => 'HP School Solutions', 'slug' => 'hp-school-solutions', 'website' => 'https://hp.com/education', 'description' => 'Classroom workstations, laptops and printers.'],
                ['name' => 'Lenovo Campus', 'slug' => 'lenovo-campus', 'website' => 'https://lenovo.com/education', 'description' => 'Rugged laptops, 2-in-1s and tablets for students and teachers.'],
                ['name' => 'LEGO Education', 'slug' => 'lego-education', 'website' => 'https://education.lego.com', 'description' => 'Hands-on STEAM learning and robotics sets for K-12.'],
                ['name' => 'Epson Education', 'slug' => 'epson-education', 'website' => 'https://epson.com/education', 'description' => 'Ultra-short-throw projectors and document cameras.'],
                // Science & Lab
                ['name' => 'Fisher Scientific Ed', 'slug' => 'fisher-scientific-ed', 'website' => 'https://fishersci.com', 'description' => 'Lab glassware, chemicals, safety supplies and equipment.'],
                ['name' => 'Vernier Science', 'slug' => 'vernier-science', 'website' => 'https://vernier.com', 'description' => 'Precision probeware, data collection sensors and experiment packages.'],
                ['name' => 'AmScope Optics', 'slug' => 'amscope-optics', 'website' => 'https://amscope.com', 'description' => 'High-resolution compound, stereo and digital microscopes.'],
                ['name' => 'Eisco Scientific', 'slug' => 'eisco-scientific', 'website' => 'https://eiscolabs.com', 'description' => 'Physics, chemistry, and biology laboratory teaching apparatus.'],
                // Furniture
                ['name' => 'Smith System', 'slug' => 'smith-system', 'website' => 'https://smithsystem.com', 'description' => 'Agile, ergonomic classroom desks, tables and active student seating.'],
                ['name' => 'VS America', 'slug' => 'vs-america', 'website' => 'https://vsamerica.com', 'description' => 'Dynamic ergonomic furniture designed for modern flexible learning environments.'],
                ['name' => 'Herman Miller Learning', 'slug' => 'herman-miller-learning', 'website' => 'https://hermanmiller.com', 'description' => 'Ergonomic administrative and faculty office furniture.'],
                ['name' => 'Virco Manufacturing', 'slug' => 'virco-manufacturing', 'website' => 'https://virco.com', 'description' => 'Durable student desks, activity tables and cafeteria seating.'],
                // Books & Curriculum
                ['name' => 'Oxford University Press', 'slug' => 'oxford-university-press', 'website' => 'https://oup.com', 'description' => 'Academic textbooks, reading programs and bilingual materials.'],
                ['name' => 'Cambridge Learning', 'slug' => 'cambridge-learning', 'website' => 'https://cambridge.org', 'description' => 'International curriculum, STEM textbooks and assessment prep.'],
                ['name' => 'Scholastic Education', 'slug' => 'scholastic-education', 'website' => 'https://scholastic.com', 'description' => 'Guided reading libraries, early literacy sets and classroom magazines.'],
                // Sports & Arts
                ['name' => 'Spalding Sports', 'slug' => 'spalding-sports', 'website' => 'https://spalding.com', 'description' => 'Institutional basketball, volleyball and athletic equipment.'],
                ['name' => 'Gopher Sport', 'slug' => 'gopher-sport', 'website' => 'https://gophersport.com', 'description' => 'Gymnasium mats, PE equipment and fitness training gear.'],
                ['name' => 'Crayola Classpack', 'slug' => 'crayola-classpack', 'website' => 'https://crayola.com', 'description' => 'Bulk classroom art supplies, markers, crayons and modeling clay.'],
                ['name' => 'French Toast Uniforms', 'slug' => 'french-toast-uniforms', 'website' => 'https://frenchtoast.com', 'description' => 'Standard school uniform polos, pants, skirts and blazers.'],
            ];

            foreach ($brands as $b) {
                Brand::firstOrCreate(
                    ['slug' => $b['slug']],
                    [
                        'name'            => $b['name'],
                        'website'         => $b['website'] ?? null,
                        'description'     => $b['description'] ?? null,
                        'owner_type'      => 'global',
                        'approval_status' => 'approved',
                        'is_active'       => true,
                    ]
                );
            }

            // ── 3. Categories & Subcategories Hierarchy ────────────────────────
            $categoryTree = [
                [
                    'name'        => 'EdTech & Classroom Hardware',
                    'slug'        => 'edtech-hardware',
                    'type'        => 'product',
                    'icon'        => 'fa-laptop-code',
                    'description' => 'Interactive whiteboards, student laptops, tablets, projectors and STEM robotics.',
                    'sort_order'  => 1,
                    'children'    => [
                        ['name' => 'Interactive Displays & Smart Boards', 'slug' => 'interactive-displays', 'description' => '4K interactive touchscreens and smart boards with stylus support.'],
                        ['name' => 'Student Laptops & Tablets', 'slug' => 'student-laptops-tablets', 'description' => 'Ruggedized Chromebooks, Windows 11 laptops and education tablets.'],
                        ['name' => 'Classroom Projectors & Audio', 'slug' => 'projectors-audio', 'description' => 'Laser short-throw projectors, wireless sound systems and microphones.'],
                        ['name' => 'STEM & Educational Robotics', 'slug' => 'stem-robotics', 'description' => 'Programmable robotics kits, microcontrollers and coding peripherals.'],
                        ['name' => 'Document Cameras & Scanners', 'slug' => 'document-cameras', 'description' => 'High-resolution document cameras and digital microscope bridges.'],
                    ],
                ],
                [
                    'name'        => 'Science & Lab Equipment',
                    'slug'        => 'science-lab-equipment',
                    'type'        => 'product',
                    'icon'        => 'fa-flask-vial',
                    'description' => 'Microscopes, lab glassware, chemical apparatus, and physics/biology experiment kits.',
                    'sort_order'  => 2,
                    'children'    => [
                        ['name' => 'Microscopes & Optics', 'slug' => 'microscopes-optics', 'description' => 'Monocular, binocular and digital trinocular student and research microscopes.'],
                        ['name' => 'Lab Glassware & Plasticware', 'slug' => 'lab-glassware', 'description' => 'Beakers, flasks, graduated cylinders, test tubes and pipettes.'],
                        ['name' => 'Physics & Mechanics Kits', 'slug' => 'physics-mechanics-kits', 'description' => 'Sensors, pulleys, optics benches, wave generators and force apparatus.'],
                        ['name' => 'Chemistry Lab Kits & Reagents', 'slug' => 'chemistry-lab-kits', 'description' => 'Safe educational chemistry experiment sets, molecular models and test kits.'],
                        ['name' => 'Biology & Anatomy Models', 'slug' => 'biology-anatomy-models', 'description' => 'Dissection kits, prepared slides, torso models and skeletal replicas.'],
                    ],
                ],
                [
                    'name'        => 'Classroom & School Furniture',
                    'slug'        => 'classroom-furniture',
                    'type'        => 'product',
                    'icon'        => 'fa-chair',
                    'description' => 'Ergonomic student chairs, adjustable desks, storage cabinets and library shelving.',
                    'sort_order'  => 3,
                    'children'    => [
                        ['name' => 'Student Desks & Workstations', 'slug' => 'student-desks', 'description' => 'Single and collaborative group desks, adjustable height tables.'],
                        ['name' => 'Ergonomic Classroom Chairs', 'slug' => 'classroom-chairs', 'description' => 'Stackable polypropylene chairs, active wobble stools and task seating.'],
                        ['name' => 'Storage Cabinets & Charging Lockers', 'slug' => 'storage-cabinets', 'description' => 'Mobile device charging carts, cubbies and lockable steel cabinets.'],
                        ['name' => 'Whiteboards & Mobile Pinboards', 'slug' => 'whiteboards-pinboards', 'description' => 'Magnetic porcelain whiteboards, acoustic dividers and mobile easels.'],
                        ['name' => 'Library Shelving & Soft Seating', 'slug' => 'library-shelving', 'description' => 'Double-sided bookcases, beanbags, reading pods and modular soft benches.'],
                    ],
                ],
                [
                    'name'        => 'Books, Curriculum & Learning Materials',
                    'slug'        => 'curriculum-learning-materials',
                    'type'        => 'product',
                    'icon'        => 'fa-books',
                    'description' => 'Textbooks, guided reading libraries, teacher lesson planners and learning kits.',
                    'sort_order'  => 4,
                    'children'    => [
                        ['name' => 'STEM & Coding Textbooks', 'slug' => 'stem-textbooks', 'description' => 'Mathematics, science, computer science and engineering student coursebooks.'],
                        ['name' => 'Early Reader & Guided Literacy Sets', 'slug' => 'early-reader-sets', 'description' => 'Leveled reading libraries, phonics boxes and literature anthologies.'],
                        ['name' => 'Teacher Guides & Lesson Planners', 'slug' => 'teacher-guides', 'description' => 'Teacher editions, pedagogical resource kits and assessment binders.'],
                        ['name' => 'Educational Flashcards & Wall Charts', 'slug' => 'flashcards-charts', 'description' => 'Laminated reference maps, periodic tables and grammar visual aids.'],
                    ],
                ],
                [
                    'name'        => 'Sports & Physical Education',
                    'slug'        => 'sports-physical-education',
                    'type'        => 'product',
                    'icon'        => 'fa-volleyball',
                    'description' => 'Gymnasium mats, team sports balls, athletic training cones and fitness gear.',
                    'sort_order'  => 5,
                    'children'    => [
                        ['name' => 'Gymnasium & Safety Mats', 'slug' => 'gymnasium-mats', 'description' => 'Folding tumbling mats, landing pads and high-density wall padding.'],
                        ['name' => 'Team Sports Equipment', 'slug' => 'team-sports-equipment', 'description' => 'Basketballs, soccer balls, volleyball nets, cones and team pinnies.'],
                        ['name' => 'Track, Field & Fitness Training', 'slug' => 'fitness-training-gear', 'description' => 'Agility ladders, stopwatches, jump ropes and fitness station sets.'],
                    ],
                ],
                [
                    'name'        => 'Arts, Crafts & Music Supplies',
                    'slug'        => 'arts-crafts-music',
                    'type'        => 'product',
                    'icon'        => 'fa-palette',
                    'description' => 'Paint packs, drawing paper, sculpting clay and school musical instruments.',
                    'sort_order'  => 6,
                    'children'    => [
                        ['name' => 'Painting, Paper & Drawing Supplies', 'slug' => 'painting-drawing-supplies', 'description' => 'Washable tempera paint, watercolor sets, brushes and heavy drawing paper.'],
                        ['name' => 'Clay Modeling & Sculpting Kits', 'slug' => 'clay-sculpting-kits', 'description' => 'Air-dry clay, modeling dough, pottery sculpting tools and glaze.'],
                        ['name' => 'School Band & Percussion Instruments', 'slug' => 'school-instruments', 'description' => 'Glockenspiels, recorders, xylophones, hand percussion and keyboards.'],
                    ],
                ],
                [
                    'name'        => 'School Uniforms & Apparel',
                    'slug'        => 'school-uniforms-apparel',
                    'type'        => 'product',
                    'icon'        => 'fa-shirt',
                    'description' => 'Student polos, blazers, gym kits, lab coats and graduation attire.',
                    'sort_order'  => 7,
                    'children'    => [
                        ['name' => 'Student Polos, Shirts & Blazers', 'slug' => 'student-polos-blazers', 'description' => 'Cotton pique polos, oxford button-downs, embroidered school blazers.'],
                        ['name' => 'PE & Athletic Kits', 'slug' => 'pe-athletic-kits', 'description' => 'Breathable mesh sports jerseys, shorts, tracksuits and pinnies.'],
                        ['name' => 'Lab Coats & Safety Aprons', 'slug' => 'lab-coats-aprons', 'description' => '100% cotton flame-resistant lab coats, splash aprons and safety goggles.'],
                    ],
                ],
                [
                    'name'        => 'IT & Campus Maintenance Services',
                    'slug'        => 'it-maintenance-services',
                    'type'        => 'service',
                    'icon'        => 'fa-screwdriver-wrench',
                    'description' => 'Network deployment, audio-visual installation, hardware repairs and maintenance.',
                    'sort_order'  => 8,
                    'children'    => [
                        ['name' => 'AV & Smartboard Installation', 'slug' => 'av-installation-services', 'description' => 'White-glove wall mounting, calibration and wiring for interactive screens.'],
                        ['name' => 'Campus Wi-Fi & Network Setup', 'slug' => 'campus-network-setup', 'description' => 'Structured cabling, access point configuration and enterprise firewall.'],
                    ],
                ],
            ];

            $categoryMap = [];

            foreach ($categoryTree as $pCat) {
                $parent = Category::firstOrCreate(
                    ['slug' => $pCat['slug']],
                    [
                        'name'            => $pCat['name'],
                        'type'            => $pCat['type'] ?? 'product',
                        'icon'            => $pCat['icon'] ?? null,
                        'description'     => $pCat['description'] ?? null,
                        'approval_status' => 'approved',
                        'is_active'       => true,
                        'sort_order'      => $pCat['sort_order'] ?? 0,
                    ]
                );

                $categoryMap[$parent->slug] = $parent->id;

                if (!empty($pCat['children'])) {
                    foreach ($pCat['children'] as $idx => $cCat) {
                        $child = Category::firstOrCreate(
                            ['slug' => $cCat['slug']],
                            [
                                'parent_id'       => $parent->id,
                                'name'            => $cCat['name'],
                                'type'            => $pCat['type'] ?? 'product',
                                'description'     => $cCat['description'] ?? null,
                                'approval_status' => 'approved',
                                'is_active'       => true,
                                'sort_order'      => $idx + 1,
                            ]
                        );
                        $categoryMap[$child->slug] = $child->id;
                    }
                }
            }

            // ── 4. Attribute Groups ───────────────────────────────────────────
            $groups = [
                ['name' => 'General Information', 'slug' => 'general-information', 'description' => 'Brand, model, country of origin, and general product identifiers.'],
                ['name' => 'Technical Specifications', 'slug' => 'technical-specifications', 'description' => 'Screen size, processor, RAM, storage, OS, and connectivity specs.'],
                ['name' => 'Physical & Material Specifications', 'slug' => 'physical-material-specifications', 'description' => 'Frame material, surface finish, dimensions, weight, and color.'],
                ['name' => 'Science & Optics Specifications', 'slug' => 'science-optics-specifications', 'description' => 'Magnification, lens head type, glass composition, and autoclavability.'],
                ['name' => 'Educational & Age Suitability', 'slug' => 'educational-age-suitability', 'description' => 'Target grade level, curriculum alignment, language, and subject area.'],
                ['name' => 'Warranty & Safety Compliance', 'slug' => 'warranty-safety-compliance', 'description' => 'Warranty duration, testing certifications, and institutional safety standards.'],
            ];

            $groupMap = [];
            foreach ($groups as $g) {
                $group = AttributeGroup::firstOrCreate(
                    ['slug' => $g['slug']],
                    [
                        'name'        => $g['name'],
                        'description' => $g['description'],
                        'is_active'   => true,
                    ]
                );
                $groupMap[$g['slug']] = $group->id;
            }

            // Lookup Input Types
            $inputTypes = InputType::pluck('id', 'code')->toArray();

            // ── 5. Attributes & Attribute Values ──────────────────────────────
            $attributeDefs = [
                // General
                [
                    'group'      => 'general-information',
                    'name'       => 'Brand',
                    'slug'       => 'brand',
                    'input_type' => 'select',
                    'filterable' => true,
                    'required'   => true,
                    'values'     => ['Dell Education', 'Promethean', 'SMART Technologies', 'HP School Solutions', 'Lenovo Campus', 'LEGO Education', 'Fisher Scientific Ed', 'Vernier Science', 'AmScope Optics', 'Smith System', 'VS America', 'Oxford University Press', 'Cambridge Learning', 'Spalding Sports', 'Crayola Classpack', 'French Toast Uniforms', 'Generic / OEM'],
                ],
                [
                    'group'      => 'general-information',
                    'name'       => 'Model / Part Number',
                    'slug'       => 'model-part-number',
                    'input_type' => 'text',
                    'filterable' => true,
                    'required'   => false,
                    'values'     => [],
                ],
                [
                    'group'      => 'general-information',
                    'name'       => 'Country of Origin',
                    'slug'       => 'country-of-origin',
                    'input_type' => 'select',
                    'filterable' => true,
                    'required'   => false,
                    'values'     => ['United States', 'Germany', 'United Kingdom', 'Japan', 'South Korea', 'Taiwan', 'India', 'China', 'Vietnam'],
                ],

                // Technical Specs
                [
                    'group'      => 'technical-specifications',
                    'name'       => 'Screen Size',
                    'slug'       => 'screen-size',
                    'input_type' => 'select',
                    'filterable' => true,
                    'is_variant' => true,
                    'values'     => ['10.1" Tablet', '11.6" Convertible', '14.0" Standard', '15.6" Full HD', '65" Interactive 4K', '75" Interactive 4K', '86" Interactive 4K'],
                ],
                [
                    'group'      => 'technical-specifications',
                    'name'       => 'RAM Capacity',
                    'slug'       => 'ram-capacity',
                    'input_type' => 'select',
                    'filterable' => true,
                    'is_variant' => true,
                    'values'     => ['4GB LPDDR4', '8GB DDR4', '16GB DDR5', '32GB DDR5'],
                ],
                [
                    'group'      => 'technical-specifications',
                    'name'       => 'Storage Capacity',
                    'slug'       => 'storage-capacity',
                    'input_type' => 'select',
                    'filterable' => true,
                    'is_variant' => true,
                    'values'     => ['64GB eMMC', '128GB SSD', '256GB NVMe SSD', '512GB NVMe SSD', '1TB NVMe SSD'],
                ],
                [
                    'group'      => 'technical-specifications',
                    'name'       => 'Processor Type',
                    'slug'       => 'processor-type',
                    'input_type' => 'select',
                    'filterable' => true,
                    'values'     => ['Intel Core i3', 'Intel Core i5', 'Intel Core i7', 'AMD Ryzen 5', 'Apple M2 Chip', 'MediaTek Octa-Core', 'ARM Quad-Core Cortex'],
                ],
                [
                    'group'      => 'technical-specifications',
                    'name'       => 'Operating System',
                    'slug'       => 'operating-system',
                    'input_type' => 'select',
                    'filterable' => true,
                    'values'     => ['ChromeOS Education', 'Windows 11 Pro Education', 'iPadOS', 'Android 13 Enterprise', 'Ubuntu Linux Edu'],
                ],
                [
                    'group'      => 'technical-specifications',
                    'name'       => 'Touchscreen Enabled',
                    'slug'       => 'touchscreen-enabled',
                    'input_type' => 'boolean',
                    'filterable' => true,
                    'values'     => [],
                ],

                // Physical & Material
                [
                    'group'      => 'physical-material-specifications',
                    'name'       => 'Color',
                    'slug'       => 'color',
                    'input_type' => 'color',
                    'filterable' => true,
                    'is_variant' => true,
                    'values'     => [
                        ['value' => 'Navy Blue', 'slug' => 'navy-blue', 'color_hex' => '#1B365D'],
                        ['value' => 'Charcoal Grey', 'slug' => 'charcoal-grey', 'color_hex' => '#374151'],
                        ['value' => 'Forest Green', 'slug' => 'forest-green', 'color_hex' => '#1E4D2B'],
                        ['value' => 'Ruby Red', 'slug' => 'ruby-red', 'color_hex' => '#991B1B'],
                        ['value' => 'Natural Oak / Wood', 'slug' => 'natural-oak', 'color_hex' => '#D4A373'],
                        ['value' => 'Arctic White', 'slug' => 'arctic-white', 'color_hex' => '#F9FAFB'],
                        ['value' => 'Jet Black', 'slug' => 'jet-black', 'color_hex' => '#111827'],
                        ['value' => 'Sunshine Yellow', 'slug' => 'sunshine-yellow', 'color_hex' => '#FBBF24'],
                    ],
                ],
                [
                    'group'      => 'physical-material-specifications',
                    'name'       => 'Material',
                    'slug'       => 'material',
                    'input_type' => 'select',
                    'filterable' => true,
                    'values'     => ['High-Pressure Laminate & Steel', 'Solid Birch Hardwood', 'Polypropylene Resin', 'Reinforced Aluminum', 'Heavy-Duty Steel Sheet', '100% Organic Cotton', 'Borosilicate 3.3 Glass'],
                ],
                [
                    'group'      => 'physical-material-specifications',
                    'name'       => 'Height Adjustable',
                    'slug'       => 'height-adjustable',
                    'input_type' => 'boolean',
                    'filterable' => true,
                    'values'     => [],
                ],
                [
                    'group'      => 'physical-material-specifications',
                    'name'       => 'Apparel Size',
                    'slug'       => 'apparel-size',
                    'input_type' => 'select',
                    'filterable' => true,
                    'is_variant' => true,
                    'values'     => ['Youth Small (4-6)', 'Youth Medium (8-10)', 'Youth Large (12-14)', 'Adult Small', 'Adult Medium', 'Adult Large', 'Adult XL', 'Adult 2XL'],
                ],

                // Science & Optics
                [
                    'group'      => 'science-optics-specifications',
                    'name'       => 'Optical Magnification',
                    'slug'       => 'optical-magnification',
                    'input_type' => 'select',
                    'filterable' => true,
                    'values'     => ['40x - 400x Standard', '40x - 1000x Compound', '40x - 2500x High-Power Compound', '10x - 30x Stereo Zoom', '20x - 80x Stereo Zoom'],
                ],
                [
                    'group'      => 'science-optics-specifications',
                    'name'       => 'Optics Head Type',
                    'slug'       => 'optics-head-type',
                    'input_type' => 'select',
                    'filterable' => true,
                    'is_variant' => true,
                    'values'     => ['Monocular 360° Rotating', 'Binocular 45° Inclined', 'Trinocular with 5MP USB/HDMI Camera'],
                ],
                [
                    'group'      => 'science-optics-specifications',
                    'name'       => 'Autoclavable & Chemical Resistant',
                    'slug'       => 'autoclavable-chemical-resistant',
                    'input_type' => 'boolean',
                    'filterable' => true,
                    'values'     => [],
                ],

                // Educational Suitability
                [
                    'group'      => 'educational-age-suitability',
                    'name'       => 'Target Grade Level',
                    'slug'       => 'target-grade-level',
                    'input_type' => 'select',
                    'filterable' => true,
                    'values'     => ['Early Childhood (Pre-K to K)', 'Elementary School (Grades 1-5)', 'Middle School (Grades 6-8)', 'High School (Grades 9-12)', 'Higher Education / University', 'All Educational Levels'],
                ],
                [
                    'group'      => 'educational-age-suitability',
                    'name'       => 'Subject Area',
                    'slug'       => 'subject-area',
                    'input_type' => 'select',
                    'filterable' => true,
                    'values'     => ['Computer Science & Coding', 'Physics & Engineering', 'Chemistry & Earth Science', 'Biology & Life Science', 'Mathematics & Geometry', 'Language Arts & Literacy', 'Physical Education & Athletics', 'Fine Arts & Music'],
                ],
                [
                    'group'      => 'educational-age-suitability',
                    'name'       => 'Language',
                    'slug'       => 'language',
                    'input_type' => 'select',
                    'filterable' => true,
                    'values'     => ['English', 'Spanish', 'French', 'Bilingual English/Spanish', 'Multilingual'],
                ],

                // Warranty & Compliance
                [
                    'group'      => 'warranty-safety-compliance',
                    'name'       => 'Warranty Duration',
                    'slug'       => 'warranty-duration',
                    'input_type' => 'select',
                    'filterable' => true,
                    'values'     => ['1-Year Limited Warranty', '2-Year Advanced Replacement', '3-Year On-Site Educational', '5-Year Structural Frame', '10-Year Lifetime Structural'],
                ],
                [
                    'group'      => 'warranty-safety-compliance',
                    'name'       => 'Safety & Environmental Certifications',
                    'slug'       => 'safety-certifications',
                    'input_type' => 'multi_select',
                    'filterable' => true,
                    'values'     => ['CE Certified', 'FCC Class B', 'RoHS Compliant', 'GREENGUARD Gold Certified', 'ANSI/BIFMA X5.1 Standard', 'ASTM F963 Toy Safety', 'UL Listed'],
                ],
            ];

            $attributeMap = [];

            foreach ($attributeDefs as $aDef) {
                $groupId = $groupMap[$aDef['group']] ?? null;
                $inputCode = $aDef['input_type'];
                $inputTypeId = $inputTypes[$inputCode] ?? null;

                $attr = Attribute::firstOrCreate(
                    ['slug' => $aDef['slug']],
                    [
                        'attribute_group_id' => $groupId,
                        'name'               => $aDef['name'],
                        'input_type'         => $inputCode,
                        'input_type_id'      => $inputTypeId,
                        'allow_custom_value' => ($inputCode === 'color' || $inputCode === 'select'),
                        'is_filterable'      => $aDef['filterable'] ?? true,
                        'is_variant'         => $aDef['is_variant'] ?? false,
                        'is_required'        => $aDef['required'] ?? false,
                        'is_active'          => true,
                    ]
                );

                $attributeMap[$aDef['slug']] = $attr->id;

                if (!empty($aDef['values'])) {
                    foreach ($aDef['values'] as $valItem) {
                        if (is_array($valItem)) {
                            AttributeValue::firstOrCreate(
                                ['attribute_id' => $attr->id, 'slug' => $valItem['slug']],
                                [
                                    'value'      => $valItem['value'],
                                    'color_hex'  => $valItem['color_hex'] ?? null,
                                    'sort_order' => 0,
                                    'is_active'  => true,
                                ]
                            );
                        } else {
                            $vSlug = Str::slug($valItem);
                            AttributeValue::firstOrCreate(
                                ['attribute_id' => $attr->id, 'slug' => $vSlug],
                                [
                                    'value'      => $valItem,
                                    'sort_order' => 0,
                                    'is_active'  => true,
                                ]
                            );
                        }
                    }
                }
            }

            // ── 6. Category-to-Attribute Linkages ─────────────────────────────
            $categoryAttributeLinks = [
                // Laptops & Tablets
                'student-laptops-tablets' => [
                    'brand', 'model-part-number', 'target-grade-level', 'screen-size', 'ram-capacity',
                    'storage-capacity', 'processor-type', 'operating-system', 'touchscreen-enabled',
                    'color', 'warranty-duration', 'safety-certifications',
                ],
                // Interactive Displays
                'interactive-displays' => [
                    'brand', 'model-part-number', 'target-grade-level', 'screen-size', 'ram-capacity',
                    'storage-capacity', 'operating-system', 'touchscreen-enabled', 'warranty-duration',
                    'safety-certifications',
                ],
                // STEM Robotics
                'stem-robotics' => [
                    'brand', 'model-part-number', 'target-grade-level', 'subject-area', 'operating-system',
                    'warranty-duration', 'safety-certifications',
                ],
                // Projectors & Audio
                'projectors-audio' => [
                    'brand', 'model-part-number', 'target-grade-level', 'warranty-duration', 'safety-certifications',
                ],
                // Microscopes & Optics
                'microscopes-optics' => [
                    'brand', 'model-part-number', 'target-grade-level', 'subject-area', 'optical-magnification',
                    'optics-head-type', 'warranty-duration', 'safety-certifications',
                ],
                // Lab Glassware
                'lab-glassware' => [
                    'brand', 'model-part-number', 'target-grade-level', 'subject-area', 'material',
                    'autoclavable-chemical-resistant', 'safety-certifications',
                ],
                // Physics & Mechanics Kits
                'physics-mechanics-kits' => [
                    'brand', 'model-part-number', 'target-grade-level', 'subject-area', 'material',
                    'warranty-duration', 'safety-certifications',
                ],
                // Biology & Anatomy
                'biology-anatomy-models' => [
                    'brand', 'model-part-number', 'target-grade-level', 'subject-area', 'material',
                    'warranty-duration', 'safety-certifications',
                ],
                // Student Desks
                'student-desks' => [
                    'brand', 'model-part-number', 'target-grade-level', 'material', 'height-adjustable',
                    'color', 'warranty-duration', 'safety-certifications',
                ],
                // Classroom Chairs
                'classroom-chairs' => [
                    'brand', 'model-part-number', 'target-grade-level', 'material', 'height-adjustable',
                    'color', 'warranty-duration', 'safety-certifications',
                ],
                // Storage & Lockers
                'storage-cabinets' => [
                    'brand', 'model-part-number', 'material', 'color', 'warranty-duration', 'safety-certifications',
                ],
                // Whiteboards
                'whiteboards-pinboards' => [
                    'brand', 'model-part-number', 'material', 'height-adjustable', 'warranty-duration', 'safety-certifications',
                ],
                // STEM Textbooks
                'stem-textbooks' => [
                    'brand', 'target-grade-level', 'subject-area', 'language', 'country-of-origin',
                ],
                // Early Reader Sets
                'early-reader-sets' => [
                    'brand', 'target-grade-level', 'language', 'subject-area',
                ],
                // Team Sports
                'team-sports-equipment' => [
                    'brand', 'target-grade-level', 'material', 'color', 'warranty-duration',
                ],
                // Gym Mats
                'gymnasium-mats' => [
                    'brand', 'material', 'color', 'warranty-duration', 'safety-certifications',
                ],
                // Art & Painting
                'painting-drawing-supplies' => [
                    'brand', 'target-grade-level', 'color', 'safety-certifications',
                ],
                // Uniforms & Polos
                'student-polos-blazers' => [
                    'brand', 'target-grade-level', 'material', 'color', 'apparel-size', 'country-of-origin',
                ],
            ];

            foreach ($categoryAttributeLinks as $cSlug => $attrSlugs) {
                $catId = $categoryMap[$cSlug] ?? null;
                if (!$catId) continue;

                foreach ($attrSlugs as $idx => $aSlug) {
                    $attrId = $attributeMap[$aSlug] ?? null;
                    if (!$attrId) continue;

                    DB::table('category_attribute')->insertOrIgnore([
                        'category_id'   => $catId,
                        'attribute_id'  => $attrId,
                        'is_required'   => ($aSlug === 'brand'),
                        'is_filterable' => true,
                        'is_variant'    => in_array($aSlug, ['color', 'apparel-size', 'screen-size', 'ram-capacity', 'storage-capacity', 'optics-head-type']),
                        'sort_order'    => $idx + 1,
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]);
                }
            }
        });
    }
}
