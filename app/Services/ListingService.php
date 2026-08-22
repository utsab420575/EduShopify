<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Listing;
use App\Models\ProductDetail;
use App\Models\ServiceDetail;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Admin moderation of the approval queue this feeds into isn't built yet
 * (no Filament ListingResource exists) — submitForApproval() moves a listing
 * to 'pending' and it stays there until that queue exists.
 */
class ListingService
{
    public function save(Account $supplierAccount, User $user, array $data, ?Listing $listing = null): Listing
    {
        return DB::transaction(function () use ($supplierAccount, $user, $data, $listing) {
            $attributes = [
                'listing_type'        => $data['listing_type'],
                'main_category_id'    => $data['main_category_id'] ?? null,
                'brand_id'            => $data['brand_id'] ?? null,
                'name'                => $data['name'],
                'sku'                 => $data['sku'] ?? null,
                'short_description'   => $data['short_description'] ?? null,
                'description'         => $data['description'] ?? null,
                'pricing_type'        => $data['pricing_type'],
                'sales_mode'          => $data['sales_mode'],
                'base_price'          => $data['base_price'] ?? null,
                'compare_at_price'    => $data['compare_at_price'] ?? null,
                'currency_code'       => $data['currency_code'] ?? null,
                'min_order_quantity'  => $data['min_order_quantity'] ?? null,
                'unit_id'             => $data['unit_id'] ?? null,
            ];

            if ($listing) {
                $listing->update($attributes);
            } else {
                $attributes['supplier_account_id'] = $supplierAccount->id;
                $attributes['created_by_user_id']  = $user->id;
                $attributes['listing_number']      = $this->generateListingNumber();
                $attributes['slug']                = $this->generateSlug($supplierAccount, $data['name']);
                $attributes['approval_status']     = 'draft';
                $attributes['is_active']           = true;
                $listing = Listing::create($attributes);
            }

            if ($data['listing_type'] === 'product') {
                ProductDetail::updateOrCreate(['listing_id' => $listing->id], [
                    'stock_status'    => $data['stock_status'] ?? 'on_request',
                    'stock_quantity'  => $data['stock_quantity'] ?? null,
                    'lead_time_days'  => $data['lead_time_days'] ?? null,
                    'warranty_terms'  => $data['warranty_terms'] ?? null,
                    'support_terms'   => $data['support_terms'] ?? null,
                ]);
            } else {
                ServiceDetail::updateOrCreate(['listing_id' => $listing->id], [
                    'service_mode'    => $data['service_mode'] ?? null,
                    'lead_time_days'  => $data['lead_time_days'] ?? null,
                    'service_terms'   => $data['service_terms'] ?? null,
                    'support_terms'   => $data['support_terms'] ?? null,
                ]);
            }

            $categoryIds = array_filter(array_map('intval', (array) ($data['category_ids'] ?? [])));
            if ($listing->main_category_id) {
                $categoryIds[] = $listing->main_category_id;
            }
            $categoryIds = array_unique($categoryIds);

            $sync = [];
            foreach ($categoryIds as $categoryId) {
                $sync[$categoryId] = ['is_primary' => $categoryId === $listing->main_category_id];
            }
            $listing->categories()->sync($sync);

            return $listing->fresh(['productDetail', 'serviceDetail', 'categories']);
        });
    }

    public function submitForApproval(Listing $listing): Listing
    {
        if (! in_array($listing->approval_status, ['draft', 'rejected'], true)) {
            throw ValidationException::withMessages(['status' => 'This listing is already submitted or approved.']);
        }

        $listing->update([
            'approval_status'  => 'pending',
            'rejection_reason' => null,
        ]);

        return $listing->fresh();
    }

    public function deactivate(Listing $listing): Listing
    {
        $listing->update(['is_active' => false]);

        return $listing;
    }

    public function reactivate(Listing $listing): Listing
    {
        $listing->update(['is_active' => true]);

        return $listing;
    }

    public function delete(Listing $listing): void
    {
        $listing->delete();
    }

    private function generateListingNumber(): string
    {
        $year   = date('Y');
        $latest = Listing::withTrashed()->where('listing_number', 'like', "LST-{$year}-%")->count();
        $seq    = str_pad($latest + 1, 6, '0', STR_PAD_LEFT);

        return "LST-{$year}-{$seq}";
    }

    private function generateSlug(Account $supplierAccount, string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i    = 2;

        while (Listing::where('supplier_account_id', $supplierAccount->id)->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
