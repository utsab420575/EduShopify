<?php

namespace App\Http\Controllers\Backend\Admin\Catalog;

use App\Http\Controllers\Backend\Admin\Concerns\InteractsWithAdmin;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Admin\Catalog\BulkCategoryAttributeRequest;
use App\Http\Requests\Backend\Admin\Catalog\CategoryAttributeRequest;
use App\Models\Attribute;
use App\Models\AttributeGroup;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryAttributeController extends Controller
{
    use InteractsWithAdmin;

    public function index(Category $category)
    {
        $this->authorize('platform.categories.manage');

        // Load all attributes assigned to this category with their group and unit
        $assignedAttributes = $category->attributes()
            ->with(['attributeGroup', 'unit', 'values'])
            ->get();

        // Group assigned attributes by their AttributeGroup
        // Ordered by AttributeGroup.sort_order, then pivot sort_order
        $groupedAttributes = $assignedAttributes
            ->groupBy(fn ($attr) => $attr->attribute_group_id ?? 0)
            ->map(function ($items, $groupId) {
                $group = $groupId > 0 ? $items->first()->attributeGroup : null;
                $sortedItems = $items->sortBy([
                    ['pivot.sort_order', 'asc'],
                    ['sort_order', 'asc'],
                    ['name', 'asc'],
                ])->values();

                return [
                    'group'      => $group,
                    'group_id'   => $groupId,
                    'group_name' => $group?->name ?? 'General / Other Specifications',
                    'sort_order' => $group?->sort_order ?? 9999,
                    'attributes' => $sortedItems,
                ];
            })
            ->sortBy('sort_order')
            ->values();

        // All available attribute groups
        $attributeGroups = AttributeGroup::active()->ordered()->with(['attributes' => fn ($q) => $q->active()->ordered()])->get();

        // Attributes not yet assigned to this category
        $assignedIds = $assignedAttributes->pluck('id')->toArray();
        $availableAttributes = Attribute::active()
            ->with(['attributeGroup', 'unit'])
            ->whereNotIn('id', $assignedIds)
            ->orderBy('name')
            ->get();

        return view('backend.admin.catalog.categories.attributes', [
            'category'            => $category,
            'assignedAttributes'  => $assignedAttributes,
            'assignedIds'         => $assignedIds,
            'groupedAttributes'   => $groupedAttributes,
            'totalAssignedCount'  => $assignedAttributes->count(),
            'attributeGroups'     => $attributeGroups,
            'availableAttributes' => $availableAttributes,
        ]);
    }

    public function store(CategoryAttributeRequest $request, Category $category)
    {
        $this->authorize('platform.categories.manage');

        $attributeId = $request->integer('attribute_id');

        if ($category->attributes()->where('attribute_id', $attributeId)->exists()) {
            return back()->with('error', 'This attribute is already assigned to this category.');
        }

        $attribute = Attribute::findOrFail($attributeId);

        $category->attributes()->attach($attributeId, [
            'is_required'   => $request->has('is_required') ? $request->boolean('is_required') : $attribute->is_required,
            'is_filterable' => $request->has('is_filterable') ? $request->boolean('is_filterable') : $attribute->is_filterable,
            'is_variant'    => $request->has('is_variant') ? $request->boolean('is_variant') : $attribute->is_variant,
            'sort_order'    => $request->filled('sort_order') ? $request->integer('sort_order') : $attribute->sort_order,
        ]);

        return back()->with('success', "'{$attribute->name}' assigned to {$category->name}.");
    }

    public function bulkStore(BulkCategoryAttributeRequest $request, Category $category)
    {
        $this->authorize('platform.categories.manage');

        $attributeIds = $request->input('attribute_ids', []);
        $existingIds = $category->attributes()->pluck('attribute_id')->toArray();
        $newIds = array_diff($attributeIds, $existingIds);

        if (empty($newIds)) {
            return back()->with('info', 'All selected attributes are already assigned to this category.');
        }

        $attributes = Attribute::whereIn('id', $newIds)->get();
        $attachData = [];

        foreach ($attributes as $attr) {
            $attachData[$attr->id] = [
                'is_required'   => $attr->is_required,
                'is_filterable' => $attr->is_filterable,
                'is_variant'    => $attr->is_variant,
                'sort_order'    => $attr->sort_order,
            ];
        }

        $category->attributes()->attach($attachData);

        $count = count($attachData);
        return back()->with('success', "{$count} attributes successfully assigned to {$category->name}.");
    }

    public function update(CategoryAttributeRequest $request, Category $category, Attribute $attribute)
    {
        $this->authorize('platform.categories.manage');

        $category->attributes()->updateExistingPivot($attribute->id, [
            'is_required'   => $request->boolean('is_required'),
            'is_filterable' => $request->boolean('is_filterable'),
            'is_variant'    => $request->boolean('is_variant'),
            'sort_order'    => $request->integer('sort_order', 0),
        ]);

        return back()->with('success', "Settings for '{$attribute->name}' updated.");
    }

    public function destroy(Category $category, Attribute $attribute)
    {
        $this->authorize('platform.categories.manage');

        // Only detaches from category_attribute pivot table. Global attribute is NOT deleted.
        $category->attributes()->detach($attribute->id);

        return back()->with('success', "Removed '{$attribute->name}' from {$category->name}.");
    }
}
