<div class="bg-white rounded-xl border border-gray-200 p-5">
    <h3 class="text-sm font-semibold text-gray-900 mb-1">Pull from GitHub</h3>
    <p class="text-xs text-gray-400 mb-4">
        Merges the chosen branch into <span class="font-mono text-gray-600">{{ $status['branch'] ?? 'HEAD' }}</span> on this server.
    </p>

    <form method="POST" action="{{ route('admin.system.deploy.pull') }}"
        onsubmit="return confirmSwal(this, 'Pull from GitHub?', 'This will run git pull origin ' + this.branch.value + ' on the server. Make sure no critical work is running.', 'warning', 'Yes, Pull Now')">
        @csrf

        <label for="pull-branch" class="block text-xs font-medium text-gray-700 mb-1.5">Branch</label>
        <select id="pull-branch" name="branch" class="focus-accent w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm bg-white mb-3"
            onchange="document.getElementById('pull-command').textContent = '$ git pull origin ' + this.value;
                      document.getElementById('pull-mismatch').classList.toggle('hidden', this.value === @js($status['branch']));">
            @foreach($remoteBranches as $name)
                <option value="{{ $name }}" @selected($name === $selectedBranch)>
                    {{ $name }}{{ $name === $defaultBranch ? ' (default)' : '' }}{{ $name === $status['branch'] ? ' — checked out' : '' }}
                </option>
            @endforeach
        </select>

        <div class="bg-gray-900 rounded-lg px-3 py-2 mb-3">
            <p id="pull-command" class="text-xs font-mono text-green-400">$ git pull origin {{ $selectedBranch ?? '...' }}</p>
        </div>

        <div id="pull-mismatch" class="{{ $selectedBranch === $status['branch'] ? 'hidden' : '' }} flex items-start gap-2 p-3 bg-blue-50 border border-blue-200 rounded-lg text-xs text-blue-800 mb-3">
            <i class="fa-solid fa-circle-info mt-0.5"></i>
            <span>The server has <strong>{{ $status['branch'] ?? 'another branch' }}</strong> checked out — this branch will be merged into it, not switched to.</span>
        </div>

        @if($status['is_dirty'])
            <div class="flex items-start gap-2 p-3 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-800 mb-3">
                <i class="fa-solid fa-triangle-exclamation mt-0.5"></i>
                <span>The working tree has uncommitted changes. A pull may fail or merge unexpectedly — review them below first.</span>
            </div>
        @endif

        <button type="submit" @disabled(! count($remoteBranches))
            class="btn-primary w-full text-sm font-semibold px-4 py-2.5 rounded-lg flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
            <i class="fa-brands fa-github"></i>
            @if(count($incomingHashes))
                Pull {{ count($incomingHashes) }} {{ Str::plural('commit', count($incomingHashes)) }}
            @else
                Pull from GitHub
            @endif
        </button>
    </form>

    @if($status['is_dirty'])
        <div class="mt-4 pt-4 border-t border-gray-100">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Uncommitted Changes</p>
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 max-h-40 overflow-y-auto">
                @foreach($status['changed_files'] as $file)
                    <p class="text-xs font-mono text-gray-600 break-all">{{ $file }}</p>
                @endforeach
            </div>
        </div>
    @endif
</div>
