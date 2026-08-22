@extends('backend.layouts.admin')

@section('title', 'Capability Applications')
@section('breadcrumb', 'Users & Accounts / Capabilities')

@section('body')

    <x-backend.page-header title="Capability Applications" subtitle="Buyer and Supplier capability applications platform-wide." />

    <x-backend.table>
        <x-slot:toolbar>
            <form method="GET" class="flex flex-wrap items-center gap-2 w-full">
                <div class="relative flex-1 min-w-[200px]">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search account..." class="focus-accent w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-gray-300">
                </div>
                <select name="type" onchange="this.form.submit()" class="focus-accent text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                    <option value="">All Types</option>
                    <option value="buyer" @selected($type === 'buyer')>Buyer</option>
                    <option value="supplier" @selected($type === 'supplier')>Supplier</option>
                </select>
                <select name="status" onchange="this.form.submit()" class="focus-accent text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                    <option value="">All Statuses</option>
                    @foreach(['pending' => 'Pending', 'active' => 'Active', 'revision_required' => 'Revision Required', 'rejected' => 'Rejected', 'suspended' => 'Suspended'] as $value => $label)
                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Filter</button>
            </form>
        </x-slot:toolbar>

        @if($capabilities->isEmpty())
            <x-slot:empty><x-backend.empty-state icon="fa-id-card" title="No applications found" /></x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Account</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Capability</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Attempts</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Applied</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </x-slot:head>
            @foreach($capabilities as $capability)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3.5 text-sm font-medium text-gray-900">{{ $capability->account?->display_name }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $capability->capabilityType?->name }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $capability->application_attempts }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $capability->applied_at?->format('d M Y') ?? '—' }}</td>
                    <td class="px-5 py-3.5"><x-backend.status-badge :status="$capability->status" /></td>
                    <td class="px-5 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            @if($capability->status === 'pending')
                                <form method="POST" action="{{ route('admin.capabilities.approve', $capability) }}">
                                    @csrf
                                    <button type="submit" title="Approve" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-green-600 hover:bg-green-50"><i class="fa-solid fa-check"></i></button>
                                </form>
                                <button type="button" title="Reject" @click="$dispatch('open-modal-reject-capability-{{ $capability->id }}')" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-red-600 hover:bg-red-50"><i class="fa-solid fa-xmark"></i></button>
                            @endif
                            <a href="{{ route('admin.capabilities.show', $capability) }}" title="View" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100"><i class="fa-regular fa-eye"></i></a>
                        </div>
                    </td>
                </tr>
            @endforeach
        @endif

        <x-slot:pagination>
            <x-backend.pagination :paginator="$capabilities" />
        </x-slot:pagination>
    </x-backend.table>

    @foreach($capabilities as $capability)
        @if($capability->status === 'pending')
            <x-backend.modal :id="'reject-capability-'.$capability->id" title="Reject Application">
                <form method="POST" action="{{ route('admin.capabilities.reject', $capability) }}">
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
