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
        $service->saveDraft($this->currentAccount(), [
            'logo' => $request->file('logo'),
            'banner' => $request->file('banner'),
            'profile_photo' => $request->file('profile_photo'),
        ]);

        return redirect()->route('supplier.company.profile', ['section' => 'media'])
            ->with('success', 'Media saved.');
    }
}
