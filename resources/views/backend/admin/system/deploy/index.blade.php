@extends('backend.layouts.admin')

@section('title', 'GitHub Deploy')
@section('breadcrumb', 'System & Settings / GitHub Deploy')

@section('body')

    <x-backend.page-header title="GitHub Deploy" subtitle="Pull the latest code from GitHub, review recent commits, and see all branches.">
        @if($githubRepoUrl)
            <x-slot:actions>
                <a href="{{ $githubRepoUrl }}" target="_blank" rel="noopener" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                    <i class="fa-brands fa-github"></i> View on GitHub
                </a>
            </x-slot:actions>
        @endif
    </x-backend.page-header>

    @if(session('pullOutput') || session('pullError'))
        <div class="bg-white rounded-xl border border-gray-200 p-5 mb-6">
            <h3 class="text-sm font-semibold text-gray-900 mb-2">Last Pull Output</h3>
            @if(session('pullOutput'))
                <pre class="text-xs bg-gray-50 border border-gray-200 rounded-lg p-3 overflow-x-auto whitespace-pre-wrap text-gray-700">{{ session('pullOutput') }}</pre>
            @endif
            @if(session('pullError'))
                <pre class="text-xs bg-red-50 border border-red-200 rounded-lg p-3 mt-2 overflow-x-auto whitespace-pre-wrap text-red-700">{{ session('pullError') }}</pre>
            @endif
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

        {{-- Repository Status --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">Repository Status</h3>
            <dl class="space-y-3 text-sm">
                <div class="flex items-center justify-between">
                    <dt class="text-gray-500">Current Branch</dt>
                    <dd>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700">
                            <i class="fa-solid fa-code-branch"></i> {{ $status['branch'] ?? 'Unknown' }}
                        </span>
                    </dd>
                </div>
                <div class="flex items-center justify-between">
                    <dt class="text-gray-500">Working Tree</dt>
                    <dd>
                        @if($status['is_dirty'])
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700">
                                <i class="fa-solid fa-circle-exclamation"></i> Has Changes
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-50 text-green-700">
                                <i class="fa-solid fa-circle-check"></i> Clean
                            </span>
                        @endif
                    </dd>
                </div>
                <div class="flex items-center justify-between">
                    <dt class="text-gray-500">Last Commit</dt>
                    <dd class="text-gray-700 font-mono text-xs">
                        {{ $status['last_commit']['date'] ?? '—' }}
                    </dd>
                </div>
            </dl>

            @if($status['is_dirty'])
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Uncommitted Changes</p>
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 max-h-32 overflow-y-auto">
                        @foreach($status['changed_files'] as $file)
                            <p class="text-xs font-mono text-gray-600">{{ $file }}</p>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- Git Pull --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">Git Pull</h3>

            <form method="POST" action="{{ route('admin.system.deploy.pull') }}"
                onsubmit="return confirmSwal(this, 'Pull from GitHub?', 'This will run git pull origin ' + this.branch.value + ' on the server. Make sure no critical work is running.', 'warning', 'Yes, Pull Now')">
                @csrf

                <label class="block text-xs font-medium text-gray-700 mb-1.5">Select Branch to Pull</label>
                <select name="branch" class="focus-accent w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm bg-white mb-3">
                    @foreach($branches as $branch)
                        <option value="{{ $branch['name'] }}" @selected($branch['is_current'])>{{ $branch['name'] }}</option>
                    @endforeach
                </select>

                @if($status['is_dirty'])
                    <div class="flex items-start gap-2 p-3 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-800 mb-3">
                        <i class="fa-solid fa-triangle-exclamation mt-0.5"></i>
                        <span>The working tree has uncommitted changes. A pull may fail or merge unexpectedly — review the changes above first.</span>
                    </div>
                @endif

                <button type="submit" class="btn-primary w-full text-sm font-semibold px-4 py-2.5 rounded-lg flex items-center justify-center gap-2">
                    <i class="fa-brands fa-github"></i> Pull from GitHub
                </button>
            </form>

            <div class="mt-3 bg-gray-900 rounded-lg px-3 py-2">
                <p class="text-xs font-mono text-green-400">$ git pull origin {{ $status['branch'] ?? '...' }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Recent Commits --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-900">Recent Commits</h3>
                <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-gray-100 text-gray-600">Last {{ count($commits) }}</span>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($commits as $commit)
                    <div class="flex items-start gap-3 px-5 py-3">
                        <div class="w-8 h-8 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs font-bold shrink-0">
                            {{ strtoupper(substr($commit['author'], 0, 1)) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $commit['message'] }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">
                                <i class="fa-regular fa-user text-[10px]"></i> {{ $commit['author'] }}
                                &nbsp;·&nbsp;
                                <i class="fa-regular fa-clock text-[10px]"></i> {{ $commit['date'] }}
                            </p>
                        </div>
                        @if($githubRepoUrl)
                            <a href="{{ $githubRepoUrl }}/commit/{{ $commit['hash'] }}" target="_blank" rel="noopener"
                                class="text-xs font-mono px-2 py-1 rounded bg-gray-100 text-gray-500 hover:bg-gray-200 shrink-0">{{ $commit['short_hash'] }}</a>
                        @else
                            <span class="text-xs font-mono px-2 py-1 rounded bg-gray-100 text-gray-500 shrink-0">{{ $commit['short_hash'] }}</span>
                        @endif
                    </div>
                @empty
                    <div class="px-5 py-8 text-center text-sm text-gray-400">No commits found.</div>
                @endforelse
            </div>
        </div>

        {{-- All Branches --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-900">All Branches</h3>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($branches as $branch)
                    <div class="flex items-center justify-between px-5 py-3">
                        <span class="flex items-center gap-2 text-sm text-gray-700">
                            <i class="fa-solid fa-code-branch text-gray-400 text-xs"></i>
                            {{ $branch['name'] }}
                        </span>
                        @if($branch['is_current'])
                            <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700">Current</span>
                        @endif
                    </div>
                @empty
                    <div class="px-5 py-8 text-center text-sm text-gray-400">No branches found.</div>
                @endforelse
            </div>
        </div>
    </div>

@endsection
