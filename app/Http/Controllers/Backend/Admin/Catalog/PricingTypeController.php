<?php

namespace App\Http\Controllers\Backend\Admin\Catalog;

use App\Http\Controllers\Backend\Admin\Concerns\InteractsWithAdmin;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Admin\Catalog\PricingTypeRequest;
use App\Models\PricingType;
use Illuminate\Http\Request;

class PricingTypeController extends Controller
{
    use InteractsWithAdmin;

    public function index(Request $request)
    {
        $this->authorize('platform.attributes.manage');

        $sortColumn = in_array($request->string('sort')->toString(), ['name', 'code', 'sort_order', 'created_at'])
            ? $request->string('sort')->toString()
            : 'sort_order';
        $direction = $request->string('direction')->toString() === 'asc' ? 'asc' : 'desc';

        $pricingTypes = PricingType::query()
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

        return view('backend.admin.catalog.pricing-types.index', [
            'pricingTypes' => $pricingTypes,
            'search'       => $request->string('search')->toString(),
            'status'       => $request->string('status')->toString(),
            'sort'         => $sortColumn,
            'direction'    => $direction,
        ]);
    }

    public function store(PricingTypeRequest $request)
    {
        $validated               = $request->validated();
        $validated['is_active']  = $request->boolean('is_active', true);
        $validated['sort_order'] = $request->integer('sort_order', 0);

        $pricingType = PricingType::create($validated);

        activity('catalog')->causedBy($this->admin())->performedOn($pricingType)->log('Pricing type created');

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => "Pricing type '{$pricingType->name}' created."]);
        }

        return redirect()->route('admin.catalog.pricing-types.index')->with('success', "Pricing type '{$pricingType->name}' created.");
    }

    public function update(PricingTypeRequest $request, PricingType $pricingType)
    {
        $validated               = $request->validated();
        $validated['is_active']  = $request->boolean('is_active', true);
        $validated['sort_order'] = $request->integer('sort_order', 0);

        $pricingType->update($validated);

        activity('catalog')->causedBy($this->admin())->performedOn($pricingType)->log('Pricing type updated');

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => "Pricing type '{$pricingType->name}' updated."]);
        }

        return redirect()->route('admin.catalog.pricing-types.index')->with('success', "Pricing type '{$pricingType->name}' updated.");
    }

    public function destroy(PricingType $pricingType)
    {
        $this->authorize('platform.attributes.manage');

        abort_if(
            $pricingType->listings()->exists(),
            422,
            "Cannot delete '{$pricingType->name}' — it is used by {$pricingType->listings()->count()} listing(s)."
        );

        $pricingType->delete();

        activity('catalog')->causedBy($this->admin())->log("Pricing type '{$pricingType->name}' deleted");

        return back()->with('success', "Pricing type '{$pricingType->name}' deleted.");
    }

    public function toggleActive(PricingType $pricingType)
    {
        $this->authorize('platform.attributes.manage');

        $pricingType->update(['is_active' => ! $pricingType->is_active]);

        $state = $pricingType->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Pricing type '{$pricingType->name}' {$state}.");
    }
}
