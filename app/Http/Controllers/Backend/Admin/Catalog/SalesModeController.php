<?php

namespace App\Http\Controllers\Backend\Admin\Catalog;

use App\Http\Controllers\Backend\Admin\Concerns\InteractsWithAdmin;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Admin\Catalog\SalesModeRequest;
use App\Models\SalesMode;
use Illuminate\Http\Request;

class SalesModeController extends Controller
{
    use InteractsWithAdmin;

    public function index(Request $request)
    {
        $this->authorize('platform.attributes.manage');

        $sortColumn = in_array($request->string('sort')->toString(), ['name', 'code', 'sort_order', 'created_at'])
            ? $request->string('sort')->toString()
            : 'sort_order';
        $direction = $request->string('direction')->toString() === 'asc' ? 'asc' : 'desc';

        $salesModes = SalesMode::query()
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

        return view('backend.admin.catalog.sales-modes.index', [
            'salesModes' => $salesModes,
            'search'     => $request->string('search')->toString(),
            'status'     => $request->string('status')->toString(),
            'sort'       => $sortColumn,
            'direction'  => $direction,
        ]);
    }

    public function store(SalesModeRequest $request)
    {
        $validated               = $request->validated();
        $validated['is_active']  = $request->boolean('is_active', true);
        $validated['sort_order'] = $request->integer('sort_order', 0);

        $salesMode = SalesMode::create($validated);

        activity('catalog')->causedBy($this->admin())->performedOn($salesMode)->log('Sales mode created');

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => "Sales mode '{$salesMode->name}' created."]);
        }

        return redirect()->route('admin.catalog.sales-modes.index')->with('success', "Sales mode '{$salesMode->name}' created.");
    }

    public function update(SalesModeRequest $request, SalesMode $salesMode)
    {
        $validated               = $request->validated();
        $validated['is_active']  = $request->boolean('is_active', true);
        $validated['sort_order'] = $request->integer('sort_order', 0);

        $salesMode->update($validated);

        activity('catalog')->causedBy($this->admin())->performedOn($salesMode)->log('Sales mode updated');

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => "Sales mode '{$salesMode->name}' updated."]);
        }

        return redirect()->route('admin.catalog.sales-modes.index')->with('success', "Sales mode '{$salesMode->name}' updated.");
    }

    public function destroy(SalesMode $salesMode)
    {
        $this->authorize('platform.attributes.manage');

        abort_if(
            $salesMode->listings()->exists(),
            422,
            "Cannot delete '{$salesMode->name}' — it is used by {$salesMode->listings()->count()} listing(s)."
        );

        $salesMode->delete();

        activity('catalog')->causedBy($this->admin())->log("Sales mode '{$salesMode->name}' deleted");

        return back()->with('success', "Sales mode '{$salesMode->name}' deleted.");
    }

    public function toggleActive(SalesMode $salesMode)
    {
        $this->authorize('platform.attributes.manage');

        $salesMode->update(['is_active' => ! $salesMode->is_active]);

        $state = $salesMode->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Sales mode '{$salesMode->name}' {$state}.");
    }
}
