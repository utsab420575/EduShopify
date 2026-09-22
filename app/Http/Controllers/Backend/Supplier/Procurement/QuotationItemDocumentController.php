<?php

namespace App\Http\Controllers\Backend\Supplier\Procurement;

use App\Http\Controllers\Backend\Supplier\Concerns\InteractsWithSupplierAccount;
use App\Http\Controllers\Controller;
use App\Models\Quotation;
use App\Models\QuotationItem;
use Illuminate\Http\Request;

class QuotationItemDocumentController extends Controller
{
    use InteractsWithSupplierAccount;

    /**
     * POST supplier/quotations/{quotation}/items/{item}/document — a
     * dedicated multipart endpoint, deliberately separate from the JSON
     * autosave() call (which can't carry files). Mirrors
     * RequirementController::attachUploadedFiles()'s exact validation/upload
     * pattern, one file per request (the client re-calls this per file).
     */
    public function store(Request $request, Quotation $quotation, QuotationItem $item)
    {
        $this->authorize('editDraft', $quotation);
        abort_unless($item->quotation_id === $quotation->id, 404);

        $request->validate([
            'document' => ['required', 'file', 'max:10240', 'mimes:jpg,jpeg,png,webp,pdf,doc,docx,xls,xlsx,zip,csv,txt'],
        ]);

        $file = $request->file('document');
        $media = $item->addMedia($file)
            ->usingFileName(sprintf('quo_item_%s_%s.%s', $item->id, uniqid(), $file->getClientOriginalExtension()))
            ->toMediaCollection('document');

        return response()->json([
            'id' => $media->id,
            'name' => $media->file_name,
            'size' => $media->human_readable_size,
            'url' => $media->getUrl(),
            'is_image' => str_starts_with($media->mime_type ?? '', 'image/'),
        ]);
    }

    /**
     * DELETE supplier/quotations/{quotation}/items/{item}/document/{media}
     */
    public function destroy(Quotation $quotation, QuotationItem $item, int $media)
    {
        $this->authorize('editDraft', $quotation);
        abort_unless($item->quotation_id === $quotation->id, 404);

        $item->getMedia('document')->where('id', $media)->first()?->delete();

        return response()->json(['success' => true]);
    }
}
