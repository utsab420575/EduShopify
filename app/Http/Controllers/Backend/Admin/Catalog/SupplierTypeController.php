<?php

namespace App\Http\Controllers\Backend\Admin\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Admin\Catalog\SupplierTypeRequest;
use App\Models\SupplierType;
use Illuminate\Support\Str;

class SupplierTypeController extends Controller
{
    public function index()
    {
        $this->authorize('platform.categories.manage');

        return view('backend.admin.catalog.supplier-types.index', [
            'supplierTypes' => SupplierType::orderBy('sort_order')->orderBy('name')->paginate(20),
        ]);
    }

    public function create()
    {
        $this->authorize('platform.categories.manage');

        return view('backend.admin.catalog.supplier-types.create', ['supplierType' => new SupplierType()]);
    }

    public function store(SupplierTypeRequest $request)
    {
        SupplierType::create($request->validated() + [
            'slug' => $this->uniqueSlug($request->string('name')),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.catalog.supplier-types.index')->with('success', 'Supplier type created.');
    }

    public function edit(SupplierType $supplierType)
    {
        $this->authorize('platform.categories.manage');

        return view('backend.admin.catalog.supplier-types.edit', ['supplierType' => $supplierType]);
    }

    public function update(SupplierTypeRequest $request, SupplierType $supplierType)
    {
        $supplierType->update($request->validated() + [
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.catalog.supplier-types.index')->with('success', 'Supplier type updated.');
    }

    public function destroy(SupplierType $supplierType)
    {
        $this->authorize('platform.categories.manage');

        abort_if($supplierType->supplierAccounts()->exists(), 422, 'This supplier type is in use.');

        $supplierType->delete();

        return back()->with('success', 'Supplier type deleted.');
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 2;

        while (SupplierType::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
