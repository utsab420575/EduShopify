<?php

namespace App\Http\Controllers\Backend\Supplier\Company;

use App\Http\Controllers\Backend\Supplier\Concerns\InteractsWithSupplierAccount;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Supplier\Company\UpdateContactRequest;
use App\Services\SupplierProfileService;

class ContactController extends Controller
{
    use InteractsWithSupplierAccount;

    public function update(UpdateContactRequest $request, SupplierProfileService $service)
    {
        $service->saveDraft($this->currentAccount(), $request->validated());

        return redirect()->route('supplier.company.profile', ['section' => 'contact'])
            ->with('success', 'Contact information saved.');
    }
}
