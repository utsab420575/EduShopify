@extends('backend.layouts.admin')

@section('title', 'Supplier Types')
@section('breadcrumb', 'Catalog & Taxonomy / Supplier Types')

@section('body')

    <x-backend.page-header title="Supplier Types" subtitle="Classification used during supplier onboarding.">
        <x-slot:actions>
            <a href="{{ route('admin.catalog.supplier-types.create') }}" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg"><i class="fa-solid fa-plus mr-1.5"></i>New Supplier Type</a>
        </x-slot:actions>
    </x-backend.page-header>

    <x-backend.table>
        @if($supplierTypes->isEmpty())
            <x-slot:empty><x-backend.empty-state icon="fa-industry" title="No supplier types found" /></x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </x-slot:head>
            @foreach($supplierTypes as $supplierType)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3.5 text-sm font-medium text-gray-900">{{ $supplierType->name }}</td>
                    <td class="px-5 py-3.5"><x-backend.status-badge :status="$supplierType->is_active ? 'active' : 'inactive'" /></td>
                    <td class="px-5 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            <a href="{{ route('admin.catalog.supplier-types.edit', $supplierType) }}" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100"><i class="fa-regular fa-pen-to-square"></i></a>
                            <form method="POST" action="{{ route('admin.catalog.supplier-types.destroy', $supplierType) }}" onsubmit="return confirm('Delete this supplier type?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-red-500 hover:bg-red-50"><i class="fa-regular fa-trash-can"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
        @endif

        <x-slot:pagination>
            <x-backend.pagination :paginator="$supplierTypes" />
        </x-slot:pagination>
    </x-backend.table>

@endsection
