<?php

namespace App\Services;

use App\Models\Account;
use App\Models\AccountCapability;
use App\Models\CapabilityApplicationHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CapabilityApplicationService
{
    /**
     * Initial submission (draft → pending).
     * Called from the review screen after profile is complete.
     */
    public function submit(Account $account, string $capability, User $actor): void
    {
        DB::transaction(function () use ($account, $capability, $actor) {
            /** @var AccountCapability $cap */
            $cap = AccountCapability::where('account_id', $account->id)
                ->whereHas('capabilityType', fn($q) => $q->where('code', $capability))
                ->lockForUpdate()
                ->firstOrFail();

            // Guard: only allow draft → pending
            if ($cap->status !== 'draft') {
                throw new \RuntimeException("Capability [{$capability}] is not in draft status (current: {$cap->status}).");
            }

            // Profile must be complete
            $this->assertProfileComplete($account, $capability);

            $attemptNo = $this->nextAttemptNumber($cap);

            $cap->update([
                'status'               => 'pending',
                'application_attempts' => $attemptNo,
                'applied_by_user_id'   => $actor->id,
                'applied_at'           => now(),
                'rejection_reason'     => null,
                'revision_reason'      => null,
            ]);

            CapabilityApplicationHistory::create([
                'account_capability_id' => $cap->id,
                'attempt_no'            => $attemptNo,
                'submitted_snapshot'    => $this->buildSnapshot($account, $capability, $actor),
                'status'                => 'submitted',
                'reviewed_by_user_id'   => null,
                'review_comment'        => null,
                'reviewed_at'           => null,
            ]);
        });
    }

    /**
     * Resubmission after revision_required.
     */
    public function resubmit(Account $account, string $capability, User $actor): void
    {
        DB::transaction(function () use ($account, $capability, $actor) {
            /** @var AccountCapability $cap */
            $cap = AccountCapability::where('account_id', $account->id)
                ->whereHas('capabilityType', fn($q) => $q->where('code', $capability))
                ->lockForUpdate()
                ->firstOrFail();

            if ($cap->status !== 'revision_required') {
                throw new \RuntimeException("Cannot resubmit: capability status is [{$cap->status}].");
            }

            if (! $this->canResubmit($cap)) {
                throw new \RuntimeException('Maximum application attempts reached. Please contact support.');
            }

            // Profile must still be complete
            $this->assertProfileComplete($account, $capability);

            $attemptNo = $this->nextAttemptNumber($cap);

            $cap->update([
                'status'               => 'pending',
                'application_attempts' => $attemptNo,
                'applied_by_user_id'   => $actor->id,
                'applied_at'           => now(),
                'revision_reason'      => null,
                'rejection_reason'     => null,
            ]);

            // NEW history row for this attempt — do not touch previous rows
            CapabilityApplicationHistory::create([
                'account_capability_id' => $cap->id,
                'attempt_no'            => $attemptNo,
                'submitted_snapshot'    => $this->buildSnapshot($account, $capability, $actor),
                'status'                => 'submitted',
                'reviewed_by_user_id'   => null,
                'review_comment'        => null,
                'reviewed_at'           => null,
            ]);
        });
    }

    /**
     * Determine whether the buyer may resubmit.
     */
    public function canResubmit(AccountCapability $cap): bool
    {
        $max = (int) $this->maxAttempts();
        return $cap->application_attempts < $max;
    }

    /**
     * Next sequential attempt number (server-computed, never from frontend).
     */
    public function nextAttemptNumber(AccountCapability $cap): int
    {
        return $cap->application_attempts + 1;
    }

    /**
     * Build a point-in-time snapshot of what the buyer submitted.
     * No sensitive data (passwords, tokens) is ever included.
     */
    public function buildSnapshot(Account $account, string $capability, User $actor): array
    {
        $snapshot = [
            'account' => [
                'id'             => $account->id,
                'account_number' => $account->account_number,
                'account_type'   => $account->account_type,
                'display_name'   => $account->display_name,
            ],
            'submitted_by_user_id' => $actor->id,
            'submitted_at'         => now()->toISOString(),
        ];

        if ($capability === 'buyer') {
            $profile = $account->buyerProfile;
            if ($profile) {
                $snapshot['buyer_profile'] = [
                    'buyer_type_id'     => $profile->buyer_type_id,
                    'buyer_type_ids'    => $account->buyerTypes()->pluck('buyer_types.id')->toArray(),
                    'display_name'      => $profile->display_name,
                    'organization_name' => $profile->organization_name,
                    'contact_person'   => $profile->contact_person,
                    'position'         => $profile->position,
                    'email'            => $profile->email,
                    'phone'            => $profile->phone,
                    'country_id'       => $profile->country_id,
                    'state_id'         => $profile->state_id,
                    'city_id'          => $profile->city_id,
                    'address'          => $profile->address,
                    'website'          => $profile->website,
                    'tax_id'           => $profile->tax_id,
                    'procurement_info' => $profile->procurement_info,
                ];
            }
        } elseif ($capability === 'supplier') {
            $profile = $account->supplierProfile;
            if ($profile) {
                $docs = \App\Models\SupplierDocument::with('documentType')
                    ->where('supplier_account_id', $account->id)
                    ->where('is_current', true)
                    ->get()
                    ->map(fn ($doc) => [
                        'supplier_document_id' => $doc->id,
                        'document_type_id'    => $doc->document_type_id,
                        'document_type_name'  => $doc->documentType?->name ?? 'Document',
                        'original_name'       => $doc->original_name,
                        'status_at_submission'=> $doc->status,
                        'expires_at'          => $doc->expires_at?->format('Y-m-d'),
                    ])->toArray();

                $snapshot['supplier_profile'] = [
                    'display_name'      => $profile->display_name,
                    'legal_name'        => $profile->legal_name,
                    'legal_entity_type' => $profile->legal_entity_type ?? $profile->company_type,
                    'supplier_type_ids' => $account->supplierTypes()->pluck('supplier_types.id')->toArray(),
                    'contact_person'    => $profile->contact_person,
                    'contact_email'     => $profile->contact_email,
                    'contact_phone'     => $profile->contact_phone,
                    'country_id'        => $profile->country_id,
                    'state_id'          => $profile->state_id,
                    'city_id'           => $profile->city_id,
                    'address'           => $profile->address,
                    'website'           => $profile->website,
                    'documents'         => $docs,
                ];
            }
        }

        return $snapshot;
    }

    /**
     * Read max attempts from settings table.
     */
    private function maxAttempts(): int
    {
        return (int) \App\Models\Setting::get('capability', 'capability_application_max_attempts', 3);
    }

    /**
     * Assert the relevant profile is complete before submission.
     */
    private function assertProfileComplete(Account $account, string $capability): void
    {
        if ($capability === 'buyer') {
            $profile = $account->buyerProfile;
            if (! $profile || ! $profile->isComplete()) {
                throw new \RuntimeException('Buyer profile must be completed before submitting an application.');
            }
        } elseif ($capability === 'supplier') {
            $profile = $account->supplierProfile;
            if (! $profile || ! $profile->isComplete() || ! $account->supplierTypes()->exists()) {
                throw new \RuntimeException('Supplier profile must be completed before submitting an application.');
            }
        }
    }
}
