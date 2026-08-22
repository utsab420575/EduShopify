@extends('backend.layouts.admin')

@section('title', 'Geography')
@section('breadcrumb', 'System & Settings / Geography')

@section('body')

    <x-backend.page-header title="Geography" subtitle="Countries available for delivery, sourcing and profile addresses." />

    <x-backend.table>
        <x-slot:toolbar>
            <form method="GET" class="flex flex-wrap items-center gap-2 w-full">
                <div class="relative flex-1 min-w-[200px]">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search countries..." class="focus-accent w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-gray-300">
                </div>
                <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Search</button>
            </form>
        </x-slot:toolbar>

        @if($countries->isEmpty())
            <x-slot:empty><x-backend.empty-state icon="fa-earth-americas" title="No countries found" /></x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Country</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">ISO</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">States</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </x-slot:head>
            @foreach($countries as $country)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3.5 text-sm font-medium text-gray-900">{{ $country->name }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $country->iso2 }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $country->states_count }}</td>
                    <td class="px-5 py-3.5"><x-backend.status-badge :status="$country->is_active ? 'active' : 'inactive'" /></td>
                    <td class="px-5 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            @if($country->states_count > 0)
                                <a href="{{ route('admin.system.geography.states', $country) }}" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100" title="View states"><i class="fa-solid fa-list"></i></a>
                            @endif
                            <form method="POST" action="{{ route('admin.system.geography.countries.toggle', $country) }}">
                                @csrf
                                <button type="submit" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100" title="Toggle active"><i class="fa-solid {{ $country->is_active ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
        @endif

        <x-slot:pagination>
            <x-backend.pagination :paginator="$countries" />
        </x-slot:pagination>
    </x-backend.table>

@endsection
