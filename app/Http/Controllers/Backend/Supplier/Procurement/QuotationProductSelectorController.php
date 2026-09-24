<?php

namespace App\Http\Controllers\Backend\Supplier\Procurement;

use App\Http\Controllers\Backend\Supplier\Concerns\InteractsWithSupplierAccount;
use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Listing;
use App\Models\Quotation;
use App\Models\Rfq;
use App\Models\RfqItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class QuotationProductSelectorController extends Controller
{
    use InteractsWithSupplierAccount;

    /**
     * GET supplier/select-products-for-quotation
     * GET supplier/quotations/create/{rfq}/select-product
     *
     * Dedicated marketplace product selector allowing suppliers to pick one or
     * multiple marketplace listings to offer against an RFQ requirement.
     * Features an explainable Matching Products tab (ranked with reasons)
     * and a Browse All Products tab with search, categories, and brands.
     */
    public function index(Request $request, ?Rfq $rfq = null)
    {
        if (! $rfq && $request->filled('rfq_id')) {
            $rfq = Rfq::findOrFail($request->integer('rfq_id'));
        }

        if ($request->filled('rfq_item_id')) {
            $rfqItem = RfqItem::with(['rfq', 'category', 'attributeValues.attribute', 'attributeValues.attributeValue'])
                ->findOrFail($request->integer('rfq_item_id'));
            $rfq = $rfq ?? $rfqItem->rfq;
        } elseif ($rfq && $rfq->items()->exists()) {
            $rfqItem = $rfq->items()->with(['category', 'attributeValues.attribute', 'attributeValues.attributeValue'])->first();
        } else {
            abort(404, 'RFQ item required.');
        }

        $this->authorize('selectProducts', [Quotation::class, $rfq]);

        $account = $this->currentAccount();
        $baseQuery = fn () => Listing::query()->where(function (Builder $query) use ($account) {
            $query->where(function (Builder $q) {
                $q->published()
                    ->orWhere(function (Builder $p) {
                        $p->where('approval_status', 'approved')
                            ->where('is_active', true);
                    });
            });
            if ($account) {
                $query->orWhere(function (Builder $q) use ($account) {
                    $q->where('supplier_account_id', $account->id)
                        ->where('approval_status', 'approved');
                });
            }
        });

        // ── 1. Smart Matching Engine ──────────────────────────────────────────
        $candidates = $baseQuery()
            ->with(['mainCategory', 'primaryImage', 'brand', 'supplierAccount.supplierProfile', 'attributeValues.attribute', 'attributeValues.attributeValue'])
            ->get();

        // A. Resolve All Relevant Categories (Primary + Buyer Category Suggestions)
        $relevantCategoryIds = [];
        if ($rfqItem->category_id) {
            $relevantCategoryIds[] = (int) $rfqItem->category_id;
        }

        $specsArray = is_array($rfqItem->specs) ? $rfqItem->specs : [];
        foreach ($specsArray as $spec) {
            if (is_array($spec) && ($spec['name'] ?? '') === '__category_ids') {
                foreach (explode(',', (string) ($spec['value'] ?? '')) as $cid) {
                    $cid = (int) trim($cid);
                    if ($cid && ! in_array($cid, $relevantCategoryIds, true)) {
                        $relevantCategoryIds[] = $cid;
                    }
                }
            }
        }

        $relevantCategories = Category::with('parent')->whereIn('id', $relevantCategoryIds)->get()->keyBy('id');
        $allCategoryWeights = [];
        $categoryMatchLabels = [];

        foreach ($relevantCategoryIds as $cid) {
            $cat = $relevantCategories->get($cid);
            if (! $cat) {
                continue;
            }
            $isPrimary = ($cid === (int) $rfqItem->category_id);
            $allCategoryWeights[$cid] = $isPrimary ? 40 : 35;
            $categoryMatchLabels[$cid] = $isPrimary
                ? 'Category matched (' . $cat->name . ')'
                : 'Suggested category matched (' . $cat->name . ')';

            // Descendant subcategories
            foreach ($cat->descendantIds() as $did) {
                if (! isset($allCategoryWeights[$did])) {
                    $allCategoryWeights[$did] = 30;
                    $categoryMatchLabels[$did] = 'Subcategory matched (' . $cat->name . ')';
                }
            }

            // Ancestor parent categories
            $curr = $cat->parent;
            while ($curr) {
                if (! isset($allCategoryWeights[$curr->id])) {
                    $allCategoryWeights[$curr->id] = 20;
                    $categoryMatchLabels[$curr->id] = 'Parent category matched (' . $curr->name . ')';
                }
                $curr = $curr->parent;
            }
        }

        // B. Tokenize buyer requirement title + suggested category names for keyword overlap
        $stopWords = ['i', 'need', 'a', 'an', 'the', 'for', 'with', 'and', 'or', 'to', 'in', 'of', 'please', 'required', 'requirement'];
        $textToTokenize = strtolower($rfqItem->item_name ?? '');
        foreach ($relevantCategories as $cat) {
            $textToTokenize .= ' ' . strtolower($cat->name);
        }

        $rfqTokens = array_unique(array_filter(
            preg_split('/[\s,\-\/]+/', $textToTokenize),
            fn ($w) => strlen($w) >= 3 && ! in_array($w, $stopWords, true)
        ));

        // C. Extract custom specs from $rfqItem->specs (excluding reserved internal metadata)
        $rfqCustomSpecs = [];
        foreach ($specsArray as $spec) {
            if (is_array($spec)) {
                $sName = strtolower(trim((string) ($spec['name'] ?? '')));
                $sVal = strtolower(trim((string) ($spec['value'] ?? '')));
                if ($sName && $sVal && ! in_array($sName, ['__is_requirement', '__category_ids'], true)) {
                    $rfqCustomSpecs[$sName] = $sVal;
                }
            }
        }

        $matches = $candidates
            ->map(function (Listing $listing) use ($rfqItem, $allCategoryWeights, $categoryMatchLabels, $rfqTokens, $rfqCustomSpecs) {
                $score = 0;
                $reasons = [];

                // 1. Category Matching (Primary, Suggested, Subcategory, Parent)
                if ($listing->main_category_id && isset($allCategoryWeights[$listing->main_category_id])) {
                    $score += $allCategoryWeights[$listing->main_category_id];
                    $reasons[] = $categoryMatchLabels[$listing->main_category_id];
                }

                // 2. Name & Keyword Matching
                $listingNameLower = strtolower($listing->name ?? '');
                $matchedWords = [];
                foreach ($rfqTokens as $token) {
                    if (str_contains($listingNameLower, $token)) {
                        $matchedWords[] = ucfirst($token);
                    }
                }
                if (! empty($matchedWords)) {
                    $score += min(30, count($matchedWords) * 15);
                    $reasons[] = 'Keyword matched: ' . implode(', ', array_slice($matchedWords, 0, 3));
                }

                similar_text(strtolower($rfqItem->item_name ?? ''), $listingNameLower, $similarityPct);
                if ($similarityPct >= 45) {
                    $score += ($similarityPct / 100) * 20;
                    $reasons[] = 'Product name similarity (' . round($similarityPct) . '%)';
                }

                // 3. Structured & Custom Attribute / Specification Matching
                $listingAttrMap = [];
                foreach ($listing->attributeValues as $val) {
                    $attrName = strtolower($val->attribute?->name ?? '');
                    if ($attrName) {
                        $listingAttrMap[$attrName] = strtolower($val->value_text ?? $val->attributeValue?->value ?? (string) $val->value_number ?? '');
                    }
                }

                // Category-defined attributes
                foreach ($rfqItem->attributeValues as $rfqVal) {
                    $attrName = strtolower($rfqVal->attribute?->name ?? '');
                    $rfqValue = strtolower($rfqVal->value_text ?? $rfqVal->attributeValue?->value ?? (string) $rfqVal->value_number ?? '');
                    if ($attrName && $rfqValue && isset($listingAttrMap[$attrName])) {
                        if (str_contains($listingAttrMap[$attrName], $rfqValue) || str_contains($rfqValue, $listingAttrMap[$attrName])) {
                            $score += 15;
                            $reasons[] = ($rfqVal->attribute?->name ?? 'Attribute') . ' matched (' . ($rfqVal->attributeValue?->value ?? $rfqValue) . ')';
                        }
                    }
                }

                // Custom specs (from buyer requirement form)
                foreach ($rfqCustomSpecs as $specKey => $specVal) {
                    if (isset($listingAttrMap[$specKey])) {
                        if (str_contains($listingAttrMap[$specKey], $specVal) || str_contains($specVal, $listingAttrMap[$specKey])) {
                            $score += 15;
                            $reasons[] = ucfirst($specKey) . ' matched (' . $specVal . ')';
                        }
                    } elseif (str_contains($listingNameLower, $specVal) || str_contains(strtolower($listing->description ?? ''), $specVal)) {
                        $score += 10;
                        $reasons[] = ucfirst($specKey) . ' matched in description';
                    }
                }

                // 4. Budget Proximity Matching
                if ($rfqItem->estimated_unit_price && $listing->base_price) {
                    $est = (float) $rfqItem->estimated_unit_price;
                    $price = (float) $listing->base_price;
                    if ($est > 0 && $price >= ($est * 0.5) && $price <= ($est * 1.5)) {
                        $score += 10;
                        $reasons[] = 'Within target budget range';
                    }
                }

                $finalPercentage = min(99, max(15, (int) round($score)));
                $listing->match_score = $finalPercentage / 100;
                $listing->match_percentage = $finalPercentage;
                $listing->matched_reasons = array_values(array_unique($reasons));

                return $listing;
            })
            ->filter(fn (Listing $listing) => $listing->match_percentage >= 30)
            ->sortByDesc('match_percentage')
            ->values();

        // ── 2. Browse & Filter Engine ─────────────────────────────────────────
        $categories = Category::query()
            ->active()
            ->approved()
            ->roots()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(function (Category $category) use ($baseQuery) {
                $ids = array_merge([$category->id], $category->descendantIds());
                $category->listing_count = $baseQuery()
                    ->where(function (Builder $q) use ($ids) {
                        $q->whereIn('main_category_id', $ids)
                            ->orWhereHas('categories', fn (Builder $c) => $c->whereIn('categories.id', $ids));
                    })
                    ->count();

                return $category;
            })
            ->filter(fn (Category $category) => $category->listing_count > 0)
            ->values();

        $brands = Brand::query()
            ->whereIn('id', $baseQuery()->whereNotNull('brand_id')->pluck('brand_id')->unique())
            ->orderBy('name')
            ->get(['id', 'name']);

        $listings = $baseQuery()
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->string('search');
                $q->where(fn ($q2) => $q2->where('name', 'like', "%{$search}%")->orWhere('short_description', 'like', "%{$search}%"));
            })
            ->when($request->filled('category'), function ($q) use ($request) {
                $cat = Category::find($request->integer('category'));
                if (! $cat) {
                    return;
                }
                $ids = array_merge([$cat->id], $cat->descendantIds());
                $q->where(function (Builder $q2) use ($ids) {
                    $q2->whereIn('main_category_id', $ids)
                        ->orWhereHas('categories', fn (Builder $c) => $c->whereIn('categories.id', $ids));
                });
            })
            ->when($request->filled('brand'), fn ($q) => $q->where('brand_id', $request->integer('brand')))
            ->when($request->filled('price_min'), fn ($q) => $q->where('base_price', '>=', $request->float('price_min')))
            ->when($request->filled('price_max'), fn ($q) => $q->where('base_price', '<=', $request->float('price_max')))
            ->with(['mainCategory', 'primaryImage', 'brand', 'supplierAccount.supplierProfile'])
            ->orderByDesc('is_featured')
            ->latest('published_at')
            ->paginate(12)
            ->withQueryString();

        // Safe return URL validation
        $returnUrl = $request->string('return_url')->toString();
        $quotationsBase = url('/supplier/quotations');
        if ($returnUrl === '' || ! str_starts_with($returnUrl, $quotationsBase)) {
            $returnUrl = route('supplier.quotations.create', $rfq);
        }

        return view('backend.supplier.procurement.quotations.select-product', [
            'rfq' => $rfq,
            'rfqItem' => $rfqItem,
            'matches' => $matches,
            'listings' => $listings,
            'search' => $request->string('search')->toString(),
            'category' => $request->integer('category'),
            'brandId' => $request->integer('brand'),
            'brands' => $brands,
            'priceMin' => $request->string('price_min')->toString(),
            'priceMax' => $request->string('price_max')->toString(),
            'categories' => $categories,
            'returnUrl' => $returnUrl,
            'itemToken' => $request->string('item_token')->toString(),
            'replaceOfferToken' => $request->string('replace_offer_token')->toString(),
            'activeTab' => $request->get('tab', 'matching'),
            'relevantCategories' => $relevantCategories,
        ]);
    }
}
