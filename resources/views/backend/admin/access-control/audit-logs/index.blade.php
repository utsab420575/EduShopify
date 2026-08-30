@extends('backend.layouts.admin')

@section('title', 'Permission Audit Logs')
@section('breadcrumb', 'Access Control / Permission Audit Logs')

@section('body')

    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Permission Audit Logs</h1>
            <p class="text-sm text-gray-500 mt-1">Audit trail tracking role modifications, permission syncs, and employee role assignments.</p>
        </div>
    </div>

    <x-backend.table>
        @if($activities->isEmpty())
            <x-slot:empty><x-backend.empty-state icon="fa-shield-halved" title="No RBAC audit logs recorded" /></x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500">Timestamp</th>
                    <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500">Action Type</th>
                    <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500">Performed By</th>
                    <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500">Description</th>
                    <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500">IP Address</th>
                </tr>
            </x-slot:head>
            @foreach($activities as $act)
                @php($action = $act->properties['action'] ?? '')
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-3.5 text-xs text-gray-500 font-mono">
                        {{ $act->created_at->format('M d, Y H:i:s') }}
                    </td>
                    <td class="px-6 py-3.5">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                            {{ $action === 'role_created' ? 'bg-emerald-100 text-emerald-800' : ($action === 'role_duplicated' ? 'bg-amber-100 text-amber-800' : ($action === 'permissions_synced' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800')) }}">
                            {{ str_replace('_', ' ', ucfirst($action ?: 'update')) }}
                        </span>
                    </td>
                    <td class="px-6 py-3.5 text-xs font-bold text-gray-900">
                        {{ $act->causer?->name ?? 'System' }}
                    </td>
                    <td class="px-6 py-3.5 text-xs text-gray-700">
                        {{ $act->description }}
                        @if(!empty($act->properties['added_permissions'] ?? []))
                            <div class="mt-1 text-[11px] text-emerald-700">
                                <strong>+ Added:</strong> {{ implode(', ', $act->properties['added_permissions']) }}
                            </div>
                        @endif
                        @if(!empty($act->properties['removed_permissions'] ?? []))
                            <div class="mt-0.5 text-[11px] text-rose-700">
                                <strong>- Removed:</strong> {{ implode(', ', $act->properties['removed_permissions']) }}
                            </div>
                        @endif
                    </td>
                    <td class="px-6 py-3.5 text-xs text-gray-400 font-mono">
                        {{ $act->properties['ip_address'] ?? '—' }}
                    </td>
                </tr>
            @endforeach
        @endif

        <x-slot:pagination>
            <x-backend.pagination :paginator="$activities" />
        </x-slot:pagination>
    </x-backend.table>

@endsection
