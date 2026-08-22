@extends('backend.layouts.admin')

@section('title', 'Platform Settings')
@section('breadcrumb', 'System & Settings / Settings')

@section('body')

    <x-backend.page-header title="Platform Settings" subtitle="Configuration that governs procurement behavior platform-wide." />

    <form method="POST" action="{{ route('admin.system.settings.update') }}" class="space-y-6">
        @csrf @method('PUT')

        @foreach($settingGroups as $group => $settings)
            <x-backend.form-card :title="ucfirst($group)">
                <div class="space-y-4">
                    @foreach($settings as $setting)
                        @php($field = $setting['group'].'__'.$setting['name'])
                        <div>
                            @if($setting['type'] === 'boolean')
                                <label class="flex items-center gap-2 text-sm text-gray-700">
                                    <input type="checkbox" name="{{ $field }}" value="1" @checked($setting['value']) style="accent-color:var(--theme-primary)">
                                    <span class="font-medium">{{ $setting['label'] }}</span>
                                </label>
                            @else
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">{{ $setting['label'] }}</label>
                                <input type="number" name="{{ $field }}" value="{{ $setting['value'] }}" class="focus-accent w-full sm:w-48 px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            @endif
                            @if($setting['hint'])
                                <p class="text-xs text-gray-400 mt-1">{{ $setting['hint'] }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </x-backend.form-card>
        @endforeach

        <div class="flex items-center justify-end gap-2 bg-white rounded-xl border border-gray-200 p-4">
            <button type="submit" class="btn-primary text-sm font-medium px-5 py-2 rounded-lg">Save Settings</button>
        </div>
    </form>

@endsection
