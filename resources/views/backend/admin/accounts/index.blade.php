@extends('backend.layouts.admin')

@section('title', 'Accounts')
@section('breadcrumb', 'Users & Accounts / Accounts')

@section('body')

    <x-backend.page-header title="Accounts" subtitle="Every marketplace account on the platform." />

    <x-backend.table>
        <x-slot:toolbar>
            <form method="GET" class="flex flex-wrap items-center gap-2 w-full">
                <div class="relative flex-1 min-w-[200px]">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search name or account number..." class="focus-accent w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-gray-300">
                </div>
                <select name="type" onchange="this.form.submit()" class="focus-accent text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                    <option value="">All Types</option>
                    <option value="individual" @selected($type === 'individual')>Individual</option>
                    <option value="organization" @selected($type === 'organization')>Organization</option>
                </select>
                <select name="status" onchange="this.form.submit()" class="focus-accent text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                    <option value="">All Statuses</option>
                    @foreach(['draft' => 'Draft', 'pending_approval' => 'Pending Approval', 'active' => 'Active', 'inactive' => 'Inactive', 'suspended' => 'Suspended', 'deletion_pending' => 'Deletion Pending', 'deleted' => 'Deleted'] as $value => $label)
                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Filter</button>
            </form>
        </x-slot:toolbar>

        @if($accounts->isEmpty())
            <x-slot:empty>
                <x-backend.empty-state icon="fa-building" title="No accounts found" />
            </x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Account</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Owner</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </x-slot:head>
            @foreach($accounts as $account)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3.5">
                        <p class="text-sm font-medium text-gray-900">{{ $account->display_name }}</p>
                        <p class="text-xs text-gray-400">{{ $account->account_number }}</p>
                    </td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $account->primaryOwner?->name ?? '—' }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ ucfirst($account->account_type) }}</td>
                    <td class="px-5 py-3.5"><x-backend.status-badge :status="$account->status" /></td>
                    <td class="px-5 py-3.5 text-right">
                        <a href="{{ route('admin.accounts.show', $account) }}" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100"><i class="fa-regular fa-eye"></i></a>
                    </td>
                </tr>
            @endforeach
        @endif

        <x-slot:pagination>
            <x-backend.pagination :paginator="$accounts" />
        </x-slot:pagination>
    </x-backend.table>

@endsection
