@extends('backend.layouts.admin')

@section('title', 'GitHub Deploy')
@section('breadcrumb', 'System & Settings / GitHub Deploy')

@section('body')

    <x-backend.page-header title="GitHub Deploy" subtitle="Review the commit history on GitHub and pull a branch onto this server.">
        <x-slot:actions>
            <a href="{{ route('admin.system.deploy.index', ['branch' => $selectedBranch]) }}" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                <i class="fa-solid fa-rotate"></i> Refresh
            </a>
            @if($githubRepoUrl)
                <a href="{{ $githubRepoUrl }}" target="_blank" rel="noopener" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                    <i class="fa-brands fa-github"></i> View on GitHub
                </a>
            @endif
        </x-slot:actions>
    </x-backend.page-header>

    @unless($fetch['ok'])
        <div class="flex items-start gap-3 p-4 mb-6 bg-amber-50 border border-amber-200 rounded-xl text-sm text-amber-800">
            <i class="fa-solid fa-cloud-exclamation mt-0.5"></i>
            <div class="min-w-0">
                <p class="font-semibold">Could not reach GitHub — showing the last fetched history.</p>
                @if($fetch['error'])
                    <p class="text-xs font-mono mt-1 break-words">{{ $fetch['error'] }}</p>
                @endif
            </div>
        </div>
    @endunless

    @include('backend.admin.system.deploy.partials._pull-output')

    @include('backend.admin.system.deploy.partials._status-cards')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 min-w-0">
            @include('backend.admin.system.deploy.partials._commit-history')
        </div>

        <div class="space-y-6 min-w-0">
            @include('backend.admin.system.deploy.partials._pull-panel')
            @include('backend.admin.system.deploy.partials._branches')
        </div>
    </div>

@endsection
