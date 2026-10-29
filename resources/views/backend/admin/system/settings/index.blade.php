@extends('backend.layouts.admin')

@section('title', 'System Settings')
@section('breadcrumb', 'System & Settings / Settings')

@section('body')

    <x-backend.page-header title="System Settings" subtitle="Platform-wide values the system reads — limits, toggles and options.">
        <x-slot:actions>
            <button type="button" @click="$dispatch('setting-form', null)" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">
                <i class="fa-solid fa-plus mr-1.5"></i>New Setting
            </button>
        </x-slot:actions>
    </x-backend.page-header>

    {{-- A product video limit above what PHP accepts would fail before validation. --}}
    @php($videoMaxKb = \App\Support\Media\ProductMediaLimits::videoMaxKb())
    @if($uploadLimit > 0 && $videoMaxKb > $uploadLimit)
        <div class="mb-4 flex items-start gap-2.5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            <i class="fa-solid fa-triangle-exclamation mt-0.5"></i>
            <p>
                Product video max size is <strong>{{ \App\Support\Media\ProductMediaLimits::videoMaxLabel() }}</strong>, but this server's PHP upload limit is
                <strong>{{ $uploadLimit >= 1024 ? round($uploadLimit / 1024, 1).' MB' : $uploadLimit.' KB' }}</strong>
                (<code>upload_max_filesize</code> / <code>post_max_size</code>). Raise the PHP limit, or larger videos will fail to upload.
            </p>
        </div>
    @endif

    <div class="bg-white rounded-xl border border-gray-200 shadow-xs overflow-hidden">
        <x-backend.table-search
            title="Settings" :count="$settings->total()"
            search-param="search" page-param="page"
            :current-search="$search" placeholder="Search name, label or group..."
            :filter-params="['group']" :has-active-filter="$group !== ''">
            <x-slot:filters>
                <div>
                    <label class="block text-[11px] font-medium text-gray-500 mb-1">Group</label>
                    <select name="group" onchange="this.form.submit()" class="focus-accent w-44 text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                        <option value="">All Groups</option>
                        @foreach($groups as $g)
                            <option value="{{ $g }}" @selected($group === $g)>{{ Str::headline($g) }}</option>
                        @endforeach
                    </select>
                </div>
                @if($group !== '')
                    <a href="{{ request()->fullUrlWithQuery(['group' => null, 'page' => null]) }}" class="text-xs font-medium text-gray-500 hover:text-gray-700 px-2 py-2">Clear</a>
                @endif
            </x-slot:filters>
        </x-backend.table-search>

        @if($settings->isEmpty())
            <x-backend.empty-state icon="fa-sliders" title="No settings found"
                :description="$search !== '' || $group !== '' ? 'Try a different search or filter.' : 'Add the first system setting.'" />
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider w-14">SL</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Setting</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Group</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Value</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Updated</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($settings as $setting)
                            @php($isSystem = isset($systemKeys[$setting->group_name.'.'.$setting->name]))
                            <tr class="hover:bg-gray-50 align-top">
                                <td class="px-5 py-3.5 text-sm text-gray-500">{{ $settings->firstItem() + $loop->index }}</td>
                                <td class="px-5 py-3.5 max-w-sm">
                                    <p class="text-sm font-medium text-gray-900 flex items-center gap-1.5">
                                        {{ $setting->displayLabel() }}
                                        @if($setting->locked)
                                            <i class="fa-solid fa-lock text-[11px] text-amber-500" title="Locked"></i>
                                        @endif
                                        @if($isSystem)
                                            <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded bg-indigo-50 text-indigo-600" title="Read by the system — group, name and type are fixed">System</span>
                                        @endif
                                    </p>
                                    <p class="text-xs font-mono text-gray-400">{{ $setting->name }}</p>
                                    @if($setting->description)
                                        <p class="text-xs text-gray-500 mt-1 line-clamp-2">{{ $setting->description }}</p>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-sm text-gray-600">{{ Str::headline($setting->group_name) }}</td>
                                <td class="px-5 py-3.5 text-xs text-gray-500">{{ \App\Models\Setting::TYPES[$setting->type] ?? $setting->type }}</td>
                                <td class="px-5 py-3.5 text-sm text-gray-800">
                                    @if($setting->type === 'boolean')
                                        <x-backend.status-badge :status="$setting->value() ? 'active' : 'inactive'" />
                                    @else
                                        <span class="break-words">{{ Str::limit($setting->displayValue(), 80) }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-xs text-gray-500 whitespace-nowrap">{{ $setting->updated_at?->diffForHumans() }}</td>
                                <td class="px-5 py-3.5 text-right">
                                    @unless($setting->locked)
                                        <div class="flex items-center justify-end gap-1.5">
                                            <button type="button" title="Edit" @click="$dispatch('setting-form', @js($editData[$setting->id]))"
                                                    class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-indigo-600 hover:bg-indigo-50"><i class="fa-regular fa-pen-to-square"></i></button>
                                            <form method="POST" action="{{ route('admin.system.settings.destroy', $setting) }}"
                                                  onsubmit="return confirmSwal(this, 'Delete this setting?', @js($isSystem ? $setting->displayLabel().' will be removed and the system will use its built-in default.' : $setting->displayLabel().' will be permanently removed.'), 'warning', 'Yes, delete')">
                                                @csrf @method('DELETE')
                                                <input type="hidden" name="_redirect" value="{{ request()->fullUrl() }}">
                                                <button type="submit" title="Delete" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-red-500 hover:bg-red-50"><i class="fa-regular fa-trash-can"></i></button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400">Locked</span>
                                    @endunless
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-4 border-t border-gray-100">
                <x-backend.pagination :paginator="$settings" />
            </div>
        @endif
    </div>

    @include('backend.admin.system.settings.partials._form-modal')

@endsection
