<?php

namespace App\Http\Controllers\Backend\Supplier\Company;

use App\Http\Controllers\Backend\Supplier\Concerns\InteractsWithSupplierAccount;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Supplier\Company\StoreSocialLinkRequest;
use App\Http\Requests\Backend\Supplier\Company\UpdateSocialLinkRequest;
use App\Models\SocialLink;

class SocialLinkController extends Controller
{
    use InteractsWithSupplierAccount;

    public function store(StoreSocialLinkRequest $request)
    {
        $account = $this->currentAccount();
        $data = $request->validated();

        $account->socialLinks()->create([
            'social_platform_id' => $data['social_platform_id'],
            'url' => $data['url'],
            'handle' => $data['handle'] ?? null,
            'label' => $data['label'] ?? null,
            'is_public' => $request->boolean('is_public', true),
            'sort_order' => (int) ($account->socialLinks()->max('sort_order') ?? 0) + 1,
        ]);

        return redirect()->route('supplier.company.profile', ['section' => 'social-links'])
            ->with('success', 'Social link added.');
    }

    public function update(UpdateSocialLinkRequest $request, SocialLink $socialLink)
    {
        $account = $this->currentAccount();
        abort_unless($socialLink->socialable_type === get_class($account) && $socialLink->socialable_id === $account->id, 404);

        $data = $request->validated();

        $socialLink->update([
            'social_platform_id' => $data['social_platform_id'],
            'url' => $data['url'],
            'handle' => $data['handle'] ?? null,
            'label' => $data['label'] ?? null,
            'is_public' => $request->boolean('is_public', true),
        ]);

        return redirect()->route('supplier.company.profile', ['section' => 'social-links'])
            ->with('success', 'Social link updated.');
    }

    public function destroy(SocialLink $socialLink)
    {
        $account = $this->currentAccount();
        abort_unless($socialLink->socialable_type === get_class($account) && $socialLink->socialable_id === $account->id, 404);

        $socialLink->delete();

        return redirect()->route('supplier.company.profile', ['section' => 'social-links'])
            ->with('success', 'Social link removed.');
    }
}
