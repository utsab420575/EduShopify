<?php

namespace App\Http\Controllers\Backend\Supplier\Catalog;

use App\Http\Controllers\Backend\Supplier\Concerns\InteractsWithSupplierAccount;
use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Listing;
use App\Models\ListingAttributeValue;
use App\Models\ListingCategory;
use App\Models\ListingTierPrice;
use App\Models\ListingType;
use App\Models\ListingVariant;
use App\Models\ListingVariantAttribute;
use App\Models\ListingVariantMedia;
use App\Models\PricingType;
use App\Models\ProductDetail;
use App\Models\SalesMode;
use App\Models\ServiceDetail;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
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
            $query->whereHas('listingType', fn ($q) => $q->where('code', $request->string('type')));
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
        $units = Unit::where('is_active', true)
            ->where(function ($q) use ($account) {
                $q->where('scope', 'global')
                  ->orWhere('supplier_account_id', $account->id);
            })
            ->orderBy('name')
            ->get();
        $currencies = Currency::active()->orderByDesc('is_default')->orderBy('code')->get();
        $pricingTypes = PricingType::where('is_active', true)->ordered()->get();
        $salesModes   = SalesMode::where('is_active', true)->ordered()->get();
        $listingTypes = ListingType::where('is_active', true)->ordered()->get();

        return view('backend.supplier.catalog.listings.create', [
            'account'         => $account,
            'user'            => $this->currentUser(),
            'categoryOptions' => $categoryOptions,
            'brands'          => $brands,
            'units'           => $units,
            'currencies'      => $currencies,
            'pricingTypes'    => $pricingTypes,
            'salesModes'      => $salesModes,
            'listingTypes'    => $listingTypes,
        ]);
    }

    public function categoryAttributes(Category $category)
    {
        return response()->json($category->attributesGroupedForForm());
    }

    public function store(Request $request)
    {
        $account = $this->currentAccount();
        $isSubmitForApproval = $request->input('action') === 'submit_approval';

        if ($request->filled('listing_type') && ! $request->filled('listing_type_id')) {
            $lt = ListingType::firstOrCreate(
                ['code' => $request->input('listing_type')],
                ['name' => ucfirst($request->input('listing_type')), 'is_active' => true]
            );
            $request->merge(['listing_type_id' => $lt->id]);
        }
        if ($request->filled('pricing_type') && ! $request->filled('pricing_type_id')) {
            $pt = PricingType::firstOrCreate(
                ['code' => $request->input('pricing_type')],
                ['name' => ucfirst(str_replace('_', ' ', $request->input('pricing_type'))), 'is_active' => true]
            );
            $request->merge(['pricing_type_id' => $pt->id]);
        }
        if ($request->filled('sales_mode') && ! $request->filled('sales_mode_id')) {
            $sm = SalesMode::firstOrCreate(
                ['code' => $request->input('sales_mode')],
                ['name' => ucfirst(str_replace('_', ' ', $request->input('sales_mode'))), 'is_active' => true]
            );
            $request->merge(['sales_mode_id' => $sm->id]);
        }

        $validated = $request->validate([
            'listing_type_id' => ['required', 'exists:listing_types,id'],
            'name' => ['required', 'string', 'max:255'],
            'main_category_id' => ['required', 'exists:categories,id'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'sku' => ['nullable', 'string', 'max:100'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'pricing_type_id' => ['required', 'exists:pricing_types,id'],
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
                'listing_type_id' => $validated['listing_type_id'],
                'listing_number' => $listingNumber,
                'main_category_id' => $validated['main_category_id'],
                'brand_id' => $validated['brand_id'] ?? null,
                'name' => $validated['name'],
                'slug' => Str::slug($validated['name']) . '-' . strtolower(Str::random(5)),
                'sku' => $validated['sku'] ?? null,
                'short_description' => $validated['short_description'] ?? null,
                'description' => $validated['description'] ?? null,
                'pricing_type_id' => $validated['pricing_type_id'],
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

            if ($listing->isProduct()) {
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
                    'lead_time_days' => $validated['delivery_time_days'] ?? null,
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

        return view('backend.supplier.catalog.listings.show', array_merge(
            ['account' => $account, 'user' => $this->currentUser()],
            $this->buildListingDetailData($listing)
        ));
    }

    /**
     * Same read-only listing detail used by show(), rendered without the
     * page layout so the Step 4 "Preview Listing" modal can inject it
     * directly instead of navigating away from the wizard.
     */
    public function previewFragment(Listing $listing)
    {
        $account = $this->currentAccount();
        abort_if($listing->supplier_account_id !== $account->id, 403);

        $html = view(
            'backend.supplier.catalog.listings.partials.listing-preview',
            $this->buildListingDetailData($listing)
        )->render();

        return response()->json(['success' => true, 'html' => $html]);
    }

    /**
     * Eager-loads and shapes everything the read-only detail view (and its
     * preview-fragment twin) need: the listing with its variants — each
     * with its own photos, not the flat listing gallery — plus attribute
     * values grouped by specification group.
     */
    private function buildListingDetailData(Listing $listing): array
    {
        $listing->load([
            'mainCategory',
            'brand',
            'unit',
            'productDetail',
            'serviceDetail',
            'variants.variantAttributes.attribute',
            'variants.variantAttributes.attributeValue',
            'variants.images',
            'variants.tierPrices',
            'allTierPrices',
            'media',
            'attributeValues.attribute.attributeGroup',
            'attributeValues.attribute.unit',
            'attributeValues.attributeValue',
        ]);

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

        return [
            'listing'               => $listing,
            'groupedSpecifications' => $groupedSpecifications,
        ];
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
            'globalTierPrices',
            'variants.variantAttributes.attribute',
            'variants.variantAttributes.attributeValue',
            'variants.tierPrices',
            'variants.images',
            'media',
            'primaryImage',
        ]);

        $categoryOptions = Category::getTreeSelectOptions();
        $brands = Brand::where('is_active', true)->orderBy('name')->get();
        $units = Unit::where('is_active', true)
            ->where(function ($q) use ($account) {
                $q->where('scope', 'global')
                  ->orWhere('supplier_account_id', $account->id);
            })
            ->orderBy('name')
            ->get();
        $currencies = Currency::active()->orderByDesc('is_default')->orderBy('code')->get();

        // Format existing attribute values as reactive dictionary
        $existingValues = [];
        foreach ($listing->attributeValues as $val) {
            $valueJson = is_array($val->value_json) ? $val->value_json : ($val->value_text ? array_map('trim', explode(',', $val->value_text)) : []);
            $valueText = $val->value_text ?? (is_array($val->value_json) ? implode(', ', $val->value_json) : null);

            // A saved custom value with no real attribute_value_id means the
            // supplier had picked "Other" — re-select that sentinel so the
            // form reopens with Other chosen and the text pre-filled, rather
            // than showing nothing selected at all. Only select/color use
            // this sentinel; multi_select's custom value is independent of
            // attribute_value_id (which it never sets in the first place).
            $attributeValueId = $val->attribute_value_id;
            if ($attributeValueId === null
                && $val->custom_value !== null && $val->custom_value !== ''
                && in_array($val->attribute?->input_type, ['select', 'color'])) {
                $attributeValueId = '__other__';
            }

            $existingValues[$val->attribute_id] = [
                'attribute_value_id' => $attributeValueId,
                'value_text'         => $valueText,
                'value_number'       => $val->value_number !== null ? (float)$val->value_number : null,
                'value_boolean'      => $val->value_boolean,
                'value_date'         => $val->value_date ? (is_string($val->value_date) ? $val->value_date : $val->value_date->format('Y-m-d')) : null,
                'value_json'         => $valueJson,
                'custom_value'       => $val->custom_value,
            ];
        }

        return view('backend.supplier.catalog.listings.edit', [
            'account'         => $account,
            'user'            => $this->currentUser(),
            'listing'         => $listing,
            'categoryOptions' => $categoryOptions,
            'brands'          => $brands,
            'units'           => $units,
            'currencies'      => $currencies,
            'pricingTypes'    => PricingType::where('is_active', true)->ordered()->get(),
            'salesModes'      => SalesMode::where('is_active', true)->ordered()->get(),
            'listingTypes'    => ListingType::where('is_active', true)->ordered()->get(),
            'existingValues'  => $existingValues,
        ]);
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
                            || (!empty($rawVal['attribute_value_id']))
                            || (!empty($rawVal['custom_value']) && trim($rawVal['custom_value']) !== '');
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
                'custom_value'       => null,
            ];

            if (is_array($rawVal)) {
                $valueText = isset($rawVal['value_text']) ? trim($rawVal['value_text']) : null;
                $valueNumber = isset($rawVal['value_number']) && $rawVal['value_number'] !== '' ? $rawVal['value_number'] : null;
                $valueBoolean = isset($rawVal['value_boolean']) && $rawVal['value_boolean'] !== '' ? (bool)$rawVal['value_boolean'] : null;
                $valueDate = !empty($rawVal['value_date']) ? $rawVal['value_date'] : null;
                $valueJson = isset($rawVal['value_json']) ? (is_array($rawVal['value_json']) ? $rawVal['value_json'] : json_decode($rawVal['value_json'], true)) : null;
                $customValue = isset($rawVal['custom_value']) ? trim($rawVal['custom_value']) : null;
                $customValue = ($customValue !== null && $customValue !== '') ? $customValue : null;
                // "__other__" is the form's sentinel for "supplier picked Other" —
                // it must never be cast/stored as a real attribute_value_id.
                $isOtherSelected = ($rawVal['attribute_value_id'] ?? null) === '__other__';
                $attributeValueId = (!$isOtherSelected && !empty($rawVal['attribute_value_id']))
                    ? (int)$rawVal['attribute_value_id']
                    : null;
            } else {
                $valueText = is_string($rawVal) ? trim($rawVal) : null;
                $valueNumber = null;
                $valueBoolean = null;
                $valueDate = null;
                $valueJson = null;
                $customValue = null;
                $isOtherSelected = false;
                $attributeValueId = null;
            }

            switch ($attr->input_type) {
                case 'select':
                    if ($isOtherSelected && $customValue !== null) {
                        $saveData['custom_value'] = $customValue;
                    } else {
                        $saveData['attribute_value_id'] = $attributeValueId;
                        if ($attributeValueId) {
                            $valObj = $attr->values->firstWhere('id', $attributeValueId);
                            $saveData['value_text'] = $valObj?->value;
                        }
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
                    // Exactly one custom "Other" entry may sit alongside the
                    // predefined picks above — not a replacement for them.
                    $saveData['custom_value'] = $customValue;
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
                    if ($isOtherSelected && $customValue !== null) {
                        $saveData['custom_value'] = $customValue;
                    } else {
                        $saveData['attribute_value_id'] = $attributeValueId;
                        $saveData['value_text'] = $attributeValueId ? $attr->values->firstWhere('id', $attributeValueId)?->value : null;
                    }
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
                || (!empty($saveData['value_json']))
                || ($saveData['custom_value'] !== null && $saveData['custom_value'] !== '');

            if ($hasAnyValue) {
                ListingAttributeValue::updateOrCreate(
                    [
                        'listing_id'   => $listing->id,
                        'attribute_id' => $attrId,
                    ],
                    $saveData
                );

                // Track custom value review if custom value was provided
                if (!empty($saveData['custom_value'])) {
                    \App\Models\AttributeCustomValueReview::firstOrCreate(
                        [
                            'attribute_id' => $attrId,
                            'custom_value' => trim($saveData['custom_value']),
                        ],
                        [
                            'supplier_account_id' => $listing->supplier_account_id,
                            'first_listing_id'    => $listing->id,
                            'submitted_by_user_id'=> $this->currentUser()?->id,
                            'usage_count'         => 1,
                            'status'              => 'pending',
                        ]
                    );
                }
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

    /* ── Step-by-Step Wizard Endpoints ───────────────────────────────────── */

    public function saveStep1(Request $request)
    {
        $account = $this->currentAccount();
        $listingId = $request->input('listing_id');
        $listing = $listingId ? $account->listings()->findOrFail($listingId) : null;

        if ($request->filled('listing_type') && ! $request->filled('listing_type_id')) {
            $lt = ListingType::firstOrCreate(
                ['code' => $request->input('listing_type')],
                ['name' => ucfirst($request->input('listing_type')), 'is_active' => true]
            );
            $request->merge(['listing_type_id' => $lt->id]);
        }

        $validated = $request->validate([
            'listing_type_id'   => ['required', 'exists:listing_types,id'],
            'name'              => ['required', 'string', 'max:255'],
            'brand_id'          => ['nullable', 'exists:brands,id'],
            'sku'               => [
                'nullable',
                'string',
                'max:100',
                \Illuminate\Validation\Rule::unique('listings', 'sku')
                    ->where('supplier_account_id', $account->id)
                    ->ignore($listing?->id),
            ],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description'       => ['nullable', 'string'],
            'images.*'          => ['nullable', 'image', 'max:10240'],
        ]);

        $listing = DB::transaction(function () use ($account, $listing, $validated, $request) {
            if (! $listing) {
                $listingNumber = 'LST-' . strtoupper(Str::random(8));
                $slug = Str::slug($validated['name']) . '-' . strtolower(Str::random(5));

                $listing = Listing::create([
                    'supplier_account_id' => $account->id,
                    'created_by_user_id'  => $this->currentUser()->id,
                    'listing_type_id'     => $validated['listing_type_id'],
                    'listing_number'      => $listingNumber,
                    'name'                => $validated['name'],
                    'slug'                => $slug,
                    'brand_id'            => $validated['brand_id'] ?? null,
                    'sku'                 => $validated['sku'] ?? null,
                    'short_description'   => $validated['short_description'] ?? null,
                    'description'         => $validated['description'] ?? null,
                    'approval_status'     => 'draft',
                    'setup_step'          => 2,
                    'last_autosaved_at'   => now(),
                    'is_active'           => true,
                ]);

                if ($listing->isProduct()) {
                    ProductDetail::create(['listing_id' => $listing->id]);
                } else {
                    ServiceDetail::create(['listing_id' => $listing->id]);
                }
            } else {
                $listing->update([
                    'listing_type_id'   => $validated['listing_type_id'],
                    'name'              => $validated['name'],
                    'brand_id'          => $validated['brand_id'] ?? null,
                    'sku'               => $validated['sku'] ?? null,
                    'short_description' => $validated['short_description'] ?? null,
                    'description'       => $validated['description'] ?? null,
                    'setup_step'        => max($listing->setup_step, 2),
                    'last_autosaved_at' => now(),
                ]);
            }

            // Process uploaded images
            if ($request->hasFile('images')) {
                $coverIdx = $request->input('cover_image_index');
                $newMediaItems = [];
                foreach ($request->file('images') as $idx => $file) {
                    if ($file->isValid()) {
                        $media = $listing->addMedia($file)
                            ->usingFileName(\App\Support\Media\ProductImagePathGenerator::supplierFileName($account->id, $file->getClientOriginalName()))
                            ->toMediaCollection('gallery');

                        $newMediaItems[$idx] = $media;
                    }
                }

                if ($coverIdx !== null && isset($newMediaItems[$coverIdx])) {
                    $listing->update(['primary_image_media_id' => $newMediaItems[$coverIdx]->id]);
                } elseif (! $listing->primary_image_media_id && ! empty($newMediaItems)) {
                    $listing->update(['primary_image_media_id' => reset($newMediaItems)->id]);
                }
            }

            return $listing->fresh(['media', 'primaryImage']);
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success'                => true,
                'message'                => 'Basics & media saved.',
                'listing_id'             => $listing->id,
                'setup_step'             => $listing->setup_step,
                'primary_image_media_id' => $listing->primary_image_media_id,
                'media'                  => $listing->getMedia('gallery')->map(fn ($m) => [
                    'id'         => $m->id,
                    'url'        => $m->getUrl(),
                    'file_name'  => $m->file_name,
                    'size'       => $m->human_readable_size,
                    'is_primary' => $m->id === $listing->primary_image_media_id,
                ]),
                'last_autosaved_at'      => $listing->last_autosaved_at?->toISOString() ?? now()->toISOString(),
            ]);
        }

        return redirect()->route('supplier.catalog.listings.edit', ['listing' => $listing, 'step' => 2])
            ->with('success', 'Product basics saved.');
    }

    public function setPrimaryMedia(Request $request, Listing $listing)
    {
        $account = $this->currentAccount();
        abort_if($listing->supplier_account_id !== $account->id, 403);

        $request->validate([
            'media_id' => ['required', 'exists:media,id'],
        ]);

        $media = $listing->media()->findOrFail($request->integer('media_id'));
        $listing->update(['primary_image_media_id' => $media->id]);

        return response()->json([
            'success'                => true,
            'message'                => 'Cover image updated.',
            'primary_image_media_id' => $media->id,
        ]);
    }

    public function saveStep2(Request $request, Listing $listing)
    {
        $account = $this->currentAccount();
        abort_if($listing->supplier_account_id !== $account->id, 403);

        $validated = $request->validate([
            'main_category_id' => ['required', 'exists:categories,id'],
            'attributes'       => ['nullable', 'array'],
        ]);

        $category = Category::findOrFail($validated['main_category_id']);

        DB::transaction(function () use ($listing, $category, $request) {
            $listing->update([
                'main_category_id'  => $category->id,
                'setup_step'        => max($listing->setup_step, 3),
                'last_autosaved_at' => now(),
            ]);

            // Sync category relation
            ListingCategory::updateOrCreate(
                ['listing_id' => $listing->id, 'category_id' => $category->id],
                ['is_primary' => true]
            );

            // Sync attributes & custom value reviews
            $this->syncAttributeValues($listing, $category, $request->input('attributes', []), false);
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success'           => true,
                'message'           => 'Category and specifications saved.',
                'setup_step'        => $listing->fresh()->setup_step,
                'last_autosaved_at' => $listing->fresh()->last_autosaved_at?->toISOString() ?? now()->toISOString(),
            ]);
        }

        return redirect()->route('supplier.catalog.listings.edit', ['listing' => $listing, 'step' => 3])
            ->with('success', 'Specifications saved.');
    }

    public function saveStep3(Request $request, Listing $listing)
    {
        $account = $this->currentAccount();
        abort_if($listing->supplier_account_id !== $account->id, 403);

        $hasTierPricing = $request->boolean('has_tier_pricing');
        $request->merge([
            'has_tier_pricing' => $hasTierPricing,
        ]);
        if (! $hasTierPricing) {
            $request->request->remove('tiers');
        }

        // Handle Custom Currency
        if ($request->input('currency_code') === '__other__') {
            $customCode = strtoupper(trim((string)$request->input('custom_currency_code')));
            if (empty($customCode)) {
                throw ValidationException::withMessages([
                    'custom_currency_code' => 'Please enter a valid 3-character currency code.',
                ]);
            }
            if (strlen($customCode) > 3) {
                throw ValidationException::withMessages([
                    'custom_currency_code' => 'Currency code must not exceed 3 characters (e.g. USD, EUR, BDT).',
                ]);
            }

            Currency::firstOrCreate(
                ['code' => $customCode],
                [
                    'name'           => $request->input('custom_currency_name') ?: $customCode,
                    'symbol'         => $request->input('custom_currency_symbol') ?: $customCode,
                    'exchange_rate'  => 1.0,
                    'is_default'     => false,
                    'is_active'      => true,
                    'decimal_places' => 2,
                ]
            );
            $request->merge(['currency_code' => $customCode]);
        }

        // Handle Custom Unit
        if ($request->input('unit_id') === '__other__') {
            $customUnitName = trim((string)$request->input('custom_unit_name'));
            if (empty($customUnitName)) {
                throw ValidationException::withMessages([
                    'custom_unit_name' => 'Please specify your custom unit name (e.g. Carton, Bundle, Set of 10).',
                ]);
            }

            $customUnitSymbol = trim((string)$request->input('custom_unit_symbol')) ?: strtolower($customUnitName);
            $unit = Unit::firstOrCreate(
                [
                    'supplier_account_id' => $account->id,
                    'name'                => $customUnitName,
                ],
                [
                    'symbol'              => $customUnitSymbol,
                    'unit_type'           => 'count',
                    'scope'               => 'supplier_custom',
                    'approval_status'     => 'approved',
                    'is_active'           => true,
                    'created_by_user_id'  => $this->currentUser()?->id,
                ]
            );
            $request->merge(['unit_id' => $unit->id]);
        }
        if ($request->filled('pricing_type') && ! $request->filled('pricing_type_id')) {
            $pt = PricingType::firstOrCreate(
                ['code' => $request->input('pricing_type')],
                ['name' => ucfirst(str_replace('_', ' ', $request->input('pricing_type'))), 'is_active' => true]
            );
            $request->merge(['pricing_type_id' => $pt->id]);
        }

        $validated = $request->validate([
            'pricing_type_id'      => ['required', 'exists:pricing_types,id'],
            'base_price'           => ['nullable', 'numeric', 'min:0'],
            'compare_at_price'     => ['nullable', 'numeric', 'min:0'],
            'currency_code'        => ['required', 'string', 'max:3'],
            'min_order_quantity'   => ['nullable', 'numeric', 'min:1'],
            'unit_id'              => ['nullable', 'exists:units,id'],
            'stock_status'         => ['nullable', 'in:in_stock,out_of_stock,limited,on_request'],
            'stock_quantity'       => ['nullable', 'numeric', 'min:0'],
            'lead_time_days'       => ['nullable', 'integer', 'min:0'],
            'warranty_terms'       => ['nullable', 'string', 'max:500'],
            'has_tier_pricing'     => ['nullable', 'boolean'],
            'tiers'                => ['nullable', 'array'],
            'tiers.*.min_quantity' => ['required_if:has_tier_pricing,true', 'numeric', 'min:1'],
            'tiers.*.max_quantity' => ['nullable', 'numeric', 'min:1'],
            'tiers.*.unit_price'   => ['required_if:has_tier_pricing,true', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($listing, $validated, $request) {
            $listing->update([
                'pricing_type_id'    => $validated['pricing_type_id'],
                'base_price'         => $validated['base_price'] ?? null,
                'compare_at_price'   => $validated['compare_at_price'] ?? null,
                'currency_code'      => $validated['currency_code'],
                'min_order_quantity' => $validated['min_order_quantity'] ?? 1,
                'unit_id'            => $validated['unit_id'] ?? null,
                'setup_step'         => max($listing->setup_step, 4),
                'last_autosaved_at'  => now(),
            ]);

            if ($listing->isProduct()) {
                ProductDetail::updateOrCreate(
                    ['listing_id' => $listing->id],
                    [
                        'stock_status'   => $validated['stock_status'] ?? 'on_request',
                        'stock_quantity' => $validated['stock_quantity'] ?? null,
                        'lead_time_days' => $validated['lead_time_days'] ?? null,
                        'warranty_terms' => $validated['warranty_terms'] ?? null,
                    ]
                );
            }

            // Sync Tier Prices
            $listing->globalTierPrices()->delete();
            if ($request->boolean('has_tier_pricing') && !empty($validated['tiers'])) {
                foreach ($validated['tiers'] as $tier) {
                    $listing->globalTierPrices()->create([
                        'min_quantity'  => $tier['min_quantity'],
                        'max_quantity'  => $tier['max_quantity'] ?? null,
                        'unit_price'    => $tier['unit_price'],
                        'currency_code' => $listing->currency_code,
                    ]);
                }
            }
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success'           => true,
                'message'           => 'Pricing & inventory saved.',
                'setup_step'        => $listing->fresh()->setup_step,
                'last_autosaved_at' => $listing->fresh()->last_autosaved_at?->toISOString() ?? now()->toISOString(),
            ]);
        }

        return redirect()->route('supplier.catalog.listings.edit', ['listing' => $listing, 'step' => 4])
            ->with('success', 'Pricing saved.');
    }

    public function saveStep4(Request $request, Listing $listing)
    {
        $account = $this->currentAccount();
        abort_if($listing->supplier_account_id !== $account->id, 403);

        $action = $request->input('action', 'save_draft');
        $duplicateVariantsSkipped = 0;

        // Persist variant matrix if submitted
        if ($request->has('variants') && is_array($request->input('variants'))) {
            $submittedVariants = $request->input('variants');

            // Validate the entire payload up front — before any database
            // write happens — so a bad row can't leave a partial save behind.
            Validator::make(['variants' => $submittedVariants], [
                'variants'                              => ['array'],
                'variants.*.name'                       => ['required', 'string', 'max:255'],
                'variants.*.sku'                         => ['nullable', 'string', 'max:100'],
                'variants.*.price'                       => ['nullable', 'numeric', 'min:0'],
                'variants.*.compare_at_price'            => ['nullable', 'numeric', 'min:0'],
                'variants.*.stock_quantity'              => ['nullable', 'numeric', 'min:0'],
                'variants.*.stock_status'                => ['nullable', 'in:in_stock,out_of_stock,limited,on_request'],
                'variants.*.attributes'                  => ['nullable', 'array'],
                'variants.*.image_media_ids'             => ['nullable', 'array'],
                'variants.*.image_media_ids.*'           => ['integer'],
                'variants.*.primary_image_media_id'      => ['nullable', 'integer'],
                'variants.*.tier_prices'                 => ['nullable', 'array'],
                'variants.*.tier_prices.*.min_quantity'  => ['required_with:variants.*.tier_prices', 'numeric', 'min:1'],
                'variants.*.tier_prices.*.max_quantity'  => ['nullable', 'numeric', 'min:1'],
                'variants.*.tier_prices.*.unit_price'    => ['required_with:variants.*.tier_prices', 'numeric', 'min:0'],
            ])->validate();

            DB::transaction(function () use ($listing, $submittedVariants, &$duplicateVariantsSkipped) {
                // Load existing variants once, indexed by ID and by
                // combination_key, so every submitted row can be matched to
                // its existing record without a query per row.
                $existingVariants = $listing->variants()->get();
                $existingById = $existingVariants->keyBy('id');
                $existingByCombKey = $existingVariants->whereNotNull('combination_key')->keyBy('combination_key');
                $listingMediaIds = $listing->media()->pluck('id')->toArray();

                // Pass 1: resolve identity + drop duplicate combinations.
                // No writes yet — this is pure planning.
                $plan = [];
                $seenCombKeys = [];
                $sort = 0;

                foreach ($submittedVariants as $item) {
                    if (empty($item['name'])) continue;

                    $attributes = (isset($item['attributes']) && is_array($item['attributes'])) ? $item['attributes'] : [];
                    // combination_key is only ever generated server-side —
                    // the client never sends one and any submitted value is ignored.
                    $combKey = ListingVariant::generateCombinationKey($attributes);

                    if ($combKey !== null) {
                        if (in_array($combKey, $seenCombKeys, true)) {
                            $duplicateVariantsSkipped++;
                            continue;
                        }
                        $seenCombKeys[] = $combKey;
                    }

                    $sort++;
                    $variantId = !empty($item['id']) ? (int) $item['id'] : null;
                    $existing = $variantId ? $existingById->get($variantId) : null;
                    if (! $existing && $combKey !== null) {
                        $existing = $existingByCombKey->get($combKey);
                    }

                    $plan[] = [
                        'existing'   => $existing,
                        'attributes' => $attributes,
                        'comb_key'   => $combKey,
                        'sort'       => $sort,
                        'raw'        => $item,
                    ];
                }

                // Pass 2: upsert variant rows one at a time (each needs its
                // own primary key back), skipping the write entirely when
                // nothing actually changed. This rebuilds the ID map that
                // every child-table insert below depends on.
                $idMap = [];
                $keptIds = [];

                foreach ($plan as $i => $row) {
                    $item = $row['raw'];
                    $data = [
                        'name'             => $item['name'],
                        'sku'              => $item['sku'] ?? null,
                        'combination_key'  => $row['comb_key'],
                        'price'            => isset($item['price']) && $item['price'] !== '' ? (float) $item['price'] : ($listing->base_price ?? 0),
                        'compare_at_price' => isset($item['compare_at_price']) && $item['compare_at_price'] !== '' ? (float) $item['compare_at_price'] : null,
                        'currency_code'    => $listing->currency_code,
                        'stock_quantity'   => isset($item['stock_quantity']) && $item['stock_quantity'] !== '' ? (float) $item['stock_quantity'] : 0,
                        'stock_status'     => $item['stock_status'] ?? 'in_stock',
                        'is_active'        => true,
                        'sort_order'       => $row['sort'],
                    ];

                    $variant = $row['existing'];
                    if ($variant) {
                        $variant->fill($data);
                        if ($variant->isDirty()) {
                            $variant->save();
                        }
                    } else {
                        $variant = $listing->variants()->create($data);
                    }

                    $idMap[$i] = $variant->id;
                    $keptIds[] = $variant->id;
                }

                // Pass 3: batch-clear child rows for the kept variants (one
                // query per table, not one per variant) then batch-insert
                // the fresh set using the ID map rebuilt above.
                if (! empty($keptIds)) {
                    ListingVariantAttribute::whereIn('listing_variant_id', $keptIds)->delete();
                    ListingVariantMedia::whereIn('listing_variant_id', $keptIds)->delete();
                    ListingTierPrice::whereIn('listing_variant_id', $keptIds)->delete();
                }

                $now = now();
                $attributeRows = [];
                $mediaRows = [];
                $tierRows = [];

                foreach ($plan as $i => $row) {
                    $variantId = $idMap[$i];
                    $item = $row['raw'];

                    foreach ($row['attributes'] as $attrId => $val) {
                        if ($val === null || $val === '') continue;
                        $attributeRows[] = [
                            'listing_variant_id' => $variantId,
                            'attribute_id'       => (int) $attrId,
                            'attribute_value_id' => is_numeric($val) ? (int) $val : null,
                            'custom_value'       => ! is_numeric($val) ? (string) $val : null,
                            'created_at'         => $now,
                            'updated_at'         => $now,
                        ];
                    }

                    $primaryMediaId = ! empty($item['primary_image_media_id']) ? (int) $item['primary_image_media_id'] : null;
                    $assignedMediaIds = isset($item['image_media_ids']) && is_array($item['image_media_ids'])
                        ? array_values(array_filter(array_map('intval', $item['image_media_ids'])))
                        : ($primaryMediaId ? [$primaryMediaId] : []);

                    $validAssignedIds = array_intersect($assignedMediaIds, $listingMediaIds);
                    if ($primaryMediaId && in_array($primaryMediaId, $listingMediaIds) && ! in_array($primaryMediaId, $validAssignedIds)) {
                        $validAssignedIds[] = $primaryMediaId;
                    }

                    $mSort = 0;
                    foreach ($validAssignedIds as $mid) {
                        $mSort++;
                        $mediaRows[] = [
                            'listing_variant_id' => $variantId,
                            'media_id'           => $mid,
                            'is_primary'         => ($mid === $primaryMediaId) || ($primaryMediaId === null && $mSort === 1),
                            'sort_order'         => $mSort,
                            'created_at'         => $now,
                            'updated_at'         => $now,
                        ];
                    }

                    if (isset($item['tier_prices']) && is_array($item['tier_prices'])) {
                        foreach ($item['tier_prices'] as $tp) {
                            if (! isset($tp['min_quantity']) || ! isset($tp['unit_price']) || $tp['unit_price'] === '') continue;
                            $tierRows[] = [
                                'listing_id'         => $listing->id,
                                'listing_variant_id' => $variantId,
                                'min_quantity'       => (float) $tp['min_quantity'],
                                'max_quantity'       => ! empty($tp['max_quantity']) ? (float) $tp['max_quantity'] : null,
                                'unit_price'         => (float) $tp['unit_price'],
                                'currency_code'      => $listing->currency_code,
                                'created_at'         => $now,
                                'updated_at'         => $now,
                            ];
                        }
                    }
                }

                foreach (array_chunk($attributeRows, 500) as $chunk) {
                    ListingVariantAttribute::insert($chunk);
                }
                foreach (array_chunk($mediaRows, 500) as $chunk) {
                    ListingVariantMedia::insert($chunk);
                }
                foreach (array_chunk($tierRows, 500) as $chunk) {
                    ListingTierPrice::insert($chunk);
                }

                // Anything not resubmitted this round is gone.
                if (! empty($keptIds)) {
                    $listing->variants()->whereNotIn('id', $keptIds)->delete();
                } elseif ($existingVariants->isNotEmpty()) {
                    $listing->variants()->delete();
                }
            });
        }

        if ($action === 'submit_approval') {
            if (empty($listing->name) || ! $listing->main_category_id) {
                throw ValidationException::withMessages([
                    'general' => 'Cannot submit for approval: Title and category are mandatory.',
                ]);
            }

            // If variable product, ensure all variants have valid combination keys
            if ($listing->variants()->count() > 0 && $listing->variants()->whereNull('combination_key')->exists()) {
                throw ValidationException::withMessages([
                    'variants' => 'Every variant must have a valid attribute combination before submission.',
                ]);
            }

            $listing->update([
                'approval_status'    => 'pending',
                'setup_completed_at' => now(),
                'last_autosaved_at'  => now(),
            ]);

            activity('supplier_catalog')
                ->causedBy($this->currentUser())
                ->performedOn($listing)
                ->log('Listing submitted for platform approval');

            if ($request->wantsJson()) {
                return response()->json([
                    'success'                   => true,
                    'message'                   => 'Listing submitted for platform approval!',
                    'redirect_url'              => route('supplier.catalog.listings.show', $listing),
                    'duplicate_variants_skipped' => $duplicateVariantsSkipped,
                ]);
            }

            return redirect()->route('supplier.catalog.listings.show', $listing)
                ->with('success', 'Listing submitted for platform approval!');
        }

        $listing->update([
            'approval_status'   => 'draft',
            'last_autosaved_at' => now(),
        ]);

        if ($request->wantsJson()) {
            $freshVariants = $listing->variants()->with(['images', 'tierPrices', 'variantAttributes.attribute', 'variantAttributes.attributeValue'])->get()->map(function ($v) {
                $primaryMedia = $v->images->firstWhere('pivot.is_primary', true) ?? $v->images->first();
                return [
                    'id'                     => $v->id,
                    'name'                   => $v->name,
                    'sku'                    => $v->sku,
                    'price'                  => (float)$v->price,
                    'compare_at_price'       => $v->compare_at_price ? (float)$v->compare_at_price : null,
                    'stock_quantity'         => (float)$v->stock_quantity,
                    'stock_status'           => $v->stock_status ?? 'in_stock',
                    'primary_image_media_id' => $primaryMedia?->id,
                    'image_media_ids'        => $v->images->pluck('id')->values()->toArray(),
                    'tier_prices'            => $v->tierPrices->map(fn ($tp) => [
                        'min_quantity' => (float)$tp->min_quantity,
                        'max_quantity' => $tp->max_quantity ? (float)$tp->max_quantity : null,
                        'unit_price'   => (float)$tp->unit_price,
                    ])->values()->toArray(),
                    'attributes'             => $v->variantAttributes->pluck('attribute_value_id', 'attribute_id')->toArray(),
                    'attribute_chips'        => $v->variantAttributes->map(fn ($va) => [
                        'name'  => $va->attribute?->name,
                        'value' => $va->attributeValue?->value ?? $va->custom_value,
                    ])->values()->toArray(),
                ];
            })->values()->toArray();

            return response()->json([
                'success'                    => true,
                'message'                    => 'Draft saved successfully.',
                'last_autosaved_at'          => $listing->fresh()->last_autosaved_at?->toISOString() ?? now()->toISOString(),
                'variants'                   => $freshVariants,
                'redirect_url'               => route('supplier.catalog.listings.index'),
                'preview_url'                => route('supplier.catalog.listings.show', $listing),
                'duplicate_variants_skipped' => $duplicateVariantsSkipped,
            ]);
        }

        return redirect()->route('supplier.catalog.listings.index')->with('success', 'Draft saved.');
    }
}
