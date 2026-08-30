<?php

namespace App\Services;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * Every git call here is read-only except pull(). All calls use Symfony
 * Process with an argument array — never a shell string — so a branch name
 * (or anything else) can never be interpreted as extra shell syntax. pull()
 * additionally re-validates its $branch argument against the real branch
 * list before it's used, so only a name that genuinely exists on the remote
 * ever reaches the process.
 */
class GitDeploymentService
{
    private readonly string $repoPath;

    public function __construct(string $repoPath = '')
    {
        $this->repoPath = $repoPath !== '' ? $repoPath : base_path();
    }

    /**
     * Current branch, ahead/behind counts against its upstream, and the
     * list of files with uncommitted changes.
     */
    public function status(): array
    {
        $branch = trim($this->run(['git', 'rev-parse', '--abbrev-ref', 'HEAD'], 10, allowFailure: true) ?? '');

        $porcelain = $this->run(['git', 'status', '--porcelain'], 10, allowFailure: true) ?? '';
        $changedFiles = array_values(array_filter(array_map('rtrim', explode("\n", $porcelain))));

        $ahead = 0;
        $behind = 0;
        $counts = $this->run(['git', 'rev-list', '--left-right', '--count', 'HEAD...@{upstream}'], 10, allowFailure: true);
        if ($counts !== null && preg_match('/^(\d+)\s+(\d+)/', trim($counts), $m)) {
            $ahead = (int) $m[1];
            $behind = (int) $m[2];
        }

        $lastCommit = $this->run(['git', 'log', '-1', '--pretty=format:%H|%an|%ar|%s'], 10, allowFailure: true);

        return [
            'branch' => $branch ?: null,
            'is_dirty' => count($changedFiles) > 0,
            'changed_files' => $changedFiles,
            'ahead' => $ahead,
            'behind' => $behind,
            'last_commit' => $lastCommit ? $this->parseCommitLine($lastCommit) : null,
        ];
    }

    /**
     * @return list<array{hash: string, short_hash: string, author: string, date: string, message: string}>
     */
    public function recentCommits(int $limit = 10): array
    {
        $output = $this->run(['git', 'log', '-'.$limit, '--pretty=format:%H|%an|%ar|%s'], 10, allowFailure: true) ?? '';

        return array_values(array_filter(array_map(
            fn ($line) => $this->parseCommitLine($line),
            explode("\n", $output)
        )));
    }

    /**
     * @return list<array{name: string, is_remote: bool, is_current: bool}>
     */
    public function branches(): array
    {
        $output = $this->run(['git', 'branch', '-a'], 10, allowFailure: true) ?? '';

        $branches = [];
        foreach (explode("\n", $output) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $isCurrent = str_starts_with($line, '*');
            $line = ltrim($line, '* ');

            // Skip the remote's own HEAD pointer line (e.g. "remotes/origin/HEAD -> origin/main").
            if (str_contains($line, ' -> ')) {
                continue;
            }

            $isRemote = str_starts_with($line, 'remotes/');
            $name = $isRemote ? preg_replace('#^remotes/[^/]+/#', '', $line) : $line;

            if ($name === null || $name === '') {
                continue;
            }

            // A branch present both locally and on the remote appears as two
            // separate lines (one already merged in from an earlier
            // iteration) — merge rather than overwrite, so a remote line
            // processed after the local "* branch" line can never reset
            // is_current back to false.
            $existing = $branches[$name] ?? ['name' => $name, 'is_remote' => false, 'is_current' => false];
            $branches[$name] = [
                'name' => $name,
                'is_remote' => $existing['is_remote'] || $isRemote,
                'is_current' => $existing['is_current'] || $isCurrent,
            ];
        }

        return array_values($branches);
    }

    /**
     * @return list<string> just the branch names, for validating a pull request against.
     */
    public function branchNames(): array
    {
        return array_column($this->branches(), 'name');
    }

    public function remoteUrl(): ?string
    {
        $url = $this->run(['git', 'remote', 'get-url', 'origin'], 10, allowFailure: true);

        return $url ? trim($url) : null;
    }

    /**
     * The origin remote parsed into a browsable https://github.com/owner/repo
     * URL, supporting both SSH (git@github.com:owner/repo.git) and HTTPS
     * (https://github.com/owner/repo.git) remote forms. Null if origin isn't
     * a GitHub URL at all (e.g. a self-hosted git server).
     */
    public function githubRepoUrl(): ?string
    {
        $remote = $this->remoteUrl();
        if (! $remote) {
            return null;
        }

        if (preg_match('#github\.com[:/]([^/]+)/(.+?)(\.git)?$#', $remote, $m)) {
            return "https://github.com/{$m[1]}/{$m[2]}";
        }

        return null;
    }

    /**
     * Pulls the given branch from origin. $branch is re-checked against the
     * real branch list here — the one and only place user input is allowed
     * anywhere near this method — so a request that slips past controller
     * validation still can't reach the process with an unknown value.
     */
    public function pull(string $branch): array
    {
        if (! in_array($branch, $this->branchNames(), true)) {
            return ['ok' => false, 'output' => '', 'error' => "Unknown branch: {$branch}"];
        }

        $process = new Process(['git', 'pull', 'origin', $branch], $this->repoPath);
        $process->setTimeout(60);
        $process->run();

        return [
            'ok' => $process->isSuccessful(),
            'output' => $process->getOutput(),
            'error' => $process->getErrorOutput(),
        ];
    }

    private function parseCommitLine(string $line): ?array
    {
        $parts = explode('|', $line, 4);
        if (count($parts) < 4) {
            return null;
        }

        [$hash, $author, $date, $message] = $parts;

        return [
            'hash' => $hash,
            'short_hash' => substr($hash, 0, 7),
            'author' => $author,
            'date' => $date,
            'message' => $message,
        ];
    }

    private function run(array $command, int $timeout, bool $allowFailure = false): ?string
    {
        $process = new Process($command, $this->repoPath);
        $process->setTimeout($timeout);

        try {
            $process->run();
        } catch (\Throwable) {
            return $allowFailure ? null : '';
        }

        if (! $process->isSuccessful()) {
            if ($allowFailure) {
                return null;
            }

            throw new ProcessFailedException($process);
        }

        return $process->getOutput();
    }
}
