<?php

namespace App\Http\Controllers\Backend\Admin\Catalog;

use App\Http\Controllers\Backend\Admin\Concerns\InteractsWithAdmin;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Admin\Catalog\ListingTypeRequest;
use App\Models\ListingType;
use Illuminate\Http\Request;

class ListingTypeController extends Controller
{
    use InteractsWithAdmin;

    public function index(Request $request)
    {
        $this->authorize('platform.attributes.manage');

        $sortColumn = in_array($request->string('sort')->toString(), ['name', 'code', 'sort_order', 'created_at'])
            ? $request->string('sort')->toString()
            : 'sort_order';
        $direction = $request->string('direction')->toString() === 'asc' ? 'asc' : 'desc';

        $listingTypes = ListingType::query()
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = '%' . $request->string('search') . '%';
                $q->where('name', 'like', $s)->orWhere('code', 'like', $s)->orWhere('description', 'like', $s);
            })
            ->when($request->filled('status'), function ($q) use ($request) {
                $q->where('is_active', $request->string('status') === 'active');
            })
            ->withCount('listings')
            ->orderBy($sortColumn, $direction)
            ->paginate(20)
            ->withQueryString();

        return view('backend.admin.catalog.listing-types.index', [
            'listingTypes' => $listingTypes,
            'search'       => $request->string('search')->toString(),
            'status'       => $request->string('status')->toString(),
            'sort'         => $sortColumn,
            'direction'    => $direction,
        ]);
    }

    public function store(ListingTypeRequest $request)
    {
        $validated               = $request->validated();
        $validated['is_active']  = $request->boolean('is_active', true);
        $validated['sort_order'] = $request->integer('sort_order', 0);

        $listingType = ListingType::create($validated);

        activity('catalog')->causedBy($this->admin())->performedOn($listingType)->log('Listing type created');

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => "Listing type '{$listingType->name}' created."]);
        }

        return redirect()->route('admin.catalog.listing-types.index')->with('success', "Listing type '{$listingType->name}' created.");
    }

    public function update(ListingTypeRequest $request, ListingType $listingType)
    {
        $validated               = $request->validated();
        $validated['is_active']  = $request->boolean('is_active', true);
        $validated['sort_order'] = $request->integer('sort_order', 0);

        $listingType->update($validated);

        activity('catalog')->causedBy($this->admin())->performedOn($listingType)->log('Listing type updated');

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => "Listing type '{$listingType->name}' updated."]);
        }

        return redirect()->route('admin.catalog.listing-types.index')->with('success', "Listing type '{$listingType->name}' updated.");
    }

    public function destroy(ListingType $listingType)
    {
        $this->authorize('platform.attributes.manage');

        abort_if(
            $listingType->listings()->exists(),
            422,
            "Cannot delete '{$listingType->name}' — it is used by {$listingType->listings()->count()} listing(s)."
        );

        $listingType->delete();

        activity('catalog')->causedBy($this->admin())->log("Listing type '{$listingType->name}' deleted");

        return back()->with('success', "Listing type '{$listingType->name}' deleted.");
    }

    public function toggleActive(ListingType $listingType)
    {
        $this->authorize('platform.attributes.manage');

        $listingType->update(['is_active' => ! $listingType->is_active]);

        $state = $listingType->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Listing type '{$listingType->name}' {$state}.");
    }
}
