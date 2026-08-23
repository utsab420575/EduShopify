<?php

namespace App\Http\Controllers\Backend\Supplier\Catalog;

use App\Http\Controllers\Backend\Supplier\Concerns\InteractsWithSupplierAccount;
use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Listing;
use App\Models\ListingAttributeValue;
use App\Models\ListingCategory;
use App\Models\ListingVariant;
use App\Models\ListingVariantAttribute;
use App\Models\ProductDetail;
use App\Models\ServiceDetail;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ListingController extends Controller
{
    use InteractsWithSupplierAccount;

    public function index(Request $request)
    {
        $account = $this->currentAccount();

        $query = $account->listings()->with(['mainCategory', 'brand', 'unit'])->latest();

        if ($request->filled('status')) {
            $query->where('approval_status', $request->string('status'));
        }

        if ($request->filled('type')) {
            $query->where('listing_type', $request->string('type'));
        }

        if ($request->filled('search')) {
            $s = '%' . $request->string('search') . '%';
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', $s)
                  ->orWhere('listing_number', 'like', $s)
                  ->orWhere('sku', 'like', $s);
            });
        }

        $listings = $query->paginate(12)->withQueryString();

        return view('backend.supplier.catalog.listings.index', [
            'account'  => $account,
            'user'     => $this->currentUser(),
            'listings' => $listings,
            'status'   => $request->string('status')->toString(),
            'type'     => $request->string('type')->toString(),
            'search'   => $request->string('search')->toString(),
        ]);
    }

    public function create()
    {
        $account = $this->currentAccount();
        $categoryOptions = Category::getTreeSelectOptions();
        $brands = Brand::where('is_active', true)->orderBy('name')->get();
        $units = Unit::where('is_active', true)->orderBy('name')->get();

        return view('backend.supplier.catalog.listings.create', [
            'account'         => $account,
            'user'            => $this->currentUser(),
            'categoryOptions' => $categoryOptions,
            'brands'          => $brands,
            'units'           => $units,
        ]);
    }

    public function categoryAttributes(Category $category)
    {
        $assignedAttributes = $category->attributes()
            ->with([
                'attributeGroup',
                'unit',
                'values' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')->orderBy('value'),
            ])
            ->get();

        // If category has no directly assigned attributes and has parent_id, fallback to parent category
        if ($assignedAttributes->isEmpty() && $category->parent_id) {
            $curr = $category;
            while ($assignedAttributes->isEmpty() && $curr->parent_id) {
                $curr = $curr->parent;
                if ($curr) {
                    $assignedAttributes = $curr->attributes()
                        ->with([
                            'attributeGroup',
                            'unit',
                            'values' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')->orderBy('value'),
                        ])
                        ->get();
                }
            }
        }

        // Group assigned attributes by their AttributeGroup
        $groupedAttributes = $assignedAttributes
            ->groupBy(fn ($attr) => $attr->attribute_group_id ?? 0)
            ->map(function ($items, $groupId) {
                $group = $groupId > 0 ? $items->first()->attributeGroup : null;
                $sortedItems = $items->sortBy([
                    ['pivot.sort_order', 'asc'],
                    ['sort_order', 'asc'],
                    ['name', 'asc'],
                ])->values()->map(function ($attr) {
                    return [
                        'id'               => $attr->id,
                        'name'             => $attr->name,
                        'slug'             => $attr->slug,
                        'input_type'       => $attr->input_type,
                        'unit_id'          => $attr->unit_id,
                        'unit_symbol'      => $attr->unit?->symbol,
                        'unit_name'        => $attr->unit?->name,
                        'placeholder'      => $attr->placeholder,
                        'is_required'      => (bool) ($attr->pivot->is_required ?? $attr->is_required),
                        'is_filterable'    => (bool) ($attr->pivot->is_filterable ?? $attr->is_filterable),
                        'is_variant'       => (bool) ($attr->pivot->is_variant ?? $attr->is_variant),
                        'sort_order'       => (int) ($attr->pivot->sort_order ?? $attr->sort_order),
                        'values'           => $attr->values->map(fn ($v) => [
                            'id'        => $v->id,
                            'value'     => $v->value,
                            'slug'      => $v->slug,
                            'color_hex' => $v->color_hex,
                        ])->values()->toArray(),
                    ];
                });

                return [
                    'group_id'   => $groupId,
                    'group_name' => $group?->name ?? 'General / Other Specifications',
                    'sort_order' => $group?->sort_order ?? 9999,
                    'attributes' => $sortedItems,
                ];
            })
            ->sortBy('sort_order')
            ->values();

        return response()->json([
            'category_id'   => $category->id,
            'category_name' => $category->name,
            'groups'        => $groupedAttributes,
            'total_count'   => $assignedAttributes->count(),
        ]);
    }

    public function store(Request $request)
    {
        $account = $this->currentAccount();
        $isSubmitForApproval = $request->input('action') === 'submit_approval';

        $validated = $request->validate([
            'listing_type' => ['required', 'in:product,service'],
            'name' => ['required', 'string', 'max:255'],
            'main_category_id' => ['required', 'exists:categories,id'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'sku' => ['nullable', 'string', 'max:100'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'pricing_type' => ['required', 'in:fixed,quote_only,rfq_enabled'],
            'base_price' => ['nullable', 'numeric', 'min:0'],
            'compare_at_price' => ['nullable', 'numeric', 'min:0'],
            'currency_code' => ['required', 'string', 'max:3'],
            'min_order_quantity' => ['nullable', 'numeric', 'min:1'],
            'unit_id' => ['nullable', 'exists:units,id'],
            'is_active' => ['nullable', 'boolean'],
            'images.*' => ['nullable', 'image', 'max:5120'],
            // Product details
            'product_type' => ['nullable', 'in:simple,variable'],
            'stock_status' => ['nullable', 'in:in_stock,out_of_stock,limited,on_request'],
            'stock_quantity' => ['nullable', 'numeric', 'min:0'],
            'lead_time_days' => ['nullable', 'integer', 'min:0'],
            'warranty_terms' => ['nullable', 'string', 'max:500'],
            // Service details
            'service_mode' => ['nullable', 'in:onsite,remote,hybrid'],
            'delivery_time_days' => ['nullable', 'integer', 'min:0'],
        ]);

        $category = Category::findOrFail($validated['main_category_id']);

        $listing = DB::transaction(function () use ($account, $validated, $request, $category, $isSubmitForApproval) {
            $listingNumber = 'LST-' . strtoupper(Str::random(8));

            $listing = Listing::create([
                'supplier_account_id' => $account->id,
                'created_by_user_id' => $this->currentUser()->id,
                'listing_type' => $validated['listing_type'],
                'listing_number' => $listingNumber,
                'main_category_id' => $validated['main_category_id'],
                'brand_id' => $validated['brand_id'] ?? null,
                'name' => $validated['name'],
                'slug' => Str::slug($validated['name']) . '-' . strtolower(Str::random(5)),
                'sku' => $validated['sku'] ?? null,
                'short_description' => $validated['short_description'] ?? null,
                'description' => $validated['description'] ?? null,
                'pricing_type' => $validated['pricing_type'],
                'base_price' => $validated['base_price'] ?? null,
                'compare_at_price' => $validated['compare_at_price'] ?? null,
                'currency_code' => $validated['currency_code'],
                'min_order_quantity' => $validated['min_order_quantity'] ?? 1,
                'unit_id' => $validated['unit_id'] ?? null,
                'approval_status' => $isSubmitForApproval ? 'pending' : 'draft',
                'is_active' => $request->boolean('is_active', true),
            ]);

            // Create listing_categories relation
            ListingCategory::create([
                'listing_id' => $listing->id,
                'category_id' => $category->id,
                'is_primary' => true,
            ]);

            if ($validated['listing_type'] === 'product') {
                ProductDetail::create([
                    'listing_id' => $listing->id,
                    'product_type' => $validated['product_type'] ?? 'simple',
                    'stock_status' => $validated['stock_status'] ?? 'on_request',
                    'stock_quantity' => $validated['stock_quantity'] ?? 0,
                    'lead_time_days' => $validated['lead_time_days'] ?? null,
                    'warranty_terms' => $validated['warranty_terms'] ?? null,
                ]);
            } else {
                ServiceDetail::create([
                    'listing_id' => $listing->id,
                    'service_mode' => $validated['service_mode'] ?? null,
                    'delivery_time_days' => $validated['delivery_time_days'] ?? null,
                ]);
            }

            // Sync dynamic category attributes
            $this->syncAttributeValues(
                $listing,
                $category,
                $request->input('attributes', []),
                $isSubmitForApproval
            );

            // Handle optional generated variants
            if ($request->filled('generated_variants') && is_array($request->input('generated_variants'))) {
                foreach ($request->input('generated_variants') as $vItem) {
                    if (empty($vItem['name'])) continue;
                    $variant = ListingVariant::create([
                        'listing_id'       => $listing->id,
                        'name'             => $vItem['name'],
                        'sku'              => $vItem['sku'] ?? null,
                        'price'            => $vItem['price'] ?? $listing->base_price ?? 0,
                        'compare_at_price' => $vItem['compare_at_price'] ?? null,
                        'currency_code'    => $listing->currency_code,
                        'stock_status'     => $vItem['stock_status'] ?? 'in_stock',
                        'stock_quantity'   => $vItem['stock_quantity'] ?? 0,
                        'is_active'        => true,
                    ]);

                    if (!empty($vItem['attributes']) && is_array($vItem['attributes'])) {
                        foreach ($vItem['attributes'] as $attrId => $attrValId) {
                            ListingVariantAttribute::create([
                                'listing_variant_id' => $variant->id,
                                'attribute_id'       => (int)$attrId,
                                'attribute_value_id' => is_numeric($attrValId) ? (int)$attrValId : null,
                                'custom_value'       => !is_numeric($attrValId) ? $attrValId : null,
                            ]);
                        }
                    }
                }
            }

            // Attach uploaded media
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $file) {
                    if ($file->isValid()) {
                        $listing->addMedia($file)
                            ->usingFileName(\App\Support\Media\ProductImagePathGenerator::supplierFileName($account->id, $file->getClientOriginalName()))
                            ->toMediaCollection('gallery');
                    }
                }
            }

            return $listing;
        });

        $message = $isSubmitForApproval 
            ? 'Listing created and submitted for platform approval.' 
            : 'Listing saved as Draft.';

        return redirect()->route('supplier.catalog.listings.show', $listing)->with('success', $message);
    }

    public function show(Listing $listing)
    {
        $account = $this->currentAccount();
        abort_if($listing->supplier_account_id !== $account->id, 403);

        $listing->load([
            'mainCategory',
            'brand',
            'unit',
            'productDetail',
            'serviceDetail',
            'variants.variantAttributes.attribute',
            'variants.variantAttributes.attributeValue',
            'variants.tierPrices',
            'allTierPrices',
            'media',
            'attributeValues.attribute.attributeGroup',
            'attributeValues.attribute.unit',
            'attributeValues.attributeValue',
        ]);

        // Group listing attribute values by AttributeGroup
        $groupedSpecifications = $listing->attributeValues
            ->groupBy(fn ($val) => $val->attribute?->attribute_group_id ?? 0)
            ->map(function ($items, $groupId) {
                $group = $groupId > 0 ? $items->first()->attribute?->attributeGroup : null;
                return [
                    'group_id'   => $groupId,
                    'group_name' => $group?->name ?? 'General Specifications',
                    'sort_order' => $group?->sort_order ?? 9999,
                    'items'      => $items->sortBy([
                        ['attribute.sort_order', 'asc'],
                        ['attribute.name', 'asc'],
                    ]),
                ];
            })
            ->sortBy('sort_order')
            ->values();

        // Extract variant-eligible attributes with their entered/available options
        $category = $listing->mainCategory;
        $rawCategoryAttrs = collect();
        if ($category) {
            $rawCategoryAttrs = $category->attributes()
                ->with(['unit', 'values' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')])
                ->get();
            if ($rawCategoryAttrs->isEmpty() && $category->parent_id) {
                $curr = $category;
                while ($rawCategoryAttrs->isEmpty() && $curr->parent_id) {
                    $curr = $curr->parent;
                    if ($curr) {
                        $rawCategoryAttrs = $curr->attributes()->with(['unit', 'values' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')])->get();
                    }
                }
            }
        }

        $variantEligibleAttributes = $rawCategoryAttrs->filter(function ($attr) {
            return (bool) ($attr->pivot->is_variant ?? $attr->is_variant);
        })->map(function ($attr) use ($listing) {
            $listingAttrVal = $listing->attributeValues->firstWhere('attribute_id', $attr->id);
            $selectedOptions = [];

            if ($listingAttrVal) {
                if ($listingAttrVal->attribute_value_id && $listingAttrVal->attributeValue) {
                    $selectedOptions[] = [
                        'id'    => $listingAttrVal->attributeValue->id,
                        'value' => $listingAttrVal->attributeValue->value,
                    ];
                } elseif (is_array($listingAttrVal->value_json) && !empty($listingAttrVal->value_json)) {
                    foreach ($listingAttrVal->value_json as $valStr) {
                        $foundVal = $attr->values->firstWhere('value', $valStr);
                        $selectedOptions[] = [
                            'id'    => $foundVal?->id ?? $valStr,
                            'value' => $valStr,
                        ];
                    }
                } elseif ($listingAttrVal->value_text) {
                    $parts = array_map('trim', explode(',', $listingAttrVal->value_text));
                    foreach ($parts as $p) {
                        if ($p === '') continue;
                        $foundVal = $attr->values->firstWhere('value', $p);
                        $selectedOptions[] = [
                            'id'    => $foundVal?->id ?? $p,
                            'value' => $p,
                        ];
                    }
                }
            }

            if (empty($selectedOptions) && $attr->values->isNotEmpty()) {
                $selectedOptions = $attr->values->map(fn ($v) => ['id' => $v->id, 'value' => $v->value])->toArray();
            }

            return [
                'id'                => $attr->id,
                'name'              => $attr->name,
                'input_type'        => $attr->input_type,
                'available_options' => $selectedOptions,
                'all_options'       => $attr->values->map(fn ($v) => ['id' => $v->id, 'value' => $v->value])->toArray(),
            ];
        })->values();

        return view('backend.supplier.catalog.listings.show', [
            'account'                   => $account,
            'user'                      => $this->currentUser(),
            'listing'                   => $listing,
            'groupedSpecifications'     => $groupedSpecifications,
            'variantEligibleAttributes' => $variantEligibleAttributes,
        ]);
    }

    public function edit(Listing $listing)
    {
        $account = $this->currentAccount();
        abort_if($listing->supplier_account_id !== $account->id, 403);

        $listing->load([
            'productDetail',
            'serviceDetail',
            'attributeValues.attribute',
            'attributeValues.attributeValue',
            'media',
        ]);

        $categoryOptions = Category::getTreeSelectOptions();
        $brands = Brand::where('is_active', true)->orderBy('name')->get();
        $units = Unit::where('is_active', true)->orderBy('name')->get();

        // Format existing attribute values as reactive dictionary
        $existingValues = [];
        foreach ($listing->attributeValues as $val) {
            $valueJson = is_array($val->value_json) ? $val->value_json : ($val->value_text ? array_map('trim', explode(',', $val->value_text)) : []);
            $valueText = $val->value_text ?? (is_array($val->value_json) ? implode(', ', $val->value_json) : null);

            $existingValues[$val->attribute_id] = [
                'attribute_value_id' => $val->attribute_value_id,
                'value_text'         => $valueText,
                'value_number'       => $val->value_number !== null ? (float)$val->value_number : null,
                'value_boolean'      => $val->value_boolean,
                'value_date'         => $val->value_date ? (is_string($val->value_date) ? $val->value_date : $val->value_date->format('Y-m-d')) : null,
                'value_json'         => $valueJson,
            ];
        }

        return view('backend.supplier.catalog.listings.edit', [
            'account'         => $account,
            'user'            => $this->currentUser(),
            'listing'         => $listing,
            'categoryOptions' => $categoryOptions,
            'brands'          => $brands,
            'units'           => $units,
            'existingValues'  => $existingValues,
        ]);
    }

    public function update(Request $request, Listing $listing)
    {
        $account = $this->currentAccount();
        abort_if($listing->supplier_account_id !== $account->id, 403);

        $isSubmitForApproval = $request->input('action') === 'submit_approval';

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'main_category_id' => ['required', 'exists:categories,id'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'sku' => ['nullable', 'string', 'max:100'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'pricing_type' => ['required', 'in:fixed,quote_only,rfq_enabled'],
            'base_price' => ['nullable', 'numeric', 'min:0'],
            'compare_at_price' => ['nullable', 'numeric', 'min:0'],
            'currency_code' => ['required', 'string', 'max:3'],
            'min_order_quantity' => ['nullable', 'numeric', 'min:1'],
            'unit_id' => ['nullable', 'exists:units,id'],
            'is_active' => ['nullable', 'boolean'],
            'images.*' => ['nullable', 'image', 'max:5120'],
            // Product details
            'product_type' => ['nullable', 'in:simple,variable'],
            'stock_status' => ['nullable', 'in:in_stock,out_of_stock,limited,on_request'],
            'stock_quantity' => ['nullable', 'numeric', 'min:0'],
            'lead_time_days' => ['nullable', 'integer', 'min:0'],
            'warranty_terms' => ['nullable', 'string', 'max:500'],
            // Service details
            'service_mode' => ['nullable', 'in:onsite,remote,hybrid'],
            'delivery_time_days' => ['nullable', 'integer', 'min:0'],
        ]);

        $category = Category::findOrFail($validated['main_category_id']);

        DB::transaction(function () use ($listing, $validated, $request, $category, $isSubmitForApproval) {
            $updateData = [
                'main_category_id' => $validated['main_category_id'],
                'brand_id' => $validated['brand_id'] ?? null,
                'name' => $validated['name'],
                'sku' => $validated['sku'] ?? null,
                'short_description' => $validated['short_description'] ?? null,
                'description' => $validated['description'] ?? null,
                'pricing_type' => $validated['pricing_type'],
                'base_price' => $validated['base_price'] ?? null,
                'compare_at_price' => $validated['compare_at_price'] ?? null,
                'currency_code' => $validated['currency_code'],
                'min_order_quantity' => $validated['min_order_quantity'] ?? 1,
                'unit_id' => $validated['unit_id'] ?? null,
                'is_active' => $request->boolean('is_active', true),
            ];

            if ($isSubmitForApproval) {
                $updateData['approval_status'] = 'pending';
            }

            $listing->update($updateData);

            // Sync category relation
            ListingCategory::updateOrCreate(
                ['listing_id' => $listing->id, 'is_primary' => true],
                ['category_id' => $category->id]
            );

            if ($listing->isProduct()) {
                ProductDetail::updateOrCreate(
                    ['listing_id' => $listing->id],
                    [
                        'product_type' => $validated['product_type'] ?? 'simple',
                        'stock_status' => $validated['stock_status'] ?? 'on_request',
                        'stock_quantity' => $validated['stock_quantity'] ?? 0,
                        'lead_time_days' => $validated['lead_time_days'] ?? null,
                        'warranty_terms' => $validated['warranty_terms'] ?? null,
                    ]
                );
            } elseif ($listing->isService()) {
                ServiceDetail::updateOrCreate(
                    ['listing_id' => $listing->id],
                    [
                        'service_mode' => $validated['service_mode'] ?? null,
                        'delivery_time_days' => $validated['delivery_time_days'] ?? null,
                    ]
                );
            }

            // Sync dynamic category attributes
            $this->syncAttributeValues(
                $listing,
                $category,
                $request->input('attributes', []),
                $isSubmitForApproval
            );

            // Attach uploaded media
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $file) {
                    if ($file->isValid()) {
                        $listing->addMedia($file)
                            ->usingFileName(\App\Support\Media\ProductImagePathGenerator::supplierFileName($listing->supplier_account_id, $file->getClientOriginalName()))
                            ->toMediaCollection('gallery');
                    }
                }
            }
        });

        $message = $isSubmitForApproval 
            ? 'Listing updated and submitted for platform approval.' 
            : 'Listing updated successfully.';

        return redirect()->route('supplier.catalog.listings.show', $listing)->with('success', $message);
    }

    public function submit(Listing $listing)
    {
        $account = $this->currentAccount();
        abort_if($listing->supplier_account_id !== $account->id, 403);

        $category = $listing->mainCategory;
        if ($category) {
            // Validate required attributes before submission
            $existingValues = $listing->attributeValues->keyBy('attribute_id');
            $categoryAttributes = $category->attributes()->get();
            if ($categoryAttributes->isEmpty() && $category->parent_id) {
                $categoryAttributes = $category->parent?->attributes()->get() ?? collect();
            }

            $missing = [];
            foreach ($categoryAttributes as $attr) {
                $isRequired = (bool)($attr->pivot->is_required ?? $attr->is_required);
                if ($isRequired) {
                    $val = $existingValues->get($attr->id);
                    if (!$val || $val->resolvedValue() === null || $val->resolvedValue() === '') {
                        $missing[] = $attr->name;
                    }
                }
            }

            if (!empty($missing)) {
                return back()->with('error', 'Cannot submit: Please fill in required specifications: ' . implode(', ', $missing));
            }
        }

        $listing->update([
            'approval_status' => 'pending',
        ]);

        return redirect()->route('supplier.catalog.listings.show', $listing)->with('success', 'Listing submitted for platform approval.');
    }

    public function destroy(Listing $listing)
    {
        $account = $this->currentAccount();
        abort_if($listing->supplier_account_id !== $account->id, 403);

        $listing->delete();

        return redirect()->route('supplier.catalog.listings.index')->with('success', 'Listing deleted.');
    }

    /**
     * Synchronize dynamic attribute values for a listing with duplicate protection.
     */
    protected function syncAttributeValues(Listing $listing, Category $category, array $attributesInput, bool $isSubmittingApproval = false): void
    {
        $categoryAttributes = $category->attributes()
            ->with('values')
            ->get();

        if ($categoryAttributes->isEmpty() && $category->parent_id) {
            $curr = $category;
            while ($categoryAttributes->isEmpty() && $curr->parent_id) {
                $curr = $curr->parent;
                if ($curr) {
                    $categoryAttributes = $curr->attributes()->with('values')->get();
                }
            }
        }

        $validAttrMap = $categoryAttributes->keyBy('id');

        if ($isSubmittingApproval) {
            $missingNames = [];
            foreach ($validAttrMap as $attrId => $attr) {
                $isRequired = (bool) ($attr->pivot->is_required ?? $attr->is_required);
                if ($isRequired) {
                    $rawVal = $attributesInput[$attrId] ?? null;
                    $hasVal = false;
                    if (is_array($rawVal)) {
                        $hasVal = (!empty($rawVal['value_text']) && trim($rawVal['value_text']) !== '')
                            || (isset($rawVal['value_number']) && $rawVal['value_number'] !== '' && $rawVal['value_number'] !== null)
                            || (isset($rawVal['value_boolean']) && $rawVal['value_boolean'] !== '' && $rawVal['value_boolean'] !== null)
                            || (!empty($rawVal['value_date']))
                            || (!empty($rawVal['value_json']))
                            || (!empty($rawVal['attribute_value_id']));
                    } elseif (!empty($rawVal)) {
                        $hasVal = true;
                    }

                    if (!$hasVal) {
                        $missingNames[] = $attr->name;
                    }
                }
            }

            if (!empty($missingNames)) {
                throw ValidationException::withMessages([
                    'attributes' => 'The following required specifications are missing: ' . implode(', ', $missingNames),
                ]);
            }
        }

        $processedAttrIds = [];

        foreach ($attributesInput as $attrId => $rawVal) {
            $attrId = (int) $attrId;
            if (!isset($validAttrMap[$attrId])) {
                continue;
            }

            $attr = $validAttrMap[$attrId];
            $processedAttrIds[] = $attrId;

            $saveData = [
                'attribute_value_id' => null,
                'value_text'         => null,
                'value_number'       => null,
                'value_boolean'      => null,
                'value_date'         => null,
                'value_json'         => null,
            ];

            if (is_array($rawVal)) {
                $valueText = isset($rawVal['value_text']) ? trim($rawVal['value_text']) : null;
                $valueNumber = isset($rawVal['value_number']) && $rawVal['value_number'] !== '' ? $rawVal['value_number'] : null;
                $valueBoolean = isset($rawVal['value_boolean']) && $rawVal['value_boolean'] !== '' ? (bool)$rawVal['value_boolean'] : null;
                $valueDate = !empty($rawVal['value_date']) ? $rawVal['value_date'] : null;
                $valueJson = isset($rawVal['value_json']) ? (is_array($rawVal['value_json']) ? $rawVal['value_json'] : json_decode($rawVal['value_json'], true)) : null;
                $attributeValueId = !empty($rawVal['attribute_value_id']) ? (int)$rawVal['attribute_value_id'] : null;
            } else {
                $valueText = is_string($rawVal) ? trim($rawVal) : null;
                $valueNumber = null;
                $valueBoolean = null;
                $valueDate = null;
                $valueJson = null;
                $attributeValueId = null;
            }

            switch ($attr->input_type) {
                case 'select':
                    $saveData['attribute_value_id'] = $attributeValueId;
                    if ($attributeValueId) {
                        $valObj = $attr->values->firstWhere('id', $attributeValueId);
                        $saveData['value_text'] = $valObj?->value;
                    }
                    break;

                case 'multi_select':
                    if (is_array($valueJson) && !empty($valueJson)) {
                        $cleanJson = array_values(array_filter($valueJson));
                        $saveData['value_json'] = $cleanJson;
                        $saveData['value_text'] = implode(', ', $cleanJson);
                    } elseif ($valueText !== null && $valueText !== '') {
                        $parts = array_values(array_filter(array_map('trim', explode(',', $valueText))));
                        $saveData['value_json'] = $parts;
                        $saveData['value_text'] = implode(', ', $parts);
                    }
                    break;

                case 'number':
                    $saveData['value_number'] = is_numeric($valueNumber) ? (float)$valueNumber : (is_numeric($valueText) ? (float)$valueText : null);
                    break;

                case 'boolean':
                    $saveData['value_boolean'] = $valueBoolean;
                    break;

                case 'date':
                    $saveData['value_date'] = $valueDate ?: $valueText;
                    break;

                case 'color':
                    $saveData['attribute_value_id'] = $attributeValueId;
                    $saveData['value_text'] = $valueText ?: ($attributeValueId ? $attr->values->firstWhere('id', $attributeValueId)?->value : null);
                    break;

                case 'textarea':
                case 'text':
                default:
                    $saveData['value_text'] = $valueText;
                    break;
            }

            $hasAnyValue = $saveData['attribute_value_id'] !== null
                || ($saveData['value_text'] !== null && $saveData['value_text'] !== '')
                || $saveData['value_number'] !== null
                || $saveData['value_boolean'] !== null
                || $saveData['value_date'] !== null
                || (!empty($saveData['value_json']));

            if ($hasAnyValue) {
                ListingAttributeValue::updateOrCreate(
                    [
                        'listing_id'   => $listing->id,
                        'attribute_id' => $attrId,
                    ],
                    $saveData
                );
            } else {
                ListingAttributeValue::where('listing_id', $listing->id)
                    ->where('attribute_id', $attrId)
                    ->delete();
            }
        }

        // Clean up any remaining attribute rows not present in this category
        ListingAttributeValue::where('listing_id', $listing->id)
            ->whereNotIn('attribute_id', $processedAttrIds)
            ->delete();
    }
}
