<?php

namespace App\Http\Controllers\Backend\Admin\System;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

/**
 * Admin-editable content for the public homepage hero section
 * (resources/views/frontend_new/home/partial/_hero.blade.php), backed by
 * the generic Setting key-value store (group: homepage_hero) rather than a
 * dedicated CMS table — see docs/AI/workflows/frontend_workflow.md §79.
 */
class HomepageHeroController extends Controller
{
    private const GROUP = 'homepage_hero';

    private const DEFAULT_IMAGE_PATH = 'System_Files/HeroSection/Hero1.png';

    public function edit()
    {
        $this->authorize('platform.homepage_content.manage');

        $settings = Setting::group(self::GROUP);

        return view('backend.admin.system.hero-section.edit', [
            'heading'             => $settings['heading'] ?? "Global Suppliers for\nEducation. All in One Place.",
            'subheading'          => $settings['subheading'] ?? 'Connect with verified education suppliers worldwide',
            'imagePath'           => $settings['image_path'] ?? self::DEFAULT_IMAGE_PATH,
            'primaryButtonText'   => $settings['primary_button_text'] ?? 'Find Suppliers',
            'primaryButtonUrl'    => $settings['primary_button_url'] ?? route('v2.suppliers.index'),
            'secondaryButtonText' => $settings['secondary_button_text'] ?? 'Post an RFQ',
            'secondaryButtonUrl'  => $settings['secondary_button_url'] ?? route('v2.handoff.post-rfq'),
        ]);
    }

    public function update(Request $request)
    {
        $this->authorize('platform.homepage_content.manage');

        $data = $request->validate([
            'heading'                => ['required', 'string', 'max:255'],
            'subheading'              => ['required', 'string', 'max:255'],
            'primary_button_text'    => ['required', 'string', 'max:60'],
            'primary_button_url'     => ['required', 'string', 'max:255'],
            'secondary_button_text'  => ['required', 'string', 'max:60'],
            'secondary_button_url'   => ['required', 'string', 'max:255'],
            'image'                  => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        foreach ([
            'heading', 'subheading', 'primary_button_text', 'primary_button_url',
            'secondary_button_text', 'secondary_button_url',
        ] as $field) {
            Setting::set(self::GROUP, $field, $data[$field]);
        }

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('System_Files/HeroSection', 'public');
            Setting::set(self::GROUP, 'image_path', $path);
        }

        activity('settings')->causedBy($request->user())->log('Homepage hero section updated');

        return back()->with('success', 'Hero section updated.');
    }
}
