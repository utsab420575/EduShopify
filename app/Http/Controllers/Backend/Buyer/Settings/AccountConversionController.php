<?php

namespace App\Http\Controllers\Backend\Buyer\Settings;

use App\Http\Controllers\Backend\Buyer\Concerns\InteractsWithBuyerAccount;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Buyer\Settings\SubmitConversionRequest;

class AccountConversionController extends Controller
{
    use InteractsWithBuyerAccount;

    public function edit()
    {
        $account = $this->currentAccount();
        abort_if($account->isOrganization(), 404);

        return view('backend.buyer.settings.conversion', [
            'latestRequest' => $account->conversionRequests()->latest()->first(),
        ]);
    }

    public function submit(SubmitConversionRequest $request)
    {
        $account = $this->currentAccount();
        abort_if($account->isOrganization(), 404);

        $existingPending = $account->conversionRequests()->whereIn('status', ['draft', 'pending', 'revision_required'])->latest()->first();

        $data = [
            'from_type' => 'individual',
            'to_type' => 'organization',
            'proposed_display_name' => $request->string('proposed_display_name'),
            'proposed_organization_data' => $request->safe()->only(['legal_name', 'registration_number', 'tax_id', 'notes']),
            'status' => 'pending',
            'submitted_by_user_id' => $this->currentUser()->id,
            'submitted_at' => now(),
        ];

        if ($existingPending) {
            $existingPending->update($data);
        } else {
            $account->conversionRequests()->create(['account_id' => $account->id] + $data);
        }

        return back()->with('success', 'Conversion request submitted for admin review.');
    }
}
