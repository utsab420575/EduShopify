@php
    $incomingCount = count($incomingHashes);
    $changedCount = count($status['changed_files']);
@endphp

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <x-backend.stat-card label="Deployed Branch" :value="$status['branch'] ?? 'Unknown'" icon="fa-code-branch"
        :hint="$status['ahead'] || $status['behind'] ? $status['ahead'].' ahead · '.$status['behind'].' behind upstream' : 'In sync with upstream'" />

    <x-backend.stat-card label="Deployed Commit" :value="$status['last_commit']['short_hash'] ?? '—'" icon="fa-server"
        :hint="$status['last_commit']['date'] ?? null" />

    <x-backend.stat-card label="Incoming Commits" :value="$incomingCount" icon="fa-cloud-arrow-down"
        :tone="$incomingCount > 0 ? 'warning' : 'success'"
        :hint="$selectedBranch ? ($incomingCount > 0 ? 'Not yet pulled from origin/'.$selectedBranch : 'Up to date with origin/'.$selectedBranch) : 'No remote branch found'" />

    <x-backend.stat-card label="Working Tree" :value="$changedCount > 0 ? $changedCount.' changed' : 'Clean'" icon="fa-folder-tree"
        :tone="$changedCount > 0 ? 'warning' : 'success'"
        :hint="$changedCount > 0 ? 'Uncommitted changes on server' : 'No local modifications'" />
</div>
