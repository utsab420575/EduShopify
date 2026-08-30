<?php

namespace App\Http\Controllers\Backend\Admin\Catalog;

use App\Http\Controllers\Backend\Admin\Concerns\InteractsWithAdmin;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Admin\ReasonRequest;
use App\Models\Listing;
use App\Services\ListingModerationService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ListingController extends Controller
{
    use InteractsWithAdmin;

    public function index(Request $request)
    {
        $this->authorize('platform.listings.moderate');

        $listings = Listing::query()
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%'.$request->string('search').'%'))
            ->when($request->filled('status'), fn ($q) => $q->where('approval_status', $request->string('status')))
            ->when($request->filled('type'), fn ($q) => $q->whereHas('listingType', fn ($lq) => $lq->where('code', $request->string('type'))))
            ->with(['supplierAccount.supplierProfile', 'mainCategory'])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('backend.admin.catalog.listings.index', [
            'listings' => $listings,
            'search' => $request->string('search')->toString(),
            'status' => $request->string('status')->toString(),
            'type' => $request->string('type')->toString(),
        ]);
    }

    public function show(Listing $listing)
    {
        $this->authorize('platform.listings.moderate');

        return view('backend.admin.catalog.listings.show', $this->loadForReview($listing));
    }

    /**
     * Same review content as show(), rendered without the page layout so
     * the Approval Center's "Review" action can open it in a modal instead
     * of navigating away from the queue.
     */
    public function panel(Listing $listing)
    {
        $this->authorize('platform.listings.moderate');

        return view('backend.admin.catalog.listings._panel', $this->loadForReview($listing));
    }

    /**
     * Eager-loads and shapes everything the review view (and its panel
     * twin) need: the listing with its variants — each with its own
     * photos, not just the flat listing gallery — plus attribute values
     * grouped by specification group.
     */
    private function loadForReview(Listing $listing): array
    {
        $listing->load([
            'supplierAccount.supplierProfile.country',
            'mainCategory',
            'brand',
            'unit',
            'categories',
            'productDetail',
            'serviceDetail',
            'variants.variantAttributes.attribute',
            'variants.variantAttributes.attributeValue',
            'variants.images',
            'variants.tierPrices',
            'allTierPrices',
            'media',
            'approvedBy',
            'attributeValues.attribute.attributeGroup',
            'attributeValues.attribute.unit',
            'attributeValues.attributeValue',
            'changeLogs.changedBy',
        ]);

        // Group listing attribute values by AttributeGroup
        $groupedSpecifications = $listing->attributeValues
            ->groupBy(fn ($val) => $val->attribute?->attribute_group_id ?? 0)
            ->map(function ($items, $groupId) {
                $group = $groupId > 0 ? $items->first()->attribute?->attributeGroup : null;
                return [
                    'group_id'   => $groupId,
                    'group_name' => $group?->name ?? 'General Specifications',
                    'sort_order' => $group?->sort_order ?? 999,
                    'items'      => $items,
                ];
            })
            ->sortBy('sort_order')
            ->values();

        return ['listing' => $listing, 'groupedSpecifications' => $groupedSpecifications];
    }

    public function approve(Request $request, Listing $listing, ListingModerationService $service)
    {
        $this->authorize('platform.listings.moderate');

        try {
            $service->approve($listing, $this->admin());
        } catch (ValidationException $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $this->firstValidationMessage($e)], 422);
            }

            return back()->withErrors($e->errors());
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Listing approved and published.', 'resolved' => true]);
        }

        return back()->with('success', 'Listing approved and published.');
    }

    public function undoApprove(Request $request, Listing $listing, ListingModerationService $service)
    {
        $this->authorize('platform.listings.moderate');

        try {
            $service->undoApprove($listing, $this->admin());
        } catch (ValidationException $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $this->firstValidationMessage($e)], 422);
            }

            return back()->withErrors($e->errors());
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Listing approval reverted to pending review.', 'resolved' => false]);
        }

        return back()->with('success', 'Listing approval reverted. Listing has been returned to Pending Review.');
    }

    public function reject(ReasonRequest $request, Listing $listing, ListingModerationService $service)
    {
        $this->authorize('platform.listings.moderate');

        try {
            $service->reject($listing, $this->admin(), $request->string('reason'));
        } catch (ValidationException $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $this->firstValidationMessage($e)], 422);
            }

            return back()->withErrors($e->errors());
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Listing rejected.', 'resolved' => true]);
        }

        return back()->with('success', 'Listing rejected.');
    }

    public function deactivate(ReasonRequest $request, Listing $listing, ListingModerationService $service)
    {
        $this->authorize('platform.listings.moderate');

        $service->suspend($listing, $request->string('reason'));

        activity('moderation')->causedBy($this->admin())->performedOn($listing)
            ->withProperties(['reason' => $request->string('reason')])->log('Listing deactivated by admin');

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Listing deactivated.', 'resolved' => false]);
        }

        return back()->with('success', 'Listing deactivated.');
    }

    public function reactivate(Request $request, Listing $listing)
    {
        $this->authorize('platform.listings.moderate');

        $listing->update(['is_active' => true]);

        activity('moderation')->causedBy($this->admin())->performedOn($listing)->log('Listing reactivated by admin');

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Listing reactivated.', 'resolved' => false]);
        }

        return back()->with('success', 'Listing reactivated.');
    }

    public function feature(Request $request, Listing $listing)
    {
        $this->authorize('platform.listings.moderate');

        $listing->update(['is_featured' => ! $listing->is_featured]);

        $message = $listing->is_featured ? 'Listing featured.' : 'Listing unfeatured.';

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $message, 'resolved' => false]);
        }

        return back()->with('success', $message);
    }

    private function firstValidationMessage(ValidationException $e): string
    {
        return collect($e->errors())->flatten()->first() ?? $e->getMessage();
    }
}
