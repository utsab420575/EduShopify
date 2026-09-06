<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Achievement;
use App\Models\Certification;
use App\Models\Review;
use App\Models\SupplierProfile;
use Illuminate\Database\Seeder;

/**
 * Demo content for the supplier profile page's "real data" sections
 * (reviews, certifications, achievement badges) so pages that previously
 * showed static config-driven filler have something real to render while
 * suppliers/buyers haven't submitted their own yet. Idempotent — safe to
 * re-run: every write is guarded against what's already there.
 */
class SupplierDemoContentSeeder extends Seeder
{
    public function run(): void
    {
        $supplierAccounts = SupplierProfile::with('account')->get()->pluck('account')->filter();
        $buyerAccountIds = Account::whereHas('buyerProfile')->pluck('id');
        $achievementIds = Achievement::pluck('id');

        if ($supplierAccounts->isEmpty()) {
            return;
        }

        foreach ($supplierAccounts as $account) {
            $this->seedCertifications($account);
            $this->seedAchievements($account, $achievementIds);
            $this->seedReviews($account, $buyerAccountIds->reject(fn ($id) => $id === $account->id)->values());
        }
    }

    private function seedCertifications(Account $account): void
    {
        if ($account->certifications()->count() >= 2) {
            return;
        }

        $catalog = [
            [
                'certification_name' => 'ISO 9001:2015',
                'certification_title' => 'Quality Management System',
                'certification_description' => 'Certified for maintaining a consistent, audited quality-management process across manufacturing and fulfillment.',
                'certification_date' => now()->subYears(3),
                'expiry_date' => now()->addYears(2),
            ],
            [
                'certification_name' => 'CE Marking',
                'certification_title' => 'European Conformity',
                'certification_description' => 'Products meet EU health, safety, and environmental protection requirements.',
                'certification_date' => now()->subYears(2),
                'expiry_date' => now()->addYears(3),
            ],
        ];

        foreach ($catalog as $cert) {
            Certification::firstOrCreate(
                ['account_id' => $account->id, 'certification_name' => $cert['certification_name']],
                $cert + ['status' => 'approved']
            );
        }
    }

    private function seedAchievements(Account $account, $achievementIds): void
    {
        if ($achievementIds->isEmpty()) {
            return;
        }

        if ($account->accountAchievements()->approved()->count() > 0) {
            return;
        }

        foreach ($achievementIds->random(min(3, $achievementIds->count())) as $achievementId) {
            $account->accountAchievements()->firstOrCreate(
                ['achievement_id' => $achievementId],
                [
                    'status' => 'approved',
                    'requested_at' => now()->subMonths(rand(1, 12)),
                    'reviewed_at' => now()->subMonths(rand(0, 1)),
                    'earned_at' => now()->subMonths(rand(0, 6)),
                ]
            );
        }
    }

    private function seedReviews(Account $supplierAccount, $buyerAccountIds): void
    {
        if ($buyerAccountIds->isEmpty()) {
            return;
        }

        if (Review::where('supplier_account_id', $supplierAccount->id)->supplier()->count() >= 2) {
            return;
        }

        $samples = [
            ['rating' => 5, 'comment' => 'Excellent communication throughout the RFQ process and the order arrived exactly as specified. Would order again.'],
            ['rating' => 4, 'comment' => 'Good quality products and fair pricing. Delivery took a little longer than expected but support kept us updated.'],
            ['rating' => 5, 'comment' => 'One of the most responsive suppliers we have worked with. Highly recommended for institutional procurement.'],
        ];

        $reviewers = $buyerAccountIds->random(min(3, $buyerAccountIds->count()));

        foreach ($reviewers->values() as $index => $buyerAccountId) {
            $buyerAccount = Account::find($buyerAccountId);
            $user = $buyerAccount?->users()->first();

            if (! $user) {
                continue;
            }

            $sample = $samples[$index % count($samples)];

            Review::create([
                'buyer_account_id' => $buyerAccountId,
                'supplier_account_id' => $supplierAccount->id,
                'review_type' => 'supplier',
                'created_by_user_id' => $user->id,
                'review_context' => 'quotation_experience',
                'rating' => $sample['rating'],
                'comment' => $sample['comment'],
                'status' => 'published',
                'published_at' => now()->subDays(rand(5, 180)),
            ]);
        }

        $stats = Review::where('supplier_account_id', $supplierAccount->id)
            ->supplier()
            ->where('status', 'published')
            ->selectRaw('COUNT(*) as cnt, AVG(rating) as avg_rating')
            ->first();

        SupplierProfile::where('account_id', $supplierAccount->id)->update([
            'rating'        => round((float) ($stats->avg_rating ?? 0), 2),
            'reviews_count' => (int) ($stats->cnt ?? 0),
        ]);
    }
}
