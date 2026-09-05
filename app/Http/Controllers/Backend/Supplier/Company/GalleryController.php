<?php

namespace App\Http\Controllers\Backend\Supplier\Company;

use App\Http\Controllers\Backend\Supplier\Concerns\InteractsWithSupplierAccount;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Supplier\Company\StoreGalleryRequest;
use App\Models\SupplierGallery;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class GalleryController extends Controller
{
    use InteractsWithSupplierAccount;

    public function store(StoreGalleryRequest $request)
    {
        $account = $this->currentAccount();

        foreach ($request->file('photos') as $file) {
            $path = $file->store('supplier/gallery/'.$account->id, 'public');
            SupplierGallery::create([
                'supplier_account_id' => $account->id,
                'image_path' => $path,
                'sort_order' => $account->galleryImages()->max('sort_order') + 1,
                'is_active' => true,
                'created_by_user_id' => Auth::id(),
            ]);
        }

        return redirect()->route('supplier.company.profile', ['section' => 'gallery'])
            ->with('success', 'Gallery updated.');
    }

    public function destroy(SupplierGallery $image)
    {
        abort_unless($image->supplier_account_id === $this->currentAccount()->id, 404);

        if ($image->image_path && Storage::disk('public')->exists($image->image_path)) {
            Storage::disk('public')->delete($image->image_path);
        }
        $image->delete();

        return redirect()->route('supplier.company.profile', ['section' => 'gallery'])
            ->with('success', 'Image removed.');
    }
}
