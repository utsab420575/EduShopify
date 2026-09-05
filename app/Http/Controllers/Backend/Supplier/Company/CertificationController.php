<?php

namespace App\Http\Controllers\Backend\Supplier\Company;

use App\Http\Controllers\Backend\Supplier\Concerns\InteractsWithSupplierAccount;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Supplier\Company\StoreCertificationRequest;
use App\Models\Certification;
use Illuminate\Support\Facades\Storage;

class CertificationController extends Controller
{
    use InteractsWithSupplierAccount;

    public function store(StoreCertificationRequest $request)
    {
        $data = $request->validated();

        $path = $request->hasFile('file')
            ? $request->file('file')->store('supplier_achivment_certificat/'.now()->format('d_m_Y'), 'public')
            : null;

        $this->currentAccount()->certifications()->create([
            'certification_name' => $data['certification_name'],
            'certification_title' => $data['certification_title'],
            'certification_description' => $data['certification_description'],
            'certification_date' => $data['certification_date'] ?? null,
            'expiry_date' => $data['expiry_date'] ?? null,
            'file' => $path,
            'status' => 'pending',
        ]);

        return redirect()->route('supplier.company.profile', ['section' => 'achievements'])
            ->with('success', 'Certification submitted for review.');
    }

    /**
     * Only a still-pending request can be edited — once admin has acted
     * (approved/rejected) the submitted facts are locked; withdraw and
     * resubmit via destroy() + store() instead.
     */
    public function update(StoreCertificationRequest $request, Certification $certification)
    {
        abort_unless($certification->account_id === $this->currentAccount()->id, 404);

        if ($certification->status !== 'pending') {
            return redirect()->route('supplier.company.profile', ['section' => 'achievements']);
        }

        $data = $request->validated();

        $certification->update([
            'certification_name' => $data['certification_name'],
            'certification_title' => $data['certification_title'],
            'certification_description' => $data['certification_description'],
            'certification_date' => $data['certification_date'] ?? null,
            'expiry_date' => $data['expiry_date'] ?? null,
        ]);

        return redirect()->route('supplier.company.profile', ['section' => 'achievements'])
            ->with('success', 'Certification updated.');
    }

    /**
     * Withdraw a request that hasn't been approved — a pending submission
     * no longer wanted, or a rejected one being cleared out.
     */
    public function destroy(Certification $certification)
    {
        abort_unless($certification->account_id === $this->currentAccount()->id, 404);
        abort_unless(in_array($certification->status, ['pending', 'rejected'], true), 422);

        if ($certification->file && Storage::disk('public')->exists($certification->file)) {
            Storage::disk('public')->delete($certification->file);
        }
        $certification->delete();

        return redirect()->route('supplier.company.profile', ['section' => 'achievements'])
            ->with('success', 'Certification request cancelled.');
    }
}
