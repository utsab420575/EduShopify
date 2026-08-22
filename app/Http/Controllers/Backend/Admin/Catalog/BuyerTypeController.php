<?php

namespace App\Http\Controllers\Backend\Admin\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Admin\Catalog\BuyerTypeRequest;
use App\Models\BuyerType;
use Illuminate\Support\Str;

class BuyerTypeController extends Controller
{
    public function index()
    {
        $this->authorize('platform.categories.manage');

        return view('backend.admin.catalog.buyer-types.index', [
            'buyerTypes' => BuyerType::orderBy('sort_order')->orderBy('name')->paginate(20),
        ]);
    }

    public function create()
    {
        $this->authorize('platform.categories.manage');

        return view('backend.admin.catalog.buyer-types.create', ['buyerType' => new BuyerType()]);
    }

    public function store(BuyerTypeRequest $request)
    {
        BuyerType::create($request->validated() + [
            'slug' => $this->uniqueSlug($request->string('name')),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.catalog.buyer-types.index')->with('success', 'Buyer type created.');
    }

    public function edit(BuyerType $buyerType)
    {
        $this->authorize('platform.categories.manage');

        return view('backend.admin.catalog.buyer-types.edit', ['buyerType' => $buyerType]);
    }

    public function update(BuyerTypeRequest $request, BuyerType $buyerType)
    {
        $buyerType->update($request->validated() + [
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.catalog.buyer-types.index')->with('success', 'Buyer type updated.');
    }

    public function destroy(BuyerType $buyerType)
    {
        $this->authorize('platform.categories.manage');

        abort_if($buyerType->buyerProfiles()->exists() || $buyerType->accounts()->exists(), 422, 'This buyer type is in use.');

        $buyerType->delete();

        return back()->with('success', 'Buyer type deleted.');
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 2;

        while (BuyerType::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
