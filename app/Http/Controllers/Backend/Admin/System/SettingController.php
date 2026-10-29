<?php

namespace App\Http\Controllers\Backend\Admin\System;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\Media\ProductMediaLimits;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * System Settings — add / edit / delete rows of the settings table (groups
 * with their own page, e.g. theme and homepage hero, are left out).
 *
 * Settings the code reads (SYSTEM_KEYS) keep their group, name and type and
 * get extra value rules; deleting one makes the code fall back to its default.
 * Locked settings can't be changed here.
 */
class SettingController extends Controller
{
    /** "group.name" => [type, extra value rules] for settings the code reads. */
    private function systemKeys(): array
    {
        return [
            'product_media.max_images' => ['number', ['integer', 'min:1', 'max:100']],
            'product_media.max_videos' => ['number', ['integer', 'min:0', 'max:50']],
            'product_media.video_max_kb' => ['number', ['integer', 'min:1', 'max:512000']],
            'product_media.video_extensions' => ['list', []],
            'rfq.rfq_requires_admin_approval' => ['boolean', []],
            'capability.capability_application_max_attempts' => ['number', ['integer', 'min:1', 'max:100']],
            'capability.buyer_application_review_days' => ['number', ['integer', 'min:0', 'max:365']],
            'capability.account_conversion_max_attempts' => ['number', ['integer', 'min:1', 'max:100']],
            'award.award_response_hours' => ['number', ['integer', 'min:1', 'max:8760']],
            'messaging.allow_unrelated_messaging' => ['boolean', []],
        ];
    }

    public function index(Request $request)
    {
        $this->authorize('platform.settings.manage');

        $search = trim($request->string('search')->toString());
        $group = $request->string('group')->toString();

        $base = Setting::query()->whereNotIn('group_name', Setting::MANAGED_GROUPS);
        $groups = (clone $base)->distinct()->orderBy('group_name')->pluck('group_name');

        $settings = $base
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%{$search}%")
                ->orWhere('label', 'like', "%{$search}%")
                ->orWhere('group_name', 'like', "%{$search}%")))
            ->when($group !== '', fn ($q) => $q->where('group_name', $group))
            ->orderBy('group_name')->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $systemKeys = $this->systemKeys();
        $editData = $settings->getCollection()->mapWithKeys(fn (Setting $s) => [$s->id => [
            'id' => $s->id,
            'group_name' => $s->group_name,
            'name' => $s->name,
            'label' => (string) $s->label,
            'type' => $s->type ?: 'text',
            'value' => $s->formValue(),
            'description' => (string) $s->description,
            'system' => isset($systemKeys["{$s->group_name}.{$s->name}"]),
            'action' => route('admin.system.settings.update', $s),
        ]]);

        return view('backend.admin.system.settings.index', [
            'settings' => $settings,
            'groups' => $groups,
            'search' => $search,
            'group' => $group,
            'editData' => $editData,
            'systemKeys' => $systemKeys,
            'uploadLimit' => $this->phpUploadLimit(),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('platform.settings.manage');

        $data = $this->validated($request);
        $setting = Setting::create($data);

        activity('settings')->causedBy($request->user())->performedOn($setting)->log("Setting {$setting->group_name}.{$setting->name} created");

        return $this->backToList($request, "Setting \"{$setting->displayLabel()}\" added.");
    }

    public function update(Request $request, Setting $setting)
    {
        $this->authorize('platform.settings.manage');
        abort_if(in_array($setting->group_name, Setting::MANAGED_GROUPS, true), 404);

        if ($setting->locked) {
            return back()->with('error', "\"{$setting->displayLabel()}\" is locked and can't be changed here.");
        }

        $setting->update($this->validated($request, $setting));

        activity('settings')->causedBy($request->user())->performedOn($setting)->log("Setting {$setting->group_name}.{$setting->name} updated");

        return $this->backToList($request, "Setting \"{$setting->displayLabel()}\" updated.");
    }

    public function destroy(Request $request, Setting $setting)
    {
        $this->authorize('platform.settings.manage');
        abort_if(in_array($setting->group_name, Setting::MANAGED_GROUPS, true), 404);

        if ($setting->locked) {
            return back()->with('error', "\"{$setting->displayLabel()}\" is locked and can't be deleted.");
        }

        $setting->delete();

        activity('settings')->causedBy($request->user())->log("Setting {$setting->group_name}.{$setting->name} deleted");

        return $this->backToList($request, "Setting \"{$setting->displayLabel()}\" deleted.");
    }

    /** Validated row data; a system setting keeps its group, name and type. */
    private function validated(Request $request, ?Setting $setting = null): array
    {
        $systemKeys = $this->systemKeys();
        $isSystem = $setting && isset($systemKeys["{$setting->group_name}.{$setting->name}"]);

        if ($isSystem) {
            $request->merge(['group_name' => $setting->group_name, 'name' => $setting->name, 'type' => $systemKeys["{$setting->group_name}.{$setting->name}"][0]]);
        } else {
            $request->merge([
                'group_name' => strtolower(trim((string) $request->input('group_name'))),
                'name' => strtolower(trim((string) $request->input('name'))),
            ]);
        }

        $key = $request->input('group_name').'.'.$request->input('name');
        if (! $isSystem && isset($systemKeys[$key])) {
            $request->merge(['type' => $systemKeys[$key][0]]); // a new row for a key the code reads
        }
        $type = (string) $request->input('type');
        $extra = $systemKeys[$key][1] ?? [];

        $valueRules = match ($type) {
            'number' => array_merge(['required', 'numeric'], $extra),
            'boolean' => ['required', 'in:0,1'],
            'list' => ['nullable', 'string', 'max:2000'],
            default => ['nullable', 'string', 'max:5000'],
        };

        $request->validate([
            'group_name' => ['required', 'string', 'max:120', 'regex:/^[a-z][a-z0-9_]*$/', Rule::notIn(Setting::MANAGED_GROUPS)],
            'name' => [
                'required', 'string', 'max:160', 'regex:/^[a-z][a-z0-9_]*$/',
                Rule::unique('settings', 'name')->where('group_name', $request->input('group_name'))->ignore($setting?->id),
            ],
            'label' => ['nullable', 'string', 'max:160'],
            'type' => ['required', Rule::in(array_keys(Setting::TYPES))],
            'value' => $valueRules,
            'description' => ['nullable', 'string', 'max:2000'],
        ], [
            'group_name.regex' => 'Use lower-case letters, numbers and underscores (e.g. product_media).',
            'name.regex' => 'Use lower-case letters, numbers and underscores (e.g. video_max_kb).',
            'name.unique' => 'This group already has a setting with that name.',
        ]);

        $value = Setting::castValue($type, $request->input('value'));

        if ($key === 'product_media.video_extensions') {
            $value = array_values(array_unique(array_map(fn ($e) => strtolower(ltrim($e, '.')), $value)));
            $unknown = array_diff($value, ProductMediaLimits::knownVideoExtensions());
            if ($value === [] || $unknown) {
                throw ValidationException::withMessages(['value' => 'Use video extensions from: '.implode(', ', ProductMediaLimits::knownVideoExtensions()).'.']);
            }
        }

        return [
            'group_name' => $request->input('group_name'),
            'name' => $request->input('name'),
            'label' => $request->input('label') ?: null,
            'type' => $type,
            'payload' => ['value' => $value],
            'description' => $request->input('description') ?: null,
        ];
    }

    /** Back to the list the request came from (search/filter/page kept). */
    private function backToList(Request $request, string $message)
    {
        $url = (string) $request->input('_redirect', '');
        $list = route('admin.system.settings.index');

        return redirect()->to($url !== '' && ($url === $list || str_starts_with($url, $list.'?')) ? $url : $list)
            ->with('success', $message);
    }

    /** The server's effective upload ceiling (smaller of the two PHP limits), in KB. */
    private function phpUploadLimit(): int
    {
        $toKb = function (string $v): int {
            $v = trim($v);
            $n = (float) $v;

            return (int) match (strtolower(substr($v, -1))) {
                'g' => $n * 1024 * 1024,
                'm' => $n * 1024,
                'k' => $n,
                default => $n / 1024,
            };
        };

        return min($toKb((string) ini_get('upload_max_filesize')), $toKb((string) ini_get('post_max_size')));
    }
}
