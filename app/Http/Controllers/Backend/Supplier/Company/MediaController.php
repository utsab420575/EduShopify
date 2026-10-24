<?php

namespace App\Http\Controllers\Backend\Supplier\Company;

use App\Http\Controllers\Backend\Supplier\Concerns\InteractsWithSupplierAccount;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Supplier\Company\UpdateMediaRequest;
use App\Services\SupplierProfileService;

class MediaController extends Controller
{
    use InteractsWithSupplierAccount;

    public function update(UpdateMediaRequest $request, SupplierProfileService $service)
    {
        $account = $this->currentAccount();

        $service->saveDraft($account, [
            'logo' => $request->file('logo'),
            'profile_photo' => $request->file('profile_photo'),
        ]);

        // Banner is cropped server-side from its original (or re-cropped
        // from the stored one when only a new crop box is sent).
        $service->updateBanner($account, $request->file('banner'), $request->validated('banner_crop'), $request->validated('banner_fit'));

        return redirect()->route('supplier.company.profile', ['section' => 'media'])
            ->with('success', 'Media saved.');
    }
}
