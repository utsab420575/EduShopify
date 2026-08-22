@extends('backend.layouts.admin')

@section('title', 'Account Closures')
@section('breadcrumb', 'Users & Accounts / Closures')

@section('body')

    <x-backend.page-header title="Account Closures" subtitle="Accounts awaiting deletion — review dependencies before finalizing." />

    <x-backend.table>
        @if($accounts->isEmpty())
            <x-slot:empty><x-backend.empty-state icon="fa-trash-can" title="No pending closures" /></x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Account</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Owner</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Requested</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </x-slot:head>
            @foreach($accounts as $account)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3.5 text-sm font-medium text-gray-900">{{ $account->display_name }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $account->primaryOwner?->name ?? '—' }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $account->deletion_requested_at?->format('d M Y') ?? '—' }}</td>
                    <td class="px-5 py-3.5 text-right">
                        <a href="{{ route('admin.closures.show', $account) }}" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100"><i class="fa-regular fa-eye"></i></a>
                    </td>
                </tr>
            @endforeach
        @endif

        <x-slot:pagination>
            <x-backend.pagination :paginator="$accounts" />
        </x-slot:pagination>
    </x-backend.table>

@endsection
