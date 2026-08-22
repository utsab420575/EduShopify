<?php

namespace App\Http\Controllers\Backend\Admin\System;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class ThemeController extends Controller
{
    /**
     * Keys mirrored 1:1 from resources/views/backend/layouts/partials/shared/_theme.blade.php,
     * where each falls back to the design.md default when unset.
     */
    private const KEYS = [
        'theme_primary', 'theme_primary_hover', 'theme_primary_soft',
        'sidebar_background', 'sidebar_border', 'sidebar_text', 'sidebar_muted',
        'sidebar_menu_text', 'sidebar_menu_hover_background', 'sidebar_menu_hover_text',
        'sidebar_menu_active_background', 'sidebar_menu_active_text', 'sidebar_menu_active_border',
        'sidebar_submenu_text', 'sidebar_submenu_hover_background', 'sidebar_submenu_hover_text',
        'sidebar_submenu_active_background', 'sidebar_submenu_active_text',
        'topbar_background', 'topbar_border',
    ];

    public function edit()
    {
        $this->authorize('platform.settings.manage');

        return view('backend.admin.system.theme.edit', [
            'theme' => Setting::group('theme'),
        ]);
    }

    public function update(Request $request)
    {
        $this->authorize('platform.settings.manage');

        $request->validate(collect(self::KEYS)->mapWithKeys(fn ($key) => [$key => ['nullable', 'string', 'max:30']])->all());

        foreach (self::KEYS as $key) {
            if ($request->filled($key)) {
                Setting::set('theme', $key, $request->string($key)->toString());
            }
        }

        return back()->with('success', 'Theme updated.');
    }

    public function reset()
    {
        $this->authorize('platform.settings.manage');

        foreach (self::KEYS as $key) {
            \App\Models\Setting::where('group_name', 'theme')->where('name', $key)->delete();
        }

        \Illuminate\Support\Facades\Cache::forget('setting-group:theme');

        return back()->with('success', 'Theme reset to defaults.');
    }
}
