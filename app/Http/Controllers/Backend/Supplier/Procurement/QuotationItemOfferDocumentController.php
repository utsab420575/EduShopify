<?php

namespace App\Http\Controllers\Backend\Supplier\Procurement;

use App\Http\Controllers\Backend\Supplier\Concerns\InteractsWithSupplierAccount;
use App\Http\Controllers\Controller;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\QuotationItemOffer;
use Illuminate\Http\Request;

/**
 * Document Quotation offer files — one level deeper than
 * QuotationItemDocumentController's item-level 'document' collection: each
 * quotation_item_offer (an individual offer under a Product Response) gets
 * its own files, so a Marketplace offer's spec sheet and a Document offer's
 * quotation PDF on the SAME item never collide.
 */
class QuotationItemOfferDocumentController extends Controller
{
    use InteractsWithSupplierAccount;

    /**
     * POST supplier/quotations/{quotation}/items/{item}/offers/{offer}/document
     * — a dedicated multipart endpoint, deliberately separate from the JSON
     * autosave() call (which can't carry files). One file per request (the
     * client re-calls this per file for multi-file upload). Only allowed for
     * offer_method 'document' — quotation files don't belong on any other
     * offer type.
     */
    public function store(Request $request, Quotation $quotation, QuotationItem $item, QuotationItemOffer $offer)
    {
        $this->authorize('editDraft', $quotation);
        abort_unless($item->quotation_id === $quotation->id, 404);
        abort_unless($offer->quotation_item_id === $item->id, 404);

        $request->validate([
            'document' => ['required', 'file', 'max:10240', 'mimes:pdf,doc,docx,xls,xlsx,csv'],
        ]);

        $file = $request->file('document');
        $fileName = \App\Support\Media\QuotationDocumentPathGenerator::supplierFileName($quotation->supplier_account_id, $file->getClientOriginalName());
        $media = $offer->addMedia($file)
            ->usingFileName($fileName)
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
     * DELETE supplier/quotations/{quotation}/items/{item}/offers/{offer}/document/{media}
     */
    public function destroy(Quotation $quotation, QuotationItem $item, QuotationItemOffer $offer, int $media)
    {
        $this->authorize('editDraft', $quotation);
        abort_unless($item->quotation_id === $quotation->id, 404);
        abort_unless($offer->quotation_item_id === $item->id, 404);

        $offer->getMedia('document')->where('id', $media)->first()?->delete();

        return response()->json(['success' => true]);
    }
}
