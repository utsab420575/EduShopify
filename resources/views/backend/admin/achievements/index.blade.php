@extends('backend.layouts.admin')

@section('title', 'Achievements')
@section('breadcrumb', 'Achievements / Achievement List')

@section('body')

    <x-backend.page-header title="Achievements" subtitle="Master catalogue of badges accounts can request and be awarded.">
        <x-slot:actions>
            <button type="button"
                    @click="$dispatch('open-modal-create-achievement')"
                    class="btn-primary text-sm font-medium px-4 py-2 rounded-lg inline-flex items-center gap-1.5 shadow-xs">
                <i class="fa-solid fa-plus"></i> New Achievement
            </button>
        </x-slot:actions>
    </x-backend.page-header>

    <x-backend.table>
        <x-slot:toolbar>
            <form method="GET" class="flex flex-wrap items-center gap-2 w-full">
                <div class="relative flex-1 min-w-[200px]">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search achievements..." class="focus-accent w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-gray-300">
                </div>
                <select name="status" onchange="this.form.submit()" class="focus-accent text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                    <option value="">All Statuses</option>
                    <option value="active" @selected($status === 'active')>Active</option>
                    <option value="inactive" @selected($status === 'inactive')>Inactive</option>
                </select>
                <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Filter</button>
                @if($search || $status)
                    <a href="{{ route('admin.achievements.index') }}" class="text-sm font-medium px-3 py-2 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50">Clear</a>
                @endif
            </form>
        </x-slot:toolbar>

        @if($achievements->isEmpty())
            <x-slot:empty><x-backend.empty-state icon="fa-trophy" title="No achievements found" description="Create an achievement badge accounts can request." /></x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Achievement</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Requirement</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Claims</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </x-slot:head>
            @foreach($achievements as $achievement)
                <tr class="hover:bg-gray-50/80 transition-colors">
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-600 flex-shrink-0">
                                <i class="fa-solid {{ $achievement->badge_icon ?: 'fa-trophy' }} text-sm"></i>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-900">{{ $achievement->name }}</p>
                                @if($achievement->description)
                                    <p class="text-xs text-gray-500 line-clamp-1 max-w-sm">{{ $achievement->description }}</p>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $achievement->type ?: '—' }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600 font-mono">{{ $achievement->requirement_value ?? '—' }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $achievement->account_achievements_count }}</td>
                    <td class="px-5 py-3.5">
                        <x-backend.status-badge :status="$achievement->is_active ? 'active' : 'inactive'" />
                    </td>
                    <td class="px-5 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            <button type="button"
                                    @click="$dispatch('open-modal-edit-achievement-{{ $achievement->id }}')"
                                    class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100 hover:text-indigo-600 transition-colors"
                                    title="Edit Achievement">
                                <i class="fa-regular fa-pen-to-square"></i>
                            </button>

                            <form method="POST"
                                  action="{{ route('admin.achievements.destroy', $achievement) }}"
                                  onsubmit="return confirmSwal(this, 'Delete Achievement?', 'Are you sure you want to delete {{ addslashes($achievement->name) }}?', 'warning', 'Yes, Delete')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-red-500 hover:bg-red-50 transition-colors"
                                        title="Delete Achievement">
                                    <i class="fa-regular fa-trash-can"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
        @endif

        <x-slot:pagination>
            <x-backend.pagination :paginator="$achievements" />
        </x-slot:pagination>
    </x-backend.table>

    {{-- Create Achievement Modal --}}
    <x-backend.modal id="create-achievement" title="New Achievement" width="max-w-xl">
        <form method="POST" action="{{ route('admin.achievements.store') }}" class="space-y-4">
            @csrf
            <x-backend.input name="name" label="Achievement Name" placeholder="e.g. Top Rated Supplier" required />
            <x-backend.textarea name="description" label="Description" placeholder="What does an account need to do to earn this?" rows="2" />

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-backend.input name="badge_icon" label="Badge Icon" placeholder="fa-trophy" hint="Font Awesome solid icon class" />
                <x-backend.input name="type" label="Type" placeholder="e.g. sales, tenure, quality" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-center pt-1">
                <x-backend.input name="requirement_value" label="Requirement Value" type="number" placeholder="e.g. 100" />
                <div class="flex items-center gap-2 pt-5">
                    <input type="checkbox" name="is_active" id="create_is_active" value="1" checked class="w-4 h-4 rounded border-gray-300" style="accent-color:var(--theme-primary)">
                    <label for="create_is_active" class="text-sm font-medium text-gray-700 cursor-pointer">Active / Requestable</label>
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-3 border-t border-gray-100">
                <button type="button" @click="open = false" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 text-xs font-medium">Cancel</button>
                <button type="submit" class="btn-primary text-xs font-semibold px-4 py-2 rounded-lg inline-flex items-center gap-1.5 shadow-xs">
                    <i class="fa-solid fa-plus"></i> Create Achievement
                </button>
            </div>
        </form>
    </x-backend.modal>

    {{-- Edit Achievement Modals --}}
    @foreach($achievements as $achievement)
        <x-backend.modal :id="'edit-achievement-'.$achievement->id" :title="'Edit Achievement: '.$achievement->name" width="max-w-xl">
            <form method="POST" action="{{ route('admin.achievements.update', $achievement) }}" class="space-y-4">
                @csrf
                @method('PUT')
                <x-backend.input name="name" label="Achievement Name" :value="$achievement->name" required />
                <x-backend.textarea name="description" label="Description" :value="$achievement->description" rows="2" />

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-backend.input name="badge_icon" label="Badge Icon" :value="$achievement->badge_icon" hint="Font Awesome solid icon class" />
                    <x-backend.input name="type" label="Type" :value="$achievement->type" />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-center pt-1">
                    <x-backend.input name="requirement_value" label="Requirement Value" type="number" :value="$achievement->requirement_value" />
                    <div class="flex items-center gap-2 pt-5">
                        <input type="checkbox" name="is_active" id="edit_is_active_{{ $achievement->id }}" value="1" @checked($achievement->is_active) class="w-4 h-4 rounded border-gray-300" style="accent-color:var(--theme-primary)">
                        <label for="edit_is_active_{{ $achievement->id }}" class="text-sm font-medium text-gray-700 cursor-pointer">Active / Requestable</label>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" @click="open = false" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 text-xs font-medium">Cancel</button>
                    <button type="submit" class="btn-primary text-xs font-semibold px-4 py-2 rounded-lg inline-flex items-center gap-1.5 shadow-xs">
                        <i class="fa-solid fa-check"></i> Update Achievement
                    </button>
                </div>
            </form>
        </x-backend.modal>
    @endforeach

@endsection
