<?php

namespace App\Http\Controllers\Backend\Admin\Catalog;

use App\Http\Controllers\Backend\Admin\Concerns\InteractsWithAdmin;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Admin\Catalog\AttributeRequest;
use App\Http\Requests\Backend\Admin\ReasonRequest;
use App\Models\Attribute;
use App\Models\AttributeGroup;
use App\Models\AttributeSuggestion;
use App\Models\AttributeValue;
use App\Models\Unit;
use App\Services\AttributeSuggestionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AttributeController extends Controller
{
    use InteractsWithAdmin;

    public function index(Request $request)
    {
        $this->authorize('platform.attributes.manage');

        $attributes = Attribute::query()
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%' . $request->string('search') . '%'))
            ->when($request->filled('group_id'), fn ($q) => $q->where('attribute_group_id', $request->integer('group_id')))
            ->when($request->filled('input_type'), fn ($q) => $q->where('input_type', $request->string('input_type')))
            ->when($request->filled('unit_id'), fn ($q) => $q->where('unit_id', $request->integer('unit_id')))
            ->when($request->filled('status'), function ($q) use ($request) {
                if ($request->string('status')->toString() === 'active') {
                    $q->where('is_active', true);
                } elseif ($request->string('status')->toString() === 'inactive') {
                    $q->where('is_active', false);
                }
            })
            ->with(['attributeGroup', 'unit'])
            ->withCount('values')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $attributeGroups = AttributeGroup::ordered()->get();
        $units = Unit::orderBy('name')->get();

        return view('backend.admin.catalog.attributes.index', [
            'attributes'      => $attributes,
            'attributeGroups' => $attributeGroups,
            'units'           => $units,
            'search'          => $request->string('search')->toString(),
            'groupId'         => $request->string('group_id')->toString(),
            'inputType'       => $request->string('input_type')->toString(),
            'unitId'          => $request->string('unit_id')->toString(),
            'status'          => $request->string('status')->toString(),
        ]);
    }

    public function create()
    {
        $this->authorize('platform.attributes.manage');

        return view('backend.admin.catalog.attributes.create', [
            'attribute'       => new Attribute(),
            'attributeGroups' => AttributeGroup::active()->ordered()->get(),
            'units'           => Unit::orderBy('name')->get(),
        ]);
    }

    public function store(AttributeRequest $request)
    {
        DB::transaction(function () use ($request) {
            $name = $request->string('name')->toString();
            $slug = $request->filled('slug')
                ? Str::slug($request->string('slug'))
                : $this->uniqueSlug($name);

            $validationRules = $this->buildValidationRules($request);

            $attribute = Attribute::create([
                'attribute_group_id' => $request->filled('attribute_group_id') ? $request->integer('attribute_group_id') : null,
                'name'               => $name,
                'slug'               => $slug,
                'input_type'         => $request->string('input_type')->toString(),
                'unit_id'            => $request->filled('unit_id') ? $request->integer('unit_id') : null,
                'placeholder'        => $request->input('placeholder'),
                'validation_rules'   => !empty($validationRules) ? $validationRules : null,
                'is_filterable'      => $request->boolean('is_filterable'),
                'is_variant'         => $request->boolean('is_variant'),
                'is_required'        => $request->boolean('is_required'),
                'is_active'          => $request->boolean('is_active', true),
                'sort_order'         => $request->integer('sort_order', 0),
            ]);

            $this->syncValues($attribute, $request->input('values'));
        });

        return redirect()->route('admin.catalog.attributes.index')->with('success', 'Attribute created.');
    }

    public function edit(Attribute $attribute)
    {
        $this->authorize('platform.attributes.manage');

        return view('backend.admin.catalog.attributes.edit', [
            'attribute'       => $attribute->load(['attributeGroup', 'values' => fn ($q) => $q->orderBy('sort_order')]),
            'attributeGroups' => AttributeGroup::ordered()->get(),
            'units'           => Unit::orderBy('name')->get(),
        ]);
    }

    public function update(AttributeRequest $request, Attribute $attribute)
    {
        DB::transaction(function () use ($request, $attribute) {
            $name = $request->string('name')->toString();
            $slug = $request->filled('slug')
                ? Str::slug($request->string('slug'))
                : $attribute->slug;

            $validationRules = $this->buildValidationRules($request);

            $attribute->update([
                'attribute_group_id' => $request->filled('attribute_group_id') ? $request->integer('attribute_group_id') : null,
                'name'               => $name,
                'slug'               => $slug,
                'input_type'         => $request->string('input_type')->toString(),
                'unit_id'            => $request->filled('unit_id') ? $request->integer('unit_id') : null,
                'placeholder'        => $request->input('placeholder'),
                'validation_rules'   => !empty($validationRules) ? $validationRules : null,
                'is_filterable'      => $request->boolean('is_filterable'),
                'is_variant'         => $request->boolean('is_variant'),
                'is_required'        => $request->boolean('is_required'),
                'is_active'          => $request->boolean('is_active', true),
                'sort_order'         => $request->integer('sort_order', 0),
            ]);

            $this->syncValues($attribute, $request->input('values'));
        });

        return redirect()->route('admin.catalog.attributes.index')->with('success', 'Attribute updated.');
    }

    public function destroy(Attribute $attribute)
    {
        $this->authorize('platform.attributes.manage');

        abort_if($attribute->listingAttributeValues()->exists(), 422, 'This attribute is in use by listings.');

        $attribute->values()->delete();
        $attribute->delete();

        return back()->with('success', 'Attribute deleted.');
    }

    public function approveSuggestion(AttributeSuggestion $suggestion, AttributeSuggestionService $service)
    {
        $this->authorize('platform.attributes.manage');

        try {
            $service->approve($suggestion, $this->admin());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        activity('moderation')->causedBy($this->admin())->performedOn($suggestion)->log('Attribute suggestion approved');

        return back()->with('success', 'Attribute suggestion approved.');
    }

    public function rejectSuggestion(ReasonRequest $request, AttributeSuggestion $suggestion, AttributeSuggestionService $service)
    {
        $this->authorize('platform.attributes.manage');

        try {
            $service->reject($suggestion, $this->admin(), $request->string('reason'));
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        activity('moderation')->causedBy($this->admin())->performedOn($suggestion)
            ->withProperties(['reason' => $request->string('reason')])->log('Attribute suggestion rejected');

        return back()->with('success', 'Attribute suggestion rejected.');
    }

    private function buildValidationRules(Request $request): array
    {
        $rules = [];

        if ($request->filled('min_value')) {
            $rules['min'] = (float) $request->input('min_value');
        }
        if ($request->filled('max_value')) {
            $rules['max'] = (float) $request->input('max_value');
        }
        if ($request->filled('min_length')) {
            $rules['min_length'] = (int) $request->input('min_length');
        }
        if ($request->filled('max_length')) {
            $rules['max_length'] = (int) $request->input('max_length');
        }
        if ($request->boolean('decimal_allowed')) {
            $rules['decimal'] = true;
        }

        return $rules;
    }

    private function syncValues(Attribute $attribute, mixed $raw): void
    {
        // Only applicable for select, multi_select, or color input types
        if (! in_array($attribute->input_type, ['select', 'multi_select', 'color'])) {
            $attribute->values()->delete();
            return;
        }

        // If structured array from dynamic repeater
        if (is_array($raw)) {
            $keptIds = [];

            foreach ($raw as $index => $item) {
                $val = trim($item['value'] ?? '');
                if ($val === '') {
                    continue;
                }

                $valSlug = !empty($item['slug']) ? Str::slug($item['slug']) : Str::slug($val);
                $colorHex = !empty($item['color_hex']) ? trim($item['color_hex']) : null;
                $sortOrder = isset($item['sort_order']) ? (int) $item['sort_order'] : $index;
                $isActive = isset($item['is_active']) ? (bool) $item['is_active'] : true;

                if (!empty($item['id'])) {
                    $attrVal = $attribute->values()->find($item['id']);
                    if ($attrVal) {
                        $attrVal->update([
                            'value'      => $val,
                            'slug'       => $valSlug,
                            'color_hex'  => $colorHex,
                            'sort_order' => $sortOrder,
                            'is_active'  => $isActive,
                        ]);
                        $keptIds[] = $attrVal->id;
                        continue;
                    }
                }

                $newVal = AttributeValue::create([
                    'attribute_id' => $attribute->id,
                    'value'        => $val,
                    'slug'         => $valSlug,
                    'color_hex'    => $colorHex,
                    'sort_order'   => $sortOrder,
                    'is_active'    => $isActive,
                ]);
                $keptIds[] = $newVal->id;
            }

            $attribute->values()->whereNotIn('id', $keptIds)->delete();
            return;
        }

        // If string / line-by-line fallback
        if (is_string($raw)) {
            $submitted = collect(preg_split('/\r\n|\r|\n/', $raw))
                ->map(fn ($v) => trim($v))
                ->filter()
                ->unique()
                ->values();

            if ($submitted->isEmpty()) {
                $attribute->values()->delete();
                return;
            }

            $attribute->values()->whereNotIn('value', $submitted)->delete();

            foreach ($submitted as $index => $value) {
                $existing = $attribute->values()->where('value', $value)->first();
                if ($existing) {
                    $existing->update(['sort_order' => $index]);
                } else {
                    AttributeValue::create([
                        'attribute_id' => $attribute->id,
                        'value'        => $value,
                        'slug'         => Str::slug($value),
                        'sort_order'   => $index,
                        'is_active'    => true,
                    ]);
                }
            }
        }
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 2;

        while (Attribute::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
