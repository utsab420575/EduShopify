<?php

namespace App\Livewire\Auth;

use App\Models\DocumentType;
use App\Models\SupplierDocument;
use App\Services\CapabilityApplicationService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SupplierApplicationReview extends Component
{
    public function mount(): void
    {
        $account = Auth::user()->account;

        if (! $account || ! $account->supplierCapability) {
            $this->redirect(route('home'), navigate: false);
            return;
        }

        // Verify profile complete
        $profile = $account->supplierProfile;
        if (! $profile || ! $profile->isComplete()) {
            $this->redirect(route('supplier.onboarding.profile'), navigate: false);
            return;
        }

        // Verify required documents uploaded
        $requiredTypeIds = DocumentType::where('is_active', true)
            ->whereHas('capabilityEnables', function ($q) {
                $q->whereHas('capabilityType', fn($c) => $c->where('code', 'supplier'))
                  ->where('is_required', true);
            })
            ->pluck('id');

        foreach ($requiredTypeIds as $typeId) {
            $hasCurrent = SupplierDocument::where('supplier_account_id', $account->id)
                ->where('document_type_id', $typeId)
                ->where('is_current', true)
                ->whereIn('status', ['pending', 'verified'])
                ->exists();

            if (! $hasCurrent) {
                $this->redirect(route('supplier.onboarding.documents'), navigate: false);
                return;
            }
        }
    }

    public function submitApplication(CapabilityApplicationService $service): void
    {
        $user    = Auth::user();
        $account = $user->account;

        try {
            $service->submit($account, 'supplier', $user);
            session()->flash('success', 'Your Supplier application has been submitted successfully for verification!');
            $this->redirect(route('supplier.dashboard'), navigate: false);
        } catch (\Throwable $e) {
            $this->addError('submission', $e->getMessage());
        }
    }

    public function render()
    {
        $account = Auth::user()->account;
        $profile = $account->supplierProfile;
        $docs    = SupplierDocument::with('documentType')
            ->where('supplier_account_id', $account->id)
            ->where('is_current', true)
            ->get();

        return view('livewire.auth.supplier-application-review', compact('account', 'profile', 'docs'))
            ->layout('components.layouts.auth', [
                'title'    => 'Review & Submit Application',
                'maxWidth' => 'max-w-3xl',
            ]);
    }
}
