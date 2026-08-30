<?php

namespace App\Http\Controllers\Backend\Supplier\Catalog;

use App\Http\Controllers\Backend\Supplier\Concerns\InteractsWithSupplierAccount;
use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Support\Media\ProductImagePathGenerator;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaController extends Controller
{
    use InteractsWithSupplierAccount;

    public function store(Request $request, Listing $listing)
    {
        $account = $this->currentAccount();
        abort_if($listing->supplier_account_id !== $account->id, 403);

        $request->validate([
            'image' => ['required', 'image', 'max:5120'],
        ]);

        $media = $listing->addMediaFromRequest('image')
            ->usingFileName(ProductImagePathGenerator::supplierFileName($account->id, $request->file('image')->getClientOriginalName()))
            ->toMediaCollection('gallery');

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'media'   => [
                    'id'         => $media->id,
                    'url'        => $media->getUrl(),
                    'file_name'  => $media->file_name,
                    'size'       => $media->human_readable_size,
                    'is_primary' => false,
                ],
            ]);
        }

        return redirect()->route('supplier.catalog.listings.show', $listing)->with('success', 'Image uploaded.');
    }

    public function destroy(Listing $listing, Media $media)
    {
        $account = $this->currentAccount();
        abort_if($listing->supplier_account_id !== $account->id, 403);
        abort_if($media->model_type !== Listing::class || $media->model_id !== $listing->id, 403);

        $media->delete();

        return redirect()->route('supplier.catalog.listings.show', $listing)->with('success', 'Media removed.');
    }
}
