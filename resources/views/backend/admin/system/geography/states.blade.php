@extends('backend.layouts.admin')

@section('title', $country->name . ' — States')
@section('breadcrumb', 'System & Settings / Geography / ' . $country->name)

@section('body')

    <x-backend.page-header :title="$country->name" subtitle="States / provinces">
        <x-slot:actions>
            <a href="{{ route('admin.system.geography.index') }}" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">&larr; Countries</a>
        </x-slot:actions>
    </x-backend.page-header>

    <x-backend.table>
        @if($states->isEmpty())
            <x-slot:empty><x-backend.empty-state icon="fa-map" title="No states found" /></x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">State</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Cities</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </x-slot:head>
            @foreach($states as $state)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3.5 text-sm font-medium text-gray-900">{{ $state->name }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $state->cities_count }}</td>
                    <td class="px-5 py-3.5"><x-backend.status-badge :status="$state->is_active ? 'active' : 'inactive'" /></td>
                    <td class="px-5 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            @if($state->cities_count > 0)
                                <a href="{{ route('admin.system.geography.cities', $state) }}" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100" title="View cities"><i class="fa-solid fa-list"></i></a>
                            @endif
                            <form method="POST" action="{{ route('admin.system.geography.states.toggle', $state) }}">
                                @csrf
                                <button type="submit" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100" title="Toggle active"><i class="fa-solid {{ $state->is_active ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
        @endif

        <x-slot:pagination>
            <x-backend.pagination :paginator="$states" />
        </x-slot:pagination>
    </x-backend.table>

@endsection
