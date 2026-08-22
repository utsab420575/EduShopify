@extends('backend.layouts.admin')

@section('title', 'Failed Jobs')
@section('breadcrumb', 'System & Settings / Failed Jobs')

@section('body')

    <x-backend.page-header title="Failed Jobs" subtitle="Queued jobs that failed and need attention." />

    <x-backend.table>
        @if($jobs->isEmpty())
            <x-slot:empty><x-backend.empty-state icon="fa-triangle-exclamation" title="No failed jobs" description="All queued jobs are processing normally." /></x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Queue</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Exception</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Failed At</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </x-slot:head>
            @foreach($jobs as $job)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3.5 text-sm font-medium text-gray-900">{{ $job->queue }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600 max-w-md truncate" title="{{ $job->exception }}">{{ \Illuminate\Support\Str::limit($job->exception, 120) }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ \Illuminate\Support\Carbon::parse($job->failed_at)->format('d M Y H:i') }}</td>
                    <td class="px-5 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            <form method="POST" action="{{ route('admin.system.jobs.retry', $job->uuid) }}">
                                @csrf
                                <button type="submit" title="Retry" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100"><i class="fa-solid fa-rotate-right"></i></button>
                            </form>
                            <form method="POST" action="{{ route('admin.system.jobs.destroy', $job->uuid) }}" onsubmit="return confirm('Discard this failed job permanently?');">
                                @csrf @method('DELETE')
                                <button type="submit" title="Discard" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-red-500 hover:bg-red-50"><i class="fa-regular fa-trash-can"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
        @endif

        <x-slot:pagination>
            <x-backend.pagination :paginator="$jobs" />
        </x-slot:pagination>
    </x-backend.table>

@endsection
