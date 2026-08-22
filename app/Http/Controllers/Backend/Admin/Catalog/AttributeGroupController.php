<?php

namespace App\Http\Controllers\Backend\Admin\Catalog;

use App\Http\Controllers\Backend\Admin\Concerns\InteractsWithAdmin;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Admin\Catalog\AttributeGroupRequest;
use App\Models\AttributeGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AttributeGroupController extends Controller
{
    use InteractsWithAdmin;

    public function index(Request $request)
    {
        $this->authorize('platform.attributes.manage');

        $groups = AttributeGroup::query()
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%' . $request->string('search') . '%'))
            ->when($request->filled('status'), function ($q) use ($request) {
                if ($request->string('status')->toString() === 'active') {
                    $q->where('is_active', true);
                } elseif ($request->string('status')->toString() === 'inactive') {
                    $q->where('is_active', false);
                }
            })
            ->withCount('attributes')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('backend.admin.catalog.attribute-groups.index', [
            'groups' => $groups,
            'search' => $request->string('search')->toString(),
            'status' => $request->string('status')->toString(),
        ]);
    }

    public function create()
    {
        $this->authorize('platform.attributes.manage');

        return view('backend.admin.catalog.attribute-groups.create', [
            'group' => new AttributeGroup(),
        ]);
    }

    public function store(AttributeGroupRequest $request)
    {
        $name = $request->string('name')->toString();
        $slug = $request->filled('slug')
            ? Str::slug($request->string('slug'))
            : $this->uniqueSlug($name);

        AttributeGroup::create([
            'name'        => $name,
            'slug'        => $slug,
            'description' => $request->input('description'),
            'sort_order'  => $request->integer('sort_order', 0),
            'is_active'   => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.catalog.attribute-groups.index')->with('success', 'Attribute group created.');
    }

    public function edit(AttributeGroup $attributeGroup)
    {
        $this->authorize('platform.attributes.manage');

        return view('backend.admin.catalog.attribute-groups.edit', [
            'group' => $attributeGroup->loadCount('attributes'),
        ]);
    }

    public function update(AttributeGroupRequest $request, AttributeGroup $attributeGroup)
    {
        $name = $request->string('name')->toString();
        $slug = $request->filled('slug')
            ? Str::slug($request->string('slug'))
            : $attributeGroup->slug;

        $attributeGroup->update([
            'name'        => $name,
            'slug'        => $slug,
            'description' => $request->input('description'),
            'sort_order'  => $request->integer('sort_order', 0),
            'is_active'   => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.catalog.attribute-groups.index')->with('success', 'Attribute group updated.');
    }

    public function toggleActive(AttributeGroup $attributeGroup)
    {
        $this->authorize('platform.attributes.manage');

        $attributeGroup->update(['is_active' => ! $attributeGroup->is_active]);

        $status = $attributeGroup->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Attribute group {$status}.");
    }

    public function destroy(AttributeGroup $attributeGroup)
    {
        $this->authorize('platform.attributes.manage');

        abort_if($attributeGroup->attributes()->exists(), 422, 'Cannot delete group with assigned attributes. Please reassign or remove attributes first.');

        $attributeGroup->delete();

        return back()->with('success', 'Attribute group deleted.');
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 2;

        while (AttributeGroup::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
