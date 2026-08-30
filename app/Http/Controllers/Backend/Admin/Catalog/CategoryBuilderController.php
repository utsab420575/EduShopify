<?php

namespace App\Http\Controllers\Backend\Admin\Catalog;

use App\Http\Controllers\Backend\Admin\Concerns\InteractsWithAdmin;
use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeGroup;
use App\Models\Category;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Category Builder — a display-only workspace that composes the existing
 * Category / Attribute Group / Attribute / Category-Attribute screens into
 * one non-linear, tab-based flow. All create/update/delete actions still go
 * through CategoryController, AttributeGroupController, AttributeController,
 * and CategoryAttributeController — this controller never writes data.
 */
class CategoryBuilderController extends Controller
{
    use InteractsWithAdmin;

    public function categories(Request $request)
    {
        $this->authorize('platform.categories.manage');

        $categories = Category::withCount(['children', 'mainCategoryListings'])
            ->orderBy('sort_order')->orderBy('name')->get();

        return view('backend.admin.catalog.builder.categories', [
            'tree'           => $this->buildCategoryTree($categories),
            'categoryModels' => $categories->keyBy('id'),
            'parents'        => $categories->sortBy('name')->values(),
        ]);
    }

    public function attributeGroups(Request $request)
    {
        $this->authorize('platform.attributes.manage');

        $groups = AttributeGroup::withCount('attributes')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('backend.admin.catalog.builder.attribute-groups', [
            'groups' => $groups,
        ]);
    }

    public function attributes(Request $request)
    {
        $this->authorize('platform.attributes.manage');

        $attributes = Attribute::with(['attributeGroup', 'unit', 'values' => fn ($q) => $q->orderBy('sort_order')])
            ->withCount(['values', 'listingAttributeValues'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('backend.admin.catalog.builder.attributes', [
            'attributes'      => $attributes,
            'attributeGroups' => AttributeGroup::active()->ordered()->get(),
            'units'           => Unit::orderBy('name')->get(),
            'inputTypes'      => \App\Models\InputType::active()->ordered()->get(),
        ]);
    }

    public function assign(Request $request)
    {
        $this->authorize('platform.categories.manage');

        $categories = Category::withCount('attributes')->orderBy('sort_order')->orderBy('name')->get();

        $attributes = Attribute::active()
            ->with(['attributeGroup', 'values' => fn ($q) => $q->active()->orderBy('sort_order')])
            ->ordered()
            ->get();

        $groupedAttributes = $attributes
            ->groupBy(fn ($a) => $a->attribute_group_id ?? 0)
            ->map(function ($items, $groupId) {
                $group = $groupId > 0 ? $items->first()->attributeGroup : null;

                return [
                    'group_id'   => (int) $groupId,
                    'group_name' => $group?->name ?? 'General / Other Specifications',
                    'sort_order' => $group?->sort_order ?? 9999,
                    'attributes' => $items->map(fn ($a) => [
                        'id'         => $a->id,
                        'name'       => $a->name,
                        'input_type' => $a->input_type,
                        'values'     => in_array($a->input_type, ['select', 'multi_select', 'color'])
                            ? $a->values->pluck('value')->values()
                            : [],
                    ])->values(),
                ];
            })
            ->sortBy('sort_order')
            ->values();

        // category_id => [attribute_id, ...] for every category, in one query,
        // so the right panel can instantly show already-assigned attributes
        // (checked + locked) the moment a category is picked, with no reload.
        $assignments = DB::table('category_attribute')
            ->select('category_id', 'attribute_id')
            ->get()
            ->groupBy('category_id')
            ->map(fn ($rows) => $rows->pluck('attribute_id')->values());

        return view('backend.admin.catalog.builder.assign', [
            'tree'              => $this->buildCategoryTree($categories, includeAttributeCount: true),
            'groupedAttributes' => $groupedAttributes,
            'assignments'       => $assignments,
            'selectedCategoryId' => $request->integer('category') ?: null,
        ]);
    }

    /**
     * Flat, depth-indented list of every category (active and inactive —
     * admin needs to manage both here, unlike the active-only tree used for
     * public-facing select dropdowns elsewhere).
     */
    private function buildCategoryTree($categories, bool $includeAttributeCount = false): array
    {
        $grouped = $categories->groupBy('parent_id');
        $options = [];

        $walk = function ($parentId, $depth = 0) use (&$walk, $grouped, &$options, $includeAttributeCount) {
            if (! isset($grouped[$parentId])) {
                return;
            }

            foreach ($grouped[$parentId] as $cat) {
                $childrenCount = $cat->children_count ?? 0;
                $listingsCount = $cat->main_category_listings_count ?? 0;

                $options[] = [
                    'id'               => $cat->id,
                    'name'             => $cat->name,
                    'depth'            => $depth,
                    'is_active'        => (bool) $cat->is_active,
                    'attributes_count' => $includeAttributeCount ? $cat->attributes_count : null,
                    'children_count'   => $childrenCount,
                    'listings_count'   => $listingsCount,
                    'can_delete'       => $childrenCount === 0 && $listingsCount === 0,
                ];

                $walk($cat->id, $depth + 1);
            }
        };

        $walk(null);

        return $options;
    }
}
