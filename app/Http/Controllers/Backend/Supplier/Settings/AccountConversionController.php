<?php

namespace App\Http\Controllers\Backend\Supplier\Settings;

use App\Http\Controllers\Backend\Supplier\Concerns\InteractsWithSupplierAccount;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AccountConversionController extends Controller
{
    use InteractsWithSupplierAccount;

    public function edit()
    {
        $account = $this->currentAccount();
        abort_if($account->isOrganization(), 404);

        return view('backend.supplier.settings.conversion', [
            'account' => $account,
            'user' => $this->currentUser(),
            'latestRequest' => $account->conversionRequests()->latest()->first(),
        ]);
    }

    public function submit(Request $request)
    {
        $account = $this->currentAccount();
        abort_if($account->isOrganization(), 404);

        $validated = $request->validate([
            'proposed_display_name' => ['required', 'string', 'max:255'],
            'legal_name' => ['required', 'string', 'max:255'],
            'registration_number' => ['nullable', 'string', 'max:100'],
            'tax_id' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $existingPending = $account->conversionRequests()->whereIn('status', ['draft', 'pending', 'revision_required'])->latest()->first();

        $data = [
            'from_type' => 'individual',
            'to_type' => 'organization',
            'proposed_display_name' => $validated['proposed_display_name'],
            'proposed_organization_data' => [
                'legal_name' => $validated['legal_name'],
                'registration_number' => $validated['registration_number'] ?? null,
                'tax_id' => $validated['tax_id'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ],
            'status' => 'pending',
            'submitted_by_user_id' => $this->currentUser()->id,
            'submitted_at' => now(),
        ];

        if ($existingPending) {
            $existingPending->update($data);
        } else {
            $account->conversionRequests()->create($data);
        }

        return back()->with('success', 'Conversion request submitted for admin review.');
    }
}
