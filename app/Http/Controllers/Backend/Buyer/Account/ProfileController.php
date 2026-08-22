<?php

namespace App\Http\Controllers\Backend\Buyer\Account;

use App\Http\Controllers\Backend\Buyer\Concerns\InteractsWithBuyerAccount;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Buyer\Account\UpdateProfileRequest;
use App\Models\BuyerProfile;
use App\Models\BuyerType;
use App\Services\BuyerProfileService;

class ProfileController extends Controller
{
    use InteractsWithBuyerAccount;

    public function edit()
    {
        $this->authorize('update', BuyerProfile::class);

        $account = $this->currentAccount();
        $account->load(['buyerProfile.country', 'buyerProfile.state', 'buyerProfile.city', 'buyerTypes']);

        return view('backend.buyer.profile.edit', [
            'account' => $account,
            'profile' => $account->buyerProfile,
            'selectedBuyerTypeIds' => $account->buyerTypes->pluck('id'),
            'buyerTypes' => BuyerType::active()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(UpdateProfileRequest $request, BuyerProfileService $service)
    {
        $this->authorize('update', BuyerProfile::class);

        $data = $request->validated();
        $data['logo'] = $request->file('logo');

        $service->saveDraft($this->currentAccount(), $data);

        return redirect()->route('buyer.profile.edit')->with('success', 'Profile updated.');
    }
}
