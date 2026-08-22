<?php

namespace App\Http\Controllers\Backend\Admin\Catalog;

use App\Http\Controllers\Backend\Admin\Concerns\InteractsWithAdmin;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Admin\Catalog\BrandRequest;
use App\Http\Requests\Backend\Admin\ReasonRequest;
use App\Models\Brand;
use App\Services\BrandModerationService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BrandController extends Controller
{
    use InteractsWithAdmin;

    public function index(Request $request)
    {
        $this->authorize('platform.brands.manage');

        $brands = Brand::query()
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%'.$request->string('search').'%'))
            ->when($request->filled('status'), fn ($q) => $q->where('approval_status', $request->string('status')))
            ->with('supplierAccount.supplierProfile')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('backend.admin.catalog.brands.index', [
            'brands' => $brands,
            'search' => $request->string('search')->toString(),
            'status' => $request->string('status')->toString(),
        ]);
    }

    public function create()
    {
        $this->authorize('platform.brands.manage');

        return view('backend.admin.catalog.brands.create', ['brand' => new Brand()]);
    }

    public function store(BrandRequest $request)
    {
        Brand::create($request->validated() + [
            'slug' => $this->uniqueSlug($request->string('name')),
            'owner_type' => 'global',
            'approval_status' => 'approved',
            'reviewed_by_user_id' => $this->admin()->id,
            'reviewed_at' => now(),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.catalog.brands.index')->with('success', 'Brand created.');
    }

    public function edit(Brand $brand)
    {
        $this->authorize('platform.brands.manage');

        return view('backend.admin.catalog.brands.edit', ['brand' => $brand]);
    }

    public function update(BrandRequest $request, Brand $brand)
    {
        $brand->update($request->validated() + [
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.catalog.brands.index')->with('success', 'Brand updated.');
    }

    public function destroy(Brand $brand)
    {
        $this->authorize('platform.brands.manage');

        abort_if($brand->listings()->exists(), 422, 'This brand is in use by listings.');

        $brand->delete();

        return back()->with('success', 'Brand deleted.');
    }

    public function approve(Brand $brand, BrandModerationService $service)
    {
        $this->authorize('platform.brands.manage');

        try {
            $service->approve($brand, $this->admin());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        activity('moderation')->causedBy($this->admin())->performedOn($brand)->log('Brand approved');

        return back()->with('success', 'Brand approved.');
    }

    public function reject(ReasonRequest $request, Brand $brand, BrandModerationService $service)
    {
        $this->authorize('platform.brands.manage');

        try {
            $service->reject($brand, $this->admin(), $request->string('reason'));
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        activity('moderation')->causedBy($this->admin())->performedOn($brand)
            ->withProperties(['reason' => $request->string('reason')])->log('Brand rejected');

        return back()->with('success', 'Brand rejected.');
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 2;

        while (Brand::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
