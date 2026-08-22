@extends('backend.layouts.admin')

@section('title', 'Theme')
@section('breadcrumb', 'System & Settings / Theme')

@section('body')

    <x-backend.page-header title="Theme" subtitle="Runtime colors applied across the Buyer, Supplier and Admin dashboards.">
        <x-slot:actions>
            <form method="POST" action="{{ route('admin.system.theme.reset') }}" onsubmit="return confirm('Reset all theme colors to the design.md defaults?');">
                @csrf
                <button type="submit" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Reset to Defaults</button>
            </form>
        </x-slot:actions>
    </x-backend.page-header>

    <form method="POST" action="{{ route('admin.system.theme.update') }}" class="space-y-6">
        @csrf @method('PUT')

        @php($fields = [
            'theme_primary' => 'Primary', 'theme_primary_hover' => 'Primary (hover)', 'theme_primary_soft' => 'Primary (soft)',
        ])
        <x-backend.form-card title="Accent">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                @foreach($fields as $key => $label)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">{{ $label }}</label>
                        <div class="flex items-center gap-2">
                            <input type="color" value="{{ $theme[$key] ?? '#4f46e5' }}" oninput="document.getElementById('{{ $key }}').value = this.value">
                            <input type="text" id="{{ $key }}" name="{{ $key }}" value="{{ $theme[$key] ?? '' }}" class="focus-accent flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        </div>
                    </div>
                @endforeach
            </div>
        </x-backend.form-card>

        @php($sidebarFields = [
            'sidebar_background' => 'Background', 'sidebar_border' => 'Border', 'sidebar_text' => 'Text', 'sidebar_muted' => 'Muted Text',
            'sidebar_menu_text' => 'Menu Text', 'sidebar_menu_hover_background' => 'Menu Hover Background', 'sidebar_menu_hover_text' => 'Menu Hover Text',
            'sidebar_menu_active_background' => 'Menu Active Background', 'sidebar_menu_active_text' => 'Menu Active Text', 'sidebar_menu_active_border' => 'Menu Active Border',
            'sidebar_submenu_text' => 'Submenu Text', 'sidebar_submenu_hover_background' => 'Submenu Hover Background', 'sidebar_submenu_hover_text' => 'Submenu Hover Text',
            'sidebar_submenu_active_background' => 'Submenu Active Background', 'sidebar_submenu_active_text' => 'Submenu Active Text',
        ])
        <x-backend.form-card title="Sidebar">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                @foreach($sidebarFields as $key => $label)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">{{ $label }}</label>
                        <div class="flex items-center gap-2">
                            <input type="color" value="{{ $theme[$key] ?? '#ffffff' }}" oninput="document.getElementById('{{ $key }}').value = this.value">
                            <input type="text" id="{{ $key }}" name="{{ $key }}" value="{{ $theme[$key] ?? '' }}" class="focus-accent flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        </div>
                    </div>
                @endforeach
            </div>
        </x-backend.form-card>

        @php($topbarFields = ['topbar_background' => 'Background', 'topbar_border' => 'Border'])
        <x-backend.form-card title="Topbar">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                @foreach($topbarFields as $key => $label)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">{{ $label }}</label>
                        <div class="flex items-center gap-2">
                            <input type="color" value="{{ $theme[$key] ?? '#ffffff' }}" oninput="document.getElementById('{{ $key }}').value = this.value">
                            <input type="text" id="{{ $key }}" name="{{ $key }}" value="{{ $theme[$key] ?? '' }}" class="focus-accent flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        </div>
                    </div>
                @endforeach
            </div>
        </x-backend.form-card>

        <div class="flex items-center justify-end gap-2 bg-white rounded-xl border border-gray-200 p-4">
            <button type="submit" class="btn-primary text-sm font-medium px-5 py-2 rounded-lg">Save Theme</button>
        </div>
    </form>

@endsection
