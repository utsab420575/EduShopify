<?php

namespace App\Http\Controllers\Backend\Supplier\Company;

use App\Http\Controllers\Backend\Supplier\Concerns\InteractsWithSupplierAccount;
use App\Http\Controllers\Controller;
use App\Models\SupplierGallery;
use App\Models\SupplierVideo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GalleryController extends Controller
{
    use InteractsWithSupplierAccount;

    public function index()
    {
        $account = $this->currentAccount();
        $gallery = $account->galleryImages()->orderBy('sort_order')->get();
        $videos = $account->videos()->orderBy('sort_order')->get();

        return view('backend.supplier.company.gallery', [
            'account' => $account,
            'user' => $this->currentUser(),
            'gallery' => $gallery,
            'videos' => $videos,
        ]);
    }

    public function store(Request $request)
    {
        $account = $this->currentAccount();

        if ($request->input('type') === 'video') {
            $validated = $request->validate([
                'title' => ['required', 'string', 'max:255'],
                'video_url' => ['required', 'url', 'max:500'],
                'caption' => ['nullable', 'string', 'max:255'],
            ]);

            SupplierVideo::create([
                'supplier_account_id' => $account->id,
                'title' => $validated['title'],
                'video_url' => $validated['video_url'],
                'caption' => $validated['caption'] ?? null,
                'sort_order' => $account->videos()->max('sort_order') + 1,
                'is_active' => true,
                'created_by_user_id' => $this->currentUser()->id,
            ]);

            return redirect()->route('supplier.company.gallery')->with('success', 'Video added to gallery.');
        }

        $validated = $request->validate([
            'image' => ['required', 'image', 'max:5120'],
            'caption' => ['nullable', 'string', 'max:255'],
            'alt_text' => ['nullable', 'string', 'max:255'],
        ]);

        $path = $request->file('image')->store('supplier/gallery/' . $account->id, 'public');

        SupplierGallery::create([
            'supplier_account_id' => $account->id,
            'image_path' => $path,
            'caption' => $validated['caption'] ?? null,
            'alt_text' => $validated['alt_text'] ?? null,
            'sort_order' => $account->galleryImages()->max('sort_order') + 1,
            'is_active' => true,
            'created_by_user_id' => $this->currentUser()->id,
        ]);

        return redirect()->route('supplier.company.gallery')->with('success', 'Image added to gallery.');
    }

    public function update(Request $request, SupplierGallery $gallery)
    {
        $account = $this->currentAccount();
        abort_if($gallery->supplier_account_id !== $account->id, 403);

        $validated = $request->validate([
            'caption' => ['nullable', 'string', 'max:255'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $gallery->update([
            'caption' => $validated['caption'] ?? null,
            'alt_text' => $validated['alt_text'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('supplier.company.gallery')->with('success', 'Gallery item updated.');
    }

    public function destroy(SupplierGallery $gallery)
    {
        $account = $this->currentAccount();
        abort_if($gallery->supplier_account_id !== $account->id, 403);

        if ($gallery->image_path && Storage::disk('public')->exists($gallery->image_path)) {
            Storage::disk('public')->delete($gallery->image_path);
        }

        $gallery->delete();

        return redirect()->route('supplier.company.gallery')->with('success', 'Image removed from gallery.');
    }

    public function reorder(Request $request)
    {
        $account = $this->currentAccount();
        $order = $request->input('order', []);

        foreach ($order as $index => $id) {
            $account->galleryImages()->where('id', $id)->update(['sort_order' => $index + 1]);
        }

        return response()->json(['status' => 'ok']);
    }
}
