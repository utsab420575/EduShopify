<?php

namespace App\Http\Controllers\Backend\Buyer\Procurement;

use App\Http\Controllers\Backend\Buyer\Concerns\InteractsWithBuyerAccount;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Rfq;
use App\Models\RfqItem;
use App\Models\Unit;
use App\Services\RfqService;
use Illuminate\Http\Request;

class RequirementController extends Controller
{
    use InteractsWithBuyerAccount;

    /**
     * Reserved specs[] keys used to smuggle metadata that isn't a real
     * database column through RfqItem.specs (json) — never shown to the
     * buyer as an editable custom specification row.
     */
    private const RESERVED_SPEC_NAMES = ['__is_requirement', '__category_ids'];

    public function create(Request $request)
    {
        $this->authorize('create', Rfq::class);

        $returnUrl = $request->string('return_url')->toString();
        $rfqsBase = url('/buyer/rfqs');
        if ($returnUrl === '' || ! str_starts_with($returnUrl, $rfqsBase)) {
            $returnUrl = route('buyer.rfqs.create');
        }

        $rfqId = $request->integer('rfq_id') ?: null;
        $rfq = null;
        if ($rfqId) {
            $rfq = $this->currentAccount()->rfqs()->find($rfqId);
        }

        // Editing an already-saved requirement — load it so the full page
        // can be pre-filled instead of always starting blank.
        $existingItem = null;
        $itemId = $request->integer('item_id') ?: null;
        if ($itemId) {
            $candidate = RfqItem::with('rfq')->find($itemId);
            if ($candidate && $candidate->rfq && $candidate->rfq->buyer_account_id === $this->currentAccount()->id) {
                $existingItem = $candidate;
                $rfq ??= $candidate->rfq;
            }
        }

        $categoryNodes = Category::getTreeSelectOptions(['product', 'service', 'both']);
        $units = Unit::active()->orderBy('name')->get(['id', 'name', 'symbol']);

        return view('backend.buyer.procurement.rfqs.add-requirement', [
            'rfq'           => $rfq,
            'rfqId'         => $rfq?->id ?? $rfqId,
            'returnUrl'     => $returnUrl,
            'categoryNodes' => $categoryNodes,
            'units'         => $units,
            'existingItem'  => $existingItem,
            'existingCategoryIds' => $existingItem ? $this->resolveCategoryIds($existingItem) : [],
            'existingAttachments' => $existingItem
                ? $existingItem->getMedia('attachments')->map(fn ($m) => [
                    'id' => $m->id, 'name' => $m->file_name, 'size' => $m->human_readable_size,
                    'url' => $m->getUrl(), 'is_image' => str_starts_with($m->mime_type ?? '', 'image/'),
                ])->values()
                : collect(),
        ]);
    }

    public function store(Request $request, RfqService $service)
    {
        $this->authorize('create', Rfq::class);
        $account = $this->currentAccount();

        $validated = $this->validateRequirement($request);

        $rfq = null;
        if (! empty($validated['rfq_id'])) {
            $rfq = $account->rfqs()->find($validated['rfq_id']);
        }

        $categoryIds = $this->cleanCategoryIds($validated['category_ids'] ?? []);
        $cleanSpecs = $this->buildSpecs($request, $validated, $categoryIds);

        // If RFQ does not exist yet, create a baseline draft RFQ with this requirement item
        if (! $rfq) {
            $rfq = $service->saveDraft($account, $this->currentUser(), [
                'title'              => $validated['item_name'] . ' RFQ',
                'visibility_type'    => 'global',
                'current_step'       => 1,
                'items'              => [
                    [
                        'item_type'            => $validated['item_type'],
                        'listing_id'           => null,
                        'category_id'          => $categoryIds[0] ?? null,
                        'item_name'            => $validated['item_name'],
                        'description'          => $validated['description'] ?? null,
                        'quantity'             => $validated['quantity'],
                        'unit_id'              => $validated['unit_id'] ?? null,
                        'custom_unit'          => $validated['custom_unit'] ?? null,
                        'estimated_unit_price' => $validated['estimated_unit_price'] ?? null,
                        'specs'                => $cleanSpecs,
                    ],
                ],
            ]);
            $rfqItem = $rfq->items()->first();
        } else {
            $rfqItem = RfqItem::create([
                'rfq_id'               => $rfq->id,
                'item_type'            => $validated['item_type'],
                'listing_id'           => null,
                'category_id'          => $categoryIds[0] ?? null,
                'item_name'            => $validated['item_name'],
                'description'          => $validated['description'] ?? null,
                'quantity'             => $validated['quantity'],
                'unit_id'              => $validated['unit_id'] ?? null,
                'custom_unit'          => $validated['custom_unit'] ?? null,
                'estimated_unit_price' => $validated['estimated_unit_price'] ?? null,
                'specs'                => $cleanSpecs,
                'sort_order'           => $rfq->items()->count(),
            ]);
            $rfq->increment('items_count');
        }

        $this->attachUploadedFiles($request, $rfqItem);

        $returnUrl = $this->resolveReturnUrl($validated['return_url'] ?? '', $rfq);

        $sep = str_contains($returnUrl, '?') ? '&' : '?';
        return redirect($returnUrl . $sep . 'restore_items=1&new_requirement_id=' . $rfqItem->id . '&rfq_id=' . $rfq->id)
            ->with('success', 'Requirement added to RFQ.');
    }

    /**
     * Save edits to an already-created requirement — reached from the
     * "Edit" button on the RFQ item card, which reopens this same full
     * page (rather than only allowing edits inline in the compact card).
     */
    public function update(Request $request, RfqItem $item)
    {
        $account = $this->currentAccount();
        abort_unless($item->rfq && $item->rfq->buyer_account_id === $account->id, 403);

        $validated = $this->validateRequirement($request);

        $categoryIds = $this->cleanCategoryIds($validated['category_ids'] ?? []);
        $cleanSpecs = $this->buildSpecs($request, $validated, $categoryIds);

        $item->update([
            'item_type'            => $validated['item_type'],
            'category_id'          => $categoryIds[0] ?? null,
            'item_name'            => $validated['item_name'],
            'description'          => $validated['description'] ?? null,
            'quantity'             => $validated['quantity'],
            'unit_id'              => $validated['unit_id'] ?? null,
            'custom_unit'          => $validated['custom_unit'] ?? null,
            'estimated_unit_price' => $validated['estimated_unit_price'] ?? null,
            'specs'                => $cleanSpecs,
        ]);

        $this->attachUploadedFiles($request, $item);

        $returnUrl = $this->resolveReturnUrl($validated['return_url'] ?? '', $item->rfq);

        $sep = str_contains($returnUrl, '?') ? '&' : '?';
        return redirect($returnUrl . $sep . 'restore_items=1&edited_requirement_id=' . $item->id . '&rfq_id=' . $item->rfq_id)
            ->with('success', 'Requirement updated.');
    }

    /**
     * Removes one already-uploaded reference file — called from the "view/
     * delete existing files" UI added to the edit form (not available
     * before a requirement is actually saved, since there's nothing to
     * delete yet on a brand-new one).
     */
    public function deleteAttachment(Request $request, RfqItem $item, int $media)
    {
        $account = $this->currentAccount();
        abort_unless($item->rfq && $item->rfq->buyer_account_id === $account->id, 403);

        $item->getMedia('attachments')->where('id', $media)->first()?->delete();

        return response()->json(['success' => true]);
    }

    public function itemData(RfqItem $item)
    {
        $account = $this->currentAccount();
        abort_unless($item->rfq && $item->rfq->buyer_account_id === $account->id, 403);

        $item->load(['media', 'category', 'unit']);

        $categoryIds = $this->resolveCategoryIds($item);

        return response()->json([
            'id'                   => $item->id,
            'item_type'            => $item->item_type,
            'listing_id'           => null,
            'category_id'          => $item->category_id,
            'category_name'        => $item->category?->name,
            // Every category the buyer picked, not just the primary one
            // stored in category_id — the RFQ item card badges loop over
            // this so multi-category selections stay visible after saving.
            'category_ids'         => $categoryIds,
            'category_names'       => $this->categoryNames($categoryIds),
            'item_name'            => $item->item_name,
            'description'          => $item->description,
            'quantity'             => (string) $item->quantity,
            'unit_id'              => $item->unit_id,
            'custom_unit'          => $item->custom_unit,
            'estimated_unit_price' => $item->estimated_unit_price,
            'specs'                => $item->specs,
            'custom_attributes'    => $this->visibleSpecs($item->specs),
            '_mode'                => 'requirement',
            '_specsOpen'           => true,
            'is_requirement'       => true,
            'attachments'          => $item->getMedia('attachments')->map(fn ($m) => [
                'id'        => $m->id,
                'name'      => $m->file_name,
                'size'      => $m->human_readable_size,
                'url'       => $m->getUrl(),
                'is_image'  => str_starts_with($m->mime_type ?? '', 'image/'),
            ])->values(),
        ]);
    }

    private function validateRequirement(Request $request): array
    {
        return $request->validate([
            'rfq_id'               => ['nullable', 'integer'],
            'item_name'            => ['required', 'string', 'max:255'],
            'item_type'            => ['required', 'in:product,service'],
            'category_ids'         => ['nullable', 'array'],
            'category_ids.*'       => ['integer', 'exists:categories,id'],
            'description'          => ['nullable', 'string', 'max:5000'],
            'quantity'             => ['required', 'numeric', 'min:0.001'],
            'unit_id'              => ['nullable', 'integer', 'exists:units,id'],
            'custom_unit'          => ['nullable', 'string', 'max:50'],
            'estimated_unit_price' => ['nullable', 'numeric', 'min:0'],
            'specs'                => ['nullable', 'array'],
            'specs.*.name'         => ['nullable', 'string', 'max:255'],
            'specs.*.value'        => ['nullable', 'string', 'max:1000'],
            'attachments'          => ['nullable', 'array'],
            'attachments.*'        => ['file', 'max:10240', 'mimes:jpg,jpeg,png,webp,pdf,doc,docx,xls,xlsx,zip,csv,txt'],
            'return_url'           => ['nullable', 'string'],
        ]);
    }

    /**
     * @return array<int, int> unique, positive category ids, in the order
     *   the buyer picked them (first = primary, stored in the category_id
     *   column — the rest are informational only, tucked into specs).
     */
    private function cleanCategoryIds(array $raw): array
    {
        return collect($raw)->map(fn ($id) => (int) $id)->filter()->unique()->values()->all();
    }

    /**
     * Reconstructs every category id a buyer picked for this item — the
     * primary one in the real category_id column, plus any extras tucked
     * into specs.__category_ids (see buildSpecs()), since RfqItem only has
     * one category_id FK. Shared by create()'s edit-mode pre-fill and
     * itemData()'s AJAX response so both stay in sync.
     */
    private function resolveCategoryIds(RfqItem $item): array
    {
        $ids = [];
        if ($item->category_id) {
            $ids[] = (int) $item->category_id;
        }
        $specs = is_array($item->specs) ? $item->specs : [];
        foreach ($specs as $s) {
            if (is_array($s) && ($s['name'] ?? '') === '__category_ids') {
                foreach (explode(',', (string) ($s['value'] ?? '')) as $id) {
                    $id = (int) $id;
                    if ($id && ! in_array($id, $ids, true)) {
                        $ids[] = $id;
                    }
                }
            }
        }

        return $ids;
    }

    /** Category names for the given ids, in the same order as $ids. */
    private function categoryNames(array $ids): array
    {
        $names = Category::whereIn('id', $ids)->pluck('name', 'id');

        return collect($ids)->map(fn ($id) => $names[$id] ?? null)->filter()->values()->all();
    }

    private function buildSpecs(Request $request, array $validated, array $categoryIds): array
    {
        $cleanSpecs = [];
        if (! empty($validated['specs']) && is_array($validated['specs'])) {
            foreach ($validated['specs'] as $k => $v) {
                if (is_array($v) && (isset($v['name']) || isset($v['value']))) {
                    $name = trim((string) ($v['name'] ?? ''));
                    $val = trim((string) ($v['value'] ?? ''));
                    if ($name !== '' || $val !== '') {
                        $cleanSpecs[] = ['name' => $name, 'value' => $val];
                    }
                } elseif (is_string($k)) {
                    $cleanSpecs[] = ['name' => (string) $k, 'value' => (string) $v];
                }
            }
        }
        if (! empty($request->input('spec_keys')) && is_array($request->input('spec_keys'))) {
            $keys = $request->input('spec_keys');
            $values = (array) $request->input('spec_values', []);
            foreach ($keys as $idx => $key) {
                $name = trim((string) $key);
                $val = trim((string) ($values[$idx] ?? ''));
                if ($name !== '' || $val !== '') {
                    $cleanSpecs[] = ['name' => $name, 'value' => $val];
                }
            }
        }
        $cleanSpecs[] = ['name' => '__is_requirement', 'value' => '1'];
        if (count($categoryIds) > 1) {
            $cleanSpecs[] = ['name' => '__category_ids', 'value' => implode(',', $categoryIds)];
        }

        return $cleanSpecs;
    }

    /**
     * Same reserved-key filter used by _form.blade.php's server-side item
     * hydration — kept here too so itemData() (the AJAX round trip after
     * saving) never leaks __is_requirement/__category_ids as a visible
     * "custom specification" row.
     */
    private function visibleSpecs(mixed $specs): array
    {
        return is_array($specs)
            ? array_values(array_filter($specs, fn ($s) => is_array($s) && ! in_array($s['name'] ?? '', self::RESERVED_SPEC_NAMES, true)))
            : [];
    }

    private function attachUploadedFiles(Request $request, RfqItem $rfqItem): void
    {
        if (! $request->hasFile('attachments')) {
            return;
        }
        foreach ($request->file('attachments') as $file) {
            $rfqItem->addMedia($file)
                ->usingFileName(sprintf('req_%s_%s.%s', $rfqItem->id, uniqid(), $file->getClientOriginalExtension()))
                ->toMediaCollection('attachments');
        }
    }

    private function resolveReturnUrl(string $returnUrl, Rfq $rfq): string
    {
        if ($returnUrl === '' || (! str_contains($returnUrl, '/buyer/rfqs') && ! str_contains($returnUrl, 'buyer/rfqs'))) {
            return route('buyer.rfqs.edit', ['rfq' => $rfq->id, 'step' => 1]);
        }

        // If returning to /buyer/rfqs/create, convert to edit route since RFQ is now persisted
        if (str_contains($returnUrl, '/buyer/rfqs/create')) {
            return route('buyer.rfqs.edit', ['rfq' => $rfq->id, 'step' => 1]);
        }

        return $returnUrl;
    }
}
