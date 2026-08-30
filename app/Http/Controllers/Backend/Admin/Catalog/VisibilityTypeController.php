<?php

namespace App\Http\Controllers\Backend\Admin\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Admin\Catalog\VisibilityTypeRequest;
use App\Models\VisibilityType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VisibilityTypeController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('platform.attributes.manage');

        $search = $request->string('search')->trim()->toString();
        $status = $request->string('status')->toString();
        $engineType = $request->string('engine_type')->toString();
        $sortField = in_array($request->string('sort')->toString(), ['name', 'code', 'engine_type', 'sort_order', 'is_active', 'created_at'], true)
            ? $request->string('sort')->toString()
            : 'sort_order';
        $sortDir = strtolower($request->string('direction')->toString()) === 'desc' ? 'desc' : 'asc';

        $visibilityTypes = VisibilityType::query()
            ->withCount('rfqs')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($status !== '', function ($query) use ($status) {
                if ($status === 'active') {
                    $query->where('is_active', true);
                } elseif ($status === 'inactive') {
                    $query->where('is_active', false);
                }
            })
            ->when($engineType !== '', function ($query) use ($engineType) {
                $query->where('engine_type', $engineType);
            })
            ->orderBy($sortField, $sortDir)
            ->paginate(15)
            ->withQueryString();

        return view('backend.admin.catalog.visibility-types.index', [
            'visibilityTypes' => $visibilityTypes,
            'search'          => $search,
            'status'          => $status,
            'engineType'      => $engineType,
            'sortField'       => $sortField,
            'sortDir'         => $sortDir,
        ]);
    }

    public function store(VisibilityTypeRequest $request): RedirectResponse
    {
        $this->authorize('platform.attributes.manage');

        VisibilityType::create($request->validated());

        return redirect()->route('admin.catalog.visibility-types.index')
            ->with('success', 'Visibility type created successfully.');
    }

    public function update(VisibilityTypeRequest $request, VisibilityType $visibilityType): RedirectResponse
    {
        $this->authorize('platform.attributes.manage');

        $visibilityType->update($request->validated());

        return redirect()->route('admin.catalog.visibility-types.index')
            ->with('success', 'Visibility type updated successfully.');
    }

    public function destroy(VisibilityType $visibilityType): RedirectResponse
    {
        $this->authorize('platform.attributes.manage');

        if ($visibilityType->rfqs()->count() > 0) {
            return redirect()->route('admin.catalog.visibility-types.index')
                ->with('error', 'Cannot delete visibility type because it is associated with existing RFQs.');
        }

        $visibilityType->delete();

        return redirect()->route('admin.catalog.visibility-types.index')
            ->with('success', 'Visibility type deleted successfully.');
    }

    public function toggleActive(VisibilityType $visibilityType): JsonResponse|RedirectResponse
    {
        $this->authorize('platform.attributes.manage');

        $visibilityType->update(['is_active' => ! $visibilityType->is_active]);

        if (request()->wantsJson()) {
            return response()->json([
                'success'   => true,
                'is_active' => $visibilityType->is_active,
                'message'   => 'Visibility type status updated.',
            ]);
        }

        return back()->with('success', 'Visibility type status updated.');
    }
}
