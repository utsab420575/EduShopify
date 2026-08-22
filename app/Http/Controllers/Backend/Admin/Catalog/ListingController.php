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
            ->when($request->filled('type'), fn ($q) => $q->where('listing_type', $request->string('type')))
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

        $listing->load(['supplierAccount.supplierProfile', 'mainCategory', 'brand', 'unit', 'categories', 'attributeValues.attribute', 'variants', 'tierPrices']);

        return view('backend.admin.catalog.listings.show', ['listing' => $listing]);
    }

    public function approve(Listing $listing, ListingModerationService $service)
    {
        $this->authorize('platform.listings.moderate');

        try {
            $service->approve($listing, $this->admin());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('success', 'Listing approved and published.');
    }

    public function reject(ReasonRequest $request, Listing $listing, ListingModerationService $service)
    {
        $this->authorize('platform.listings.moderate');

        try {
            $service->reject($listing, $this->admin(), $request->string('reason'));
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('success', 'Listing rejected.');
    }

    public function deactivate(ReasonRequest $request, Listing $listing, ListingModerationService $service)
    {
        $this->authorize('platform.listings.moderate');

        $service->suspend($listing, $request->string('reason'));

        activity('moderation')->causedBy($this->admin())->performedOn($listing)
            ->withProperties(['reason' => $request->string('reason')])->log('Listing deactivated by admin');

        return back()->with('success', 'Listing deactivated.');
    }

    public function reactivate(Listing $listing)
    {
        $this->authorize('platform.listings.moderate');

        $listing->update(['is_active' => true]);

        activity('moderation')->causedBy($this->admin())->performedOn($listing)->log('Listing reactivated by admin');

        return back()->with('success', 'Listing reactivated.');
    }

    public function feature(Listing $listing)
    {
        $this->authorize('platform.listings.moderate');

        $listing->update(['is_featured' => ! $listing->is_featured]);

        return back()->with('success', $listing->is_featured ? 'Listing featured.' : 'Listing unfeatured.');
    }
}
