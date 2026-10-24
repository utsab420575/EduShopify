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
            // Approved on upload; an admin can still reject it later. No
            // approver is recorded, which marks it as auto-approved.
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        return redirect()->route('supplier.company.profile', ['section' => 'achievements'])
            ->with('success', 'Certification added and approved.');
    }

    /**
     * Editable while pending or auto-approved — once an admin has acted
     * (approved/rejected) the submitted facts are locked; withdraw and
     * resubmit via destroy() + store() instead.
     */
    public function update(StoreCertificationRequest $request, Certification $certification)
    {
        abort_unless($certification->account_id === $this->currentAccount()->id, 404);

        if (! $certification->isEditableBySupplier()) {
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
     * Remove a certification an admin hasn't approved — pending, rejected,
     * or auto-approved on upload.
     */
    public function destroy(Certification $certification)
    {
        abort_unless($certification->account_id === $this->currentAccount()->id, 404);
        abort_unless($certification->isRemovableBySupplier(), 422);

        if ($certification->file && Storage::disk('public')->exists($certification->file)) {
            Storage::disk('public')->delete($certification->file);
        }
        $certification->delete();

        return redirect()->route('supplier.company.profile', ['section' => 'achievements'])
            ->with('success', 'Certification request cancelled.');
    }
}
