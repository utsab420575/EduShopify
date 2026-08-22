@extends('backend.layouts.admin')

@section('title', 'Currencies')
@section('breadcrumb', 'System & Settings / Currencies')

@section('body')

    <x-backend.page-header title="Currencies" subtitle="Currencies supported for pricing across the platform.">
        <x-slot:actions>
            <a href="{{ route('admin.system.currencies.create') }}" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg"><i class="fa-solid fa-plus mr-1.5"></i>New Currency</a>
        </x-slot:actions>
    </x-backend.page-header>

    <x-backend.table>
        @if($currencies->isEmpty())
            <x-slot:empty><x-backend.empty-state icon="fa-coins" title="No currencies found" /></x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Currency</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Symbol</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Exchange Rate</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Default</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </x-slot:head>
            @foreach($currencies as $currency)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3.5 text-sm font-medium text-gray-900">{{ $currency->code }} &mdash; {{ $currency->name }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $currency->symbol }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $currency->exchange_rate }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">
                        @if($currency->is_default)
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-indigo-50" style="color:var(--theme-primary)">Default</span>
                        @else
                            <form method="POST" action="{{ route('admin.system.currencies.default', $currency) }}">
                                @csrf
                                <button type="submit" class="text-xs font-medium" style="color:var(--theme-primary)">Make default</button>
                            </form>
                        @endif
                    </td>
                    <td class="px-5 py-3.5"><x-backend.status-badge :status="$currency->is_active ? 'active' : 'inactive'" /></td>
                    <td class="px-5 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            <a href="{{ route('admin.system.currencies.edit', $currency) }}" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100"><i class="fa-regular fa-pen-to-square"></i></a>
                            @if(!$currency->is_default)
                                <form method="POST" action="{{ route('admin.system.currencies.destroy', $currency) }}" onsubmit="return confirm('Delete this currency?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-red-500 hover:bg-red-50"><i class="fa-regular fa-trash-can"></i></button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        @endif
    </x-backend.table>

@endsection
