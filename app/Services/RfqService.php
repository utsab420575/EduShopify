<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Rfq;
use App\Models\RfqChangeLog;
use App\Models\RfqDeadlineExtension;
use App\Models\RfqItem;
use App\Models\RfqSelectedSupplier;
use App\Models\User;
use App\Notifications\DashboardNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

/**
 * Phase 1: rfq_requires_admin_approval defaults to false, so publish()
 * moves a draft straight to 'open' — no separate approval queue yet.
 */
class RfqService
{
    public function saveDraft(Account $buyerAccount, User $user, array $data, ?Rfq $rfq = null): Rfq
    {
        $this->assertItems($data['items'] ?? []);

        // A published RFQ is edited through the version-aware path: changes
        // are diffed against the current state, logged, and (for material
        // changes) flagged to engaged suppliers instead of silently overwritten.
        if ($rfq && ! in_array($rfq->status, ['draft', 'pending_approval'], true)) {
            return $this->applyVersionedUpdate($rfq, $user, $data);
        }

        return DB::transaction(function () use ($buyerAccount, $user, $data, $rfq) {
            $attributes = [
                'buyer_account_id'           => $buyerAccount->id,
                'created_by_user_id'         => $rfq?->created_by_user_id ?? $user->id,
                'visibility_type'            => $data['visibility_type'],
                'title'                      => $data['title'],
                'description'                => $data['description'] ?? null,
                'currency_code'              => $data['currency_code'] ?? null,
                'budget_min'                 => $data['budget_min'] ?? null,
                'budget_max'                 => $data['budget_max'] ?? null,
                'delivery_country_id'        => $data['delivery_country_id'] ?? null,
                'delivery_state_id'          => $data['delivery_state_id'] ?? null,
                'delivery_city_id'           => $data['delivery_city_id'] ?? null,
                'delivery_address'           => $data['delivery_address'] ?? null,
                'allow_partial_quotation'    => $data['allow_partial_quotation'] ?? true,
                'allow_alternative_products' => $data['allow_alternative_products'] ?? true,
                'quotation_deadline'         => $data['quotation_deadline'],
                'qna_deadline'               => $data['qna_deadline'] ?? null,
                'expected_delivery_date'     => $data['expected_delivery_date'] ?? null,
            ];

            if ($rfq) {
                $rfq->update($attributes);
            } else {
                $attributes['rfq_number'] = $this->generateRfqNumber();
                $attributes['status']     = 'draft';
                $rfq                      = Rfq::create($attributes);
            }

            $this->syncItems($rfq, $data['items']);
            $this->syncSelectedSuppliers(
                $rfq,
                $user,
                $data['visibility_type'] === 'selected_suppliers' ? ($data['selected_supplier_ids'] ?? []) : []
            );

            $rfq->update(['items_count' => count($data['items'])]);

            return $rfq->fresh(['items', 'invitedSupplierAccounts']);
        });
    }

    public function publish(Rfq $rfq): Rfq
    {
        if (! in_array($rfq->status, ['draft', 'pending_approval'], true)) {
            throw ValidationException::withMessages(['status' => 'Only a draft RFQ can be published.']);
        }

        if ($rfq->items()->count() === 0) {
            throw ValidationException::withMessages(['items' => 'Add at least one item before publishing.']);
        }

        if ($rfq->visibility_type === 'selected_suppliers' && $rfq->selectedSuppliers()->count() === 0) {
            throw ValidationException::withMessages(['selected_supplier_ids' => 'Select at least one supplier to invite.']);
        }

        if (\App\Models\Setting::get('rfq', 'rfq_requires_admin_approval', false)) {
            $rfq->update(['status' => 'pending_approval']);

            return $rfq->fresh();
        }

        $rfq->update([
            'status'       => 'open',
            'published_at' => now(),
        ]);

        app(RfqQueueService::class)->generateForRfq($rfq);

        return $rfq->fresh();
    }

    /**
     * Admin approval for the pending_approval path — only reachable when the
     * rfq_requires_admin_approval setting is on.
     */
    public function approve(Rfq $rfq, \App\Models\User $admin): Rfq
    {
        if ($rfq->status !== 'pending_approval') {
            throw ValidationException::withMessages(['status' => 'Only an RFQ awaiting approval can be approved.']);
        }

        $rfq->update([
            'status'            => 'open',
            'published_at'      => now(),
            'approved_by_user_id' => $admin->id,
            'approved_at'       => now(),
        ]);

        app(RfqQueueService::class)->generateForRfq($rfq);

        return $rfq->fresh();
    }

    /**
     * Version-aware edit of an already-published RFQ. Diffs the incoming
     * data against the current row + items, writes an RfqChangeLog snapshot,
     * bumps current_version_no, and — for changes material enough to affect
     * a supplier's existing quotation — notifies suppliers with a live
     * quotation on this RFQ.
     */
    private function applyVersionedUpdate(Rfq $rfq, User $user, array $data): Rfq
    {
        return DB::transaction(function () use ($rfq, $user, $data) {
            $rfq = Rfq::whereKey($rfq->id)->lockForUpdate()->firstOrFail();
            $rfq->load('items');

            $oldSnapshot = $this->snapshot($rfq);

            $fields = [
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'currency_code' => $data['currency_code'] ?? null,
                'budget_min' => $data['budget_min'] ?? null,
                'budget_max' => $data['budget_max'] ?? null,
                'delivery_country_id' => $data['delivery_country_id'] ?? null,
                'delivery_state_id' => $data['delivery_state_id'] ?? null,
                'delivery_city_id' => $data['delivery_city_id'] ?? null,
                'delivery_address' => $data['delivery_address'] ?? null,
                'allow_partial_quotation' => $data['allow_partial_quotation'] ?? $rfq->allow_partial_quotation,
                'allow_alternative_products' => $data['allow_alternative_products'] ?? $rfq->allow_alternative_products,
                'quotation_deadline' => $data['quotation_deadline'],
                'qna_deadline' => $data['qna_deadline'] ?? null,
                'expected_delivery_date' => $data['expected_delivery_date'] ?? null,
            ];

            $changedFields = $this->diffFields($rfq, $fields);

            $newSupplierIds = $data['visibility_type'] === 'selected_suppliers' ? ($data['selected_supplier_ids'] ?? []) : [];
            $oldSupplierIds = $rfq->selectedSuppliers()->pluck('supplier_account_id')->sort()->values()->all();
            sort($newSupplierIds);

            if ($rfq->visibility_type !== $data['visibility_type']) {
                $changedFields[] = 'visibility_type';
            }
            if ($newSupplierIds !== $oldSupplierIds) {
                $changedFields[] = 'selected_suppliers';
            }
            if ($this->itemsChanged($rfq->items, $data['items'])) {
                $changedFields[] = 'items';
            }

            if (empty($changedFields)) {
                return $rfq->fresh(['items', 'invitedSupplierAccounts']);
            }

            $majorTriggers = ['quotation_deadline', 'budget_max', 'visibility_type', 'selected_suppliers', 'items'];
            $changeLevel = array_intersect($changedFields, $majorTriggers) ? 'major' : 'minor';

            $rfq->update($fields + ['visibility_type' => $data['visibility_type']]);
            $this->syncItems($rfq, $data['items']);
            $this->syncSelectedSuppliers($rfq, $user, $newSupplierIds);
            $rfq->update([
                'items_count' => count($data['items']),
                'current_version_no' => $rfq->current_version_no + 1,
            ]);

            $rfq = $rfq->fresh(['items', 'invitedSupplierAccounts']);

            RfqChangeLog::create([
                'rfq_id' => $rfq->id,
                'from_version_no' => $oldSnapshot['current_version_no'],
                'to_version_no' => $rfq->current_version_no,
                'change_level' => $changeLevel,
                'changed_fields' => $changedFields,
                'old_snapshot' => $oldSnapshot,
                'new_snapshot' => $this->snapshot($rfq),
                'requires_quotation_revision' => $changeLevel === 'major',
                'changed_by_user_id' => $user->id,
                'changed_at' => now(),
            ]);

            if ($changeLevel === 'major') {
                $this->notifyEngagedSuppliers($rfq, "The RFQ \"{$rfq->title}\" was updated by the buyer (v{$rfq->current_version_no}). Please review the changes — your quotation may need revision.");
            }

            return $rfq;
        });
    }

    /**
     * Formally extend the quotation or Q&A deadline, recording old/new
     * values in rfq_deadline_extensions rather than silently editing the
     * column, and notifying engaged suppliers.
     */
    public function extendDeadline(Rfq $rfq, User $user, string $type, string $newDeadline, ?string $reason = null): Rfq
    {
        if ($rfq->status !== 'open') {
            throw ValidationException::withMessages(['status' => 'Only an open RFQ can have its deadline extended.']);
        }

        if (! in_array($type, ['quotation', 'qna'], true)) {
            throw ValidationException::withMessages(['deadline_type' => 'Invalid deadline type.']);
        }

        return DB::transaction(function () use ($rfq, $user, $type, $newDeadline, $reason) {
            $field = $type === 'qna' ? 'qna_deadline' : 'quotation_deadline';
            $oldDeadline = $rfq->{$field};

            RfqDeadlineExtension::create([
                'rfq_id' => $rfq->id,
                'extended_by_user_id' => $user->id,
                'deadline_type' => $type,
                'old_deadline' => $oldDeadline,
                'new_deadline' => $newDeadline,
                'reason' => $reason,
            ]);

            $rfq->update([$field => $newDeadline]);
            $rfq = $rfq->fresh();

            $label = $type === 'qna' ? 'Q&A' : 'quotation';
            $this->notifyEngagedSuppliers($rfq, "The {$label} deadline for \"{$rfq->title}\" has been extended to ".\Illuminate\Support\Carbon::parse($newDeadline)->format('d M Y, h:i A').'.');

            return $rfq;
        });
    }

    /**
     * A compact snapshot of buyer-editable RFQ state, used for change-log
     * old/new comparison and display — not the full row.
     */
    private function snapshot(Rfq $rfq): array
    {
        return [
            'current_version_no' => $rfq->current_version_no,
            'title' => $rfq->title,
            'description' => $rfq->description,
            'currency_code' => $rfq->currency_code,
            'budget_min' => $rfq->budget_min,
            'budget_max' => $rfq->budget_max,
            'delivery_country_id' => $rfq->delivery_country_id,
            'delivery_state_id' => $rfq->delivery_state_id,
            'delivery_city_id' => $rfq->delivery_city_id,
            'delivery_address' => $rfq->delivery_address,
            'quotation_deadline' => optional($rfq->quotation_deadline)->toDateTimeString(),
            'qna_deadline' => optional($rfq->qna_deadline)->toDateTimeString(),
            'visibility_type' => $rfq->visibility_type,
            'items' => $rfq->items->map(fn (RfqItem $i) => [
                'item_name' => $i->item_name,
                'quantity' => (string) $i->quantity,
                'unit_id' => $i->unit_id,
                'category_id' => $i->category_id,
                'estimated_unit_price' => $i->estimated_unit_price,
            ])->values()->all(),
        ];
    }

    /**
     * @return array<int, string> changed attribute keys
     */
    private function diffFields(Rfq $rfq, array $fields): array
    {
        $changed = [];

        foreach ($fields as $key => $value) {
            $existing = $rfq->getAttribute($key);
            $existing = $existing instanceof \Illuminate\Support\Carbon ? $existing->toDateTimeString() : $existing;
            $normalizedValue = $value instanceof \Illuminate\Support\Carbon ? $value->toDateTimeString() : $value;

            if ((string) $existing !== (string) $normalizedValue) {
                $changed[] = $key;
            }
        }

        return $changed;
    }

    private function itemsChanged(\Illuminate\Support\Collection $currentItems, array $incomingItems): bool
    {
        if ($currentItems->count() !== count($incomingItems)) {
            return true;
        }

        $current = $currentItems->map(fn (RfqItem $i) => [
            (string) $i->id, $i->item_name, (string) $i->quantity, $i->unit_id, $i->category_id, $i->estimated_unit_price,
        ])->all();

        $incoming = array_map(fn ($i) => [
            (string) ($i['id'] ?? ''), $i['item_name'], (string) $i['quantity'], $i['unit_id'] ?? null, $i['category_id'] ?? null, $i['estimated_unit_price'] ?? null,
        ], array_values($incomingItems));

        sort($current);
        sort($incoming);

        return $current !== $incoming;
    }

    /**
     * Suppliers with a live quotation on this RFQ, plus (for a
     * selected-suppliers RFQ) invited suppliers who haven't quoted yet.
     */
    private function notifyEngagedSuppliers(Rfq $rfq, string $message): void
    {
        $supplierAccountIds = $rfq->quotations()
            ->whereNotIn('status', ['withdrawn', 'rejected', 'expired'])
            ->pluck('supplier_account_id');

        if ($rfq->visibility_type === 'selected_suppliers') {
            $supplierAccountIds = $supplierAccountIds->merge($rfq->selectedSuppliers()->pluck('supplier_account_id'));
        }

        $supplierAccountIds = $supplierAccountIds->unique()->values();

        if ($supplierAccountIds->isEmpty()) {
            return;
        }

        $users = User::whereHas('accountMember', fn ($q) => $q->whereIn('account_id', $supplierAccountIds)->where('status', 'active'))->get();

        if ($users->isNotEmpty()) {
            Notification::send($users, new DashboardNotification($message));
        }
    }

    public function cancel(Rfq $rfq, string $reason): Rfq
    {
        if (in_array($rfq->status, ['cancelled', 'completed'], true)) {
            throw ValidationException::withMessages(['status' => 'This RFQ can no longer be cancelled.']);
        }

        $rfq->update([
            'status'              => 'cancelled',
            'cancelled_at'        => now(),
            'cancellation_reason' => $reason,
        ]);

        return $rfq;
    }

    private function assertItems(array $items): void
    {
        if (empty($items)) {
            throw ValidationException::withMessages(['items' => 'At least one item is required.']);
        }
    }

    private function syncItems(Rfq $rfq, array $items): void
    {
        $keepIds = [];

        foreach (array_values($items) as $i => $item) {
            $attributes = [
                'rfq_id'                => $rfq->id,
                'item_type'             => $item['item_type'],
                'listing_id'            => $item['listing_id'] ?? null,
                'category_id'           => $item['category_id'] ?? null,
                'item_name'             => $item['item_name'],
                'description'           => $item['description'] ?? null,
                'quantity'              => $item['quantity'],
                'unit_id'               => $item['unit_id'] ?? null,
                'custom_unit'           => $item['custom_unit'] ?? null,
                'estimated_unit_price'  => $item['estimated_unit_price'] ?? null,
                'sort_order'            => $i,
            ];

            $row = null;

            if (! empty($item['id'])) {
                $row = RfqItem::where('rfq_id', $rfq->id)->find($item['id']);
            }

            if ($row) {
                $row->update($attributes);
            } else {
                $row = RfqItem::create($attributes);
            }

            $keepIds[] = $row->id;
        }

        RfqItem::where('rfq_id', $rfq->id)->whereNotIn('id', $keepIds)->delete();
    }

    private function syncSelectedSuppliers(Rfq $rfq, User $user, array $supplierAccountIds): void
    {
        RfqSelectedSupplier::where('rfq_id', $rfq->id)
            ->whereNotIn('supplier_account_id', $supplierAccountIds)
            ->delete();

        foreach ($supplierAccountIds as $supplierAccountId) {
            RfqSelectedSupplier::firstOrCreate(
                ['rfq_id' => $rfq->id, 'supplier_account_id' => $supplierAccountId],
                ['selected_by_user_id' => $user->id, 'invited_at' => now()]
            );
        }
    }

    private function generateRfqNumber(): string
    {
        $year   = date('Y');
        $latest = Rfq::withTrashed()->where('rfq_number', 'like', "RFQ-{$year}-%")->count();
        $seq    = str_pad($latest + 1, 6, '0', STR_PAD_LEFT);

        return "RFQ-{$year}-{$seq}";
    }
}
