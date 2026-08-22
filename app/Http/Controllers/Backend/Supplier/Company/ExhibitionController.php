<?php

namespace App\Http\Controllers\Backend\Supplier\Company;

use App\Http\Controllers\Backend\Supplier\Concerns\InteractsWithSupplierAccount;
use App\Http\Controllers\Controller;
use App\Models\Exhibition;
use Illuminate\Http\Request;

class ExhibitionController extends Controller
{
    use InteractsWithSupplierAccount;

    public function index()
    {
        $account = $this->currentAccount();

        // Exhibitions this supplier participates in
        $participating = Exhibition::whereHas(
            'supplierAccounts',
            fn ($q) => $q->where('supplier_account_id', $account->id)
        )->with('supplierAccounts')->active()->get();

        // All active exhibitions available to join
        $available = Exhibition::active()
            ->whereDoesntHave(
                'supplierAccounts',
                fn ($q) => $q->where('supplier_account_id', $account->id)
            )->get();

        return view('backend.supplier.company.exhibitions', [
            'participating' => $participating,
            'available'     => $available,
        ]);
    }

    public function join(Request $request, Exhibition $exhibition)
    {
        $account = $this->currentAccount();

        $validated = $request->validate([
            'booth_number'       => ['nullable', 'string', 'max:50'],
            'participation_year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
        ]);

        // Attach if not already participating
        if (! $exhibition->supplierAccounts()->where('supplier_account_id', $account->id)->exists()) {
            $exhibition->supplierAccounts()->attach($account->id, [
                'booth_number'       => $validated['booth_number'] ?? null,
                'participation_year' => $validated['participation_year'] ?? now()->year,
            ]);
        }

        return redirect()
            ->route('supplier.company.exhibitions')
            ->with('success', 'You have joined "' . $exhibition->getTranslation('name', app()->getLocale()) . '".');
    }

    public function leave(Exhibition $exhibition)
    {
        $account = $this->currentAccount();

        $exhibition->supplierAccounts()->detach($account->id);

        return redirect()
            ->route('supplier.company.exhibitions')
            ->with('success', 'You have withdrawn from the exhibition.');
    }
}
