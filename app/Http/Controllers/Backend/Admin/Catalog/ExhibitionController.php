<?php

namespace App\Http\Controllers\Backend\Admin\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Admin\Catalog\ExhibitionRequest;
use App\Models\Exhibition;
use Illuminate\Support\Str;

class ExhibitionController extends Controller
{
    public function index()
    {
        $this->authorize('platform.categories.manage');

        return view('backend.admin.catalog.exhibitions.index', [
            'exhibitions' => Exhibition::orderBy('sort_order')->orderByDesc('starts_at')->paginate(20),
        ]);
    }

    public function create()
    {
        $this->authorize('platform.categories.manage');

        return view('backend.admin.catalog.exhibitions.create', ['exhibition' => new Exhibition()]);
    }

    public function store(ExhibitionRequest $request)
    {
        Exhibition::create($request->validated() + [
            'slug' => $this->uniqueSlug($request->string('name')),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.catalog.exhibitions.index')->with('success', 'Exhibition created.');
    }

    public function edit(Exhibition $exhibition)
    {
        $this->authorize('platform.categories.manage');

        return view('backend.admin.catalog.exhibitions.edit', ['exhibition' => $exhibition]);
    }

    public function update(ExhibitionRequest $request, Exhibition $exhibition)
    {
        $exhibition->update($request->validated() + [
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.catalog.exhibitions.index')->with('success', 'Exhibition updated.');
    }

    public function destroy(Exhibition $exhibition)
    {
        $this->authorize('platform.categories.manage');

        abort_if($exhibition->supplierAccounts()->exists(), 422, 'This exhibition has participating suppliers.');

        $exhibition->delete();

        return back()->with('success', 'Exhibition deleted.');
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 2;

        while (Exhibition::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
