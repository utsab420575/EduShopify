<?php

namespace App\Http\Controllers\Backend\Supplier\Catalog;

use App\Http\Controllers\Backend\Supplier\Concerns\InteractsWithSupplierAccount;
use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Services\Catalog\ProductMediaService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Single-file product media actions (the wizard's variant-photo upload,
 * re-crop and delete). All storage goes through ProductMediaService.
 */
class MediaController extends Controller
{
    use InteractsWithSupplierAccount;

    public function __construct(private readonly ProductMediaService $media) {}

    public function store(Request $request, Listing $listing)
    {
        $account = $this->currentAccount();
        abort_if($listing->supplier_account_id !== $account->id, 403);

        $request->validate([
            'image' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:'.config('product_media.images.max_upload_kb')],
            'crop' => ['nullable', 'json'],
        ]);

        $max = \App\Support\Media\ProductMediaLimits::maxImages();
        if ($listing->getMedia('gallery')->count() >= $max) {
            throw ValidationException::withMessages(['image' => "A product can have at most {$max} images."]);
        }

        $crop = $request->filled('crop') ? json_decode($request->input('crop'), true) : null;
        $media = $this->media->storeImage($listing, $account->id, $request->file('image'), is_array($crop) ? $crop : null);

        if ($request->wantsJson()) {
            $item = collect($this->media->payload($listing->fresh())['media'])->firstWhere('id', $media->id);

            return response()->json(['success' => true, 'media' => $item]);
        }

        return redirect()->supplierRoute('catalog.listings.show', $listing)->with('success', 'Image uploaded.');
    }

    /**
     * Re-crop a gallery image (original-image pixels): "banner" = product page
     * box (cover: stage-shaped; without x/width = full width) + "fit",
     * "card" = 4:3 card box.
     * Any part may be omitted → kept as is. Regenerates the copies.
     */
    public function crop(Request $request, Listing $listing, Media $media)
    {
        $this->ensureOwned($listing, $media);
        abort_unless($media->collection_name === 'gallery', 422);

        $data = $request->validate([
            'card' => ['nullable', 'array'],
            'card.x' => ['required_with:card', 'numeric', 'min:0'],
            'card.y' => ['required_with:card', 'numeric', 'min:0'],
            'card.width' => ['required_with:card', 'numeric', 'min:1'],
            'card.height' => ['required_with:card', 'numeric', 'min:1'],
            'banner' => ['nullable', 'array'],
            'banner.x' => ['nullable', 'numeric', 'min:0'],
            'banner.width' => ['nullable', 'numeric', 'min:1'],
            'banner.y' => ['required_with:banner', 'numeric', 'min:0'],
            'banner.height' => ['required_with:banner', 'numeric', 'min:1'],
            'fit' => ['nullable', 'in:'.implode(',', config('product_media.images.fits'))],
        ]);

        $media = $this->media->recrop($media, $data['card'] ?? null, $data['banner'] ?? null, $data['fit'] ?? null);
        $item = collect($this->media->payload($listing->fresh())['media'])->firstWhere('id', $media->id);

        return response()->json(['success' => true, 'media' => $item]);
    }

    public function destroy(Request $request, Listing $listing, Media $media)
    {
        $this->ensureOwned($listing, $media);

        $this->media->delete($listing, $media);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'primary_image_media_id' => $listing->fresh()->primary_image_media_id]);
        }

        return redirect()->supplierRoute('catalog.listings.show', $listing)->with('success', 'Media removed.');
    }

    private function ensureOwned(Listing $listing, Media $media): void
    {
        abort_if($listing->supplier_account_id !== $this->currentAccount()->id, 403);
        abort_if($media->model_type !== $listing->getMorphClass() || (int) $media->model_id !== (int) $listing->id, 403);
    }
}
