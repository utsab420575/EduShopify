<?php

namespace App\Http\Controllers\Backend\Admin\Catalog;

use App\Http\Controllers\Backend\Admin\Concerns\InteractsWithAdmin;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Admin\Catalog\UnitRequest;
use App\Http\Requests\Backend\Admin\ReasonRequest;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class UnitController extends Controller
{
    use InteractsWithAdmin;

    public function index(Request $request)
    {
        $this->authorize('platform.attributes.manage');

        $units = Unit::query()
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%'.$request->string('search').'%'))
            ->when($request->filled('status'), fn ($q) => $q->where('approval_status', $request->string('status')))
            ->with('supplierAccount.supplierProfile')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('backend.admin.catalog.units.index', [
            'units' => $units,
            'search' => $request->string('search')->toString(),
            'status' => $request->string('status')->toString(),
        ]);
    }

    public function create()
    {
        $this->authorize('platform.attributes.manage');

        return view('backend.admin.catalog.units.create', ['unit' => new Unit()]);
    }

    public function store(UnitRequest $request)
    {
        Unit::create($request->validated() + [
            'scope' => 'global',
            'approval_status' => 'approved',
            'created_by_user_id' => $this->admin()->id,
            'reviewed_by_user_id' => $this->admin()->id,
            'reviewed_at' => now(),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.catalog.units.index')->with('success', 'Unit created.');
    }

    public function edit(Unit $unit)
    {
        $this->authorize('platform.attributes.manage');

        return view('backend.admin.catalog.units.edit', ['unit' => $unit]);
    }

    public function update(UnitRequest $request, Unit $unit)
    {
        $unit->update($request->validated() + [
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.catalog.units.index')->with('success', 'Unit updated.');
    }

    public function destroy(Unit $unit)
    {
        $this->authorize('platform.attributes.manage');

        abort_if($unit->listings()->exists() || $unit->attributeDefinitions()->exists(), 422, 'This unit is in use.');

        $unit->delete();

        return back()->with('success', 'Unit deleted.');
    }

    public function approve(Unit $unit)
    {
        $this->authorize('platform.attributes.manage');

        abort_unless($unit->approval_status === 'pending', 422, 'Only a pending unit can be approved.');

        $unit->update([
            'approval_status' => 'approved',
            'rejection_reason' => null,
            'reviewed_by_user_id' => $this->admin()->id,
            'reviewed_at' => now(),
        ]);

        activity('moderation')->causedBy($this->admin())->performedOn($unit)->log('Unit approved');

        return back()->with('success', 'Unit approved.');
    }

    public function reject(ReasonRequest $request, Unit $unit)
    {
        $this->authorize('platform.attributes.manage');

        abort_unless($unit->approval_status === 'pending', 422, 'Only a pending unit can be rejected.');

        $unit->update([
            'approval_status' => 'rejected',
            'rejection_reason' => $request->string('reason'),
            'reviewed_by_user_id' => $this->admin()->id,
            'reviewed_at' => now(),
        ]);

        activity('moderation')->causedBy($this->admin())->performedOn($unit)
            ->withProperties(['reason' => $request->string('reason')])->log('Unit rejected');

        return back()->with('success', 'Unit rejected.');
    }
}
