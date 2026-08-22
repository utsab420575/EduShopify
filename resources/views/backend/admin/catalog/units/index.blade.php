@extends('backend.layouts.admin')

@section('title', 'Units')
@section('breadcrumb', 'Catalog & Taxonomy / Units')

@section('body')

    <x-backend.page-header title="Units" subtitle="Global units plus supplier-submitted custom units awaiting review.">
        <x-slot:actions>
            <a href="{{ route('admin.catalog.units.create') }}" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg"><i class="fa-solid fa-plus mr-1.5"></i>New Unit</a>
        </x-slot:actions>
    </x-backend.page-header>

    <x-backend.table>
        <x-slot:toolbar>
            <form method="GET" class="flex flex-wrap items-center gap-2 w-full">
                <div class="relative flex-1 min-w-[200px]">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search units..." class="focus-accent w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-gray-300">
                </div>
                <select name="status" onchange="this.form.submit()" class="focus-accent text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                    <option value="">All Statuses</option>
                    @foreach(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $value => $label)
                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Filter</button>
            </form>
        </x-slot:toolbar>

        @if($units->isEmpty())
            <x-slot:empty><x-backend.empty-state icon="fa-ruler" title="No units found" /></x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Symbol</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Owner</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </x-slot:head>
            @foreach($units as $unit)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3.5 text-sm font-medium text-gray-900">{{ $unit->name }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $unit->symbol }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ ucfirst($unit->unit_type) }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $unit->isGlobal() ? 'Global' : ($unit->supplierAccount?->supplierProfile?->display_name ?? '—') }}</td>
                    <td class="px-5 py-3.5"><x-backend.status-badge :status="$unit->approval_status" /></td>
                    <td class="px-5 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            @if($unit->approval_status === 'pending')
                                <form method="POST" action="{{ route('admin.catalog.units.approve', $unit) }}">
                                    @csrf
                                    <button type="submit" title="Approve" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-green-600 hover:bg-green-50"><i class="fa-solid fa-check"></i></button>
                                </form>
                                <button type="button" title="Reject" @click="$dispatch('open-modal-reject-unit-{{ $unit->id }}')" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-red-600 hover:bg-red-50"><i class="fa-solid fa-xmark"></i></button>
                            @endif
                            <a href="{{ route('admin.catalog.units.edit', $unit) }}" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100"><i class="fa-regular fa-pen-to-square"></i></a>
                            <form method="POST" action="{{ route('admin.catalog.units.destroy', $unit) }}" onsubmit="return confirm('Delete this unit?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-red-500 hover:bg-red-50"><i class="fa-regular fa-trash-can"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
        @endif

        <x-slot:pagination>
            <x-backend.pagination :paginator="$units" />
        </x-slot:pagination>
    </x-backend.table>

    @foreach($units as $unit)
        @if($unit->approval_status === 'pending')
            <x-backend.modal :id="'reject-unit-'.$unit->id" title="Reject Unit">
                <form method="POST" action="{{ route('admin.catalog.units.reject', $unit) }}">
                    @csrf
                    <x-backend.textarea name="reason" label="Rejection reason" required />
                    <div class="flex justify-end gap-2 mt-4">
                        <button type="button" @click="open = false" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                        <button type="submit" class="text-sm font-medium px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700">Reject</button>
                    </div>
                </form>
            </x-backend.modal>
        @endif
    @endforeach

@endsection
