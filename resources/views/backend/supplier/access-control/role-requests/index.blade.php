@extends('backend.layouts.supplier')

@section('title', 'Role Requests')
@section('breadcrumb', 'Access Control / Role Requests')

@section('body')

    <x-backend.page-header title="Role Elevation Requests" subtitle="Request additional organizational roles from account administrators.">
        <x-slot:actions>
            <a href="{{ route('supplier.role-requests.create') }}" class="btn-primary text-xs font-semibold px-4 py-2 rounded-lg flex items-center gap-1.5">
                <i class="fa-solid fa-plus"></i> Request New Role
            </a>
        </x-slot:actions>
    </x-backend.page-header>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        @if($roleRequests->isEmpty())
            <div class="p-8 text-center">
                <x-backend.empty-state icon="fa-key" title="No role requests" description="Submit requests when additional platform permissions are required." />
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-gray-500 bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-5 py-3.5 font-semibold">User</th>
                            <th class="px-3 py-3.5 font-semibold">Requested Role</th>
                            <th class="px-3 py-3.5 font-semibold">Justification</th>
                            <th class="px-3 py-3.5 font-semibold">Status</th>
                            <th class="px-5 py-3.5 font-semibold text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-xs">
                        @foreach($roleRequests as $req)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3.5 font-bold text-gray-900">{{ $req->user?->name }}</td>
                                <td class="px-3 py-3.5 font-semibold text-indigo-700">{{ $req->role?->name }}</td>
                                <td class="px-3 py-3.5 text-gray-600 max-w-xs truncate">{{ $req->justification }}</td>
                                <td class="px-3 py-3.5"><x-backend.status-badge :status="$req->status" /></td>
                                <td class="px-5 py-3.5 text-right">
                                    @if($req->status === 'pending')
                                        <form method="POST" action="{{ route('supplier.role-requests.cancel', $req) }}">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:underline text-[11px]">Cancel</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($roleRequests->hasPages())
                <div class="p-4 border-t border-gray-100">
                    {{ $roleRequests->links() }}
                </div>
            @endif
        @endif
    </div>

@endsection
