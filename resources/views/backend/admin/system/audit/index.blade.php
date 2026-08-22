@extends('backend.layouts.admin')

@section('title', 'Audit Log')
@section('breadcrumb', 'System & Settings / Audit Log')

@section('body')

    <x-backend.page-header title="Audit Log" subtitle="Every moderation and administrative action taken on the platform." />

    <x-backend.table>
        <x-slot:toolbar>
            <form method="GET" class="flex flex-wrap items-center gap-2 w-full">
                <div class="relative flex-1 min-w-[200px]">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search description..." class="focus-accent w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-gray-300">
                </div>
                <select name="log" onchange="this.form.submit()" class="focus-accent text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                    <option value="">All Logs</option>
                    @foreach($logNames as $name)
                        <option value="{{ $name }}" @selected($log === $name)>{{ ucfirst($name) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Filter</button>
            </form>
        </x-slot:toolbar>

        @if($activities->isEmpty())
            <x-slot:empty><x-backend.empty-state icon="fa-clock-rotate-left" title="No activity recorded" /></x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Description</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Causer</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Log</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </x-slot:head>
            @foreach($activities as $activity)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3.5 text-sm font-medium text-gray-900">{{ $activity->description }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $activity->causer?->name ?? 'System' }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $activity->log_name }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $activity->created_at->format('d M Y H:i') }}</td>
                    <td class="px-5 py-3.5 text-right">
                        <a href="{{ route('admin.system.audit.show', $activity) }}" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100"><i class="fa-regular fa-eye"></i></a>
                    </td>
                </tr>
            @endforeach
        @endif

        <x-slot:pagination>
            <x-backend.pagination :paginator="$activities" />
        </x-slot:pagination>
    </x-backend.table>

@endsection
