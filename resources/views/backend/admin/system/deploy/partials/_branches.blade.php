<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100">
        <h3 class="text-sm font-semibold text-gray-900">Branches on GitHub</h3>
        <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-gray-100 text-gray-600">{{ count($remoteBranches) }}</span>
    </div>
    <div class="divide-y divide-gray-100 max-h-80 overflow-y-auto">
        @forelse($remoteBranches as $name)
            <a href="{{ route('admin.system.deploy.index', ['branch' => $name]) }}"
                class="flex items-center justify-between gap-2 px-5 py-2.5 hover:bg-gray-50 {{ $name === $selectedBranch ? 'bg-gray-50' : '' }}">
                <span class="flex items-center gap-2 text-sm min-w-0 {{ $name === $selectedBranch ? 'font-semibold text-gray-900' : 'text-gray-700' }}">
                    <i class="fa-solid fa-code-branch text-gray-400 text-xs"></i>
                    <span class="truncate">{{ $name }}</span>
                </span>
                <span class="flex items-center gap-1 shrink-0">
                    @if($name === $defaultBranch)
                        <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700">Default</span>
                    @endif
                    @if($name === $status['branch'])
                        <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-full bg-green-100 text-green-700">Live</span>
                    @endif
                </span>
            </a>
        @empty
            <div class="px-5 py-8 text-center text-sm text-gray-400">No remote branches found.</div>
        @endforelse
    </div>
</div>
