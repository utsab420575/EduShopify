<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\BlogCategory;
use App\Models\BlogComment;
use App\Models\BlogCommentLike;
use App\Models\BlogPost;
use App\Models\BlogPostImage;
use App\Models\BlogPostLike;
use App\Models\BlogTag;
use App\Models\SupplierProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BlogSeeder extends Seeder
{
    public function run(): void
    {
        $adminUser = User::whereHas('roles', fn ($q) => $q->where('name', 'admin'))->first() ?? User::first();
        $supplierAccounts = SupplierProfile::with('account')->get()->pluck('account')->filter();
        $buyerAccounts = Account::whereHas('buyerProfile')->get();

        $defaultAccount = $supplierAccounts->first() ?? Account::first();
        if (! $defaultAccount) {
            return;
        }

        // 1. Seed Categories
        $categoriesData = [
            [
                'name'        => 'Procurement & Tenders',
                'slug'        => 'procurement-and-tenders',
                'description' => 'Best practices, compliance guidelines, and procurement frameworks for educational institutions.',
                'icon'        => 'fa-solid fa-file-invoice-dollar',
                'sort_order'  => 1,
            ],
            [
                'name'        => 'EdTech & Classroom Innovation',
                'slug'        => 'edtech-and-classroom-innovation',
                'description' => 'Emerging digital classroom tools, interactive displays, and educational software.',
                'icon'        => 'fa-solid fa-laptop-code',
                'sort_order'  => 2,
            ],
            [
                'name'        => 'STEM & Laboratory Supplies',
                'slug'        => 'stem-and-laboratory-supplies',
                'description' => 'Sourcing curriculum-aligned science lab equipment, robotics kits, and maker tools.',
                'icon'        => 'fa-solid fa-flask-vial',
                'sort_order'  => 3,
            ],
            [
                'name'        => 'School Furniture & Facilities',
                'slug'        => 'school-furniture-and-facilities',
                'description' => 'Ergonomic classroom layouts, library furniture, and sustainable campus architecture.',
                'icon'        => 'fa-solid fa-chair',
                'sort_order'  => 4,
            ],
            [
                'name'        => 'Marketplace News & Insights',
                'slug'        => 'marketplace-news-and-insights',
                'description' => 'Updates, trade events, and success stories across the global education supply chain.',
                'icon'        => 'fa-solid fa-newspaper',
                'sort_order'  => 5,
            ],
        ];

        $categories = collect();
        foreach ($categoriesData as $catData) {
            $categories->push(BlogCategory::firstOrCreate(
                ['slug' => $catData['slug']],
                $catData
            ));
        }

        // 2. Seed Tags
        $tagNames = [
            'EdTech', 'Procurement', 'Classroom Design', 'STEM',
            'Sustainability', 'Robotics', 'School Lab', 'Budgeting',
            'Digital Learning', 'Global Supply',
        ];

        $tags = collect();
        foreach ($tagNames as $tagName) {
            $tags->push(BlogTag::firstOrCreate(
                ['slug' => Str::slug($tagName)],
                ['name' => $tagName]
            ));
        }

        // 3. Seed Posts
        $postsData = [
            [
                'title'                 => 'How Modern Schools Are Streamlining B2B Procurement with Digital RFQs',
                'slug'                  => 'how-modern-schools-are-streamlining-b2b-procurement-with-digital-rfqs',
                'category_slug'         => 'procurement-and-tenders',
                'excerpt'               => 'Discover how school districts and universities are reducing procurement lead times by up to 40% using verified digital RFQ marketplaces.',
                'content'               => "Institutional procurement in education has traditionally been bogged down by fragmented spreadsheets, manual email chains, and opaque pricing comparisons. As institutional budgets face closer scrutiny, digital procurement marketplaces are transforming how educational buyers find and award contracts.\n\n### The Shift from Paper to Platform\nDigital RFQs allow procurement managers to define precise technical criteria — such as EN71 safety compliance, voltage standards, and institutional warranty requirements — and receive structured, line-item bids from pre-vetted suppliers.\n\n### Transparency and Compliance\nKey advantages of digitized tender management include:\n- **Audit-ready trails**: Every clarification, revision, and quote acceptance is timestamped.\n- **Direct supplier communication**: Eliminates intermediary markups and miscommunications.\n- **Smarter budgeting**: Bulk tier pricing unlocks immediate volume discounts for unified districts.\n\nBy centralizing procurement, institutions not only save tens of thousands in operating costs but also ensure their students get certified materials on time.",
                'cover_image'           => '/images/herosection.png',
                'featured'              => true,
                'status'                => 'approved',
                'reading_time_minutes'  => 5,
                'views_count'           => 1420,
                'published_at'          => now()->subDays(12),
                'tag_slugs'             => ['procurement', 'budgeting', 'global-supply'],
            ],
            [
                'title'                 => 'The Complete Guide to Sourcing STEM & Robotics Kits for K-12 Classrooms',
                'slug'                  => 'the-complete-guide-to-sourcing-stem-robotics-kits-for-k-12-classrooms',
                'category_slug'         => 'stem-and-laboratory-supplies',
                'excerpt'               => 'A comprehensive buyer guide for educators looking to equip robotics and maker labs with modular, curriculum-aligned hardware.',
                'content'               => "Integrating robotics into elementary and secondary education is no longer an extracurricular luxury — it is a core foundational skill. However, outfitting a 30-student lab presents unique procurement hurdles.\n\n### 1. Durability and Modular Spare Parts\nStudent kits will be dropped, disassembled, and pushed to their limits. Look for suppliers who provide modular replacement packs (sensors, motor cables, gears) so an entire kit is not rendered useless by a single misplaced component.\n\n### 2. Software Agnosticism and Longevity\nAvoid hardware locked into proprietary subscription software. Prioritize kits that support open-source programming standards like Scratch, Blockly, and Python.\n\n### 3. Safety and Batch Testing\nEnsure all kits carry CE, RoHS, and FCC compliance certificates before placing district-level purchase orders.",
                'cover_image'           => '/images/herosection.png',
                'featured'              => true,
                'status'                => 'approved',
                'reading_time_minutes'  => 6,
                'views_count'           => 890,
                'published_at'          => now()->subDays(18),
                'tag_slugs'             => ['stem', 'robotics', 'classroom-design'],
            ],
            [
                'title'                 => 'Ergonomic Classroom Furniture: Improving Student Focus and Posture',
                'slug'                  => 'ergonomic-classroom-furniture-improving-student-focus-and-posture',
                'category_slug'         => 'school-furniture-and-facilities',
                'excerpt'               => 'Why active seating, adjustable height desks, and flexible learning layouts are essential for contemporary collaborative learning spaces.',
                'content'               => "Traditional static rows of wooden desks are rapidly being replaced by dynamic, modular learning environments designed to support collaborative projects and varied student body types.\n\n### Active Seating in Modern Pedagogy\nStudies show that flexible seating options allow students to channel restlessness into subtle physical motion, noticeably improving cognitive engagement and retention during lectures.\n\nKey procurement considerations include:\n- High-density polyethylene surfaces that resist staining and harsh disinfectant chemicals.\n- Silent casters that allow teachers to reconfigure classrooms into small pods within two minutes.\n- BIFMA compliance ensuring structural integrity over a 10-year lifespan.",
                'cover_image'           => '/images/herosection.png',
                'featured'              => false,
                'status'                => 'approved',
                'reading_time_minutes'  => 4,
                'views_count'           => 620,
                'published_at'          => now()->subDays(25),
                'tag_slugs'             => ['classroom-design', 'sustainability'],
            ],
            [
                'title'                 => '5 Common Pitfalls in Educational Science Lab Equipment Procurement',
                'slug'                  => '5-common-pitfalls-in-educational-science-lab-equipment-procurement',
                'category_slug'         => 'stem-and-laboratory-supplies',
                'excerpt'               => 'From regional voltage compatibility to hazardous material compliance, here are five crucial factors to review before issuing purchase orders.',
                'content'               => "Procuring laboratory instrumentation — from spectrophotometers and fume hoods to precision balances — involves stringent technical parameters that standard procurement officers may inadvertently overlook.\n\n1. **Overlooking Voltage and Plug Standards**: Sourcing international lab equipment without specifying regional 220V vs 110V specs causes costly retrofits.\n2. **Ignoring Calibration Requirements**: Precision balances and probes require factory calibration certificates (ISO/IEC 17025) for high-school or collegiate accreditation.\n3. **Consumable Lock-In**: Verify whether proprietary reagent cartridges or cuvettes are required, or if standard glassware can be utilized.\n4. **Chemical Storage Compatibility**: Flammable storage cabinets must meet OSHA 1910 and NFPA 30 codes.\n5. **Warranty & Local Service SLAs**: Clarify whether on-site technician servicing is guaranteed during the initial 24 months.",
                'cover_image'           => '/images/herosection.png',
                'featured'              => false,
                'status'                => 'approved',
                'reading_time_minutes'  => 5,
                'views_count'           => 410,
                'published_at'          => now()->subDays(34),
                'tag_slugs'             => ['school-lab', 'procurement'],
            ],
            [
                'title'                 => 'Interactive Flat Panels vs Projectors: Total Cost of Ownership Analysis',
                'slug'                  => 'interactive-flat-panels-vs-projectors-total-cost-of-ownership-analysis',
                'category_slug'         => 'edtech-and-classroom-innovation',
                'excerpt'               => 'An in-depth total cost of ownership (TCO) breakdown comparing lamp-based classroom projection systems with 4K touch displays.',
                'content'               => "When equipping 50 classrooms across an entire campus, deciding between 4K Interactive Flat Panels (IFPs) and traditional ultra-short-throw projectors is a multi-million-dollar decision.\n\n### The Direct Cost Breakdown\nWhile projectors frequently boast lower initial sticker prices, their hidden costs compound quickly:\n- **Lamp & Filter Replacements**: Projector bulbs require periodic replacements every 3,000–5,000 operating hours.\n- **Ambient Light Performance**: Projectors require dedicated blackout curtains to maintain contrast, whereas anti-glare 450-nit IFPs operate seamlessly in daylight.\n- **Touch Latency & Collaboration**: Modern multi-touch IFPs allow 40 simultaneous touch points, making interactive group work engaging and immediate without calibration drifts.",
                'cover_image'           => '/images/herosection.png',
                'featured'              => false,
                'status'                => 'approved',
                'reading_time_minutes'  => 7,
                'views_count'           => 1180,
                'published_at'          => now()->subDays(42),
                'tag_slugs'             => ['edtech', 'digital-learning'],
            ],
            [
                'title'                 => 'Sustainable Campus Initiatives: Transitioning to Eco-Friendly Consumables',
                'slug'                  => 'sustainable-campus-initiatives-transitioning-to-eco-friendly-consumables',
                'category_slug'         => 'procurement-and-tenders',
                'excerpt'               => 'How institutional buyers are prioritizing recycled paper, biodegradable stationery, and energy-efficient electronics.',
                'content'               => "School districts globally are enacting green purchasing resolutions. This article explores how volume buyers evaluate lifecycle impact assessments when sourcing classroom consumables.",
                'cover_image'           => '/images/herosection.png',
                'featured'              => false,
                'status'                => 'pending',
                'reading_time_minutes'  => 4,
                'views_count'           => 0,
                'published_at'          => null,
                'tag_slugs'             => ['sustainability', 'procurement'],
            ],
        ];

        $sampleComments = [
            'Very insightful article! We recently completed our district tender using this exact criteria and cut our turnaround time in half.',
            'Could you elaborate on the warranty terms typically negotiated with overseas manufacturers?',
            'The points on consumable lock-in are spot on. We were burned by proprietary supplies last year.',
            'Great overview. The shift to active classroom seating has made a noticeable difference in our middle school labs.',
        ];

        $sampleReplies = [
            'Thanks for sharing your experience! We recommend always including a 3-year minimum parts availability clause in the RFQ specifications.',
            'Completely agree. Standardizing on open consumables should be mandatory across all public school tenders.',
        ];

        foreach ($postsData as $index => $data) {
            $cat = $categories->firstWhere('slug', $data['category_slug']) ?? $categories->first();
            $authorAccount = $supplierAccounts->isNotEmpty()
                ? $supplierAccounts[$index % $supplierAccounts->count()]
                : $defaultAccount;

            $authorUser = $authorAccount->users()->first() ?? $adminUser;

            $post = BlogPost::firstOrCreate(
                ['slug' => $data['slug']],
                [
                    'account_id'           => $authorAccount->id,
                    'created_by_user_id'   => $authorUser?->id,
                    'category_id'          => $cat->id,
                    'title'                => $data['title'],
                    'excerpt'              => $data['excerpt'],
                    'content'              => $data['content'],
                    'cover_image'          => $data['cover_image'],
                    'featured'             => $data['featured'],
                    'status'               => $data['status'],
                    'approved_by_user_id'  => $data['status'] === 'approved' ? $adminUser?->id : null,
                    'approved_at'          => $data['status'] === 'approved' ? $data['published_at'] : null,
                    'published_at'         => $data['published_at'],
                    'reading_time_minutes' => $data['reading_time_minutes'],
                    'views_count'          => $data['views_count'],
                    'meta_title'           => $data['title'],
                    'meta_description'     => $data['excerpt'],
                    'meta_keywords'        => implode(', ', $data['tag_slugs']),
                ]
            );

            // Attach Tags
            $tagIds = $tags->whereIn('slug', $data['tag_slugs'])->pluck('id');
            $post->tags()->syncWithoutDetaching($tagIds);

            // Add Post Images
            if ($post->images()->count() === 0) {
                BlogPostImage::create([
                    'blog_post_id' => $post->id,
                    'image_path'   => '/images/herosection.png',
                    'caption'      => 'High-resolution diagram illustrating key workflow and specifications.',
                    'sort_order'   => 1,
                ]);
            }

            // Only add comments & likes for approved posts
            if ($data['status'] === 'approved') {
                // Post Likes
                $likingAccounts = $buyerAccounts->isNotEmpty()
                    ? $buyerAccounts->take(3)
                    : collect([$authorAccount]);

                foreach ($likingAccounts as $liker) {
                    BlogPostLike::firstOrCreate([
                        'blog_post_id' => $post->id,
                        'account_id'   => $liker->id,
                    ]);
                }

                // Post Comments
                if ($post->comments()->count() === 0 && $buyerAccounts->isNotEmpty()) {
                    $commentingBuyer = $buyerAccounts[$index % $buyerAccounts->count()];
                    $commentUser = $commentingBuyer->users()->first() ?? $adminUser;

                    $comment = BlogComment::create([
                        'blog_post_id'        => $post->id,
                        'account_id'          => $commentingBuyer->id,
                        'created_by_user_id'  => $commentUser?->id,
                        'content'             => $sampleComments[$index % count($sampleComments)],
                        'status'              => 'approved',
                        'approved_by_user_id' => $adminUser?->id,
                        'approved_at'         => now()->subDays(max(1, rand(2, 8))),
                        'likes_count'         => 2,
                    ]);

                    // Comment Like
                    BlogCommentLike::firstOrCreate([
                        'blog_comment_id' => $comment->id,
                        'account_id'      => $authorAccount->id,
                    ]);

                    // Supplier Reply to Comment
                    $reply = BlogComment::create([
                        'blog_post_id'        => $post->id,
                        'account_id'          => $authorAccount->id,
                        'created_by_user_id'  => $authorUser?->id,
                        'parent_id'           => $comment->id,
                        'content'             => $sampleReplies[$index % count($sampleReplies)],
                        'status'              => 'approved',
                        'approved_by_user_id' => $adminUser?->id,
                        'approved_at'         => now()->subDays(1),
                        'likes_count'         => 1,
                    ]);

                    BlogCommentLike::firstOrCreate([
                        'blog_comment_id' => $reply->id,
                        'account_id'      => $commentingBuyer->id,
                    ]);
                }

                // Update denormalized likes and comments counters
                $post->update([
                    'likes_count'    => $post->likes()->count(),
                    'comments_count' => $post->comments()->where('status', 'approved')->count(),
                ]);
            }
        }
    }
}
