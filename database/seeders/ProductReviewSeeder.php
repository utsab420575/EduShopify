<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Listing;
use App\Models\Review;
use Illuminate\Database\Seeder;

/**
 * Seeds published buyer reviews for product listings and calculates
 * the denormalized product_rating and product_reviews_count columns.
 * Idempotent: safe to re-run.
 */
class ProductReviewSeeder extends Seeder
{
    public function run(): void
    {
        $listings = Listing::where('approval_status', 'approved')
            ->where('is_active', true)
            ->get();

        if ($listings->isEmpty()) {
            $listings = Listing::all();
        }

        $buyerAccounts = Account::whereHas('buyerProfile')->get();

        if ($buyerAccounts->isEmpty()) {
            return;
        }

        $sampleReviews = [
            'laptop' => [
                [
                    'rating' => 5,
                    'comment' => 'We procured 35 units of this laptop for our school computer lab and STEM department. Excellent build quality, fast boot times, and great battery life throughout the school day.',
                ],
                [
                    'rating' => 5,
                    'comment' => 'Extremely reliable devices for faculty and students alike. The ruggedized chassis has held up very well to daily classroom use.',
                ],
                [
                    'rating' => 4,
                    'comment' => 'Solid performance and great value for institutional purchasing. Displays are crisp and customer support was responsive when we requested bulk deployment images.',
                ],
            ],
            'default' => [
                [
                    'rating' => 5,
                    'comment' => 'Outstanding quality and arrived on schedule. Fits our curriculum requirements perfectly and the students find it easy to use.',
                ],
                [
                    'rating' => 5,
                    'comment' => 'High quality build, well packaged, and supported by thorough documentation. Will definitely reorder for our upcoming academic term.',
                ],
                [
                    'rating' => 4,
                    'comment' => 'Very satisfied with this purchase. Pricing was competitive and delivery was smooth with all warranty certifications included.',
                ],
            ],
        ];

        foreach ($listings as $listing) {
            // Check if product already has reviews
            if (Review::where('listing_id', $listing->id)->product()->count() >= 2) {
                $this->syncListingRating($listing);
                continue;
            }

            // Pick buyers who are not the supplier of this listing
            $eligibleBuyers = $buyerAccounts->reject(fn ($b) => $b->id === $listing->supplier_account_id)->values();
            if ($eligibleBuyers->isEmpty()) {
                $eligibleBuyers = $buyerAccounts;
            }

            $isLaptop = str_contains(strtolower($listing->name . ' ' . $listing->slug), 'laptop');
            $reviewsPool = $isLaptop ? $sampleReviews['laptop'] : $sampleReviews['default'];

            $countToSeed = min(count($reviewsPool), $eligibleBuyers->count());
            $reviewers = $eligibleBuyers->random($countToSeed);

            foreach ($reviewers->values() as $index => $buyerAccount) {
                $user = $buyerAccount->users()->first();
                if (! $user) {
                    continue;
                }

                $sample = $reviewsPool[$index % count($reviewsPool)];

                Review::create([
                    'buyer_account_id'    => $buyerAccount->id,
                    'supplier_account_id' => $listing->supplier_account_id,
                    'listing_id'          => $listing->id,
                    'review_type'         => 'product',
                    'created_by_user_id'  => $user->id,
                    'review_context'      => 'purchase_experience',
                    'rating'              => $sample['rating'],
                    'comment'             => $sample['comment'],
                    'status'              => 'published',
                    'published_at'        => now()->subDays(rand(5, 120)),
                ]);
            }

            $this->syncListingRating($listing);
        }
    }

    private function syncListingRating(Listing $listing): void
    {
        $stats = Review::where('listing_id', $listing->id)
            ->product()
            ->where('status', 'published')
            ->selectRaw('COUNT(*) as cnt, AVG(rating) as avg_rating')
            ->first();

        $listing->update([
            'product_rating'        => round((float) ($stats->avg_rating ?? 0), 2),
            'product_reviews_count' => (int) ($stats->cnt ?? 0),
        ]);
    }
}
