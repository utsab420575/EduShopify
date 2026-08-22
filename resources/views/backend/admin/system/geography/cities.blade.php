@extends('backend.layouts.admin')

@section('title', $state->name . ' — Cities')
@section('breadcrumb', 'System & Settings / Geography / ' . $state->name)

@section('body')

    <x-backend.page-header :title="$state->name" subtitle="Cities">
        <x-slot:actions>
            <a href="{{ route('admin.system.geography.states', $state->country) }}" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">&larr; States</a>
        </x-slot:actions>
    </x-backend.page-header>

    <x-backend.table>
        @if($cities->isEmpty())
            <x-slot:empty><x-backend.empty-state icon="fa-city" title="No cities found" /></x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">City</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </x-slot:head>
            @foreach($cities as $city)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3.5 text-sm font-medium text-gray-900">{{ $city->name }}</td>
                    <td class="px-5 py-3.5"><x-backend.status-badge :status="$city->is_active ? 'active' : 'inactive'" /></td>
                    <td class="px-5 py-3.5 text-right">
                        <form method="POST" action="{{ route('admin.system.geography.cities.toggle', $city) }}">
                            @csrf
                            <button type="submit" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100" title="Toggle active"><i class="fa-solid {{ $city->is_active ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i></button>
                        </form>
                    </td>
                </tr>
            @endforeach
        @endif

        <x-slot:pagination>
            <x-backend.pagination :paginator="$cities" />
        </x-slot:pagination>
    </x-backend.table>

@endsection
