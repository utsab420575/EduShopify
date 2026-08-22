<?php

namespace App\Http\Controllers\Backend\Admin\System;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * The curated list of settings actually read elsewhere in the app
     * (spec Part 5 / System & Settings). Each entry: [group, name, type, label, hint].
     */
    private const DEFINITIONS = [
        ['rfq', 'rfq_requires_admin_approval', 'boolean', 'RFQs require admin approval before publishing', 'When on, every new RFQ enters a pending_approval state until an admin approves it.'],
        ['capability', 'capability_application_max_attempts', 'integer', 'Maximum capability application attempts', 'How many times a buyer or supplier may re-apply after a rejection.'],
        ['award', 'award_response_hours', 'integer', 'Award response window (hours)', 'How long a supplier has to accept or reject an award before it expires.'],
    ];

    public function index()
    {
        $this->authorize('platform.settings.manage');

        $settings = collect(self::DEFINITIONS)->map(function ($definition) {
            [$group, $name, $type, $label, $hint] = $definition;

            return [
                'group' => $group,
                'name' => $name,
                'type' => $type,
                'label' => $label,
                'hint' => $hint,
                'value' => Setting::get($group, $name),
            ];
        })->groupBy('group');

        return view('backend.admin.system.settings.index', ['settingGroups' => $settings]);
    }

    public function update(Request $request)
    {
        $this->authorize('platform.settings.manage');

        foreach (self::DEFINITIONS as [$group, $name, $type]) {
            $field = "{$group}__{$name}";

            $value = match ($type) {
                'boolean' => $request->boolean($field),
                'integer' => $request->filled($field) ? (int) $request->input($field) : null,
                default => $request->input($field),
            };

            if ($value !== null) {
                try {
                    Setting::set($group, $name, $value);
                } catch (\RuntimeException $e) {
                    return back()->with('error', $e->getMessage());
                }
            }
        }

        activity('settings')->causedBy($request->user())->log('Platform settings updated');

        return back()->with('success', 'Settings updated.');
    }
}
