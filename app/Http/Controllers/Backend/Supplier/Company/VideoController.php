<?php

namespace App\Http\Controllers\Backend\Supplier\Company;

use App\Http\Controllers\Backend\Supplier\Concerns\InteractsWithSupplierAccount;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Supplier\Company\StoreVideoRequest;
use App\Models\SupplierVideo;
use Illuminate\Support\Facades\Auth;

class VideoController extends Controller
{
    use InteractsWithSupplierAccount;

    public function store(StoreVideoRequest $request)
    {
        $account = $this->currentAccount();
        $data = $request->validated();

        $provider = 'other';
        $videoId = null;
        if (str_contains($data['video_url'], 'vimeo.com')) {
            $provider = 'vimeo';
            $videoId = SupplierVideo::extractVimeoId($data['video_url']);
        } elseif (str_contains($data['video_url'], 'youtube.com') || str_contains($data['video_url'], 'youtu.be')) {
            $provider = 'youtube';
            $videoId = SupplierVideo::extractYoutubeId($data['video_url']);
        }

        SupplierVideo::create([
            'supplier_account_id' => $account->id,
            'provider' => $provider,
            'video_id' => $videoId,
            'title' => $data['title'],
            'video_url' => $data['video_url'],
            'caption' => $data['caption'] ?? null,
            'sort_order' => $account->videos()->max('sort_order') + 1,
            'is_active' => true,
            'created_by_user_id' => Auth::id(),
        ]);

        return redirect()->route('supplier.company.profile', ['section' => 'gallery'])
            ->with('success', 'Video added.');
    }

    public function destroy(SupplierVideo $video)
    {
        abort_unless($video->supplier_account_id === $this->currentAccount()->id, 404);

        $video->delete();

        return redirect()->route('supplier.company.profile', ['section' => 'gallery'])
            ->with('success', 'Video removed.');
    }
}
