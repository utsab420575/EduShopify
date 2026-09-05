<?php

namespace App\Http\Controllers\Backend\Supplier\Company;

use App\Http\Controllers\Backend\Supplier\Concerns\InteractsWithSupplierAccount;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Supplier\Company\JoinExhibitionRequest;
use App\Models\Exhibition;

class ExhibitionParticipationController extends Controller
{
    use InteractsWithSupplierAccount;

    public function join(JoinExhibitionRequest $request, Exhibition $exhibition)
    {
        $account = $this->currentAccount();
        $data = $request->validated();

        if (! $exhibition->supplierAccounts()->where('supplier_account_id', $account->id)->exists()) {
            $exhibition->supplierAccounts()->attach($account->id, [
                'booth_number' => $data['booth_number'] ?: null,
                'participation_year' => $data['participation_year'] ?: now()->year,
            ]);
        }

        return redirect()->route('supplier.company.profile', ['section' => 'exhibitions'])
            ->with('success', 'You have joined "'.$exhibition->getTranslation('name', app()->getLocale()).'".');
    }

    public function leave(Exhibition $exhibition)
    {
        $exhibition->supplierAccounts()->detach($this->currentAccount()->id);

        return redirect()->route('supplier.company.profile', ['section' => 'exhibitions'])
            ->with('success', 'You have withdrawn from the exhibition.');
    }
}
