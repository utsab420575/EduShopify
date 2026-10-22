<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-3 border-b border-gray-100">
        <div>
            <h3 class="text-sm font-semibold text-gray-900">Commit History</h3>
            <p class="text-xs text-gray-400 mt-0.5">Latest {{ count($commits) }} commits on GitHub</p>
        </div>

        @if(count($remoteBranches))
            <form method="GET" action="{{ route('admin.system.deploy.index') }}" class="flex items-center gap-2">
                <label for="history-branch" class="text-xs text-gray-500"><i class="fa-solid fa-code-branch"></i></label>
                <select id="history-branch" name="branch" onchange="this.form.submit()"
                    class="focus-accent px-3 py-1.5 border border-gray-300 rounded-lg text-sm bg-white">
                    @foreach($remoteBranches as $name)
                        <option value="{{ $name }}" @selected($name === $selectedBranch)>origin/{{ $name }}</option>
                    @endforeach
                </select>
            </form>
        @endif
    </div>

    @if(count($commits))
        {{-- Fixed-height scroll area; the header row stays pinned while the history scrolls. --}}
        <div class="max-h-[560px] overflow-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 sticky top-0 z-10 shadow-[0_1px_0_0_#e5e7eb]">
                    <tr class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">
                        <th class="px-4 py-2.5 w-10 text-center">#</th>
                        <th class="px-4 py-2.5">Commit Message</th>
                        <th class="px-4 py-2.5 whitespace-nowrap">Author</th>
                        <th class="px-4 py-2.5 whitespace-nowrap">Date</th>
                        <th class="px-4 py-2.5 whitespace-nowrap">Status</th>
                        <th class="px-4 py-2.5 whitespace-nowrap text-right">Hash</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($commits as $commit)
                        @php
                            $isIncoming = in_array($commit['hash'], $incomingHashes, true);
                            $isLive = $commit['hash'] === $deployedHash;
                        @endphp
                        <tr class="text-sm align-top hover:bg-gray-50 {{ $isLive ? 'bg-green-50/60' : ($isIncoming ? 'bg-amber-50/40' : '') }}">
                            <td class="px-4 py-3 text-xs text-gray-400 text-center tabular-nums">{{ $loop->iteration }}</td>
                            <td class="px-4 py-3 min-w-[220px]">
                                <p class="font-medium text-gray-900 break-words">{{ $commit['message'] }}</p>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center gap-2 text-gray-700">
                                    <span class="w-6 h-6 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center text-[10px] font-bold">
                                        {{ strtoupper(mb_substr($commit['author'], 0, 1)) }}
                                    </span>
                                    {{ $commit['author'] }}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-gray-500">
                                <span title="{{ $commit['date_iso'] }}">{{ \Illuminate\Support\Carbon::parse($commit['date_iso'])->format('d M Y, h:i A') }}</span>
                                <p class="text-xs text-gray-400">{{ $commit['date'] }}</p>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if($isLive)
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold uppercase px-2 py-0.5 rounded-full bg-green-100 text-green-700">
                                        <i class="fa-solid fa-circle text-[6px]"></i> Live
                                    </span>
                                @elseif($isIncoming)
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold uppercase px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">
                                        <i class="fa-solid fa-cloud-arrow-down"></i> Not pulled
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold uppercase px-2 py-0.5 rounded-full bg-gray-100 text-gray-500">
                                        <i class="fa-solid fa-check"></i> Deployed
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-right">
                                @if($githubRepoUrl)
                                    <a href="{{ $githubRepoUrl }}/commit/{{ $commit['hash'] }}" target="_blank" rel="noopener" title="Open commit on GitHub"
                                        class="text-xs font-mono px-2 py-1 rounded bg-gray-100 text-gray-500 hover:bg-gray-200">{{ $commit['short_hash'] }}</a>
                                @else
                                    <span class="text-xs font-mono px-2 py-1 rounded bg-gray-100 text-gray-500">{{ $commit['short_hash'] }}</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <x-backend.empty-state icon="fa-code-commit" title="No commits found"
            description="No remote branch could be read. Check that the server can reach GitHub and that the origin remote is configured." />
    @endif

    @if($githubRepoUrl && $selectedBranch && count($commits))
        <div class="px-5 py-3 border-t border-gray-100 text-center">
            <a href="{{ $githubRepoUrl }}/commits/{{ $selectedBranch }}" target="_blank" rel="noopener" class="text-xs font-medium text-gray-500 hover:text-gray-800">
                See full history on GitHub <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
            </a>
        </div>
    @endif
</div>
