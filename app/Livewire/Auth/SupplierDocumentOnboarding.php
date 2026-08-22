<?php

namespace App\Livewire\Auth;

use App\Models\DocumentType;
use App\Models\SupplierDocument;
use App\Services\SupplierDocumentService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class SupplierDocumentOnboarding extends Component
{
    use WithFileUploads;

    // Unified "Add Document" form (used for both catalog document types and custom/"Other" uploads)
    public string $new_document_type_id = ''; // numeric id as string, or 'other'
    public $new_file;
    public string $new_expiry = '';
    public string $new_custom_name = '';

    public function mount(): void
    {
        $account = Auth::user()->account;

        if (! $account || ! $account->supplierCapability) {
            $this->redirect(route('home'), navigate: false);
            return;
        }

        // Verify profile is complete
        $profile = $account->supplierProfile;
        if (! $profile || ! $profile->isComplete()) {
            $this->redirect(route('supplier.onboarding.profile'), navigate: false);
        }
    }

    /**
     * Open the "Add Document" modal, optionally pre-selecting a catalog document type
     * (used by the "Re-upload"/"Upload" action on a specific checklist row).
     */
    public function openAddDocument(?int $documentTypeId = null): void
    {
        $this->resetValidation();
        $this->reset(['new_document_type_id', 'new_file', 'new_expiry', 'new_custom_name']);
        $this->new_document_type_id = $documentTypeId ? (string) $documentTypeId : '';
        $this->dispatch('open-add-document');
    }

    public function addDocument(SupplierDocumentService $service): void
    {
        $isOther = $this->new_document_type_id === 'other';

        $rules = [
            'new_document_type_id' => 'required|string',
            'new_file'             => 'required|file|max:10240', // 10MB
            'new_expiry'           => 'nullable|date',
        ];

        if ($isOther) {
            $rules['new_custom_name'] = 'required|string|max:200';
        }

        $this->validate($rules);

        $expiry = ! empty($this->new_expiry)
            ? new \DateTime($this->new_expiry)
            : null;

        try {
            $account = Auth::user()->account;
            $documentTypeId = $isOther ? null : (int) $this->new_document_type_id;
            $customName = $isOther ? $this->new_custom_name : null;

            $service->upload($account, $documentTypeId, $this->new_file, Auth::user(), $expiry, $customName);

            $this->reset(['new_document_type_id', 'new_file', 'new_expiry', 'new_custom_name']);
            $this->dispatch('document-uploaded');
        } catch (\Throwable $e) {
            $this->addError('new_file', $e->getMessage());
        }
    }

    public function continueToReview(): void
    {
        $account = Auth::user()->account;

        // Check required documents present
        $requiredTypes = DocumentType::where('is_active', true)
            ->whereHas('capabilityEnables', function ($q) {
                $q->whereHas('capabilityType', fn($c) => $c->where('code', 'supplier'))
                  ->where('is_required', true);
            })
            ->get(['id', 'name']);

        $missing = [];
        foreach ($requiredTypes as $docType) {
            $hasCurrent = SupplierDocument::where('supplier_account_id', $account->id)
                ->where('document_type_id', $docType->id)
                ->where('is_current', true)
                ->whereIn('status', ['pending', 'verified'])
                ->exists();

            if (! $hasCurrent) {
                $missing[] = $docType->name;
            }
        }

        if (count($missing) > 0) {
            $this->dispatch('missing-required-documents', missing: $missing);
            return;
        }

        $this->redirect(route('supplier.onboarding.review'), navigate: false);
    }

    public function render()
    {
        $account       = Auth::user()->account;
        $documentTypes = DocumentType::where('is_active', true)
            ->whereHas('capabilityEnables', function ($q) {
                $q->whereHas('capabilityType', fn($c) => $c->where('code', 'supplier'));
            })
            ->with(['capabilityEnables' => function ($q) {
                $q->whereHas('capabilityType', fn($c) => $c->where('code', 'supplier'));
            }])
            ->orderBy('sort_order')
            ->get();

        $currentDocs = SupplierDocument::with(['documentType.capabilityEnables' => function ($q) {
            $q->whereHas('capabilityType', fn($c) => $c->where('code', 'supplier'));
        }])
            ->where('supplier_account_id', $account->id)
            ->where('is_current', true)
            ->whereNotNull('document_type_id')
            ->get()
            ->sortBy(fn ($doc) => $doc->documentType?->sort_order ?? 0)
            ->values();

        $customDocs = SupplierDocument::where('supplier_account_id', $account->id)
            ->whereNull('document_type_id')
            ->where('is_current', true)
            ->get();

        return view('livewire.auth.supplier-document-onboarding', compact('documentTypes', 'currentDocs', 'customDocs', 'account'))
            ->layout('components.layouts.auth', [
                'title'    => 'Supplier Document Upload',
                'maxWidth' => 'max-w-3xl',
            ]);
    }
}
