<?php

namespace App\Http\Controllers\Backend\Supplier\Company;

use App\Http\Controllers\Backend\Supplier\Concerns\InteractsWithSupplierAccount;
use App\Http\Controllers\Controller;
use App\Models\CapabilityType;
use App\Models\DocumentType;
use App\Models\SupplierDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    use InteractsWithSupplierAccount;

    public function index()
    {
        $account = $this->currentAccount();
        $supplierCapType = CapabilityType::where('code', 'supplier')->first();

        // Get document types enabled for supplier capability
        $requiredDocumentTypes = DocumentType::where('is_active', true)
            ->whereHas('capabilityEnables', function ($q) use ($supplierCapType) {
                if ($supplierCapType) {
                    $q->where('capability_type_id', $supplierCapType->id);
                }
            })->get();

        // Current supplier documents
        $documents = $account->supplierDocuments()->with('documentType')->latest()->get();

        return view('backend.supplier.company.documents', [
            'account' => $account,
            'user' => $this->currentUser(),
            'requiredDocumentTypes' => $requiredDocumentTypes,
            'documents' => $documents,
        ]);
    }

    public function store(Request $request)
    {
        $account = $this->currentAccount();

        $validated = $request->validate([
            'document_type_id' => ['nullable', 'exists:document_types,id'],
            'custom_name' => ['nullable', 'string', 'max:255'],
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'expires_at' => ['nullable', 'date'],
        ]);

        $file = $request->file('file');
        $path = $file->store('supplier/documents/' . $account->id, 'public');

        // If replacing an existing current document of same type, mark old as not current
        if (! empty($validated['document_type_id'])) {
            $account->supplierDocuments()
                ->where('document_type_id', $validated['document_type_id'])
                ->update(['is_current' => false]);
        }

        SupplierDocument::create([
            'supplier_account_id' => $account->id,
            'document_type_id' => $validated['document_type_id'] ?? null,
            'custom_name' => $validated['custom_name'] ?? null,
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'file_size_kb' => round($file->getSize() / 1024),
            'status' => 'pending',
            'uploaded_by_user_id' => $this->currentUser()->id,
            'expires_at' => $validated['expires_at'] ?? null,
            'is_current' => true,
        ]);

        return redirect()->route('supplier.company.documents')->with('success', 'Document uploaded and submitted for review.');
    }

    public function destroy(SupplierDocument $document)
    {
        $account = $this->currentAccount();
        abort_if($document->supplier_account_id !== $account->id, 403);

        if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        return redirect()->route('supplier.company.documents')->with('success', 'Document deleted.');
    }
}
