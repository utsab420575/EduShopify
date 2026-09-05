<?php

namespace App\Http\Controllers\Backend\Supplier\Company;

use App\Http\Controllers\Backend\Supplier\Concerns\InteractsWithSupplierAccount;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Supplier\Company\StoreDocumentRequest;
use App\Models\SupplierDocument;
use App\Services\SupplierDocumentService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    use InteractsWithSupplierAccount;

    public function store(StoreDocumentRequest $request, SupplierDocumentService $service)
    {
        $data = $request->validated();
        $documentTypeId = $data['document_type_id'] ?? null;
        $expiry = ! empty($data['expires_at']) ? new \DateTime($data['expires_at']) : null;

        try {
            $service->upload(
                $this->currentAccount(),
                $documentTypeId,
                $request->file('file'),
                Auth::user(),
                $expiry,
                $documentTypeId ? null : $data['custom_name']
            );

            return redirect()->route('supplier.company.profile', ['section' => 'documents'])
                ->with('success', 'Document uploaded and submitted for review.');
        } catch (\Throwable $e) {
            return redirect()->route('supplier.company.profile', ['section' => 'documents'])
                ->withErrors(['file' => $e->getMessage()]);
        }
    }

    public function destroy(SupplierDocument $document)
    {
        abort_unless($document->supplier_account_id === $this->currentAccount()->id, 404);

        if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }
        $document->delete();

        return redirect()->route('supplier.company.profile', ['section' => 'documents'])
            ->with('success', 'Document deleted.');
    }
}
